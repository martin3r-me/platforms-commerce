<?php

namespace Platform\Commerce\Services;

use Platform\Commerce\Enums\CatalogStatus;
use Platform\Commerce\Models\CommerceCatalog;
use Platform\Core\Contracts\CatalogListProviderInterface;

class CoreCatalogListProvider implements CatalogListProviderInterface
{
    public function list(int $teamId): array
    {
        return CommerceCatalog::where('team_id', $teamId)
            ->where('status', CatalogStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->all();
    }
}
