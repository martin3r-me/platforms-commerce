<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceArticle;

/**
 * Listet Artikel für ein Team.
 */
class ListArticlesTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.articles.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/articles - Listet Artikel (id, name, sku, price). Nutze dieses Tool um vorhandene Artikel zu finden, bevor du SlotVariants erstellst. Unterstützt filters/search/sort/limit/offset.';
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
                    'commerce_article_type_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Artikel-Typ ID.',
                    ],
                    'is_available' => [
                        'type' => 'boolean',
                        'description' => 'Optional: Filter nach Verfügbarkeit.',
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

            $q = CommerceArticle::query()
                ->with('taxCategory:id,revenue_account')
                ->where('team_id', $team->id);

            // Filter by article type
            if (array_key_exists('commerce_article_type_id', $arguments) && $arguments['commerce_article_type_id'] !== null && $arguments['commerce_article_type_id'] !== '') {
                $q->where('commerce_article_type_id', (int)$arguments['commerce_article_type_id']);
            }

            // Filter by availability
            if (array_key_exists('is_available', $arguments)) {
                $q->where('is_available', (bool)$arguments['is_available']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'commerce_article_type_id', 'is_available', 'sku', 'name', 'created_at']);
            $this->applyStandardSearch($q, $arguments, ['name', 'sku', 'description']);
            $this->applyStandardSort($q, $arguments, ['name', 'sku', 'price', 'id', 'created_at'], 'name', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($article) => [
                'id' => $article->id,
                'name' => $article->name,
                'sku' => $article->sku,
                'price' => (float)$article->price,
                'description' => $article->description,
                'commerce_article_type_id' => $article->commerce_article_type_id,
                'revenue_account' => $article->revenue_account,
                'effective_revenue_account' => $article->effective_revenue_account,
                'stock_level' => (int)$article->stock_level,
                'is_available' => (bool)$article->is_available,
                'team_id' => $article->team_id,
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Artikel: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'articles', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
