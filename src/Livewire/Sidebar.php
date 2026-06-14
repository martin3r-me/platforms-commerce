<?php

/**
 * Commerce Sidebar Livewire Component
 *
 * Modul-spezifische Sidebar für Commerce.
 *
 * WICHTIG FÜR LLMs:
 * - Wird automatisch in der Haupt-Sidebar eingebunden
 * - Zeigt modul-spezifische Navigation
 * - Zeigt Kataloge gruppiert nach Entity-Type (analog Planner-Sidebar für Projekte)
 */

namespace Platform\Commerce\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceCatalog;
use Platform\Organization\Services\EntityAncestorService;
use Platform\Organization\Services\EntityDimensionBridge;
use Platform\Organization\Models\OrganizationEntity;

class Sidebar extends Component
{
    public function render()
    {
        $user = auth()->user();
        $teamId = $user?->currentTeam->id ?? null;

        if (!$user || !$teamId) {
            return view('commerce::livewire.sidebar', [
                'catalogEntityTypeGroups' => collect(),
                'unlinkedCatalogs' => collect(),
            ]);
        }

        // 1. Kataloge des Teams laden
        $catalogs = CommerceCatalog::query()
            ->where('team_id', $teamId)
            ->orderBy('name')
            ->get();

        if ($catalogs->isEmpty()) {
            return view('commerce::livewire.sidebar', [
                'catalogEntityTypeGroups' => collect(),
                'unlinkedCatalogs' => collect(),
            ]);
        }

        // 2. Entity-Verknüpfungen via DimensionLink laden
        $catalogIds = $catalogs->pluck('id')->toArray();

        $entityCatalogMap = []; // entity_id => [catalog_ids]
        $linkedCatalogIds = [];

        $contextMorphTypes = ['commerce_catalog'];
        $entityLinks = EntityDimensionBridge::linksForLinkables($contextMorphTypes, $catalogIds);

        foreach ($entityLinks as $link) {
            $entityId = $link->entity_id;
            $catalogId = $link->linkable_id;
            $entityCatalogMap[$entityId][] = $catalogId;
            $linkedCatalogIds[] = $catalogId;
        }

        foreach ($entityCatalogMap as $entityId => $cids) {
            $entityCatalogMap[$entityId] = array_unique($cids);
        }
        $linkedCatalogIds = array_unique($linkedCatalogIds);

        // 3. Aufwärts-Traversierung (Tree-Parents + Channel-Targets) — analog Planner
        $ancestorService = new EntityAncestorService();
        $directEntityIds = array_keys($entityCatalogMap);
        $expandedEntityIds = $ancestorService->expandEntitiesWithAncestors(
            $directEntityIds,
            ['engagement_with']
        );

        foreach ($expandedEntityIds as $entityId) {
            if (!isset($entityCatalogMap[$entityId])) {
                $entityCatalogMap[$entityId] = [];
            }
        }

        // 4. EntityType → Entity-Baum → Kataloge
        $catalogEntityTypeGroups = collect();

        $entityIds = array_keys($entityCatalogMap);
        if (!empty($entityIds)) {
            $entities = OrganizationEntity::with('type')
                ->whereIn('id', $entityIds)
                ->get()
                ->keyBy('id');

            $hierarchy = $ancestorService->buildParentChildrenMap($entities, ['engagement_with']);
            $entityChildrenMap = $hierarchy['parent_to_children'];
            $rootEntityIds = $hierarchy['roots'];

            // Rekursiver Baum-Builder — ein Knoten mit Katalog-Liste, Kindern, Gesamtzahl
            $buildTree = function (int $entityId) use (&$buildTree, $entities, $entityChildrenMap, $entityCatalogMap, $catalogs): ?array {
                $entity = $entities->get($entityId);
                if (!$entity) {
                    return null;
                }

                $childIds = $entityChildrenMap[$entityId] ?? [];
                $childNodes = collect($childIds)
                    ->map(fn ($childId) => $buildTree($childId))
                    ->filter();

                $childrenByType = $childNodes
                    ->groupBy(fn ($child) => $child['type_id'])
                    ->map(function ($group) use ($entities) {
                        $firstChild = $group->first();
                        $typeEntity = $entities->get($firstChild['entity_id']);
                        $type = $typeEntity?->type;

                        return [
                            'type_id' => $firstChild['type_id'],
                            'type_name' => $type?->name ?? 'Sonstige',
                            'type_icon' => $type?->icon ?? null,
                            'sort_order' => $type?->sort_order ?? 999,
                            'children' => $group->sortBy('entity_name')->values(),
                        ];
                    })
                    ->sortBy('sort_order')
                    ->values();

                $catalogList = collect($entityCatalogMap[$entityId] ?? [])
                    ->map(fn ($cid) => $catalogs->firstWhere('id', $cid))
                    ->filter()
                    ->values();

                $totalCatalogs = $catalogList->count();
                foreach ($childNodes as $child) {
                    $totalCatalogs += $child['total_catalogs'];
                }

                if ($totalCatalogs === 0) {
                    return null;
                }

                return [
                    'entity_id' => $entityId,
                    'entity_name' => $entity->name,
                    'type_id' => $entity->type?->id,
                    'catalogs' => $catalogList,
                    'children_by_type' => $childrenByType,
                    'total_catalogs' => $totalCatalogs,
                ];
            };

            $groupedByType = [];
            foreach ($rootEntityIds as $entityId) {
                $entity = $entities->get($entityId);
                if (!$entity || !$entity->type) {
                    continue;
                }

                $tree = $buildTree($entityId);
                if (!$tree) {
                    continue;
                }

                $typeId = $entity->type->id;
                if (!isset($groupedByType[$typeId])) {
                    $groupedByType[$typeId] = [
                        'type_id' => $typeId,
                        'type_name' => $entity->type->name,
                        'type_icon' => $entity->type->icon,
                        'sort_order' => $entity->type->sort_order ?? 999,
                        'entities' => [],
                    ];
                }
                $groupedByType[$typeId]['entities'][] = $tree;
            }

            $catalogEntityTypeGroups = collect($groupedByType)
                ->sortBy('sort_order')
                ->map(function ($group) {
                    $group['entities'] = collect($group['entities'])
                        ->sortBy('entity_name')
                        ->values();
                    return $group;
                })
                ->values();
        }

        // 5. Unverknüpfte Kataloge
        $unlinkedCatalogs = $catalogs->filter(function ($catalog) use ($linkedCatalogIds) {
            return !in_array($catalog->id, $linkedCatalogIds);
        })->values();

        return view('commerce::livewire.sidebar', [
            'catalogEntityTypeGroups' => $catalogEntityTypeGroups,
            'unlinkedCatalogs' => $unlinkedCatalogs,
        ]);
    }
}
