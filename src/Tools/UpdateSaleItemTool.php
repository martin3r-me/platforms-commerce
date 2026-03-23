<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceSaleItem;

/**
 * Aktualisiert eine bestehende Verkaufsposition.
 */
class UpdateSaleItemTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.sale_items.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/sale_items/{id} - Aktualisiert eine Verkaufsposition. Nutze commerce.sale_items.GET um die ID zu finden.';
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
                    'description' => 'ID der Verkaufsposition (ERFORDERLICH). Nutze commerce.sale_items.GET.',
                ],
                'quantity' => [
                    'type' => 'number',
                    'description' => 'Optional: Neue Menge.',
                ],
                'price' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Preis.',
                ],
                'commerce_product_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Produkt-ID (null zum Leeren).',
                ],
                'commerce_article_batch_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Artikelchargen-ID (null zum Leeren).',
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
                CommerceSaleItem::class,
                'NOT_FOUND',
                'Verkaufsposition nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceSaleItem $item */
            $item = $found['model'];
            $sale = $item->sale;
            if (!$sale || (int)$sale->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Verkaufsposition gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('quantity', $arguments)) {
                $update['quantity'] = (float)$arguments['quantity'];
            }

            if (array_key_exists('price', $arguments)) {
                $update['price'] = (float)$arguments['price'];
            }

            if (array_key_exists('commerce_product_id', $arguments)) {
                $v = $arguments['commerce_product_id'];
                $update['commerce_product_id'] = ($v === null || $v === '' || $v === 0) ? null : (int)$v;
            }

            if (array_key_exists('commerce_article_batch_id', $arguments)) {
                $v = $arguments['commerce_article_batch_id'];
                $update['commerce_article_batch_id'] = ($v === null || $v === '' || $v === 0) ? null : (int)$v;
            }

            if (!empty($update)) {
                $item->update($update);
            }
            $item->refresh();

            return ToolResult::success([
                'id' => $item->id,
                'commerce_sale_id' => $item->commerce_sale_id,
                'commerce_product_id' => $item->commerce_product_id,
                'commerce_article_batch_id' => $item->commerce_article_batch_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'created_at' => $item->created_at?->toIso8601String(),
                'message' => 'Verkaufsposition erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Verkaufsposition: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'sale_items', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
