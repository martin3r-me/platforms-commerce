<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceManufacturer;

/**
 * Aktualisiert einen bestehenden Hersteller.
 */
class UpdateManufacturerTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.manufacturers.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/manufacturers/{id} - Aktualisiert einen Hersteller. Nutze commerce.manufacturers.GET um die ID zu finden.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Team-ID. Default: Team aus Kontext.',
                ],
                'id' => [
                    'type' => 'integer',
                    'description' => 'ID des Herstellers (ERFORDERLICH). Nutze commerce.manufacturers.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name des Herstellers.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
            ],
            'required' => ['id'],
        ]);
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

            $found = $this->validateAndFindModel(
                $arguments,
                $context,
                'id',
                CommerceManufacturer::class,
                'NOT_FOUND',
                'Hersteller nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceManufacturer $manufacturer */
            $manufacturer = $found['model'];
            if ((int)$manufacturer->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Hersteller gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                // Check if name already exists in team (excluding current)
                $exists = CommerceManufacturer::query()
                    ->where('team_id', $team->id)
                    ->where('name', $name)
                    ->where('id', '!=', $manufacturer->id)
                    ->whereNull('deleted_at')
                    ->exists();
                if ($exists) {
                    return ToolResult::error('VALIDATION_ERROR', "Hersteller mit Name '{$name}' existiert bereits in diesem Team.");
                }
                $update['name'] = $name;
            }

            if (array_key_exists('description', $arguments)) {
                $d = (string)($arguments['description'] ?? '');
                $update['description'] = $d === '' ? null : $d;
            }

            if (!empty($update)) {
                $manufacturer->update($update);
            }
            $manufacturer->refresh();

            return ToolResult::success([
                'id' => $manufacturer->id,
                'name' => $manufacturer->name,
                'description' => $manufacturer->description,
                'user_id' => $manufacturer->user_id,
                'team_id' => $manufacturer->team_id,
                'message' => 'Hersteller erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Herstellers: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'manufacturers', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
