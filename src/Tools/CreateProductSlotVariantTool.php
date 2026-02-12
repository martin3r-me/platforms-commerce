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
 * Erstellt eine neue Variant (verknüpft Artikel mit Dimensions-Kombination).
 *
 * KONZEPT: Eine Variant verknüpft einen Artikel mit spezifischen Dimensions-Werten.
 * Z.B. Artikel "SKU-12345" wird zur Variant für Größe=M + Farbe=Blau.
 */
class CreateProductSlotVariantTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_variants.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/product-slot-variants - Erstellt eine Variant. Verknüpft einen Artikel mit Dimensions-Werten. Benötigt: commerce_product_slot_id, commerce_article_id, dimension_value_ids (Array).';
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
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Artikels (ERFORDERLICH). Nutze commerce.articles.GET.',
                ],
                'dimension_value_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'integer'],
                    'description' => 'Array von Dimension-Value-IDs (ERFORDERLICH). Definiert die Kombination z.B. [Größe=M, Farbe=Blau].',
                ],
            ],
            'required' => ['commerce_product_slot_id', 'commerce_article_id', 'dimension_value_ids'],
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

            // Validate slot
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

            // Validate article
            $articleId = $arguments['commerce_article_id'] ?? null;
            if (!$articleId) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_article_id ist erforderlich.');
            }

            $article = CommerceArticle::find((int)$articleId);
            if (!$article) {
                return ToolResult::error('NOT_FOUND', 'Artikel nicht gefunden.');
            }
            if ((int)$article->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Artikel gehört nicht zum angegebenen Team.');
            }

            // Validate dimension values
            $dimensionValueIds = $arguments['dimension_value_ids'] ?? [];
            if (!is_array($dimensionValueIds) || empty($dimensionValueIds)) {
                return ToolResult::error('VALIDATION_ERROR', 'dimension_value_ids muss ein nicht-leeres Array sein.');
            }

            // Verify all dimension values exist and belong to this slot's dimensions
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

            // Create the variant
            $variant = CommerceProductSlotVariant::create([
                'commerce_product_slot_id' => $slot->id,
                'commerce_article_id' => $article->id,
            ]);

            // Link dimension values
            foreach ($validValues as $dimValue) {
                CommerceProductSlotVariantDimensionValue::create([
                    'commerce_product_slot_variant_id' => $variant->id,
                    'commerce_product_slot_dimension_value_id' => $dimValue->id,
                ]);
            }

            // Load for response
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
                'article_name' => $article->name,
                'article_sku' => $article->sku,
                'dimensions' => $dimensionInfo,
                'slot_name' => $slot->name,
                'message' => "Variant '{$variant->variant_name}' erfolgreich erstellt und mit Artikel '{$article->name}' verknüpft.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Variant: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_slots', 'variants', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
