<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProductSlot;
use Platform\Commerce\Models\CommerceProductSlotVariant;

/**
 * Listet Variants eines Product-Slots.
 *
 * KONZEPT: Eine Variant verknüpft einen Artikel mit einer bestimmten Dimensions-Kombination.
 * Z.B. Artikel "T-Shirt Blau M" ist die Variant für Größe=M + Farbe=Blau.
 */
class ListProductSlotVariantsTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_variants.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/product-slot-variants - Listet Variants eines Slots. Eine Variant verknüpft einen Artikel mit Dimensions-Werten (z.B. Größe=M, Farbe=Blau). Parameter: commerce_product_slot_id (ERFORDERLICH).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Team-ID. Default: Team aus Kontext.',
                ],
                'commerce_product_slot_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Product-Slots (ERFORDERLICH). Nutze commerce.product_slots.GET.',
                ],
                'include_article' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Artikel-Daten mit laden. Default: true.',
                ],
                'include_dimension_values' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Dimensions-Values mit laden. Default: true.',
                ],
            ],
            'required' => ['commerce_product_slot_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $teamId = $arguments['team_id'] ?? $context->team?->id;
            if ($teamId === 0 || $teamId === '0') {
                $teamId = null;
            }
            if (!$teamId) {
                return ToolResult::error('MISSING_TEAM', 'Kein Team angegeben und kein Team im Kontext gefunden.');
            }

            $team = Team::find((int)$teamId);
            if (!$team) {
                return ToolResult::error('TEAM_NOT_FOUND', 'Team nicht gefunden.');
            }

            if (!$context->user) {
                return ToolResult::error('AUTH_ERROR', 'Kein User im Kontext gefunden.');
            }
            $userHasAccess = $context->user->teams()->where('teams.id', $team->id)->exists();
            if (!$userHasAccess) {
                return ToolResult::error('ACCESS_DENIED', 'Du hast keinen Zugriff auf dieses Team.');
            }

            $slotId = $arguments['commerce_product_slot_id'] ?? null;
            if (!$slotId) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_product_slot_id ist erforderlich.');
            }

            $slot = CommerceProductSlot::find((int)$slotId);
            if (!$slot) {
                return ToolResult::error('NOT_FOUND', 'Product-Slot nicht gefunden.');
            }
            if ((int)$slot->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Product-Slot gehört nicht zum angegebenen Team.');
            }

            $includeArticle = (bool)($arguments['include_article'] ?? true);
            $includeDimensionValues = (bool)($arguments['include_dimension_values'] ?? true);

            $q = CommerceProductSlotVariant::query()
                ->where('commerce_product_slot_id', $slot->id);

            $with = [];
            if ($includeArticle) {
                $with[] = 'article';
            }
            if ($includeDimensionValues) {
                $with[] = 'dimensionValues.dimensionValue.dimension';
            }
            if (!empty($with)) {
                $q->with($with);
            }

            $variants = $q->get();

            $items = $variants->map(function ($variant) use ($includeArticle, $includeDimensionValues) {
                $data = [
                    'id' => $variant->id,
                    'commerce_product_slot_id' => $variant->commerce_product_slot_id,
                    'commerce_article_id' => $variant->commerce_article_id,
                    'variant_name' => $variant->variant_name,
                ];

                if ($includeArticle && $variant->relationLoaded('article') && $variant->article) {
                    $data['article'] = [
                        'id' => $variant->article->id,
                        'name' => $variant->article->name,
                        'sku' => $variant->article->sku,
                        'price' => $variant->article->price,
                    ];
                }

                if ($includeDimensionValues && $variant->relationLoaded('dimensionValues')) {
                    $data['dimension_values'] = $variant->dimensionValues->map(function ($dv) {
                        $dimVal = $dv->dimensionValue;
                        return [
                            'id' => $dv->id,
                            'dimension_value_id' => $dimVal?->id,
                            'dimension_name' => $dimVal?->dimension?->name,
                            'value' => $dimVal?->value,
                        ];
                    })->values()->toArray();
                }

                return $data;
            })->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'commerce_product_slot_id' => $slot->id,
                'slot_name' => $slot->name,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Variants: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'product_slots', 'variants', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
