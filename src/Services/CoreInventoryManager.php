<?php

namespace Platform\Commerce\Services;

use Platform\Core\Contracts\InventoryManagerInterface;
use Platform\Commerce\Models\CommerceStockLevel;
use Platform\Commerce\Models\CommerceStockReservation;

/**
 * Core-Adapter fuer die Bestandsfuehrung.
 *
 * Implementiert das Core-Contract InventoryManagerInterface und delegiert an
 * den internen InventoryManager. Der einzige Zweck ist die Entkopplung:
 * andere Module sehen nur primitive Arrays/Floats, nicht die Commerce-Models.
 *
 * Der interne InventoryManager bleibt unveraendert und wird weiterhin direkt
 * von den Commerce-Tools/-Livewire-Komponenten genutzt (die Models erwarten).
 */
class CoreInventoryManager implements InventoryManagerInterface
{
    public function __construct(private readonly InventoryManager $inventory)
    {
    }

    public function addStock(
        int $articleId,
        int $warehouseId,
        float $quantity,
        int $teamId,
        ?int $userId = null,
        ?string $reason = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): array {
        return $this->levelToArray(
            $this->inventory->addStock($articleId, $warehouseId, $quantity, $teamId, $userId, $reason, $referenceType, $referenceId)
        );
    }

    public function removeStock(
        int $articleId,
        int $warehouseId,
        float $quantity,
        int $teamId,
        ?int $userId = null,
        ?string $reason = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): array {
        return $this->levelToArray(
            $this->inventory->removeStock($articleId, $warehouseId, $quantity, $teamId, $userId, $reason, $referenceType, $referenceId)
        );
    }

    public function transferStock(
        int $articleId,
        int $fromWarehouseId,
        int $toWarehouseId,
        float $quantity,
        int $teamId,
        ?int $userId = null,
        ?string $reason = null,
    ): array {
        $result = $this->inventory->transferStock($articleId, $fromWarehouseId, $toWarehouseId, $quantity, $teamId, $userId, $reason);

        return [
            'from' => $this->levelToArray($result['from']),
            'to' => $this->levelToArray($result['to']),
        ];
    }

    public function reserveStock(
        int $articleId,
        int $warehouseId,
        float $quantity,
        int $teamId,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?\DateTimeInterface $expiresAt = null,
    ): array {
        return $this->reservationToArray(
            $this->inventory->reserveStock($articleId, $warehouseId, $quantity, $teamId, $userId, $referenceType, $referenceId, $expiresAt)
        );
    }

    public function releaseReservation(int $reservationId): void
    {
        $this->inventory->releaseReservation($reservationId);
    }

    public function getAvailableStock(int $articleId, int $teamId, ?int $warehouseId = null): float
    {
        return $this->inventory->getAvailableStock($articleId, $teamId, $warehouseId);
    }

    /**
     * @return array{article_id: int, warehouse_id: int, quantity: float, reserved_quantity: float, available: float}
     */
    private function levelToArray(CommerceStockLevel $level): array
    {
        $quantity = (float) $level->quantity;
        $reserved = (float) $level->reserved_quantity;

        return [
            'article_id' => (int) $level->commerce_article_id,
            'warehouse_id' => (int) $level->commerce_warehouse_id,
            'quantity' => $quantity,
            'reserved_quantity' => $reserved,
            'available' => $quantity - $reserved,
        ];
    }

    /**
     * @return array{reservation_id: int, article_id: int, warehouse_id: int, quantity: float, expires_at: ?string}
     */
    private function reservationToArray(CommerceStockReservation $reservation): array
    {
        return [
            'reservation_id' => (int) $reservation->id,
            'article_id' => (int) $reservation->commerce_article_id,
            'warehouse_id' => (int) $reservation->commerce_warehouse_id,
            'quantity' => (float) $reservation->quantity,
            'expires_at' => $reservation->expires_at ? (string) $reservation->expires_at : null,
        ];
    }
}
