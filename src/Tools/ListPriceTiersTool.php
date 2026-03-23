<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommercePriceTier;

/**
 * Listet Preisstaffeln für ein Team.
 */
class ListPriceTiersTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.price_tiers.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/price-tiers - Listet Preisstaffeln (id, commerce_price_list_id, commerce_article_id, min_quantity, max_quantity, price, discount_percentage). Unterstützt filters/search/sort/limit/offset.';
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
                    'commerce_price_list_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Nur Staffeln für diese Preisliste anzeigen.',
                    ],
                    'commerce_article_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Nur Staffeln für diesen Artikel anzeigen.',
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

            $q = CommercePriceTier::query()
                ->where('team_id', $team->id);

            if (!empty($arguments['commerce_price_list_id'])) {
                $q->where('commerce_price_list_id', (int)$arguments['commerce_price_list_id']);
            }
            if (!empty($arguments['commerce_article_id'])) {
                $q->where('commerce_article_id', (int)$arguments['commerce_article_id']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'commerce_price_list_id', 'commerce_article_id', 'created_at']);
            $this->applyStandardSearch($q, $arguments, []);
            $this->applyStandardSort($q, $arguments, ['id', 'min_quantity', 'price', 'created_at'], 'id', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($item) => [
                'id' => $item->id,
                'commerce_price_list_id' => $item->commerce_price_list_id,
                'commerce_article_id' => $item->commerce_article_id,
                'min_quantity' => $item->min_quantity,
                'max_quantity' => $item->max_quantity,
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
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Preisstaffeln: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'price_tiers', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
