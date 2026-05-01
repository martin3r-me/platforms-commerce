<?php

namespace Platform\Commerce\Services;

use Illuminate\Support\Collection;
use Platform\Commerce\Models\CommerceProduct;
use Platform\Core\Contracts\CatalogArticleSearchProviderInterface;

class CoreCatalogArticleSearchProvider implements CatalogArticleSearchProviderInterface
{
    public function search(int $teamId, string $query, int $limit = 20, ?int $catalogId = null): Collection
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return collect();
        }

        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query);
        $like = '%' . $escaped . '%';
        $prefixLike = $escaped . '%';

        $builder = CommerceProduct::query()
            ->join('commerce_articles', 'commerce_products.commerce_article_id', '=', 'commerce_articles.id')
            ->where('commerce_articles.team_id', $teamId)
            ->whereNotNull('commerce_products.commerce_article_id')
            ->where(function ($q) use ($like) {
                $q->where('commerce_articles.name', 'like', $like)
                    ->orWhere('commerce_articles.sku', 'like', $like);
            });

        // Katalog-Filter: Product muss ueber BoardSlot → Board → CatalogSectionBoard im Katalog sein
        if ($catalogId !== null) {
            $builder->whereExists(function ($sub) use ($catalogId) {
                $sub->select(\DB::raw(1))
                    ->from('commerce_product_board_slots')
                    ->join('commerce_catalog_section_boards', 'commerce_catalog_section_boards.commerce_product_board_id', '=', 'commerce_product_board_slots.commerce_product_board_id')
                    ->join('commerce_catalog_sections', 'commerce_catalog_sections.id', '=', 'commerce_catalog_section_boards.commerce_catalog_section_id')
                    ->whereColumn('commerce_product_board_slots.id', 'commerce_products.commerce_product_board_slot_id')
                    ->where('commerce_catalog_sections.commerce_catalog_id', $catalogId);
            });
        }

        return $builder
            ->orderByRaw(
                'CASE WHEN commerce_articles.name LIKE ? THEN 0 WHEN commerce_articles.sku LIKE ? THEN 1 ELSE 2 END',
                [$prefixLike, $prefixLike]
            )
            ->orderBy('commerce_articles.name')
            ->limit($limit)
            ->select('commerce_products.*')
            ->with(['article.taxCategory', 'article.salesUnit'])
            ->get()
            ->map(function (CommerceProduct $product) {
                $article = $product->article;
                if (!$article) return null;

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
                    'id'             => $product->id,
                    'article_number' => $article->sku,
                    'name'           => $article->name,
                    'gebinde'        => $article->salesUnit?->name,
                    'ek'             => (float) ($netPrice ?? 0),
                    'vk'             => round($vk, 2),
                    'mwst'           => $mwst,
                ];
            })
            ->filter();
    }
}
