<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Services\InventoryManager;

/**
 * Transferiert Bestand zwischen Lagern.
 */
class TransferStockTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.stock.transfer';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/stock/transfer - Transferiert Bestand zwischen Lagern.';
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
                'from_warehouse_id' => [
                    'type' => 'integer',
                    'description' => 'Quell-Lager-ID (ERFORDERLICH).',
                ],
                'to_warehouse_id' => [
                    'type' => 'integer',
                    'description' => 'Ziel-Lager-ID (ERFORDERLICH).',
                ],
                'quantity' => [
                    'type' => 'number',
                    'description' => 'Zu transferierende Menge (ERFORDERLICH).',
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Optional: Grund des Transfers.',
                ],
            ],
            'required' => ['commerce_article_id', 'from_warehouse_id', 'to_warehouse_id', 'quantity'],
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
            $fromWarehouseId = (int)$arguments['from_warehouse_id'];
            $toWarehouseId = (int)$arguments['to_warehouse_id'];
            $quantity = (float)$arguments['quantity'];

            if ($quantity <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'quantity muss größer als 0 sein.');
            }

            if ($fromWarehouseId === $toWarehouseId) {
                return ToolResult::error('VALIDATION_ERROR', 'Quell- und Ziel-Lager dürfen nicht identisch sein.');
            }

            $manager = new InventoryManager();

            $result = $manager->transferStock(
                $articleId,
                $fromWarehouseId,
                $toWarehouseId,
                $quantity,
                $team->id,
                $context->user->id,
                $arguments['reason'] ?? null,
            );

            $fromLevel = $result['from'];
            $toLevel = $result['to'];

            return ToolResult::success([
                'message' => 'Transfer erfolgreich durchgeführt.',
                'from_stock_level' => [
                    'id' => $fromLevel->id,
                    'commerce_warehouse_id' => $fromLevel->commerce_warehouse_id,
                    'quantity' => (float)$fromLevel->quantity,
                    'reserved_quantity' => (float)$fromLevel->reserved_quantity,
                    'available_quantity' => (float)$fromLevel->quantity - (float)$fromLevel->reserved_quantity,
                ],
                'to_stock_level' => [
                    'id' => $toLevel->id,
                    'commerce_warehouse_id' => $toLevel->commerce_warehouse_id,
                    'quantity' => (float)$toLevel->quantity,
                    'reserved_quantity' => (float)$toLevel->reserved_quantity,
                    'available_quantity' => (float)$toLevel->quantity - (float)$toLevel->reserved_quantity,
                ],
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'stock', 'transfer'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
