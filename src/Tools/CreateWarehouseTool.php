<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceWarehouse;

/**
 * Erstellt ein neues Lager.
 */
class CreateWarehouseTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.warehouses.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/warehouses - Erstellt ein neues Lager.';
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
                    'description' => 'Name des Lagers (ERFORDERLICH).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung des Lagers.',
                ],
                'address' => [
                    'type' => 'string',
                    'description' => 'Optional: Adresse des Lagers.',
                ],
                'city' => [
                    'type' => 'string',
                    'description' => 'Optional: Stadt des Lagers.',
                ],
                'postal_code' => [
                    'type' => 'string',
                    'description' => 'Optional: Postleitzahl des Lagers.',
                ],
                'country' => [
                    'type' => 'string',
                    'description' => 'Optional: Land des Lagers.',
                ],
                'is_active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ob das Lager aktiv ist. Default: true.',
                ],
                'is_default' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ob das Lager das Standard-Lager ist. Default: false.',
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

            $warehouse = CommerceWarehouse::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'address' => (array_key_exists('address', $arguments) && $arguments['address'] !== '')
                    ? (string)$arguments['address']
                    : null,
                'city' => (array_key_exists('city', $arguments) && $arguments['city'] !== '')
                    ? (string)$arguments['city']
                    : null,
                'postal_code' => (array_key_exists('postal_code', $arguments) && $arguments['postal_code'] !== '')
                    ? (string)$arguments['postal_code']
                    : null,
                'country' => (array_key_exists('country', $arguments) && $arguments['country'] !== '')
                    ? (string)$arguments['country']
                    : null,
                'is_active' => array_key_exists('is_active', $arguments) ? (bool)$arguments['is_active'] : true,
                'is_default' => array_key_exists('is_default', $arguments) ? (bool)$arguments['is_default'] : false,
            ]);

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
                'message' => 'Lager erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Lagers: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'warehouses', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
