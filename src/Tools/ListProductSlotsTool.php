<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceProductSlot;

/**
 * Listet Product-Slots (Dimensions-Sets) für ein Team.
 *
 * KONZEPT: Ein ProductSlot ist ein "Dimensions-Set" - z.B. "Größe & Farbe".
 * Ein Slot enthält mehrere Dimensions (z.B. "Größe", "Farbe").
 * Jede Dimension hat Values (z.B. "S", "M", "L" oder "Rot", "Blau").
 * Produkte können mit Slots verknüpft werden, um Varianten anzubieten.
 */
class ListProductSlotsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.product_slots.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/product-slots - Listet Product-Slots (Dimensions-Sets). Ein Slot definiert Varianten-Dimensionen wie "Größe & Farbe". Nutze dies vor dem Erstellen/Verknüpfen von Slots.';
    }

    public function getSchema(): array
    {
        return $this->mergeSchemas(
            $this->getStandardGetSchema(),
            [
                'properties' => [
                    'team_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Team-ID. Default: Team aus Kontext.',
                    ],
                    'active' => [
                        'type' => 'boolean',
                        'description' => 'Optional: Filter nach aktiv/inaktiv.',
                    ],
                    'include_dimensions' => [
                        'type' => 'boolean',
                        'description' => 'Optional: Dimensions und Values mit laden. Default: false.',
                    ],
                ],
            ]
        );
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

            $q = CommerceProductSlot::query()
                ->where('team_id', $team->id);

            if (array_key_exists('active', $arguments) && $arguments['active'] !== null) {
                $q->where('active', (bool)$arguments['active']);
            }

            $includeDimensions = (bool)($arguments['include_dimensions'] ?? false);
            if ($includeDimensions) {
                $q->with(['dimensions.values']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'name', 'active', 'required', 'created_at']);
            $this->applyStandardSearch($q, $arguments, ['name', 'description']);
            $this->applyStandardSort($q, $arguments, ['name', 'order', 'id', 'created_at'], 'order', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(function ($slot) use ($includeDimensions) {
                $data = [
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
                ];

                if ($includeDimensions && $slot->relationLoaded('dimensions')) {
                    $data['dimensions'] = $slot->dimensions->map(fn ($dim) => [
                        'id' => $dim->id,
                        'name' => $dim->name,
                        'values' => $dim->relationLoaded('values')
                            ? $dim->values->map(fn ($val) => [
                                'id' => $val->id,
                                'value' => $val->value,
                            ])->values()->toArray()
                            : [],
                    ])->values()->toArray();
                }

                return $data;
            })->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Product-Slots: ' . $e->getMessage());
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
