<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceArticleCategory;

/**
 * Aktualisiert eine bestehende Artikel-Kategorie.
 */
class UpdateArticleCategoryTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.article_categories.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/article-categories/{id} - Aktualisiert eine Artikel-Kategorie. Nutze commerce.article_categories.GET um die ID zu finden.';
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
                    'description' => 'ID der Artikel-Kategorie (ERFORDERLICH). Nutze commerce.article_categories.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name der Artikel-Kategorie.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Farbe als Hex-Code ("" zum Leeren).',
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
                CommerceArticleCategory::class,
                'NOT_FOUND',
                'Artikel-Kategorie nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceArticleCategory $category */
            $category = $found['model'];
            if ((int)$category->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Artikel-Kategorie gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                // Check if name already exists in team (excluding current)
                $exists = CommerceArticleCategory::query()
                    ->where('team_id', $team->id)
                    ->where('name', $name)
                    ->where('id', '!=', $category->id)
                    ->whereNull('deleted_at')
                    ->exists();
                if ($exists) {
                    return ToolResult::error('VALIDATION_ERROR', "Artikel-Kategorie mit Name '{$name}' existiert bereits in diesem Team.");
                }
                $update['name'] = $name;
            }

            if (array_key_exists('description', $arguments)) {
                $d = (string)($arguments['description'] ?? '');
                $update['description'] = $d === '' ? null : $d;
            }

            if (array_key_exists('color', $arguments)) {
                $c = (string)($arguments['color'] ?? '');
                $update['color'] = $c === '' ? null : $c;
            }

            if (!empty($update)) {
                $category->update($update);
            }
            $category->refresh();

            return ToolResult::success([
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'color' => $category->color,
                'team_id' => $category->team_id,
                'message' => 'Artikel-Kategorie erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Artikel-Kategorie: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'article_categories', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
