<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceArticleType;

/**
 * Listet Artikel-Typen für ein Team.
 */
class ListArticleTypesTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.article_types.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/article-types - Listet Artikel-Typen (id, name, description, color, active). Nutze dieses Tool bevor du article_type_id an Artikeln setzt. Unterstützt filters/search/sort/limit/offset.';
    }

    public function getSchema(): array
    {
        return $this->mergeSchemas(
            $this->getStandardGetSchema(),
            [
                'properties' => [
                    'team_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Team-ID. Default: Team aus Kontext.',
                    ],
                    'active' => [
                        'type' => 'boolean',
                        'description' => 'Optional: nur aktive/inaktive Artikel-Typen. Default: true (nur aktive).',
                        'default' => true,
                    ],
                ],
            ]
        );
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

            $activeOnly = (bool)($arguments['active'] ?? true);

            $q = CommerceArticleType::query()
                ->where('team_id', $team->id);

            if ($activeOnly) {
                $q->where('active', true);
            } elseif (array_key_exists('active', $arguments)) {
                $q->where('active', false);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'active', 'name', 'color', 'created_at']);
            $this->applyStandardSearch($q, $arguments, ['name', 'description']);
            $this->applyStandardSort($q, $arguments, ['name', 'id', 'created_at'], 'name', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'color' => $type->color,
                'active' => (bool)$type->active,
                'team_id' => $type->team_id,
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Artikel-Typen: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'article_types', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
