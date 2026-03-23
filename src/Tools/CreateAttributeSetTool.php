<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceAttributeSet;

/**
 * Erstellt ein neues Attribute-Set.
 */
class CreateAttributeSetTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.attribute_sets.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/attribute-sets - Erstellt ein neues Attribute-Set.';
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
                    'description' => 'Name des Attribute-Sets (ERFORDERLICH).',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Farbe des Attribute-Sets.',
                ],
                'is_required' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ob das Attribute-Set erforderlich ist. Default: false.',
                ],
                'is_multiselect' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ob Mehrfachauswahl möglich ist. Default: false.',
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

            $set = CommerceAttributeSet::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'color' => (array_key_exists('color', $arguments) && $arguments['color'] !== '')
                    ? (string)$arguments['color']
                    : null,
                'is_required' => (bool)($arguments['is_required'] ?? false),
                'is_multiselect' => (bool)($arguments['is_multiselect'] ?? false),
            ]);

            return ToolResult::success([
                'id' => $set->id,
                'name' => $set->name,
                'color' => $set->color,
                'is_required' => (bool)$set->is_required,
                'is_multiselect' => (bool)$set->is_multiselect,
                'user_id' => $set->user_id,
                'team_id' => $set->team_id,
                'message' => 'Attribute-Set erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Attribute-Sets: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'attribute_sets', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
