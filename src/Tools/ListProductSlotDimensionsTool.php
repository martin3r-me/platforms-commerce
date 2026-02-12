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
 * Listet Dimensions eines Product-Slots.
 *
 * KONZEPT: Eine Dimension ist z.B. "Größe" oder "Farbe" innerhalb eines Slots.
 * Jede Dimension hat Values (z.B. "S", "M", "L").
 */
class ListProductSlotDimensionsTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_slot_dimensions.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/product-slot-dimensions - Listet Dimensions eines Slots. Eine Dimension ist z.B. "Größe" oder "Farbe". Parameter: commerce_product_slot_id (ERFORDERLICH).';
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
                'commerce_product_slot_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Product-Slots (ERFORDERLICH). Nutze commerce.product_slots.GET.',
                ],
                'include_values' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Values mit laden. Default: true.',
                ],
            ],
            'required' => ['commerce_product_slot_id'],
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

            $slotId = $arguments['commerce_product_slot_id'] ?? null;
            if (!$slotId) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_product_slot_id ist erforderlich.');
            }

            $slot = CommerceProductSlot::find((int)$slotId);
            if (!$slot) {
                return ToolResult::error('NOT_FOUND', 'Product-Slot nicht gefunden.');
            }
            if ((int)$slot->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Product-Slot gehört nicht zum angegebenen Team.');
            }

            $includeValues = (bool)($arguments['include_values'] ?? true);

            $q = CommerceProductSlotDimension::query()
                ->where('commerce_product_slot_id', $slot->id);

            if ($includeValues) {
                $q->with('values');
            }

            $dimensions = $q->get();

            $items = $dimensions->map(function ($dim) use ($includeValues) {
                $data = [
                    'id' => $dim->id,
                    'commerce_product_slot_id' => $dim->commerce_product_slot_id,
                    'name' => $dim->name,
                ];

                if ($includeValues && $dim->relationLoaded('values')) {
                    $data['values'] = $dim->values->map(fn ($val) => [
                        'id' => $val->id,
                        'value' => $val->value,
                    ])->values()->toArray();
                }

                return $data;
            })->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'commerce_product_slot_id' => $slot->id,
                'slot_name' => $slot->name,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Dimensions: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'product_slots', 'dimensions', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
