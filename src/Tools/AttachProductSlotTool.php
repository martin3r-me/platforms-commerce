<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProduct;
use Platform\Commerce\Models\CommerceProductSlot;

/**
 * Verknüpft einen Product-Slot mit einem Produkt.
 *
 * KONZEPT: Produkte können mit mehreren Slots verknüpft werden.
 * Dadurch erhält das Produkt Varianten-Optionen (z.B. Größe & Farbe).
 */
class AttachProductSlotTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.products.slots.ATTACH';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/products/{id}/slots/attach - Verknüpft einen Slot mit einem Produkt. Dadurch erhält das Produkt Varianten-Optionen. Benötigt: product_id, commerce_product_slot_id.';
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
                    'description' => 'ID des Produkts (ERFORDERLICH). Nutze commerce.products.GET.',
                ],
                'commerce_product_slot_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Slots (ERFORDERLICH). Nutze commerce.product_slots.GET.',
                ],
            ],
            'required' => ['product_id', 'commerce_product_slot_id'],
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

            // Validate product
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

            // Check if already attached
            $alreadyAttached = $product->productSlots()->where('commerce_product_slot_id', $slot->id)->exists();
            if ($alreadyAttached) {
                return ToolResult::error('ALREADY_EXISTS', "Slot '{$slot->name}' ist bereits mit Produkt '{$product->name}' verknüpft.");
            }

            // Attach
            $product->productSlots()->attach($slot->id);

            // Get all attached slots for response
            $attachedSlots = $product->productSlots()->get()->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
            ])->values()->toArray();

            return ToolResult::success([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'attached_slot_id' => $slot->id,
                'attached_slot_name' => $slot->name,
                'all_attached_slots' => $attachedSlots,
                'message' => "Slot '{$slot->name}' erfolgreich mit Produkt '{$product->name}' verknüpft.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Verknüpfen: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'products', 'product_slots', 'attach'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
