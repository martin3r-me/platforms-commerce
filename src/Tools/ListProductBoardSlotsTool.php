<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceProductBoardSlot;

/**
 * Listet Product-Board-Slots für ein Team.
 */
class ListProductBoardSlotsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.product_board_slots.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/product-board-slots - Listet Product-Board-Slots. Kann nach commerce_product_board_id gefiltert werden. Unterstützt filters/search/sort/limit/offset.';
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
                    'commerce_product_board_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Product-Board ID.',
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

            $q = CommerceProductBoardSlot::query()
                ->where('team_id', $team->id);

            if (array_key_exists('commerce_product_board_id', $arguments) && $arguments['commerce_product_board_id'] !== null && $arguments['commerce_product_board_id'] !== '') {
                $q->where('commerce_product_board_id', (int)$arguments['commerce_product_board_id']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'commerce_product_board_id', 'name', 'created_at']);
            $this->applyStandardSearch($q, $arguments, ['name', 'description']);
            $this->applyStandardSort($q, $arguments, ['name', 'order', 'id', 'created_at'], 'order', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($slot) => [
                'id' => $slot->id,
                'uuid' => $slot->uuid,
                'commerce_product_board_id' => $slot->commerce_product_board_id,
                'name' => $slot->name,
                'order' => $slot->order,
                'description' => $slot->description,
                'color' => $slot->color,
                'user_id' => $slot->user_id,
                'team_id' => $slot->team_id,
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Product-Board-Slots: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'product_board_slots', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
