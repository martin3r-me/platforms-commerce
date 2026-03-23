<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceWarehouse;

/**
 * Aktualisiert ein bestehendes Lager.
 */
class UpdateWarehouseTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.warehouses.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/warehouses/{id} - Aktualisiert ein Lager. Nutze commerce.warehouses.GET um die ID zu finden.';
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
                    'description' => 'ID des Lagers (ERFORDERLICH). Nutze commerce.warehouses.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name des Lagers.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
                'address' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Adresse ("" zum Leeren).',
                ],
                'city' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Stadt ("" zum Leeren).',
                ],
                'postal_code' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Postleitzahl ("" zum Leeren).',
                ],
                'country' => [
                    'type' => 'string',
                    'description' => 'Optional: Neues Land ("" zum Leeren).',
                ],
                'is_active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ob das Lager aktiv ist.',
                ],
                'is_default' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ob das Lager das Standard-Lager ist.',
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
                CommerceWarehouse::class,
                'NOT_FOUND',
                'Lager nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceWarehouse $warehouse */
            $warehouse = $found['model'];
            if ((int)$warehouse->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Lager gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            foreach (['description', 'address', 'city', 'postal_code', 'country'] as $field) {
                if (array_key_exists($field, $arguments)) {
                    $val = (string)($arguments[$field] ?? '');
                    $update[$field] = $val === '' ? null : $val;
                }
            }

            if (array_key_exists('is_active', $arguments)) {
                $update['is_active'] = (bool)$arguments['is_active'];
            }

            if (array_key_exists('is_default', $arguments)) {
                $update['is_default'] = (bool)$arguments['is_default'];
            }

            if (!empty($update)) {
                $warehouse->update($update);
            }
            $warehouse->refresh();

            return ToolResult::success([
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
                'message' => 'Lager erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Lagers: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'warehouses', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
