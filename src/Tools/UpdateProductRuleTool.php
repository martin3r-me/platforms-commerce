<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceProductRule;

/**
 * Aktualisiert eine bestehende Produktregel.
 */
class UpdateProductRuleTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.product_rules.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/product_rules/{id} - Aktualisiert eine Produktregel. Nutze commerce.product_rules.GET um die ID zu finden.';
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
                    'description' => 'ID der Produktregel (ERFORDERLICH). Nutze commerce.product_rules.GET.',
                ],
                'max_quantity_per_order' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue maximale Menge pro Bestellung (null zum Leeren).',
                ],
                'min_order_value' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Mindestbestellwert (null zum Leeren).',
                ],
                'sale_period_start' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Verkaufszeitraum Start (ISO 8601, "" zum Leeren).',
                ],
                'sale_period_end' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Verkaufszeitraum Ende (ISO 8601, "" zum Leeren).',
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
                CommerceProductRule::class,
                'NOT_FOUND',
                'Produktregel nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceProductRule $rule */
            $rule = $found['model'];
            $product = $rule->product;
            if (!$product || (int)$product->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Produktregel gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('max_quantity_per_order', $arguments)) {
                $v = $arguments['max_quantity_per_order'];
                $update['max_quantity_per_order'] = ($v === null || $v === '' || $v === 0) ? null : (int)$v;
            }

            if (array_key_exists('min_order_value', $arguments)) {
                $v = $arguments['min_order_value'];
                $update['min_order_value'] = ($v === null || $v === '') ? null : (float)$v;
            }

            if (array_key_exists('sale_period_start', $arguments)) {
                $v = $arguments['sale_period_start'];
                $update['sale_period_start'] = ($v === null || $v === '') ? null : (string)$v;
            }

            if (array_key_exists('sale_period_end', $arguments)) {
                $v = $arguments['sale_period_end'];
                $update['sale_period_end'] = ($v === null || $v === '') ? null : (string)$v;
            }

            if (!empty($update)) {
                $rule->update($update);
            }
            $rule->refresh();

            return ToolResult::success([
                'id' => $rule->id,
                'commerce_product_id' => $rule->commerce_product_id,
                'max_quantity_per_order' => $rule->max_quantity_per_order,
                'min_order_value' => $rule->min_order_value,
                'sale_period_start' => $rule->sale_period_start?->toIso8601String(),
                'sale_period_end' => $rule->sale_period_end?->toIso8601String(),
                'created_at' => $rule->created_at?->toIso8601String(),
                'message' => 'Produktregel erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Produktregel: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'product_rules', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
