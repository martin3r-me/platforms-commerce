<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceCostStandard;

class UpdateCostStandardTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.cost_standards.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/cost-standards/{id} - Aktualisiert einen Personalkostensatz. Änderungen wirken sich automatisch auf alle Artikel aus, die diesen Kostensatz referenzieren.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'id' => ['type' => 'integer', 'description' => 'ERFORDERLICH.'],
                'name' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'cost_per_hour' => ['type' => 'number'],
                'cost_per_day' => ['type' => 'number'],
                'valid_from' => ['type' => 'string'],
                'valid_until' => ['type' => 'string'],
                'is_active' => ['type' => 'boolean'],
                'color' => ['type' => 'string'],
                'sort_order' => ['type' => 'integer'],
            ],
            'required' => ['id'],
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

            $id = (int) ($arguments['id'] ?? 0);
            if (!$id) {
                return ToolResult::error('VALIDATION_ERROR', 'id ist erforderlich.');
            }

            $row = CommerceCostStandard::where('team_id', $team->id)->find($id);
            if (!$row) {
                return ToolResult::error('NOT_FOUND', "Kostensatz {$id} nicht in Team {$team->id} gefunden.");
            }

            $updates = [];
            foreach (['name', 'description', 'cost_per_hour', 'cost_per_day', 'valid_from', 'valid_until', 'is_active', 'color', 'sort_order'] as $field) {
                if (array_key_exists($field, $arguments)) {
                    $updates[$field] = $arguments[$field];
                }
            }
            $row->fill($updates)->save();
            $row->refresh();

            return ToolResult::success([
                'id' => $row->id,
                'name' => $row->name,
                'cost_per_hour' => $row->cost_per_hour,
                'cost_per_day' => $row->cost_per_day,
                'is_active' => (bool) $row->is_active,
                'message' => "Kostensatz '{$row->name}' erfolgreich aktualisiert.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return ['category' => 'action', 'tags' => ['commerce', 'cost_standards', 'update'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => true];
    }
}
