<?php

namespace Platform\Commerce\Services;

use Platform\Commerce\Models\CommerceArticle;
use Platform\Core\Contracts\CatalogArticleResolverInterface;

class CoreCatalogArticleResolver implements CatalogArticleResolverInterface
{
    public function resolve(int $articleId, int $teamId): ?array
    {
        $article = CommerceArticle::with(['category', 'taxCategory', 'salesUnit'])
            ->where('team_id', $teamId)
            ->find($articleId);

        if (!$article) {
            return null;
        }

        $netPrice = $article->articleNetPrices()
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->orderByDesc('valid_from')
            ->value('net_price');

        $taxRate = $article->taxCategory?->default_rate;
        $mwst = $taxRate !== null ? ((int) $taxRate) . '%' : null;

        return [
            'id'               => $article->id,
            'name'             => $article->name,
            'category_name'    => $article->category?->name,
            'description'      => $article->description,
            'offer_text'       => $article->short_description,
            'gebinde'          => $article->salesUnit?->name,
            'ek'               => (float) ($netPrice ?? 0),
            'vk'               => (float) ($article->price ?? 0),
            'mwst'             => $mwst,
            'procurement_type' => $article->procurement_type,
        ];
    }
}
