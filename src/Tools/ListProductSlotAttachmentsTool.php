<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProduct;

/**
 * Listet alle Slots, die mit einem Produkt verknüpft sind.
 */
class ListProductSlotAttachmentsTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.products.slots.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/products/{id}/slots - Listet alle Slots eines Produkts mit Dimensions und Values. Parameter: product_id (ERFORDERLICH).';
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
                'product_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Produkts (ERFORDERLICH).',
                ],
                'include_dimensions' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Dimensions und Values mit laden. Default: true.',
                ],
                'include_variants' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Variants mit laden. Default: false.',
                ],
            ],
            'required' => ['product_id'],
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

            $productId = $arguments['product_id'] ?? null;
            if (!$productId) {
                return ToolResult::error('VALIDATION_ERROR', 'product_id ist erforderlich.');
            }

            $product = CommerceProduct::find((int)$productId);
            if (!$product) {
                return ToolResult::error('NOT_FOUND', 'Produkt nicht gefunden.');
            }
            if ((int)$product->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Produkt gehört nicht zum angegebenen Team.');
            }

            $includeDimensions = (bool)($arguments['include_dimensions'] ?? true);
            $includeVariants = (bool)($arguments['include_variants'] ?? false);

            $with = [];
            if ($includeDimensions) {
                $with[] = 'dimensions.values';
            }
            if ($includeVariants) {
                $with[] = 'variants.article';
                $with[] = 'variants.dimensionValues.dimensionValue.dimension';
            }

            $slots = $product->productSlots();
            if (!empty($with)) {
                $slots->with($with);
            }
            $slots = $slots->get();

            $items = $slots->map(function ($slot) use ($includeDimensions, $includeVariants) {
                $data = [
                    'id' => $slot->id,
                    'name' => $slot->name,
                    'description' => $slot->description,
                    'required' => (bool)$slot->required,
                    'multi_select' => (bool)$slot->multi_select,
                    'active' => (bool)$slot->active,
                ];

                if ($includeDimensions && $slot->relationLoaded('dimensions')) {
                    $data['dimensions'] = $slot->dimensions->map(fn ($dim) => [
                        'id' => $dim->id,
                        'name' => $dim->name,
                        'values' => $dim->relationLoaded('values')
                            ? $dim->values->map(fn ($val) => [
                                'id' => $val->id,
                                'value' => $val->value,
                            ])->values()->toArray()
                            : [],
                    ])->values()->toArray();
                }

                if ($includeVariants && $slot->relationLoaded('variants')) {
                    $data['variants'] = $slot->variants->map(function ($variant) {
                        $variantData = [
                            'id' => $variant->id,
                            'commerce_article_id' => $variant->commerce_article_id,
                            'variant_name' => $variant->variant_name,
                        ];

                        if ($variant->relationLoaded('article') && $variant->article) {
                            $variantData['article'] = [
                                'id' => $variant->article->id,
                                'name' => $variant->article->name,
                                'sku' => $variant->article->sku,
                                'price' => $variant->article->price,
                            ];
                        }

                        if ($variant->relationLoaded('dimensionValues')) {
                            $variantData['dimension_values'] = $variant->dimensionValues->map(fn ($dv) => [
                                'dimension_name' => $dv->dimensionValue?->dimension?->name,
                                'value' => $dv->dimensionValue?->value,
                            ])->values()->toArray();
                        }

                        return $variantData;
                    })->values()->toArray();
                }

                return $data;
            })->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Slots: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'products', 'product_slots', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
