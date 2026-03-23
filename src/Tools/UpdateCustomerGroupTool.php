<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceCustomerGroup;

/**
 * Aktualisiert eine bestehende Kundengruppe.
 */
class UpdateCustomerGroupTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.customer_groups.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/customer-groups/{id} - Aktualisiert eine Kundengruppe. Nutze commerce.customer_groups.GET um die ID zu finden.';
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
                    'description' => 'ID der Kundengruppe (ERFORDERLICH). Nutze commerce.customer_groups.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Farbe ("" zum Leeren).',
                ],
                'is_active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Aktiv/Inaktiv setzen.',
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
                CommerceCustomerGroup::class,
                'NOT_FOUND',
                'Kundengruppe nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceCustomerGroup $group */
            $group = $found['model'];
            if ((int)$group->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Kundengruppe gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $update['name'] = $arguments['name'];
            }
            if (array_key_exists('description', $arguments)) {
                $v = (string)($arguments['description'] ?? '');
                $update['description'] = $v === '' ? null : $v;
            }
            if (array_key_exists('color', $arguments)) {
                $v = (string)($arguments['color'] ?? '');
                $update['color'] = $v === '' ? null : $v;
            }
            if (array_key_exists('is_active', $arguments)) {
                $update['is_active'] = (bool)$arguments['is_active'];
            }

            if (!empty($update)) {
                $group->update($update);
            }
            $group->refresh();

            return ToolResult::success([
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'color' => $group->color,
                'is_active' => $group->is_active,
                'user_id' => $group->user_id,
                'team_id' => $group->team_id,
                'message' => 'Kundengruppe erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Kundengruppe: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'customer_groups', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
