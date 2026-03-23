<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceProductBoardSlot;

/**
 * Aktualisiert einen bestehenden Product-Board-Slot.
 */
class UpdateProductBoardSlotTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.product_board_slots.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/product-board-slots/{id} - Aktualisiert einen Product-Board-Slot. Nutze commerce.product_board_slots.GET um die ID zu finden.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Team-ID. Default: Team aus Kontext.',
                ],
                'id' => [
                    'type' => 'integer',
                    'description' => 'ID des Product-Board-Slots (ERFORDERLICH). Nutze commerce.product_board_slots.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name des Slots.',
                ],
                'order' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Reihenfolge.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Farbe ("" zum Leeren).',
                ],
            ],
            'required' => ['id'],
        ]);
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

            $found = $this->validateAndFindModel(
                $arguments,
                $context,
                'id',
                CommerceProductBoardSlot::class,
                'NOT_FOUND',
                'Product-Board-Slot nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceProductBoardSlot $slot */
            $slot = $found['model'];
            if ((int)$slot->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Product-Board-Slot gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            if (array_key_exists('order', $arguments)) {
                $update['order'] = $arguments['order'] !== null ? (int)$arguments['order'] : 0;
            }

            if (array_key_exists('description', $arguments)) {
                $d = (string)($arguments['description'] ?? '');
                $update['description'] = $d === '' ? null : $d;
            }

            if (array_key_exists('color', $arguments)) {
                $c = (string)($arguments['color'] ?? '');
                $update['color'] = $c === '' ? null : $c;
            }

            if (!empty($update)) {
                $slot->update($update);
            }
            $slot->refresh();

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
                'message' => 'Product-Board-Slot erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Product-Board-Slots: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_board_slots', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
