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
 * Listet Values einer Dimension.
 *
 * KONZEPT: Values sind die konkreten Ausprägungen einer Dimension.
 * Z.B. Dimension "Größe" hat Values "S", "M", "L", "XL".
 */
class ListProductSlotDimensionValuesTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_dimension_values.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/product-slot-dimension-values - Listet Values einer Dimension. Z.B. für Dimension "Größe": "S", "M", "L". Parameter: commerce_product_slot_dimension_id (ERFORDERLICH).';
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
            ],
            'required' => ['commerce_product_slot_dimension_id'],
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

            $values = CommerceProductSlotDimensionValue::query()
                ->where('commerce_product_slot_dimension_id', $dimension->id)
                ->get();

            $items = $values->map(fn ($val) => [
                'id' => $val->id,
                'commerce_product_slot_dimension_id' => $val->commerce_product_slot_dimension_id,
                'value' => $val->value,
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'commerce_product_slot_dimension_id' => $dimension->id,
                'dimension_name' => $dimension->name,
                'slot_name' => $slot->name,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Values: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'product_slots', 'dimensions', 'values', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
