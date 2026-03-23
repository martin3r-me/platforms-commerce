<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceTaxRule;

/**
 * Erstellt eine neue Steuerregel.
 */
class CreateTaxRuleTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.tax_rules.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/tax-rules - Erstellt eine neue Steuerregel. Verknüpft einen Verkaufskontext mit einer Steuerkategorie und einem Steuersatz.';
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
                'commerce_sales_context_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Verkaufskontexts (ERFORDERLICH). Nutze commerce.sales_contexts.GET.',
                ],
                'commerce_tax_category_id' => [
                    'type' => 'integer',
                    'description' => 'ID der Steuerkategorie (ERFORDERLICH). Nutze commerce.tax_categories.GET.',
                ],
                'tax_rate' => [
                    'type' => 'number',
                    'description' => 'Steuersatz (ERFORDERLICH). Z.B. 19.0 für 19%.',
                ],
                'valid_from' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültig ab (YYYY-MM-DD).',
                ],
                'valid_until' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültig bis (YYYY-MM-DD).',
                ],
            ],
            'required' => ['commerce_sales_context_id', 'commerce_tax_category_id', 'tax_rate'],
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

            if (!array_key_exists('commerce_sales_context_id', $arguments) || $arguments['commerce_sales_context_id'] === null || $arguments['commerce_sales_context_id'] === '') {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_sales_context_id ist erforderlich.');
            }
            $salesContextId = (int)$arguments['commerce_sales_context_id'];

            if (!array_key_exists('commerce_tax_category_id', $arguments) || $arguments['commerce_tax_category_id'] === null || $arguments['commerce_tax_category_id'] === '') {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_tax_category_id ist erforderlich.');
            }
            $taxCategoryId = (int)$arguments['commerce_tax_category_id'];

            if (!array_key_exists('tax_rate', $arguments) || $arguments['tax_rate'] === null || $arguments['tax_rate'] === '') {
                return ToolResult::error('VALIDATION_ERROR', 'tax_rate ist erforderlich.');
            }
            $taxRate = (float)$arguments['tax_rate'];

            $rule = CommerceTaxRule::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'commerce_sales_context_id' => $salesContextId,
                'commerce_tax_category_id' => $taxCategoryId,
                'tax_rate' => $taxRate,
                'valid_from' => (array_key_exists('valid_from', $arguments) && $arguments['valid_from'] !== '')
                    ? (string)$arguments['valid_from']
                    : null,
                'valid_until' => (array_key_exists('valid_until', $arguments) && $arguments['valid_until'] !== '')
                    ? (string)$arguments['valid_until']
                    : null,
            ]);

            return ToolResult::success([
                'id' => $rule->id,
                'commerce_sales_context_id' => $rule->commerce_sales_context_id,
                'commerce_tax_category_id' => $rule->commerce_tax_category_id,
                'tax_rate' => (float)$rule->tax_rate,
                'valid_from' => $rule->valid_from,
                'valid_until' => $rule->valid_until,
                'team_id' => $rule->team_id,
                'message' => 'Steuerregel erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Steuerregel: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'tax_rules', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
