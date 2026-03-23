<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceCustomerGroupPrice;

/**
 * Erstellt einen neuen Kundengruppen-Preis.
 */
class CreateCustomerGroupPriceTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.customer_group_prices.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/customer-group-prices - Erstellt einen neuen Kundengruppen-Preis. Nutze zuerst commerce.customer_group_prices.GET um vorhandene Preise zu prüfen.';
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
                'commerce_customer_group_id' => [
                    'type' => 'integer',
                    'description' => 'ID der Kundengruppe (ERFORDERLICH).',
                ],
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Artikels (ERFORDERLICH).',
                ],
                'price' => [
                    'type' => 'number',
                    'description' => 'Preis (ERFORDERLICH).',
                ],
                'commerce_price_list_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: ID der Preisliste.',
                ],
                'discount_percentage' => [
                    'type' => 'number',
                    'description' => 'Optional: Rabattprozentsatz.',
                ],
            ],
            'required' => ['commerce_customer_group_id', 'commerce_article_id', 'price'],
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

            if (empty($arguments['commerce_customer_group_id'])) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_customer_group_id ist erforderlich.');
            }
            if (empty($arguments['commerce_article_id'])) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_article_id ist erforderlich.');
            }
            if (!isset($arguments['price'])) {
                return ToolResult::error('VALIDATION_ERROR', 'price ist erforderlich.');
            }

            $data = [
                'team_id' => $team->id,
                'commerce_customer_group_id' => (int)$arguments['commerce_customer_group_id'],
                'commerce_article_id' => (int)$arguments['commerce_article_id'],
                'price' => $arguments['price'],
            ];

            if (!empty($arguments['commerce_price_list_id'])) {
                $data['commerce_price_list_id'] = (int)$arguments['commerce_price_list_id'];
            }
            if (array_key_exists('discount_percentage', $arguments) && $arguments['discount_percentage'] !== null) {
                $data['discount_percentage'] = $arguments['discount_percentage'];
            }

            $groupPrice = CommerceCustomerGroupPrice::create($data);

            return ToolResult::success([
                'id' => $groupPrice->id,
                'commerce_customer_group_id' => $groupPrice->commerce_customer_group_id,
                'commerce_article_id' => $groupPrice->commerce_article_id,
                'commerce_price_list_id' => $groupPrice->commerce_price_list_id,
                'price' => $groupPrice->price,
                'discount_percentage' => $groupPrice->discount_percentage,
                'team_id' => $groupPrice->team_id,
                'message' => 'Kundengruppen-Preis erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Kundengruppen-Preises: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'customer_group_prices', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
