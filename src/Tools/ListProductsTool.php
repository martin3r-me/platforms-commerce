<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceProduct;

/**
 * Listet Produkte für ein Team.
 */
class ListProductsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.products.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/products - Listet Produkte (id, uuid, name, description, price). Nutze dieses Tool bevor du product_id setzt. Unterstützt filters/search/sort/limit/offset.';
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
                    'commerce_product_board_slot_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Board-Slot-ID.',
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

            $q = CommerceProduct::query()
                ->where('team_id', $team->id);

            if (array_key_exists('commerce_product_board_slot_id', $arguments) && $arguments['commerce_product_board_slot_id'] !== null) {
                $q->where('commerce_product_board_slot_id', (int)$arguments['commerce_product_board_slot_id']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'name', 'price', 'commerce_product_board_slot_id', 'created_at']);
            $this->applyStandardSearch($q, $arguments, ['name', 'description']);
            $this->applyStandardSort($q, $arguments, ['name', 'price', 'order', 'id', 'created_at'], 'name', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($product) => [
                'id' => $product->id,
                'uuid' => $product->uuid,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'commerce_product_board_slot_id' => $product->commerce_product_board_slot_id,
                'price_deviation_type' => $product->price_deviation_type,
                'price_deviation_value' => $product->price_deviation_value,
                'order' => $product->order,
                'team_id' => $product->team_id,
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Produkte: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'products', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
