<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticle;
use Platform\Commerce\Models\CommerceProductSlot;
use Platform\Commerce\Models\CommerceProductSlotDimensionValue;
use Platform\Commerce\Models\CommerceProductSlotVariant;
use Platform\Commerce\Models\CommerceProductSlotVariantDimensionValue;

/**
 * Aktualisiert eine bestehende Variant.
 */
class UpdateProductSlotVariantTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_variants.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/product-slot-variants/{id} - Aktualisiert eine Variant. Kann Artikel oder Dimension-Values ändern.';
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
                'id' => [
                    'type' => 'integer',
                    'description' => 'ID der Variant (ERFORDERLICH).',
                ],
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Artikel-ID.',
                ],
                'dimension_value_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'integer'],
                    'description' => 'Optional: Neue Dimension-Value-IDs. ERSETZT alle bisherigen!',
                ],
            ],
            'required' => ['id'],
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

            $variantId = $arguments['id'] ?? null;
            if (!$variantId) {
                return ToolResult::error('VALIDATION_ERROR', 'id ist erforderlich.');
            }

            $variant = CommerceProductSlotVariant::find((int)$variantId);
            if (!$variant) {
                return ToolResult::error('NOT_FOUND', 'Variant nicht gefunden.');
            }

            // Validate team ownership via slot
            $slot = CommerceProductSlot::find($variant->commerce_product_slot_id);
            if (!$slot || (int)$slot->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Variant gehört nicht zum angegebenen Team.');
            }

            $update = [];

            // Update article if provided
            if (array_key_exists('commerce_article_id', $arguments)) {
                $articleId = $arguments['commerce_article_id'];
                if ($articleId !== null) {
                    $article = CommerceArticle::find((int)$articleId);
                    if (!$article) {
                        return ToolResult::error('NOT_FOUND', 'Artikel nicht gefunden.');
                    }
                    if ((int)$article->team_id !== (int)$team->id) {
                        return ToolResult::error('ACCESS_DENIED', 'Artikel gehört nicht zum angegebenen Team.');
                    }
                    $update['commerce_article_id'] = $article->id;
                } else {
                    $update['commerce_article_id'] = null;
                }
            }

            if (!empty($update)) {
                $variant->update($update);
            }

            // Update dimension values if provided
            if (array_key_exists('dimension_value_ids', $arguments)) {
                $dimensionValueIds = $arguments['dimension_value_ids'];
                if (!is_array($dimensionValueIds)) {
                    return ToolResult::error('VALIDATION_ERROR', 'dimension_value_ids muss ein Array sein.');
                }

                // Validate all new values
                $validValues = [];
                foreach ($dimensionValueIds as $dvId) {
                    $dimValue = CommerceProductSlotDimensionValue::with('dimension')->find((int)$dvId);
                    if (!$dimValue) {
                        return ToolResult::error('NOT_FOUND', "Dimension-Value mit ID {$dvId} nicht gefunden.");
                    }
                    if (!$dimValue->dimension || (int)$dimValue->dimension->commerce_product_slot_id !== (int)$slot->id) {
                        return ToolResult::error('VALIDATION_ERROR', "Dimension-Value {$dvId} gehört nicht zu diesem Slot.");
                    }
                    $validValues[] = $dimValue;
                }

                // Delete old dimension values
                CommerceProductSlotVariantDimensionValue::where('commerce_product_slot_variant_id', $variant->id)->delete();

                // Create new dimension values
                foreach ($validValues as $dimValue) {
                    CommerceProductSlotVariantDimensionValue::create([
                        'commerce_product_slot_variant_id' => $variant->id,
                        'commerce_product_slot_dimension_value_id' => $dimValue->id,
                    ]);
                }
            }

            // Reload for response
            $variant->refresh();
            $variant->load(['dimensionValues.dimensionValue.dimension', 'article']);

            $dimensionInfo = $variant->dimensionValues->map(fn ($dv) => [
                'dimension' => $dv->dimensionValue?->dimension?->name,
                'value' => $dv->dimensionValue?->value,
            ])->values()->toArray();

            return ToolResult::success([
                'id' => $variant->id,
                'commerce_product_slot_id' => $variant->commerce_product_slot_id,
                'commerce_article_id' => $variant->commerce_article_id,
                'variant_name' => $variant->variant_name,
                'article_name' => $variant->article?->name,
                'article_sku' => $variant->article?->sku,
                'dimensions' => $dimensionInfo,
                'slot_name' => $slot->name,
                'message' => 'Variant erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Variant: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_slots', 'variants', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
