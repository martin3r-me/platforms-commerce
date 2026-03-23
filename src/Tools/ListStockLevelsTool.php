<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceStockLevel;

/**
 * Listet Lagerbestände für ein Team.
 */
class ListStockLevelsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.stock_levels.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/stock_levels - Listet Lagerbestände (id, commerce_article_id, commerce_warehouse_id, quantity, reserved_quantity, available_quantity, minimum_quantity). Unterstützt filters/sort/limit/offset.';
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

            $q = CommerceStockLevel::query()
                ->where('team_id', $team->id);

            if (array_key_exists('commerce_article_id', $arguments) && $arguments['commerce_article_id'] !== null) {
                $q->where('commerce_article_id', (int)$arguments['commerce_article_id']);
            }

            if (array_key_exists('commerce_warehouse_id', $arguments) && $arguments['commerce_warehouse_id'] !== null) {
                $q->where('commerce_warehouse_id', (int)$arguments['commerce_warehouse_id']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'commerce_article_id', 'commerce_warehouse_id', 'quantity', 'created_at']);
            $this->applyStandardSort($q, $arguments, ['id', 'quantity', 'created_at'], 'id', 'desc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($level) => [
                'id' => $level->id,
                'team_id' => $level->team_id,
                'commerce_article_id' => $level->commerce_article_id,
                'commerce_warehouse_id' => $level->commerce_warehouse_id,
                'quantity' => (float)$level->quantity,
                'reserved_quantity' => (float)$level->reserved_quantity,
                'available_quantity' => (float)$level->quantity - (float)$level->reserved_quantity,
                'minimum_quantity' => $level->minimum_quantity !== null ? (float)$level->minimum_quantity : null,
                'created_at' => $level->created_at?->toIso8601String(),
                'updated_at' => $level->updated_at?->toIso8601String(),
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Lagerbestände: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'stock_levels', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
