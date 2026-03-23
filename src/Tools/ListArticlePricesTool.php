<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceArticlePrice;

/**
 * Listet Artikel-Preise für ein Team.
 */
class ListArticlePricesTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.article_prices.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/article-prices - Listet Artikel-Preise (id, commerce_article_id, net_price, gross_price, tax_rate, etc.). Unterstützt filters/search/sort/limit/offset.';
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
                    'commerce_article_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Nur Preise für diesen Artikel anzeigen.',
                    ],
                    'commerce_sales_context_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Nur Preise für diesen Verkaufskontext anzeigen.',
                    ],
                    'commerce_tax_category_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Nur Preise für diese Steuerkategorie anzeigen.',
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

            $q = CommerceArticlePrice::query()
                ->where('team_id', $team->id);

            // Apply entity-specific filters before standard filters
            if (!empty($arguments['commerce_article_id'])) {
                $q->where('commerce_article_id', (int)$arguments['commerce_article_id']);
            }
            if (!empty($arguments['commerce_sales_context_id'])) {
                $q->where('commerce_sales_context_id', (int)$arguments['commerce_sales_context_id']);
            }
            if (!empty($arguments['commerce_tax_category_id'])) {
                $q->where('commerce_tax_category_id', (int)$arguments['commerce_tax_category_id']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'commerce_article_id', 'commerce_sales_context_id', 'commerce_tax_category_id', 'created_at']);
            $this->applyStandardSearch($q, $arguments, []);
            $this->applyStandardSort($q, $arguments, ['id', 'created_at', 'net_price', 'gross_price'], 'id', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($item) => [
                'id' => $item->id,
                'commerce_article_id' => $item->commerce_article_id,
                'commerce_sales_context_id' => $item->commerce_sales_context_id,
                'commerce_tax_category_id' => $item->commerce_tax_category_id,
                'net_price' => $item->net_price,
                'gross_price' => $item->gross_price,
                'tax_rate' => $item->tax_rate,
                'valid_from' => $item->valid_from?->toIso8601String(),
                'valid_until' => $item->valid_until?->toIso8601String(),
                'user_id' => $item->user_id,
                'team_id' => $item->team_id,
                'created_at' => $item->created_at?->toIso8601String(),
                'updated_at' => $item->updated_at?->toIso8601String(),
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Artikel-Preise: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'article_prices', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
