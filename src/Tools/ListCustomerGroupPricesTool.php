<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceCustomerGroupPrice;

/**
 * Listet Kundengruppen-Preise für ein Team.
 */
class ListCustomerGroupPricesTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.customer_group_prices.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/customer-group-prices - Listet Kundengruppen-Preise (id, commerce_customer_group_id, commerce_article_id, commerce_price_list_id, price, discount_percentage). Unterstützt filters/search/sort/limit/offset.';
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
                    'commerce_customer_group_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Nur Preise für diese Kundengruppe anzeigen.',
                    ],
                    'commerce_article_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Nur Preise für diesen Artikel anzeigen.',
                    ],
                    'commerce_price_list_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Nur Preise für diese Preisliste anzeigen.',
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

            $q = CommerceCustomerGroupPrice::query()
                ->where('team_id', $team->id);

            if (!empty($arguments['commerce_customer_group_id'])) {
                $q->where('commerce_customer_group_id', (int)$arguments['commerce_customer_group_id']);
            }
            if (!empty($arguments['commerce_article_id'])) {
                $q->where('commerce_article_id', (int)$arguments['commerce_article_id']);
            }
            if (!empty($arguments['commerce_price_list_id'])) {
                $q->where('commerce_price_list_id', (int)$arguments['commerce_price_list_id']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'commerce_customer_group_id', 'commerce_article_id', 'commerce_price_list_id', 'created_at']);
            $this->applyStandardSearch($q, $arguments, []);
            $this->applyStandardSort($q, $arguments, ['id', 'price', 'created_at'], 'id', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($item) => [
                'id' => $item->id,
                'commerce_customer_group_id' => $item->commerce_customer_group_id,
                'commerce_article_id' => $item->commerce_article_id,
                'commerce_price_list_id' => $item->commerce_price_list_id,
                'price' => $item->price,
                'discount_percentage' => $item->discount_percentage,
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
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Kundengruppen-Preise: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'customer_group_prices', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
