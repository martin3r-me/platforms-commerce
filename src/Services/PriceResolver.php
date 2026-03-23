<?php

namespace Platform\Commerce\Services;

use Platform\Commerce\Models\CommerceArticle;
use Platform\Commerce\Models\CommerceArticlePrice;
use Platform\Commerce\Models\CommerceCustomerGroupPrice;
use Platform\Commerce\Models\CommercePriceList;
use Platform\Commerce\Models\CommercePriceTier;
use Platform\Commerce\Enums\PriceType;

class PriceResolver
{
    /**
     * Resolve the best price for an article given context.
     *
     * Priority chain: Promotional > CustomerGroup > Tier > Time-based > Standard
     */
    public function resolve(
        int $articleId,
        int $teamId,
        ?int $customerGroupId = null,
        ?int $salesContextId = null,
        float $quantity = 1,
        ?\DateTimeInterface $date = null,
    ): array {
        $date = $date ?? now();

        // 1. Check promotional prices (highest priority)
        $promotional = $this->findPromotionalPrice($articleId, $teamId, $date);
        if ($promotional) {
            return [
                'price' => (float)$promotional->gross_price,
                'net_price' => (float)$promotional->net_price,
                'tax_rate' => (float)$promotional->tax_rate,
                'price_type' => PriceType::Promotional->value,
                'source' => 'article_price',
                'source_id' => $promotional->id,
            ];
        }

        // 2. Check customer group prices
        if ($customerGroupId) {
            $groupPrice = $this->findCustomerGroupPrice($articleId, $teamId, $customerGroupId);
            if ($groupPrice) {
                return [
                    'price' => (float)$groupPrice->price,
                    'net_price' => null,
                    'tax_rate' => null,
                    'price_type' => PriceType::CustomerGroup->value,
                    'source' => 'customer_group_price',
                    'source_id' => $groupPrice->id,
                    'discount_percentage' => $groupPrice->discount_percentage ? (float)$groupPrice->discount_percentage : null,
                ];
            }
        }

        // 3. Check tier prices (quantity-based)
        $tierPrice = $this->findTierPrice($articleId, $teamId, $quantity);
        if ($tierPrice) {
            return [
                'price' => (float)$tierPrice->price,
                'net_price' => null,
                'tax_rate' => null,
                'price_type' => PriceType::Tier->value,
                'source' => 'price_tier',
                'source_id' => $tierPrice->id,
                'discount_percentage' => $tierPrice->discount_percentage ? (float)$tierPrice->discount_percentage : null,
            ];
        }

        // 4. Check time-based prices
        $timeBased = $this->findTimeBasedPrice($articleId, $teamId, $salesContextId, $date);
        if ($timeBased) {
            return [
                'price' => (float)$timeBased->gross_price,
                'net_price' => (float)$timeBased->net_price,
                'tax_rate' => (float)$timeBased->tax_rate,
                'price_type' => PriceType::TimeBased->value,
                'source' => 'article_price',
                'source_id' => $timeBased->id,
            ];
        }

        // 5. Check standard context price
        if ($salesContextId) {
            $contextPrice = $this->findContextPrice($articleId, $teamId, $salesContextId);
            if ($contextPrice) {
                return [
                    'price' => (float)$contextPrice->gross_price,
                    'net_price' => (float)$contextPrice->net_price,
                    'tax_rate' => (float)$contextPrice->tax_rate,
                    'price_type' => PriceType::Standard->value,
                    'source' => 'article_price',
                    'source_id' => $contextPrice->id,
                ];
            }
        }

        // 6. Fallback: article base price
        $article = CommerceArticle::where('id', $articleId)
            ->where('team_id', $teamId)
            ->first();

        if (!$article) {
            return ['price' => null, 'error' => 'Article not found'];
        }

        return [
            'price' => (float)$article->price,
            'net_price' => null,
            'tax_rate' => null,
            'price_type' => PriceType::Standard->value,
            'source' => 'article',
            'source_id' => $article->id,
        ];
    }

    protected function findPromotionalPrice(int $articleId, int $teamId, \DateTimeInterface $date): ?CommerceArticlePrice
    {
        return CommerceArticlePrice::query()
            ->where('commerce_article_id', $articleId)
            ->where('team_id', $teamId)
            ->where('price_type', PriceType::Promotional->value)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $date);
            })
            ->orderByDesc('id')
            ->first();
    }

    protected function findCustomerGroupPrice(int $articleId, int $teamId, int $customerGroupId): ?CommerceCustomerGroupPrice
    {
        return CommerceCustomerGroupPrice::query()
            ->where('commerce_article_id', $articleId)
            ->where('team_id', $teamId)
            ->where('commerce_customer_group_id', $customerGroupId)
            ->orderByDesc('id')
            ->first();
    }

    protected function findTierPrice(int $articleId, int $teamId, float $quantity): ?CommercePriceTier
    {
        return CommercePriceTier::query()
            ->where('commerce_article_id', $articleId)
            ->where('team_id', $teamId)
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
            })
            ->whereHas('priceList', function ($q) {
                $q->where('is_active', true);
            })
            ->orderByDesc('min_quantity')
            ->first();
    }

    protected function findTimeBasedPrice(int $articleId, int $teamId, ?int $salesContextId, \DateTimeInterface $date): ?CommerceArticlePrice
    {
        $q = CommerceArticlePrice::query()
            ->where('commerce_article_id', $articleId)
            ->where('team_id', $teamId)
            ->where('price_type', PriceType::TimeBased->value)
            ->where('valid_from', '<=', $date)
            ->where('valid_until', '>=', $date);

        if ($salesContextId) {
            $q->where('commerce_sales_context_id', $salesContextId);
        }

        return $q->orderByDesc('id')->first();
    }

    protected function findContextPrice(int $articleId, int $teamId, int $salesContextId): ?CommerceArticlePrice
    {
        return CommerceArticlePrice::query()
            ->where('commerce_article_id', $articleId)
            ->where('team_id', $teamId)
            ->where('commerce_sales_context_id', $salesContextId)
            ->where('price_type', PriceType::Standard->value)
            ->orderByDesc('id')
            ->first();
    }
}
