<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProductSlot;

/**
 * Aktualisiert einen bestehenden Product-Slot.
 */
class UpdateProductSlotTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slots.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/product-slots/{id} - Aktualisiert einen Product-Slot. Nutze commerce.product_slots.GET um die ID zu finden.';
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
                    'description' => 'ID des Product-Slots (ERFORDERLICH).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
                'required' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Pflichtauswahl?',
                ],
                'multi_select' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Mehrfachauswahl erlaubt?',
                ],
                'min_selection' => [
                    'type' => 'integer',
                    'description' => 'Optional: Minimale Auswahl (null zum Leeren).',
                ],
                'max_selection' => [
                    'type' => 'integer',
                    'description' => 'Optional: Maximale Auswahl (null zum Leeren).',
                ],
                'order' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Sortierreihenfolge.',
                ],
                'active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Aktiv/Inaktiv setzen.',
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

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            if (array_key_exists('description', $arguments)) {
                $d = (string)($arguments['description'] ?? '');
                $update['description'] = $d === '' ? null : $d;
            }

            if (array_key_exists('required', $arguments)) {
                $update['required'] = (bool)$arguments['required'];
            }

            if (array_key_exists('multi_select', $arguments)) {
                $update['multi_select'] = (bool)$arguments['multi_select'];
            }

            if (array_key_exists('min_selection', $arguments)) {
                $update['min_selection'] = $arguments['min_selection'] !== null ? (int)$arguments['min_selection'] : null;
            }

            if (array_key_exists('max_selection', $arguments)) {
                $update['max_selection'] = $arguments['max_selection'] !== null ? (int)$arguments['max_selection'] : null;
            }

            if (array_key_exists('order', $arguments)) {
                $update['order'] = (int)$arguments['order'];
            }

            if (array_key_exists('active', $arguments)) {
                $update['active'] = (bool)$arguments['active'];
            }

            if (!empty($update)) {
                $slot->update($update);
            }
            $slot->refresh();

            return ToolResult::success([
                'id' => $slot->id,
                'name' => $slot->name,
                'description' => $slot->description,
                'required' => (bool)$slot->required,
                'multi_select' => (bool)$slot->multi_select,
                'min_selection' => $slot->min_selection,
                'max_selection' => $slot->max_selection,
                'order' => $slot->order,
                'active' => (bool)$slot->active,
                'team_id' => $slot->team_id,
                'message' => 'Product-Slot erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Product-Slots: ' . $e->getMessage());
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
