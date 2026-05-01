<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProduct;
use Platform\Commerce\Models\CommerceProductRule;

/**
 * Erstellt eine neue Produktregel.
 */
class CreateProductRuleTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.product_rules.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/product_rules - Erstellt eine neue Produktregel. Regeltypen: Mengenlimit (max_quantity_per_order), Mindestbestellwert (min_order_value), Verkaufszeitraum (sale_period_start/end). Nutze zuerst commerce.products.GET um die Produkt-ID zu finden.';
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
                'max_quantity_per_order' => [
                    'type' => 'integer',
                    'description' => 'Optional: Maximale Menge pro Bestellung.',
                ],
                'min_order_value' => [
                    'type' => 'number',
                    'description' => 'Optional: Mindestbestellwert.',
                ],
                'sale_period_start' => [
                    'type' => 'string',
                    'description' => 'Optional: Verkaufszeitraum Start (ISO 8601).',
                ],
                'sale_period_end' => [
                    'type' => 'string',
                    'description' => 'Optional: Verkaufszeitraum Ende (ISO 8601).',
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
                'max_quantity_per_order' => array_key_exists('max_quantity_per_order', $arguments) && $arguments['max_quantity_per_order'] !== null
                    ? (int)$arguments['max_quantity_per_order']
                    : null,
                'min_order_value' => array_key_exists('min_order_value', $arguments) && $arguments['min_order_value'] !== null
                    ? (float)$arguments['min_order_value']
                    : null,
                'sale_period_start' => array_key_exists('sale_period_start', $arguments) && $arguments['sale_period_start'] !== null
                    ? (string)$arguments['sale_period_start']
                    : null,
                'sale_period_end' => array_key_exists('sale_period_end', $arguments) && $arguments['sale_period_end'] !== null
                    ? (string)$arguments['sale_period_end']
                    : null,
            ];

            $rule = CommerceProductRule::create($data);

            return ToolResult::success([
                'id' => $rule->id,
                'commerce_product_id' => $rule->commerce_product_id,
                'max_quantity_per_order' => $rule->max_quantity_per_order,
                'min_order_value' => $rule->min_order_value,
                'sale_period_start' => $rule->sale_period_start?->toIso8601String(),
                'sale_period_end' => $rule->sale_period_end?->toIso8601String(),
                'created_at' => $rule->created_at?->toIso8601String(),
                'message' => 'Produktregel erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Produktregel: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_rules', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
