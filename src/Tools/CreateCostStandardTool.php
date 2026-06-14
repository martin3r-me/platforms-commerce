<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceCostStandard;

class CreateCostStandardTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.cost_standards.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/cost-standards - Legt einen internen Personalkostensatz an (Skill-Level). Mindestens entweder cost_per_hour oder cost_per_day sollte gesetzt sein.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'name' => ['type' => 'string', 'description' => 'ERFORDERLICH. Bezeichnung des Kostensatzes (z.B. "Senior", "IT-Service").'],
                'description' => ['type' => 'string'],
                'cost_per_hour' => ['type' => 'number', 'description' => 'Interner Stundensatz in EUR.'],
                'cost_per_day' => ['type' => 'number', 'description' => 'Interner Tagessatz in EUR. Wenn leer, kann z.B. cost_per_hour × 8 verwendet werden.'],
                'valid_from' => ['type' => 'string'],
                'valid_until' => ['type' => 'string'],
                'is_active' => ['type' => 'boolean'],
                'color' => ['type' => 'string'],
                'sort_order' => ['type' => 'integer'],
            ],
            'required' => ['name'],
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

            $name = trim((string) ($arguments['name'] ?? ''));
            if ($name === '') {
                return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
            }

            $data = [
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'is_active' => array_key_exists('is_active', $arguments) ? (bool) $arguments['is_active'] : true,
                'sort_order' => array_key_exists('sort_order', $arguments) ? (int) $arguments['sort_order'] : 0,
            ];
            foreach (['description', 'cost_per_hour', 'cost_per_day', 'valid_from', 'valid_until', 'color'] as $field) {
                if (array_key_exists($field, $arguments) && $arguments[$field] !== null && $arguments[$field] !== '') {
                    $data[$field] = $arguments[$field];
                }
            }

            $row = CommerceCostStandard::create($data);

            return ToolResult::success([
                'id' => $row->id,
                'name' => $row->name,
                'cost_per_hour' => $row->cost_per_hour,
                'cost_per_day' => $row->cost_per_day,
                'is_active' => (bool) $row->is_active,
                'message' => "Kostensatz '{$row->name}' erfolgreich angelegt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return ['category' => 'action', 'tags' => ['commerce', 'cost_standards', 'create'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false];
    }
}
