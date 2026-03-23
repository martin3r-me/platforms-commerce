<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceSaleItem;

/**
 * Listet Verkaufspositionen für ein Team.
 */
class ListSaleItemsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.sale_items.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/sale_items - Listet Verkaufspositionen (id, commerce_sale_id, quantity, price). Unterstützt filters/search/sort/limit/offset.';
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
                    'commerce_sale_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Verkaufs-ID.',
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

            $q = CommerceSaleItem::query()
                ->whereHas('sale', fn($sq) => $sq->where('team_id', $team->id));

            if (array_key_exists('commerce_sale_id', $arguments) && $arguments['commerce_sale_id'] !== null) {
                $q->where('commerce_sale_id', (int)$arguments['commerce_sale_id']);
            }

            $this->applyStandardFilters($q, $arguments, ['commerce_sale_id', 'created_at']);
            $this->applyStandardSearch($q, $arguments, []);
            $this->applyStandardSort($q, $arguments, ['id', 'created_at', 'quantity', 'price'], 'id', 'desc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($item) => [
                'id' => $item->id,
                'commerce_sale_id' => $item->commerce_sale_id,
                'commerce_product_id' => $item->commerce_product_id,
                'commerce_article_batch_id' => $item->commerce_article_batch_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'created_at' => $item->created_at?->toIso8601String(),
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Verkaufspositionen: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'sale_items', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
