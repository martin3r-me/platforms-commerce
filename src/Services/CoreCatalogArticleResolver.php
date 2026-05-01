<?php

namespace Platform\Commerce\Services;

use Platform\Commerce\Models\CommerceProduct;
use Platform\Core\Contracts\CatalogArticleResolverInterface;

class CoreCatalogArticleResolver implements CatalogArticleResolverInterface
{
    public function resolve(int $productId, int $teamId): ?array
    {
        $product = CommerceProduct::with(['article.category', 'article.taxCategory', 'article.salesUnit'])
            ->whereHas('article', fn ($q) => $q->where('team_id', $teamId))
            ->find($productId);

        if (!$product || !$product->article) {
            return null;
        }

        $article = $product->article;

        $netPrice = $article->articleNetPrices()
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->orderByDesc('valid_from')
            ->value('net_price');

        $taxRate = $article->taxCategory?->default_rate;
        $mwst = $taxRate !== null ? ((int) $taxRate) . '%' : null;

        // Effektiver VK: Article-Basispreis + Product-Deviation
        $basePrice = (float) ($article->price ?? 0);
        $vk = match ($product->price_deviation_type) {
            'relative' => $basePrice * (1 + ((float) ($product->price_deviation_value ?? 0) / 100)),
            default    => $basePrice + (float) ($product->price_deviation_value ?? 0),
        };

        return [
            'id'               => $product->id,
            'name'             => $article->name,
            'category_name'    => $article->category?->name,
            'description'      => $article->description,
            'offer_text'       => $article->short_description,
            'gebinde'          => $article->salesUnit?->name,
            'ek'               => (float) ($netPrice ?? 0),
            'vk'               => round($vk, 2),
            'mwst'             => $mwst,
            'procurement_type' => $article->procurement_type,
        ];
    }
}
