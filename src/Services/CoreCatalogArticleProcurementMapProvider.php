<?php

namespace Platform\Commerce\Services;

use Platform\Commerce\Models\CommerceArticle;
use Platform\Core\Contracts\CatalogArticleProcurementMapProviderInterface;

class CoreCatalogArticleProcurementMapProvider implements CatalogArticleProcurementMapProviderInterface
{
    public function buildMap(int $teamId): array
    {
        return CommerceArticle::where('team_id', $teamId)
            ->whereNotNull('procurement_type')
            ->select('name', 'procurement_type')
            ->get()
            ->mapWithKeys(fn ($a) => [
                mb_strtolower(trim((string) $a->name)) => $a->procurement_type,
            ])
            ->all();
    }
}
