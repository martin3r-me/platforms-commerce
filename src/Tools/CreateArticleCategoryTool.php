<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticleCategory;

/**
 * Erstellt eine neue Artikel-Kategorie.
 */
class CreateArticleCategoryTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.article_categories.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/article-categories - Erstellt eine neue Artikel-Kategorie. Nutze zuerst commerce.article_categories.GET um vorhandene Kategorien zu prüfen.';
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
                    'description' => 'Name der Artikel-Kategorie (ERFORDERLICH).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung der Artikel-Kategorie.',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Farbe als Hex-Code (z.B. #FF5733).',
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
            $exists = CommerceArticleCategory::query()
                ->where('team_id', $team->id)
                ->where('name', $name)
                ->whereNull('deleted_at')
                ->exists();
            if ($exists) {
                return ToolResult::error('VALIDATION_ERROR', "Artikel-Kategorie mit Name '{$name}' existiert bereits in diesem Team.");
            }

            $category = CommerceArticleCategory::create([
                'team_id' => $team->id,
                'name' => $name,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'color' => (array_key_exists('color', $arguments) && $arguments['color'] !== '')
                    ? (string)$arguments['color']
                    : null,
            ]);

            return ToolResult::success([
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'color' => $category->color,
                'team_id' => $category->team_id,
                'message' => 'Artikel-Kategorie erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Artikel-Kategorie: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'article_categories', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
