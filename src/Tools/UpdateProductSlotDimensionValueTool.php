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
 * Aktualisiert einen bestehenden Dimension-Value.
 */
class UpdateProductSlotDimensionValueTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_dimension_values.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/product-slot-dimension-values/{id} - Aktualisiert einen Value. Nutze commerce.product_slot_dimension_values.GET um die ID zu finden.';
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
                    'description' => 'ID des Values (ERFORDERLICH).',
                ],
                'value' => [
                    'type' => 'string',
                    'description' => 'Neuer Wert.',
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

            $update = [];

            if (array_key_exists('value', $arguments)) {
                $value = trim((string)($arguments['value'] ?? ''));
                if ($value === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'value darf nicht leer sein.');
                }
                $update['value'] = $value;
            }

            if (!empty($update)) {
                $dimValue->update($update);
            }
            $dimValue->refresh();

            return ToolResult::success([
                'id' => $dimValue->id,
                'commerce_product_slot_dimension_id' => $dimValue->commerce_product_slot_dimension_id,
                'value' => $dimValue->value,
                'dimension_name' => $dimension->name,
                'slot_name' => $slot->name,
                'message' => 'Value erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Values: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_slots', 'dimensions', 'values', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
