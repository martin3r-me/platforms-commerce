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
 * Aktualisiert eine bestehende Dimension.
 */
class UpdateProductSlotDimensionTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_dimensions.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/product-slot-dimensions/{id} - Aktualisiert eine Dimension. Nutze commerce.product_slot_dimensions.GET um die ID zu finden.';
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
                    'description' => 'ID der Dimension (ERFORDERLICH).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Neuer Name der Dimension.',
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

            $dimId = $arguments['id'] ?? null;
            if (!$dimId) {
                return ToolResult::error('VALIDATION_ERROR', 'id ist erforderlich.');
            }

            $dimension = CommerceProductSlotDimension::with('slot')->find((int)$dimId);
            if (!$dimension) {
                return ToolResult::error('NOT_FOUND', 'Dimension nicht gefunden.');
            }

            // Validate team ownership via slot
            $slot = CommerceProductSlot::find($dimension->commerce_product_slot_id);
            if (!$slot || (int)$slot->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Dimension gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            if (!empty($update)) {
                $dimension->update($update);
            }
            $dimension->refresh();

            return ToolResult::success([
                'id' => $dimension->id,
                'commerce_product_slot_id' => $dimension->commerce_product_slot_id,
                'name' => $dimension->name,
                'slot_name' => $slot->name,
                'message' => 'Dimension erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Dimension: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_slots', 'dimensions', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
