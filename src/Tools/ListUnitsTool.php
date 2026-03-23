<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceUnit;

/**
 * Listet Einheiten für ein Team.
 */
class ListUnitsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.units.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/units - Listet Einheiten (id, name, symbol, type, is_base_unit, base_unit_id, factor_to_base). Unterstützt filters/search/sort/limit/offset.';
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
                    'type' => [
                        'type' => 'string',
                        'description' => 'Optional: Nur Einheiten dieses Typs anzeigen.',
                    ],
                    'is_base_unit' => [
                        'type' => 'boolean',
                        'description' => 'Optional: Nur Basis-Einheiten (true) oder Nicht-Basis-Einheiten (false) anzeigen.',
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

            $q = CommerceUnit::query()
                ->where('team_id', $team->id);

            if (!empty($arguments['type'])) {
                $q->where('type', (string)$arguments['type']);
            }
            if (array_key_exists('is_base_unit', $arguments) && $arguments['is_base_unit'] !== null) {
                $q->where('is_base_unit', (bool)$arguments['is_base_unit']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'type', 'is_base_unit', 'created_at']);
            $this->applyStandardSearch($q, $arguments, ['name', 'symbol']);
            $this->applyStandardSort($q, $arguments, ['name', 'id', 'type', 'created_at'], 'name', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'type' => $unit->type,
                'is_base_unit' => (bool)$unit->is_base_unit,
                'base_unit_id' => $unit->base_unit_id,
                'factor_to_base' => $unit->factor_to_base,
                'user_id' => $unit->user_id,
                'team_id' => $unit->team_id,
                'created_at' => $unit->created_at?->toIso8601String(),
                'updated_at' => $unit->updated_at?->toIso8601String(),
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Einheiten: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'units', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
