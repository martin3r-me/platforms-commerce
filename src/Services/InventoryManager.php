<?php

namespace Platform\Commerce\Services;

use Illuminate\Support\Facades\DB;
use Platform\Commerce\Models\CommerceStockLevel;
use Platform\Commerce\Models\CommerceStockMovement;
use Platform\Commerce\Models\CommerceStockReservation;
use Platform\Commerce\Enums\StockMovementType;

class InventoryManager
{
    public function addStock(
        int $articleId,
        int $warehouseId,
        float $quantity,
        int $teamId,
        ?int $userId = null,
        ?string $reason = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): CommerceStockLevel {
        return DB::transaction(function () use ($articleId, $warehouseId, $quantity, $teamId, $userId, $reason, $referenceType, $referenceId) {
            $stockLevel = CommerceStockLevel::firstOrCreate(
                ['commerce_article_id' => $articleId, 'commerce_warehouse_id' => $warehouseId],
                ['team_id' => $teamId, 'quantity' => 0, 'reserved_quantity' => 0]
            );

            $stockLevel->increment('quantity', $quantity);

            CommerceStockMovement::create([
                'team_id' => $teamId,
                'user_id' => $userId,
                'commerce_article_id' => $articleId,
                'commerce_warehouse_id' => $warehouseId,
                'type' => StockMovementType::Inbound->value,
                'quantity' => $quantity,
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            return $stockLevel->fresh();
        });
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
    ): CommerceStockLevel {
        return DB::transaction(function () use ($articleId, $warehouseId, $quantity, $teamId, $userId, $reason, $referenceType, $referenceId) {
            $stockLevel = CommerceStockLevel::where('commerce_article_id', $articleId)
                ->where('commerce_warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->firstOrFail();

            $available = (float)$stockLevel->quantity - (float)$stockLevel->reserved_quantity;
            if ($quantity > $available) {
                throw new \RuntimeException("Insufficient available stock. Available: {$available}, Requested: {$quantity}");
            }

            $stockLevel->decrement('quantity', $quantity);

            CommerceStockMovement::create([
                'team_id' => $teamId,
                'user_id' => $userId,
                'commerce_article_id' => $articleId,
                'commerce_warehouse_id' => $warehouseId,
                'type' => StockMovementType::Outbound->value,
                'quantity' => $quantity,
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            return $stockLevel->fresh();
        });
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
        return DB::transaction(function () use ($articleId, $fromWarehouseId, $toWarehouseId, $quantity, $teamId, $userId, $reason) {
            $fromLevel = CommerceStockLevel::where('commerce_article_id', $articleId)
                ->where('commerce_warehouse_id', $fromWarehouseId)
                ->lockForUpdate()
                ->firstOrFail();

            $available = (float)$fromLevel->quantity - (float)$fromLevel->reserved_quantity;
            if ($quantity > $available) {
                throw new \RuntimeException("Insufficient available stock for transfer. Available: {$available}, Requested: {$quantity}");
            }

            $fromLevel->decrement('quantity', $quantity);

            $toLevel = CommerceStockLevel::firstOrCreate(
                ['commerce_article_id' => $articleId, 'commerce_warehouse_id' => $toWarehouseId],
                ['team_id' => $teamId, 'quantity' => 0, 'reserved_quantity' => 0]
            );
            $toLevel->increment('quantity', $quantity);

            CommerceStockMovement::create([
                'team_id' => $teamId,
                'user_id' => $userId,
                'commerce_article_id' => $articleId,
                'commerce_warehouse_id' => $fromWarehouseId,
                'target_warehouse_id' => $toWarehouseId,
                'type' => StockMovementType::Transfer->value,
                'quantity' => $quantity,
                'reason' => $reason,
            ]);

            return [
                'from' => $fromLevel->fresh(),
                'to' => $toLevel->fresh(),
            ];
        });
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
    ): CommerceStockReservation {
        return DB::transaction(function () use ($articleId, $warehouseId, $quantity, $teamId, $userId, $referenceType, $referenceId, $expiresAt) {
            $stockLevel = CommerceStockLevel::where('commerce_article_id', $articleId)
                ->where('commerce_warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->firstOrFail();

            $available = (float)$stockLevel->quantity - (float)$stockLevel->reserved_quantity;
            if ($quantity > $available) {
                throw new \RuntimeException("Insufficient available stock for reservation. Available: {$available}, Requested: {$quantity}");
            }

            $stockLevel->increment('reserved_quantity', $quantity);

            $reservation = CommerceStockReservation::create([
                'team_id' => $teamId,
                'user_id' => $userId,
                'commerce_article_id' => $articleId,
                'commerce_warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'expires_at' => $expiresAt,
            ]);

            CommerceStockMovement::create([
                'team_id' => $teamId,
                'user_id' => $userId,
                'commerce_article_id' => $articleId,
                'commerce_warehouse_id' => $warehouseId,
                'type' => StockMovementType::Reservation->value,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            return $reservation;
        });
    }

    public function releaseReservation(int $reservationId): void
    {
        DB::transaction(function () use ($reservationId) {
            $reservation = CommerceStockReservation::lockForUpdate()->findOrFail($reservationId);

            $stockLevel = CommerceStockLevel::where('commerce_article_id', $reservation->commerce_article_id)
                ->where('commerce_warehouse_id', $reservation->commerce_warehouse_id)
                ->lockForUpdate()
                ->firstOrFail();

            $stockLevel->decrement('reserved_quantity', (float)$reservation->quantity);

            CommerceStockMovement::create([
                'team_id' => $reservation->team_id,
                'user_id' => $reservation->user_id,
                'commerce_article_id' => $reservation->commerce_article_id,
                'commerce_warehouse_id' => $reservation->commerce_warehouse_id,
                'type' => StockMovementType::ReservationRelease->value,
                'quantity' => (float)$reservation->quantity,
                'reference_type' => $reservation->reference_type,
                'reference_id' => $reservation->reference_id,
            ]);

            $reservation->delete();
        });
    }

    public function getAvailableStock(int $articleId, int $teamId, ?int $warehouseId = null): float
    {
        $query = CommerceStockLevel::where('commerce_article_id', $articleId)
            ->where('team_id', $teamId);

        if ($warehouseId) {
            $query->where('commerce_warehouse_id', $warehouseId);
        }

        $levels = $query->get();
        $total = 0;
        foreach ($levels as $level) {
            $total += (float)$level->quantity - (float)$level->reserved_quantity;
        }

        return $total;
    }
}
