<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProductSlot;

/**
 * Löscht einen Product-Slot.
 *
 * ACHTUNG: Löscht auch alle zugehörigen Dimensions, Values und Variants (Cascade).
 */
class DeleteProductSlotTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slots.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /commerce/product-slots/{id} - Löscht einen Product-Slot. ACHTUNG: Löscht auch alle Dimensions, Values und Variants!';
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
                    'description' => 'ID des zu löschenden Product-Slots (ERFORDERLICH).',
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

            $slotId = $arguments['id'] ?? null;
            if (!$slotId) {
                return ToolResult::error('VALIDATION_ERROR', 'id ist erforderlich.');
            }

            $slot = CommerceProductSlot::find((int)$slotId);
            if (!$slot) {
                return ToolResult::error('NOT_FOUND', 'Product-Slot nicht gefunden.');
            }
            if ((int)$slot->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Product-Slot gehört nicht zum angegebenen Team.');
            }

            $slotName = $slot->name;
            $slot->delete();

            return ToolResult::success([
                'deleted_id' => (int)$slotId,
                'deleted_name' => $slotName,
                'message' => "Product-Slot '{$slotName}' erfolgreich gelöscht (inkl. aller Dimensions, Values und Variants).",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Löschen des Product-Slots: ' . $e->getMessage());
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
