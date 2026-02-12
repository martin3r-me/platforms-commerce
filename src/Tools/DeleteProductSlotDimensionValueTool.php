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
 * Löscht einen Dimension-Value.
 */
class DeleteProductSlotDimensionValueTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_dimension_values.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /commerce/product-slot-dimension-values/{id} - Löscht einen Value.';
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
                    'description' => 'ID des zu löschenden Values (ERFORDERLICH).',
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

            $valueId = $arguments['id'] ?? null;
            if (!$valueId) {
                return ToolResult::error('VALIDATION_ERROR', 'id ist erforderlich.');
            }

            $dimValue = CommerceProductSlotDimensionValue::find((int)$valueId);
            if (!$dimValue) {
                return ToolResult::error('NOT_FOUND', 'Value nicht gefunden.');
            }

            // Validate team ownership via dimension -> slot
            $dimension = CommerceProductSlotDimension::find($dimValue->commerce_product_slot_dimension_id);
            if (!$dimension) {
                return ToolResult::error('NOT_FOUND', 'Zugehörige Dimension nicht gefunden.');
            }

            $slot = CommerceProductSlot::find($dimension->commerce_product_slot_id);
            if (!$slot || (int)$slot->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Value gehört nicht zum angegebenen Team.');
            }

            $deletedValue = $dimValue->value;
            $dimValue->delete();

            return ToolResult::success([
                'deleted_id' => (int)$valueId,
                'deleted_value' => $deletedValue,
                'dimension_name' => $dimension->name,
                'slot_name' => $slot->name,
                'message' => "Value '{$deletedValue}' erfolgreich gelöscht.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Löschen des Values: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_slots', 'dimensions', 'values', 'delete'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'destructive',
            'idempotent' => false,
        ];
    }
}
