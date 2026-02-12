<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProductSlot;
use Platform\Commerce\Models\CommerceProductSlotDimension;
use Platform\Commerce\Models\CommerceProductSlotDimensionValue;

/**
 * Erstellt einen neuen Value für eine Dimension.
 *
 * KONZEPT: Values sind die konkreten Ausprägungen.
 * Z.B. Dimension "Größe" bekommt Values "S", "M", "L".
 */
class CreateProductSlotDimensionValueTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_dimension_values.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/product-slot-dimension-values - Erstellt einen Value für eine Dimension. Z.B. "S", "M", "L" für Dimension "Größe".';
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
                'commerce_product_slot_dimension_id' => [
                    'type' => 'integer',
                    'description' => 'ID der Dimension (ERFORDERLICH). Nutze commerce.product_slot_dimensions.GET.',
                ],
                'value' => [
                    'type' => 'string',
                    'description' => 'Der Wert (ERFORDERLICH). Z.B. "S", "M", "L", "Rot", "Blau".',
                ],
            ],
            'required' => ['commerce_product_slot_dimension_id', 'value'],
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

            $dimId = $arguments['commerce_product_slot_dimension_id'] ?? null;
            if (!$dimId) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_product_slot_dimension_id ist erforderlich.');
            }

            $dimension = CommerceProductSlotDimension::find((int)$dimId);
            if (!$dimension) {
                return ToolResult::error('NOT_FOUND', 'Dimension nicht gefunden.');
            }

            // Validate team ownership via slot
            $slot = CommerceProductSlot::find($dimension->commerce_product_slot_id);
            if (!$slot || (int)$slot->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Dimension gehört nicht zum angegebenen Team.');
            }

            $value = trim((string)($arguments['value'] ?? ''));
            if ($value === '') {
                return ToolResult::error('VALIDATION_ERROR', 'value ist erforderlich.');
            }

            $dimValue = CommerceProductSlotDimensionValue::create([
                'commerce_product_slot_dimension_id' => $dimension->id,
                'value' => $value,
            ]);

            return ToolResult::success([
                'id' => $dimValue->id,
                'commerce_product_slot_dimension_id' => $dimValue->commerce_product_slot_dimension_id,
                'value' => $dimValue->value,
                'dimension_name' => $dimension->name,
                'slot_name' => $slot->name,
                'message' => "Value '{$value}' erfolgreich zu Dimension '{$dimension->name}' hinzugefügt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Values: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_slots', 'dimensions', 'values', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
