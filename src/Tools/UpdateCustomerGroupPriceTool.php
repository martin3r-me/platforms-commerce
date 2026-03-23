<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceCustomerGroupPrice;

/**
 * Aktualisiert einen bestehenden Kundengruppen-Preis.
 */
class UpdateCustomerGroupPriceTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.customer_group_prices.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/customer-group-prices/{id} - Aktualisiert einen Kundengruppen-Preis. Nutze commerce.customer_group_prices.GET um die ID zu finden.';
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
                    'description' => 'ID des Kundengruppen-Preises (ERFORDERLICH). Nutze commerce.customer_group_prices.GET.',
                ],
                'commerce_customer_group_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Kundengruppen-ID.',
                ],
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Artikel-ID.',
                ],
                'commerce_price_list_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Preislisten-ID (0 zum Leeren).',
                ],
                'price' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Preis.',
                ],
                'discount_percentage' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Rabattprozentsatz (0 zum Leeren).',
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
                CommerceCustomerGroupPrice::class,
                'NOT_FOUND',
                'Kundengruppen-Preis nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceCustomerGroupPrice $groupPrice */
            $groupPrice = $found['model'];
            if ((int)$groupPrice->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Kundengruppen-Preis gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('commerce_customer_group_id', $arguments)) {
                $update['commerce_customer_group_id'] = (int)$arguments['commerce_customer_group_id'];
            }
            if (array_key_exists('commerce_article_id', $arguments)) {
                $update['commerce_article_id'] = (int)$arguments['commerce_article_id'];
            }
            if (array_key_exists('commerce_price_list_id', $arguments)) {
                $val = $arguments['commerce_price_list_id'];
                $update['commerce_price_list_id'] = ($val === 0 || $val === '0') ? null : (int)$val;
            }
            if (array_key_exists('price', $arguments)) {
                $update['price'] = $arguments['price'];
            }
            if (array_key_exists('discount_percentage', $arguments)) {
                $val = $arguments['discount_percentage'];
                $update['discount_percentage'] = ($val === 0 || $val === '0') ? null : $val;
            }

            if (!empty($update)) {
                $groupPrice->update($update);
            }
            $groupPrice->refresh();

            return ToolResult::success([
                'id' => $groupPrice->id,
                'commerce_customer_group_id' => $groupPrice->commerce_customer_group_id,
                'commerce_article_id' => $groupPrice->commerce_article_id,
                'commerce_price_list_id' => $groupPrice->commerce_price_list_id,
                'price' => $groupPrice->price,
                'discount_percentage' => $groupPrice->discount_percentage,
                'team_id' => $groupPrice->team_id,
                'message' => 'Kundengruppen-Preis erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Kundengruppen-Preises: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'customer_group_prices', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
