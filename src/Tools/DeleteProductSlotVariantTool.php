<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProductSlot;
use Platform\Commerce\Models\CommerceProductSlotVariant;
use Platform\Commerce\Models\CommerceProductSlotVariantDimensionValue;

/**
 * Löscht eine Variant.
 */
class DeleteProductSlotVariantTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_variants.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /commerce/product-slot-variants/{id} - Löscht eine Variant (Artikel-Dimensions-Verknüpfung).';
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
                    'description' => 'ID der zu löschenden Variant (ERFORDERLICH).',
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

            $variant = CommerceProductSlotVariant::with(['dimensionValues.dimensionValue.dimension', 'article'])->find((int)$variantId);
            if (!$variant) {
                return ToolResult::error('NOT_FOUND', 'Variant nicht gefunden.');
            }

            // Validate team ownership via slot
            $slot = CommerceProductSlot::find($variant->commerce_product_slot_id);
            if (!$slot || (int)$slot->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Variant gehört nicht zum angegebenen Team.');
            }

            $variantName = $variant->variant_name;
            $articleName = $variant->article?->name;

            // Delete dimension value links first
            CommerceProductSlotVariantDimensionValue::where('commerce_product_slot_variant_id', $variant->id)->delete();

            // Delete variant
            $variant->delete();

            return ToolResult::success([
                'deleted_id' => (int)$variantId,
                'deleted_variant_name' => $variantName,
                'deleted_article_name' => $articleName,
                'slot_name' => $slot->name,
                'message' => "Variant '{$variantName}' erfolgreich gelöscht.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Löschen der Variant: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_slots', 'variants', 'delete'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'destructive',
            'idempotent' => false,
        ];
    }
}
