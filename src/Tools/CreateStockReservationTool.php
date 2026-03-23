<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Services\InventoryManager;

/**
 * Erstellt eine neue Bestandsreservierung.
 */
class CreateStockReservationTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.stock_reservations.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/stock_reservations - Erstellt eine Bestandsreservierung über den InventoryManager.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Team-ID. Default: Team aus Kontext.',
                ],
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'Artikel-ID (ERFORDERLICH).',
                ],
                'commerce_warehouse_id' => [
                    'type' => 'integer',
                    'description' => 'Lager-ID (ERFORDERLICH).',
                ],
                'quantity' => [
                    'type' => 'number',
                    'description' => 'Zu reservierende Menge (ERFORDERLICH).',
                ],
                'reference_type' => [
                    'type' => 'string',
                    'description' => 'Optional: Referenz-Typ (z.B. order, cart).',
                ],
                'reference_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Referenz-ID.',
                ],
                'expires_at' => [
                    'type' => 'string',
                    'description' => 'Optional: Ablaufdatum der Reservierung (ISO 8601).',
                ],
            ],
            'required' => ['commerce_article_id', 'commerce_warehouse_id', 'quantity'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $teamId = $arguments['team_id'] ?? $context->team?->id;
            if ($teamId === 0 || $teamId === '0') {
                $teamId = null;
            }
            if (!$teamId) {
                return ToolResult::error('MISSING_TEAM', 'Kein Team angegeben und kein Team im Kontext gefunden.');
            }

            $team = Team::find((int)$teamId);
            if (!$team) {
                return ToolResult::error('TEAM_NOT_FOUND', 'Team nicht gefunden.');
            }

            if (!$context->user) {
                return ToolResult::error('AUTH_ERROR', 'Kein User im Kontext gefunden.');
            }
            $userHasAccess = $context->user->teams()->where('teams.id', $team->id)->exists();
            if (!$userHasAccess) {
                return ToolResult::error('ACCESS_DENIED', 'Du hast keinen Zugriff auf dieses Team.');
            }

            $articleId = (int)$arguments['commerce_article_id'];
            $warehouseId = (int)$arguments['commerce_warehouse_id'];
            $quantity = (float)$arguments['quantity'];

            if ($quantity <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'quantity muss größer als 0 sein.');
            }

            $expiresAt = null;
            if (!empty($arguments['expires_at'])) {
                $expiresAt = new \DateTimeImmutable((string)$arguments['expires_at']);
            }

            $manager = new InventoryManager();

            $reservation = $manager->reserveStock(
                $articleId,
                $warehouseId,
                $quantity,
                $team->id,
                $context->user->id,
                $arguments['reference_type'] ?? null,
                isset($arguments['reference_id']) ? (int)$arguments['reference_id'] : null,
                $expiresAt,
            );

            return ToolResult::success([
                'id' => $reservation->id,
                'team_id' => $reservation->team_id,
                'user_id' => $reservation->user_id,
                'commerce_article_id' => $reservation->commerce_article_id,
                'commerce_warehouse_id' => $reservation->commerce_warehouse_id,
                'quantity' => (float)$reservation->quantity,
                'reference_type' => $reservation->reference_type,
                'reference_id' => $reservation->reference_id,
                'expires_at' => $reservation->expires_at?->toIso8601String(),
                'message' => 'Reservierung erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'stock_reservations', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
