<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticleType;

/**
 * Erstellt einen neuen Artikel-Typ.
 */
class CreateArticleTypeTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.article_types.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/article-types - Erstellt einen neuen Artikel-Typ. Nutze zuerst commerce.article_types.GET um vorhandene Typen zu prüfen.';
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
                    'description' => 'Name des Artikel-Typs (ERFORDERLICH).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung des Artikel-Typs.',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Farbe als Hex-Code (z.B. #FF5733).',
                ],
                'active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: aktiv/inaktiv. Default: true.',
                    'default' => true,
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
            $exists = CommerceArticleType::query()
                ->where('team_id', $team->id)
                ->where('name', $name)
                ->whereNull('deleted_at')
                ->exists();
            if ($exists) {
                return ToolResult::error('VALIDATION_ERROR', "Artikel-Typ mit Name '{$name}' existiert bereits in diesem Team.");
            }

            $articleType = CommerceArticleType::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'color' => (array_key_exists('color', $arguments) && $arguments['color'] !== '')
                    ? (string)$arguments['color']
                    : null,
                'active' => (bool)($arguments['active'] ?? true),
            ]);

            return ToolResult::success([
                'id' => $articleType->id,
                'name' => $articleType->name,
                'description' => $articleType->description,
                'color' => $articleType->color,
                'active' => (bool)$articleType->active,
                'team_id' => $articleType->team_id,
                'message' => 'Artikel-Typ erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Artikel-Typs: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'article_types', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
