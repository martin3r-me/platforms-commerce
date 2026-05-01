<?php

namespace Platform\Commerce\Services;

use Illuminate\Support\Collection;
use Platform\Commerce\Models\CommerceArticle;
use Platform\Core\Contracts\CatalogArticleSearchProviderInterface;

class CoreCatalogArticleSearchProvider implements CatalogArticleSearchProviderInterface
{
    public function search(int $teamId, string $query, int $limit = 20): Collection
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return collect();
        }

        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query);
        $like = '%' . $escaped . '%';
        $prefixLike = $escaped . '%';

        return CommerceArticle::where('team_id', $teamId)
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like);
            })
            ->orderByRaw(
                'CASE WHEN name LIKE ? THEN 0 WHEN sku LIKE ? THEN 1 ELSE 2 END',
                [$prefixLike, $prefixLike]
            )
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function (CommerceArticle $a) {
                $netPrice = $a->articleNetPrices()
                    ->where(function ($q) {
                        $q->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', now());
                    })
                    ->orderByDesc('valid_from')
                    ->value('net_price');

                $taxRate = $a->taxCategory?->default_rate;
                $mwst = $taxRate !== null ? ((int) $taxRate) . '%' : null;

                return [
                    'id'             => $a->id,
                    'article_number' => $a->sku,
                    'name'           => $a->name,
                    'gebinde'        => $a->salesUnit?->name,
                    'ek'             => (float) ($netPrice ?? 0),
                    'vk'             => (float) ($a->price ?? 0),
                    'mwst'           => $mwst,
                ];
            });
    }
}
