<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceProductPromotion;

/**
 * Listet Produktaktionen für ein Team.
 */
class ListProductPromotionsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.product_promotions.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/product_promotions - Listet Produktaktionen (id, commerce_product_id, discount_value, etc.). Unterstützt filters/search/sort/limit/offset.';
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
                    'commerce_product_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Produkt-ID.',
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

            $q = CommerceProductPromotion::query()
                ->whereHas('product', fn($sq) => $sq->where('team_id', $team->id));

            if (array_key_exists('commerce_product_id', $arguments) && $arguments['commerce_product_id'] !== null) {
                $q->where('commerce_product_id', (int)$arguments['commerce_product_id']);
            }

            $this->applyStandardFilters($q, $arguments, ['commerce_product_id', 'created_at']);
            $this->applyStandardSearch($q, $arguments, []);
            $this->applyStandardSort($q, $arguments, ['id', 'created_at', 'promotion_start'], 'id', 'desc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($promo) => [
                'id' => $promo->id,
                'commerce_product_id' => $promo->commerce_product_id,
                'discount_value' => $promo->discount_value,
                'discount_percentage' => $promo->discount_percentage,
                'min_cart_value' => $promo->min_cart_value,
                'promotion_start' => $promo->promotion_start?->toIso8601String(),
                'promotion_end' => $promo->promotion_end?->toIso8601String(),
                'created_at' => $promo->created_at?->toIso8601String(),
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Produktaktionen: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'product_promotions', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
