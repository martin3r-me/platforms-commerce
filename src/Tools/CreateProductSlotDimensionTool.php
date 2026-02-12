<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProductSlot;
use Platform\Commerce\Models\CommerceProductSlotDimension;

/**
 * Erstellt eine neue Dimension in einem Product-Slot.
 *
 * KONZEPT: Eine Dimension ist z.B. "Größe" oder "Farbe".
 * Nach dem Erstellen füge Values hinzu (commerce.product_slot_dimension_values.POST).
 */
class CreateProductSlotDimensionTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_dimensions.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/product-slot-dimensions - Erstellt eine Dimension (z.B. "Größe", "Farbe") in einem Slot. Nach Erstellen: Values hinzufügen mit commerce.product_slot_dimension_values.POST.';
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
                'name' => [
                    'type' => 'string',
                    'description' => 'Name der Dimension (ERFORDERLICH). Z.B. "Größe", "Farbe", "Material".',
                ],
            ],
            'required' => ['commerce_product_slot_id', 'name'],
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

            $name = trim((string)($arguments['name'] ?? ''));
            if ($name === '') {
                return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
            }

            $dimension = CommerceProductSlotDimension::create([
                'commerce_product_slot_id' => $slot->id,
                'name' => $name,
            ]);

            return ToolResult::success([
                'id' => $dimension->id,
                'commerce_product_slot_id' => $dimension->commerce_product_slot_id,
                'name' => $dimension->name,
                'slot_name' => $slot->name,
                'message' => "Dimension '{$name}' erfolgreich erstellt. Nächster Schritt: Values hinzufügen mit commerce.product_slot_dimension_values.POST.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Dimension: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_slots', 'dimensions', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
