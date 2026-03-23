<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceStockMovement;

/**
 * Listet Lagerbewegungen für ein Team.
 */
class ListStockMovementsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.stock_movements.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/stock_movements - Listet Lagerbewegungen (id, type, quantity, commerce_article_id, commerce_warehouse_id, target_warehouse_id, reason). Unterstützt filters/sort/limit/offset.';
    }

    public function getSchema(): array
    {
        return $this->mergeSchemas(
            $this->getStandardGetSchema(),
            [
                'properties' => [
                    'team_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Team-ID. Default: Team aus Kontext.',
                    ],
                    'commerce_article_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Artikel-ID.',
                    ],
                    'commerce_warehouse_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Lager-ID.',
                    ],
                    'type' => [
                        'type' => 'string',
                        'description' => 'Optional: Filter nach Bewegungstyp (inbound, outbound, transfer, adjustment, reservation, reservation_release).',
                    ],
                ],
            ]
        );
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

            $q = CommerceStockMovement::query()
                ->where('team_id', $team->id);

            if (array_key_exists('commerce_article_id', $arguments) && $arguments['commerce_article_id'] !== null) {
                $q->where('commerce_article_id', (int)$arguments['commerce_article_id']);
            }

            if (array_key_exists('commerce_warehouse_id', $arguments) && $arguments['commerce_warehouse_id'] !== null) {
                $q->where('commerce_warehouse_id', (int)$arguments['commerce_warehouse_id']);
            }

            if (array_key_exists('type', $arguments) && $arguments['type'] !== null && $arguments['type'] !== '') {
                $q->where('type', (string)$arguments['type']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'commerce_article_id', 'commerce_warehouse_id', 'type', 'created_at']);
            $this->applyStandardSort($q, $arguments, ['id', 'created_at', 'quantity'], 'id', 'desc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($movement) => [
                'id' => $movement->id,
                'team_id' => $movement->team_id,
                'user_id' => $movement->user_id,
                'commerce_article_id' => $movement->commerce_article_id,
                'commerce_warehouse_id' => $movement->commerce_warehouse_id,
                'target_warehouse_id' => $movement->target_warehouse_id,
                'type' => $movement->type instanceof \BackedEnum ? $movement->type->value : $movement->type,
                'quantity' => (float)$movement->quantity,
                'reason' => $movement->reason,
                'reference_type' => $movement->reference_type,
                'reference_id' => $movement->reference_id,
                'created_at' => $movement->created_at?->toIso8601String(),
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Lagerbewegungen: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'stock_movements', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
