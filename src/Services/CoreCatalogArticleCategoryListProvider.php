<?php

namespace Platform\Commerce\Services;

use Platform\Commerce\Models\CommerceArticleCategory;
use Platform\Core\Contracts\CatalogArticleCategoryListProviderInterface;

class CoreCatalogArticleCategoryListProvider implements CatalogArticleCategoryListProviderInterface
{
    public function list(int $teamId): array
    {
        return CommerceArticleCategory::where('team_id', $teamId)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
