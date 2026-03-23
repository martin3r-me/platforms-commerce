<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceWarehouse;

/**
 * Listet Lager für ein Team.
 */
class ListWarehousesTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.warehouses.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/warehouses - Listet Lager (id, name, description, address, city, postal_code, country, is_active, is_default). Unterstützt filters/search/sort/limit/offset.';
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
                    'is_active' => [
                        'type' => 'boolean',
                        'description' => 'Optional: Filter nach aktiven/inaktiven Lagern.',
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

            $q = CommerceWarehouse::query()
                ->where('team_id', $team->id);

            if (array_key_exists('is_active', $arguments)) {
                $q->where('is_active', (bool)$arguments['is_active']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'name', 'is_active', 'is_default', 'created_at']);
            $this->applyStandardSearch($q, $arguments, ['name', 'description', 'city']);
            $this->applyStandardSort($q, $arguments, ['name', 'id', 'created_at'], 'name', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($warehouse) => [
                'id' => $warehouse->id,
                'team_id' => $warehouse->team_id,
                'user_id' => $warehouse->user_id,
                'name' => $warehouse->name,
                'description' => $warehouse->description,
                'address' => $warehouse->address,
                'city' => $warehouse->city,
                'postal_code' => $warehouse->postal_code,
                'country' => $warehouse->country,
                'is_active' => $warehouse->is_active,
                'is_default' => $warehouse->is_default,
                'created_at' => $warehouse->created_at?->toIso8601String(),
                'updated_at' => $warehouse->updated_at?->toIso8601String(),
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Lager: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'warehouses', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
