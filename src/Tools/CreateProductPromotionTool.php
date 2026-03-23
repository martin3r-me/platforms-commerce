<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProduct;
use Platform\Commerce\Models\CommerceProductPromotion;

/**
 * Erstellt eine neue Produktaktion.
 */
class CreateProductPromotionTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_promotions.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/product_promotions - Erstellt eine neue Produktaktion. Nutze zuerst commerce.products.GET um die Produkt-ID zu finden.';
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
                'commerce_product_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Produkts (ERFORDERLICH).',
                ],
                'discount_value' => [
                    'type' => 'number',
                    'description' => 'Optional: Rabattwert (absolut).',
                ],
                'discount_percentage' => [
                    'type' => 'number',
                    'description' => 'Optional: Rabattprozentsatz.',
                ],
                'min_cart_value' => [
                    'type' => 'number',
                    'description' => 'Optional: Mindestwarenkorbwert.',
                ],
                'promotion_start' => [
                    'type' => 'string',
                    'description' => 'Optional: Aktionszeitraum Start (ISO 8601).',
                ],
                'promotion_end' => [
                    'type' => 'string',
                    'description' => 'Optional: Aktionszeitraum Ende (ISO 8601).',
                ],
            ],
            'required' => ['commerce_product_id'],
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

            if (!array_key_exists('commerce_product_id', $arguments) || $arguments['commerce_product_id'] === null) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_product_id ist erforderlich.');
            }

            $product = CommerceProduct::find((int)$arguments['commerce_product_id']);
            if (!$product) {
                return ToolResult::error('NOT_FOUND', 'Produkt nicht gefunden.');
            }
            if ((int)$product->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Produkt gehört nicht zum angegebenen Team.');
            }

            $data = [
                'commerce_product_id' => $product->id,
                'discount_value' => array_key_exists('discount_value', $arguments) && $arguments['discount_value'] !== null
                    ? (float)$arguments['discount_value']
                    : null,
                'discount_percentage' => array_key_exists('discount_percentage', $arguments) && $arguments['discount_percentage'] !== null
                    ? (float)$arguments['discount_percentage']
                    : null,
                'min_cart_value' => array_key_exists('min_cart_value', $arguments) && $arguments['min_cart_value'] !== null
                    ? (float)$arguments['min_cart_value']
                    : null,
                'promotion_start' => array_key_exists('promotion_start', $arguments) && $arguments['promotion_start'] !== null
                    ? (string)$arguments['promotion_start']
                    : null,
                'promotion_end' => array_key_exists('promotion_end', $arguments) && $arguments['promotion_end'] !== null
                    ? (string)$arguments['promotion_end']
                    : null,
            ];

            $promo = CommerceProductPromotion::create($data);

            return ToolResult::success([
                'id' => $promo->id,
                'commerce_product_id' => $promo->commerce_product_id,
                'discount_value' => $promo->discount_value,
                'discount_percentage' => $promo->discount_percentage,
                'min_cart_value' => $promo->min_cart_value,
                'promotion_start' => $promo->promotion_start?->toIso8601String(),
                'promotion_end' => $promo->promotion_end?->toIso8601String(),
                'created_at' => $promo->created_at?->toIso8601String(),
                'message' => 'Produktaktion erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Produktaktion: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_promotions', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
