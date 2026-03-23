<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceSale;
use Platform\Commerce\Models\CommerceSaleItem;

/**
 * Erstellt eine neue Verkaufsposition.
 */
class CreateSaleItemTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.sale_items.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/sale_items - Erstellt eine neue Verkaufsposition. Nutze zuerst commerce.sales.GET um die Sale-ID zu finden.';
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
                'commerce_sale_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Verkaufs (ERFORDERLICH).',
                ],
                'quantity' => [
                    'type' => 'number',
                    'description' => 'Menge (ERFORDERLICH).',
                ],
                'price' => [
                    'type' => 'number',
                    'description' => 'Preis (ERFORDERLICH).',
                ],
                'commerce_product_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Produkt-ID.',
                ],
                'commerce_article_batch_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Artikelchargen-ID.',
                ],
            ],
            'required' => ['commerce_sale_id', 'quantity', 'price'],
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

            if (!array_key_exists('commerce_sale_id', $arguments) || $arguments['commerce_sale_id'] === null) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_sale_id ist erforderlich.');
            }
            if (!array_key_exists('quantity', $arguments) || $arguments['quantity'] === null) {
                return ToolResult::error('VALIDATION_ERROR', 'quantity ist erforderlich.');
            }
            if (!array_key_exists('price', $arguments) || $arguments['price'] === null) {
                return ToolResult::error('VALIDATION_ERROR', 'price ist erforderlich.');
            }

            $sale = CommerceSale::find((int)$arguments['commerce_sale_id']);
            if (!$sale) {
                return ToolResult::error('NOT_FOUND', 'Verkauf nicht gefunden.');
            }
            if ((int)$sale->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Verkauf gehört nicht zum angegebenen Team.');
            }

            $data = [
                'commerce_sale_id' => $sale->id,
                'quantity' => (float)$arguments['quantity'],
                'price' => (float)$arguments['price'],
                'commerce_product_id' => array_key_exists('commerce_product_id', $arguments) && $arguments['commerce_product_id'] !== null
                    ? (int)$arguments['commerce_product_id']
                    : null,
                'commerce_article_batch_id' => array_key_exists('commerce_article_batch_id', $arguments) && $arguments['commerce_article_batch_id'] !== null
                    ? (int)$arguments['commerce_article_batch_id']
                    : null,
            ];

            $item = CommerceSaleItem::create($data);

            return ToolResult::success([
                'id' => $item->id,
                'commerce_sale_id' => $item->commerce_sale_id,
                'commerce_product_id' => $item->commerce_product_id,
                'commerce_article_batch_id' => $item->commerce_article_batch_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'created_at' => $item->created_at?->toIso8601String(),
                'message' => 'Verkaufsposition erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Verkaufsposition: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'sale_items', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
