<?php

namespace Platform\Commerce\Flynk;

use Illuminate\Support\Str;
use Platform\Commerce\Models\CommerceCatalog;
use Platform\FlynkConnector\Contracts\ProvidesFlynkContext;
use Platform\Organization\Models\OrganizationEntity;
use Platform\Organization\Services\EntityDimensionBridge;

/**
 * FLYNK-Kontext-Lieferant für die Leistungen (services[]).
 *
 * Kataloge hängen via Morph-Alias 'commerce_catalog' am Knoten — oft am
 * Kunden-Knoten, von dem die Websites erben. Darum läuft resolveCatalogs()
 * den Baum hoch bis zur ersten Ebene mit Katalogen und sammelt deren Produkte.
 *
 * Kette: Katalog → Sektionen → Produkt-Boards → Slots → Produkte.
 *
 * Vertrag: context['commerce'] = { services: [ {name, description, benefits[], price, url}, … ] }.
 * Präsenz-Prüfung im Connector; die Leistungen-Sektion baut FLYNK ab zwei Einträgen.
 */
class CommerceFlynkContextProvider implements ProvidesFlynkContext
{
    private const MAX_DEPTH = 10;

    public function contextKey(): string
    {
        return 'commerce';
    }

    public function contextForEntity(OrganizationEntity $node): ?array
    {
        $catalogs = $this->resolveCatalogs($node);
        if ($catalogs->isEmpty()) {
            return null;
        }

        $services = $catalogs
            ->flatMap(fn (CommerceCatalog $catalog) => $this->services($catalog))
            ->values()
            ->all();

        return $services ? ['services' => $services] : null;
    }

    /**
     * Erste Baum-Ebene (Knoten oder Vorfahr), an der Kataloge hängen.
     *
     * @return \Illuminate\Support\Collection<int, CommerceCatalog>
     */
    protected function resolveCatalogs(OrganizationEntity $node)
    {
        $current = $node;
        $depth = 0;

        while ($current && $depth < self::MAX_DEPTH) {
            $ids = EntityDimensionBridge::linksForEntity($current->id)
                ->filter(fn ($l) => $l->linkable_type === 'commerce_catalog')
                ->pluck('linkable_id')
                ->unique();

            if ($ids->isNotEmpty()) {
                return CommerceCatalog::whereIn('id', $ids)
                    ->with('sections.productBoards.productBoardSlots.products.article')
                    ->get();
            }

            $current = $current->parent;
            $depth++;
        }

        return collect();
    }

    /** Baut die services[]-Einträge eines Katalogs aus seinen Produkten. */
    protected function services(CommerceCatalog $catalog): \Illuminate\Support\Collection
    {
        return $catalog->sections
            ->flatMap(fn ($section) => $section->productBoards)
            ->flatMap(fn ($board) => $board->productBoardSlots)
            ->flatMap(fn ($slot) => $slot->products)
            ->map(function ($product) {
                $article = $product->article;

                $name = $product->name ?: $article?->name;
                if (! $name) {
                    return null;
                }

                $description = $product->description
                    ?: $article?->short_description
                    ?: $article?->description;

                // benefits: product_highlights liegen am Article (JSON-Array).
                $benefits = array_values(array_filter((array) ($article?->product_highlights ?? [])));

                // Preis nur wenn numerisch gesetzt — kein "auf Anfrage"-Flag im Modell.
                $price = $product->price ?? $product->selling_price;
                $slug = $product->slug ?: Str::slug((string) $name);

                return array_filter([
                    'name'        => $name,
                    'description' => $description,
                    'benefits'    => $benefits,
                    'price'       => $price !== null ? (string) $price : null,
                    'url'         => $slug ? '/'.ltrim($slug, '/') : null,
                ], fn ($v) => $v !== null && $v !== '' && $v !== []);
            })
            ->filter();
    }
}
