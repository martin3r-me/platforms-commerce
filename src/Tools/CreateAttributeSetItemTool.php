<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceAttributeSetItem;
use Platform\Commerce\Models\CommerceAttributeSet;

/**
 * Erstellt ein neues Attribute-Set-Item.
 */
class CreateAttributeSetItemTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.attribute_set_items.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/attribute-set-items - Erstellt ein neues Attribute-Set-Item.';
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
                'commerce_attribute_set_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Attribute-Sets (ERFORDERLICH).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Name des Items (ERFORDERLICH).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung des Items.',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Farbe des Items.',
                ],
            ],
            'required' => ['commerce_attribute_set_id', 'name'],
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

            if (!array_key_exists('commerce_attribute_set_id', $arguments) || $arguments['commerce_attribute_set_id'] === null || $arguments['commerce_attribute_set_id'] === '') {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_attribute_set_id ist erforderlich.');
            }
            $setId = (int)$arguments['commerce_attribute_set_id'];
            $setExists = CommerceAttributeSet::query()
                ->where('id', $setId)
                ->where('team_id', $team->id)
                ->whereNull('deleted_at')
                ->exists();
            if (!$setExists) {
                return ToolResult::error('VALIDATION_ERROR', "Attribute-Set mit ID {$setId} nicht gefunden in diesem Team.");
            }

            $item = CommerceAttributeSetItem::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'commerce_attribute_set_id' => $setId,
                'name' => $name,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'color' => (array_key_exists('color', $arguments) && $arguments['color'] !== '')
                    ? (string)$arguments['color']
                    : null,
            ]);

            return ToolResult::success([
                'id' => $item->id,
                'commerce_attribute_set_id' => $item->commerce_attribute_set_id,
                'name' => $item->name,
                'description' => $item->description,
                'color' => $item->color,
                'user_id' => $item->user_id,
                'team_id' => $item->team_id,
                'message' => 'Attribute-Set-Item erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Attribute-Set-Items: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'attribute_set_items', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
