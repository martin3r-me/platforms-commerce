<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProductSlot;

/**
 * Erstellt einen neuen Product-Slot (Dimensions-Set).
 *
 * KONZEPT: Ein ProductSlot ist ein "Dimensions-Set" - z.B. "Größe & Farbe".
 * Nach dem Erstellen füge Dimensions hinzu (commerce.product_slot_dimensions.POST).
 */
class CreateProductSlotTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slots.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/product-slots - Erstellt einen Product-Slot (Dimensions-Set). Ein Slot gruppiert Dimensionen wie "Größe" und "Farbe". Nach Erstellen: Dimensions hinzufügen mit commerce.product_slot_dimensions.POST.';
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
                'name' => [
                    'type' => 'string',
                    'description' => 'Name des Slots (ERFORDERLICH). Z.B. "Größe & Farbe", "Material-Optionen".',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung des Slots.',
                ],
                'required' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Muss eine Auswahl getroffen werden? Default: false.',
                ],
                'multi_select' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Können mehrere Werte gewählt werden? Default: false.',
                ],
                'min_selection' => [
                    'type' => 'integer',
                    'description' => 'Optional: Minimale Anzahl zu wählender Werte.',
                ],
                'max_selection' => [
                    'type' => 'integer',
                    'description' => 'Optional: Maximale Anzahl zu wählender Werte.',
                ],
                'order' => [
                    'type' => 'integer',
                    'description' => 'Optional: Sortierreihenfolge. Default: 0.',
                ],
                'active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Slot aktiv? Default: true.',
                ],
            ],
            'required' => ['name'],
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

            $slot = CommerceProductSlot::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'required' => (bool)($arguments['required'] ?? false),
                'multi_select' => (bool)($arguments['multi_select'] ?? false),
                'min_selection' => array_key_exists('min_selection', $arguments) ? (int)$arguments['min_selection'] : null,
                'max_selection' => array_key_exists('max_selection', $arguments) ? (int)$arguments['max_selection'] : null,
                'order' => (int)($arguments['order'] ?? 0),
                'active' => (bool)($arguments['active'] ?? true),
            ]);

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
                'message' => 'Product-Slot erfolgreich erstellt. Nächster Schritt: Dimensions hinzufügen mit commerce.product_slot_dimensions.POST.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Product-Slots: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_slots', 'dimensions', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
