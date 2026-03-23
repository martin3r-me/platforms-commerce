<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceManufacturer;

/**
 * Erstellt einen neuen Hersteller.
 */
class CreateManufacturerTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.manufacturers.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/manufacturers - Erstellt einen neuen Hersteller. Nutze zuerst commerce.manufacturers.GET um vorhandene Hersteller zu prüfen.';
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
                'name' => [
                    'type' => 'string',
                    'description' => 'Name des Herstellers (ERFORDERLICH).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung des Herstellers.',
                ],
            ],
            'required' => ['name'],
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

            $name = trim((string)($arguments['name'] ?? ''));
            if ($name === '') {
                return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
            }

            // Check if name already exists in team
            $exists = CommerceManufacturer::query()
                ->where('team_id', $team->id)
                ->where('name', $name)
                ->whereNull('deleted_at')
                ->exists();
            if ($exists) {
                return ToolResult::error('VALIDATION_ERROR', "Hersteller mit Name '{$name}' existiert bereits in diesem Team.");
            }

            $manufacturer = CommerceManufacturer::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
            ]);

            return ToolResult::success([
                'id' => $manufacturer->id,
                'name' => $manufacturer->name,
                'description' => $manufacturer->description,
                'user_id' => $manufacturer->user_id,
                'team_id' => $manufacturer->team_id,
                'message' => 'Hersteller erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Herstellers: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'manufacturers', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
