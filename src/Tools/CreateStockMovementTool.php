<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceStockMovement;
use Platform\Commerce\Services\InventoryManager;

/**
 * Erstellt eine neue Lagerbewegung.
 */
class CreateStockMovementTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.stock_movements.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/stock_movements - Erstellt eine Lagerbewegung (inbound/outbound/adjustment). Nutze commerce.warehouses.GET + commerce.articles.GET für IDs. Für inbound/outbound wird der InventoryManager verwendet.';
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
                'type' => [
                    'type' => 'string',
                    'description' => 'Bewegungstyp (ERFORDERLICH): inbound, outbound, adjustment.',
                    'enum' => ['inbound', 'outbound', 'adjustment'],
                ],
                'quantity' => [
                    'type' => 'number',
                    'description' => 'Menge (ERFORDERLICH). Bei adjustment kann negativ sein.',
                ],
                'target_warehouse_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Ziel-Lager-ID (nur bei Transfer relevant).',
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Optional: Grund der Bewegung.',
                ],
                'reference_type' => [
                    'type' => 'string',
                    'description' => 'Optional: Referenz-Typ (z.B. order, return).',
                ],
                'reference_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Referenz-ID.',
                ],
            ],
            'required' => ['commerce_article_id', 'commerce_warehouse_id', 'type', 'quantity'],
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

            $type = $arguments['type'] ?? '';
            $articleId = (int)$arguments['commerce_article_id'];
            $warehouseId = (int)$arguments['commerce_warehouse_id'];
            $quantity = (float)$arguments['quantity'];

            if ($quantity == 0) {
                return ToolResult::error('VALIDATION_ERROR', 'quantity darf nicht 0 sein.');
            }

            $manager = new InventoryManager();

            if ($type === 'inbound') {
                if ($quantity < 0) {
                    return ToolResult::error('VALIDATION_ERROR', 'quantity muss bei inbound positiv sein.');
                }

                $stockLevel = $manager->addStock(
                    $articleId,
                    $warehouseId,
                    $quantity,
                    $team->id,
                    $context->user->id,
                    $arguments['reason'] ?? null,
                    $arguments['reference_type'] ?? null,
                    isset($arguments['reference_id']) ? (int)$arguments['reference_id'] : null,
                );

                return ToolResult::success([
                    'message' => 'Bestand hinzugefügt.',
                    'stock_level' => [
                        'id' => $stockLevel->id,
                        'commerce_article_id' => $stockLevel->commerce_article_id,
                        'commerce_warehouse_id' => $stockLevel->commerce_warehouse_id,
                        'quantity' => (float)$stockLevel->quantity,
                        'reserved_quantity' => (float)$stockLevel->reserved_quantity,
                        'available_quantity' => (float)$stockLevel->quantity - (float)$stockLevel->reserved_quantity,
                    ],
                ]);
            } elseif ($type === 'outbound') {
                if ($quantity < 0) {
                    return ToolResult::error('VALIDATION_ERROR', 'quantity muss bei outbound positiv sein.');
                }

                $stockLevel = $manager->removeStock(
                    $articleId,
                    $warehouseId,
                    $quantity,
                    $team->id,
                    $context->user->id,
                    $arguments['reason'] ?? null,
                    $arguments['reference_type'] ?? null,
                    isset($arguments['reference_id']) ? (int)$arguments['reference_id'] : null,
                );

                return ToolResult::success([
                    'message' => 'Bestand entnommen.',
                    'stock_level' => [
                        'id' => $stockLevel->id,
                        'commerce_article_id' => $stockLevel->commerce_article_id,
                        'commerce_warehouse_id' => $stockLevel->commerce_warehouse_id,
                        'quantity' => (float)$stockLevel->quantity,
                        'reserved_quantity' => (float)$stockLevel->reserved_quantity,
                        'available_quantity' => (float)$stockLevel->quantity - (float)$stockLevel->reserved_quantity,
                    ],
                ]);
            } else {
                // adjustment - use addStock or removeStock depending on sign
                if ($quantity > 0) {
                    $stockLevel = $manager->addStock(
                        $articleId,
                        $warehouseId,
                        $quantity,
                        $team->id,
                        $context->user->id,
                        $arguments['reason'] ?? 'Adjustment',
                        $arguments['reference_type'] ?? null,
                        isset($arguments['reference_id']) ? (int)$arguments['reference_id'] : null,
                    );
                } else {
                    $stockLevel = $manager->removeStock(
                        $articleId,
                        $warehouseId,
                        abs($quantity),
                        $team->id,
                        $context->user->id,
                        $arguments['reason'] ?? 'Adjustment',
                        $arguments['reference_type'] ?? null,
                        isset($arguments['reference_id']) ? (int)$arguments['reference_id'] : null,
                    );
                }

                return ToolResult::success([
                    'message' => 'Bestandskorrektur durchgeführt.',
                    'stock_level' => [
                        'id' => $stockLevel->id,
                        'commerce_article_id' => $stockLevel->commerce_article_id,
                        'commerce_warehouse_id' => $stockLevel->commerce_warehouse_id,
                        'quantity' => (float)$stockLevel->quantity,
                        'reserved_quantity' => (float)$stockLevel->reserved_quantity,
                        'available_quantity' => (float)$stockLevel->quantity - (float)$stockLevel->reserved_quantity,
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'stock_movements', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
