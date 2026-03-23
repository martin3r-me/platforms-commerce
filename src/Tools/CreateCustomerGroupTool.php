<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceCustomerGroup;

/**
 * Erstellt eine neue Kundengruppe.
 */
class CreateCustomerGroupTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.customer_groups.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/customer-groups - Erstellt eine neue Kundengruppe. Nutze zuerst commerce.customer_groups.GET um vorhandene Gruppen zu prüfen.';
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
                    'description' => 'Name der Kundengruppe (ERFORDERLICH).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung der Kundengruppe.',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Farbe der Kundengruppe (z.B. Hex-Code).',
                ],
                'is_active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ob die Kundengruppe aktiv ist.',
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

            if (empty($arguments['name'])) {
                return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
            }

            $data = [
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $arguments['name'],
            ];

            if (array_key_exists('description', $arguments) && $arguments['description'] !== null) {
                $data['description'] = $arguments['description'];
            }
            if (array_key_exists('color', $arguments) && $arguments['color'] !== null) {
                $data['color'] = $arguments['color'];
            }
            if (array_key_exists('is_active', $arguments)) {
                $data['is_active'] = (bool)$arguments['is_active'];
            }

            $group = CommerceCustomerGroup::create($data);

            return ToolResult::success([
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'color' => $group->color,
                'is_active' => $group->is_active,
                'user_id' => $group->user_id,
                'team_id' => $group->team_id,
                'message' => 'Kundengruppe erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Kundengruppe: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'customer_groups', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
