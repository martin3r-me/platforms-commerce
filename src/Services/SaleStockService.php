<?php

namespace Platform\Commerce\Services;

use Illuminate\Support\Facades\DB;
use Platform\Commerce\Enums\SaleStatus;
use Platform\Commerce\Models\CommerceSale;
use Platform\Commerce\Models\CommerceSaleItem;
use Platform\Commerce\Models\CommerceStockReservation;
use Platform\Commerce\Models\CommerceWarehouse;

/**
 * Koppelt den Verkauf an die Bestandsfuehrung.
 *
 * Ein Statuswechsel ist eine BEWUSSTE Beleg-Aktion (confirm/complete/cancel/
 * refund), kein Nebeneffekt eines gespeicherten Feldes. Jede Aktion setzt den
 * Status UND bucht den Bestand in EINER Transaktion -- schlaegt die Buchung fehl
 * (z.B. zu wenig Bestand), bleibt auch der Status unveraendert.
 *
 * Statusmodell -> Buchungszustand des Bestands:
 *   Draft/Pending/Cancelled/Refunded -> NICHTS gebucht
 *   Confirmed                        -> Bestand RESERVIERT
 *   Completed                        -> Bestand ABGEBUCHT (Outbound)
 *
 * Der Uebergang wird als "alten Buchungszustand rueckgaengig machen, dann neuen
 * anwenden" umgesetzt (undo/apply). Damit sind alle Uebergaenge korrekt, ohne
 * jede Kombination einzeln zu behandeln:
 *   Draft->Confirmed      = reservieren
 *   Confirmed->Completed  = Reservierung aufloesen + abbuchen
 *   Draft->Completed      = direkt abbuchen
 *   Confirmed->Cancelled  = Reservierung freigeben
 *   Completed->Refunded   = Ware zurueck ins Lager
 *
 * Jede Buchung haengt die Verkaufsposition als polymorphe Referenz an
 * (reference_type/reference_id) -> daraus entsteht die Rueckverfolgung.
 */
class SaleStockService
{
    private const NONE = 0;
    private const RESERVED = 1;
    private const REMOVED = 2;

    public function __construct(private readonly InventoryManager $inventory)
    {
    }

    /** Verkauf bestaetigen: reserviert den Bestand. */
    public function confirm(CommerceSale $sale): CommerceSale
    {
        return $this->transitionTo($sale, SaleStatus::Confirmed);
    }

    /** Verkauf abschliessen: bucht den Bestand ab (Outbound). */
    public function complete(CommerceSale $sale): CommerceSale
    {
        return $this->transitionTo($sale, SaleStatus::Completed);
    }

    /** Verkauf stornieren: gibt reservierten Bestand frei (bzw. bucht zurueck). */
    public function cancel(CommerceSale $sale): CommerceSale
    {
        return $this->transitionTo($sale, SaleStatus::Cancelled);
    }

    /** Verkauf erstatten: bucht bereits abgebuchte Ware zurueck ins Lager. */
    public function refund(CommerceSale $sale): CommerceSale
    {
        return $this->transitionTo($sale, SaleStatus::Refunded);
    }

    /**
     * Fuehrt den Statuswechsel durch: bucht den Bestand um und persistiert den
     * neuen Status in EINER Transaktion. Wirft (und laesst den Status unangetastet),
     * wenn nicht genuegend Bestand fuer eine Reservierung/Abbuchung vorhanden ist.
     */
    private function transitionTo(CommerceSale $sale, SaleStatus $target): CommerceSale
    {
        $from = $this->currentStatus($sale);
        if ($from === $target) {
            return $sale;
        }

        return DB::transaction(function () use ($sale, $from, $target) {
            $this->bookTransition($sale, $from, $target);
            $sale->status = $target;
            $sale->save();

            return $sale;
        });
    }

    /**
     * Bucht den Bestand fuer den Uebergang von $from nach $to um (undo/apply).
     */
    private function bookTransition(CommerceSale $sale, ?SaleStatus $from, ?SaleStatus $to): void
    {
        $fromLevel = $this->bookingLevel($from);
        $toLevel = $this->bookingLevel($to);

        if ($fromLevel === $toLevel) {
            return;
        }

        $lines = $this->resolveLines($sale);
        if ($lines === []) {
            return;
        }

        $this->undo($fromLevel, $lines);
        $this->apply($toLevel, $lines);
    }

    private function currentStatus(CommerceSale $sale): ?SaleStatus
    {
        $status = $sale->status;
        if ($status instanceof SaleStatus) {
            return $status;
        }

        return ($status !== null && $status !== '') ? SaleStatus::tryFrom((string) $status) : null;
    }

    /**
     * Macht den bisherigen Buchungszustand rueckgaengig.
     *
     * @param array<int, array<string, mixed>> $lines
     */
    private function undo(int $level, array $lines): void
    {
        if ($level === self::RESERVED) {
            foreach ($lines as $line) {
                $this->releaseReservations($line['item']);
            }

            return;
        }

        if ($level === self::REMOVED) {
            foreach ($lines as $line) {
                $this->inventory->addStock(
                    $line['article_id'],
                    $line['warehouse_id'],
                    $line['quantity'],
                    $line['team_id'],
                    $line['user_id'],
                    'Sale reversal',
                    $line['reference_type'],
                    $line['reference_id'],
                );
            }
        }
    }

    /**
     * Wendet den neuen Buchungszustand an.
     *
     * @param array<int, array<string, mixed>> $lines
     */
    private function apply(int $level, array $lines): void
    {
        if ($level === self::RESERVED) {
            foreach ($lines as $line) {
                // Idempotent: nicht doppelt reservieren (z.B. Confirmed erneut gespeichert).
                if ($this->hasReservation($line['item'])) {
                    continue;
                }

                $this->inventory->reserveStock(
                    $line['article_id'],
                    $line['warehouse_id'],
                    $line['quantity'],
                    $line['team_id'],
                    $line['user_id'],
                    $line['reference_type'],
                    $line['reference_id'],
                );
            }

            return;
        }

        if ($level === self::REMOVED) {
            foreach ($lines as $line) {
                $this->inventory->removeStock(
                    $line['article_id'],
                    $line['warehouse_id'],
                    $line['quantity'],
                    $line['team_id'],
                    $line['user_id'],
                    'Sale fulfillment',
                    $line['reference_type'],
                    $line['reference_id'],
                );
            }
        }
    }

    /**
     * Loest Verkaufspositionen in bestandsfaehige Zeilen auf (Artikel + Lager).
     * Positionen ohne bestandsgefuehrten Artikel (z.B. reine Dienstleistung)
     * werden uebersprungen.
     *
     * @return array<int, array<string, mixed>>
     */
    private function resolveLines(CommerceSale $sale): array
    {
        $sale->loadMissing('items.product', 'items.batch');

        $lines = [];
        $defaultWarehouseId = null;

        foreach ($sale->items as $item) {
            $quantity = (float) $item->quantity;
            if ($quantity <= 0) {
                continue;
            }

            // Artikel: bevorzugt aus der Charge, sonst aus dem Produkt.
            $articleId = $item->batch?->commerce_article_id
                ?? $item->product?->commerce_article_id;

            if (! $articleId) {
                continue; // nicht bestandsgefuehrt -> ignorieren
            }

            // Lager: Charge-Lager, sonst Default-Lager des Teams.
            $warehouseId = $item->batch?->commerce_warehouse_id;
            if (! $warehouseId) {
                $defaultWarehouseId ??= $this->defaultWarehouseId((int) $sale->team_id);
                $warehouseId = $defaultWarehouseId;
            }

            if (! $warehouseId) {
                throw new \RuntimeException(
                    "Kein Lager fuer Verkaufsposition {$item->id} bestimmbar "
                    ."(kein Charge-Lager und kein Default-Warehouse fuer Team {$sale->team_id})."
                );
            }

            $lines[] = [
                'item' => $item,
                'article_id' => (int) $articleId,
                'warehouse_id' => (int) $warehouseId,
                'quantity' => $quantity,
                'team_id' => (int) $sale->team_id,
                'user_id' => $sale->user_id !== null ? (int) $sale->user_id : null,
                'reference_type' => $item->getMorphClass(),
                'reference_id' => (int) $item->id,
            ];
        }

        return $lines;
    }

    private function defaultWarehouseId(int $teamId): ?int
    {
        $warehouse = CommerceWarehouse::where('team_id', $teamId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        return $warehouse?->id;
    }

    private function hasReservation(CommerceSaleItem $item): bool
    {
        return CommerceStockReservation::query()
            ->where('reference_type', $item->getMorphClass())
            ->where('reference_id', $item->id)
            ->exists();
    }

    private function releaseReservations(CommerceSaleItem $item): void
    {
        $reservations = CommerceStockReservation::query()
            ->where('reference_type', $item->getMorphClass())
            ->where('reference_id', $item->id)
            ->get();

        foreach ($reservations as $reservation) {
            $this->inventory->releaseReservation((int) $reservation->id);
        }
    }

    private function bookingLevel(?SaleStatus $status): int
    {
        return match ($status) {
            SaleStatus::Confirmed => self::RESERVED,
            SaleStatus::Completed => self::REMOVED,
            default => self::NONE, // Draft, Pending, Cancelled, Refunded, null
        };
    }
}
