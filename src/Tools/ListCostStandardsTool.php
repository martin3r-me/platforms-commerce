<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceCostStandard;

class ListCostStandardsTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.cost_standards.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/cost-standards - Listet interne Personalkostensätze (Skill-Level wie Junior/Senior/etc.) mit Stunden- und Tagessatz.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer', 'description' => 'Optional: Team-ID.'],
                'active_only' => ['type' => 'boolean', 'description' => 'Optional: Nur aktive Sätze.'],
                'limit' => ['type' => 'integer'],
                'offset' => ['type' => 'integer'],
            ],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $teamId = $arguments['team_id'] ?? $context->team?->id;
            if (!$teamId) {
                return ToolResult::error('MISSING_TEAM', 'Kein Team angegeben.');
            }
            $team = Team::find((int) $teamId);
            if (!$team) {
                return ToolResult::error('TEAM_NOT_FOUND', 'Team nicht gefunden.');
            }
            if (!$context->user || !$context->user->teams()->where('teams.id', $team->id)->exists()) {
                return ToolResult::error('ACCESS_DENIED', 'Kein Zugriff auf dieses Team.');
            }

            $limit  = (int) ($arguments['limit'] ?? 50);
            $offset = (int) ($arguments['offset'] ?? 0);

            $query = CommerceCostStandard::where('team_id', $team->id);
            if (!empty($arguments['active_only'])) {
                $query->where('is_active', true);
            }
            $total = (clone $query)->count();

            $rows = $query->orderBy('sort_order')->orderBy('name')
                ->skip($offset)->take($limit)->get();

            return ToolResult::success([
                'data' => $rows->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'description' => $r->description,
                    'cost_per_hour' => $r->cost_per_hour,
                    'cost_per_day'  => $r->cost_per_day,
                    'valid_from' => $r->valid_from?->toDateString(),
                    'valid_until' => $r->valid_until?->toDateString(),
                    'is_active' => (bool) $r->is_active,
                    'color' => $r->color,
                    'sort_order' => $r->sort_order,
                ])->all(),
                'pagination' => [
                    'limit' => $limit, 'offset' => $offset, 'total' => $total,
                    'returned' => $rows->count(),
                    'has_more' => ($offset + $rows->count()) < $total,
                ],
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return ['category' => 'lookup', 'tags' => ['commerce', 'cost_standards', 'lookup'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true];
    }
}
