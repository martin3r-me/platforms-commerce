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
 * Löscht eine Dimension.
 *
 * ACHTUNG: Löscht auch alle zugehörigen Values (Cascade).
 */
class DeleteProductSlotDimensionTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_dimensions.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /commerce/product-slot-dimensions/{id} - Löscht eine Dimension. ACHTUNG: Löscht auch alle Values!';
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
                    'description' => 'ID der zu löschenden Dimension (ERFORDERLICH).',
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

            $dimension = CommerceProductSlotDimension::find((int)$dimId);
            if (!$dimension) {
                return ToolResult::error('NOT_FOUND', 'Dimension nicht gefunden.');
            }

            // Validate team ownership via slot
            $slot = CommerceProductSlot::find($dimension->commerce_product_slot_id);
            if (!$slot || (int)$slot->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Dimension gehört nicht zum angegebenen Team.');
            }

            $dimName = $dimension->name;
            $dimension->delete();

            return ToolResult::success([
                'deleted_id' => (int)$dimId,
                'deleted_name' => $dimName,
                'slot_name' => $slot->name,
                'message' => "Dimension '{$dimName}' erfolgreich gelöscht (inkl. aller Values).",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Löschen der Dimension: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_slots', 'dimensions', 'delete'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'destructive',
            'idempotent' => false,
        ];
    }
}
