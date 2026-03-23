<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProductBoardSlot;
use Platform\Commerce\Models\CommerceProductBoard;

/**
 * Erstellt einen neuen Product-Board-Slot.
 */
class CreateProductBoardSlotTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_board_slots.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/product-board-slots - Erstellt einen neuen Product-Board-Slot. UUID wird automatisch generiert.';
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
                'commerce_product_board_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Product-Boards (ERFORDERLICH).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Name des Slots (ERFORDERLICH).',
                ],
                'order' => [
                    'type' => 'integer',
                    'description' => 'Optional: Reihenfolge des Slots.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung des Slots.',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Farbe des Slots.',
                ],
            ],
            'required' => ['commerce_product_board_id', 'name'],
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

            $name = trim((string)($arguments['name'] ?? ''));
            if ($name === '') {
                return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
            }

            if (!array_key_exists('commerce_product_board_id', $arguments) || $arguments['commerce_product_board_id'] === null || $arguments['commerce_product_board_id'] === '') {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_product_board_id ist erforderlich.');
            }
            $boardId = (int)$arguments['commerce_product_board_id'];
            $boardExists = CommerceProductBoard::query()
                ->where('id', $boardId)
                ->where('team_id', $team->id)
                ->whereNull('deleted_at')
                ->exists();
            if (!$boardExists) {
                return ToolResult::error('VALIDATION_ERROR', "Product-Board mit ID {$boardId} nicht gefunden in diesem Team.");
            }

            $slot = CommerceProductBoardSlot::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'commerce_product_board_id' => $boardId,
                'name' => $name,
                'order' => array_key_exists('order', $arguments) && $arguments['order'] !== null
                    ? (int)$arguments['order']
                    : 0,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'color' => (array_key_exists('color', $arguments) && $arguments['color'] !== '')
                    ? (string)$arguments['color']
                    : null,
            ]);

            return ToolResult::success([
                'id' => $slot->id,
                'uuid' => $slot->uuid,
                'commerce_product_board_id' => $slot->commerce_product_board_id,
                'name' => $slot->name,
                'order' => $slot->order,
                'description' => $slot->description,
                'color' => $slot->color,
                'user_id' => $slot->user_id,
                'team_id' => $slot->team_id,
                'message' => 'Product-Board-Slot erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Product-Board-Slots: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_board_slots', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
