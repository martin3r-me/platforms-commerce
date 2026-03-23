<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceAttributeSet;

/**
 * Aktualisiert ein bestehendes Attribute-Set.
 */
class UpdateAttributeSetTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.attribute_sets.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/attribute-sets/{id} - Aktualisiert ein Attribute-Set. Nutze commerce.attribute_sets.GET um die ID zu finden.';
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
                    'description' => 'ID des Attribute-Sets (ERFORDERLICH). Nutze commerce.attribute_sets.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name des Attribute-Sets.',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Farbe ("" zum Leeren).',
                ],
                'is_required' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ob das Attribute-Set erforderlich ist.',
                ],
                'is_multiselect' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ob Mehrfachauswahl möglich ist.',
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
                CommerceAttributeSet::class,
                'NOT_FOUND',
                'Attribute-Set nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceAttributeSet $set */
            $set = $found['model'];
            if ((int)$set->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Attribute-Set gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            if (array_key_exists('color', $arguments)) {
                $c = (string)($arguments['color'] ?? '');
                $update['color'] = $c === '' ? null : $c;
            }

            if (array_key_exists('is_required', $arguments)) {
                $update['is_required'] = (bool)$arguments['is_required'];
            }

            if (array_key_exists('is_multiselect', $arguments)) {
                $update['is_multiselect'] = (bool)$arguments['is_multiselect'];
            }

            if (!empty($update)) {
                $set->update($update);
            }
            $set->refresh();

            return ToolResult::success([
                'id' => $set->id,
                'name' => $set->name,
                'color' => $set->color,
                'is_required' => (bool)$set->is_required,
                'is_multiselect' => (bool)$set->is_multiselect,
                'user_id' => $set->user_id,
                'team_id' => $set->team_id,
                'message' => 'Attribute-Set erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Attribute-Sets: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'attribute_sets', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
