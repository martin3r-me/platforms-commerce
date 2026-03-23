<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceProductPromotion;

/**
 * Aktualisiert eine bestehende Produktaktion.
 */
class UpdateProductPromotionTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.product_promotions.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/product_promotions/{id} - Aktualisiert eine Produktaktion. Nutze commerce.product_promotions.GET um die ID zu finden.';
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
                    'description' => 'ID der Produktaktion (ERFORDERLICH). Nutze commerce.product_promotions.GET.',
                ],
                'discount_value' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Rabattwert (null zum Leeren).',
                ],
                'discount_percentage' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Rabattprozentsatz (null zum Leeren).',
                ],
                'min_cart_value' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Mindestwarenkorbwert (null zum Leeren).',
                ],
                'promotion_start' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Aktionszeitraum Start (ISO 8601, "" zum Leeren).',
                ],
                'promotion_end' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Aktionszeitraum Ende (ISO 8601, "" zum Leeren).',
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
                CommerceProductPromotion::class,
                'NOT_FOUND',
                'Produktaktion nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceProductPromotion $promo */
            $promo = $found['model'];
            $product = $promo->product;
            if (!$product || (int)$product->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Produktaktion gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('discount_value', $arguments)) {
                $v = $arguments['discount_value'];
                $update['discount_value'] = ($v === null || $v === '') ? null : (float)$v;
            }

            if (array_key_exists('discount_percentage', $arguments)) {
                $v = $arguments['discount_percentage'];
                $update['discount_percentage'] = ($v === null || $v === '') ? null : (float)$v;
            }

            if (array_key_exists('min_cart_value', $arguments)) {
                $v = $arguments['min_cart_value'];
                $update['min_cart_value'] = ($v === null || $v === '') ? null : (float)$v;
            }

            if (array_key_exists('promotion_start', $arguments)) {
                $v = $arguments['promotion_start'];
                $update['promotion_start'] = ($v === null || $v === '') ? null : (string)$v;
            }

            if (array_key_exists('promotion_end', $arguments)) {
                $v = $arguments['promotion_end'];
                $update['promotion_end'] = ($v === null || $v === '') ? null : (string)$v;
            }

            if (!empty($update)) {
                $promo->update($update);
            }
            $promo->refresh();

            return ToolResult::success([
                'id' => $promo->id,
                'commerce_product_id' => $promo->commerce_product_id,
                'discount_value' => $promo->discount_value,
                'discount_percentage' => $promo->discount_percentage,
                'min_cart_value' => $promo->min_cart_value,
                'promotion_start' => $promo->promotion_start?->toIso8601String(),
                'promotion_end' => $promo->promotion_end?->toIso8601String(),
                'created_at' => $promo->created_at?->toIso8601String(),
                'message' => 'Produktaktion erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Produktaktion: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_promotions', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
