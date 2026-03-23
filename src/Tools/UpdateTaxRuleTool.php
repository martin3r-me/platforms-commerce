<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceTaxRule;

/**
 * Aktualisiert eine bestehende Steuerregel.
 */
class UpdateTaxRuleTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.tax_rules.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/tax-rules/{id} - Aktualisiert eine Steuerregel. Nutze commerce.tax_rules.GET um die ID zu finden.';
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
                    'description' => 'ID der Steuerregel (ERFORDERLICH). Nutze commerce.tax_rules.GET.',
                ],
                'commerce_sales_context_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Verkaufskontext ID.',
                ],
                'commerce_tax_category_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Steuerkategorie ID.',
                ],
                'tax_rate' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Steuersatz.',
                ],
                'valid_from' => [
                    'type' => 'string',
                    'description' => 'Optional: Neues Gültig-ab-Datum (YYYY-MM-DD, "" zum Leeren).',
                ],
                'valid_until' => [
                    'type' => 'string',
                    'description' => 'Optional: Neues Gültig-bis-Datum (YYYY-MM-DD, "" zum Leeren).',
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
                CommerceTaxRule::class,
                'NOT_FOUND',
                'Steuerregel nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceTaxRule $rule */
            $rule = $found['model'];
            if ((int)$rule->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Steuerregel gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('commerce_sales_context_id', $arguments)) {
                if ($arguments['commerce_sales_context_id'] === null || $arguments['commerce_sales_context_id'] === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'commerce_sales_context_id darf nicht leer sein.');
                }
                $update['commerce_sales_context_id'] = (int)$arguments['commerce_sales_context_id'];
            }

            if (array_key_exists('commerce_tax_category_id', $arguments)) {
                if ($arguments['commerce_tax_category_id'] === null || $arguments['commerce_tax_category_id'] === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'commerce_tax_category_id darf nicht leer sein.');
                }
                $update['commerce_tax_category_id'] = (int)$arguments['commerce_tax_category_id'];
            }

            if (array_key_exists('tax_rate', $arguments)) {
                if ($arguments['tax_rate'] === null || $arguments['tax_rate'] === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'tax_rate darf nicht leer sein.');
                }
                $update['tax_rate'] = (float)$arguments['tax_rate'];
            }

            if (array_key_exists('valid_from', $arguments)) {
                $v = (string)($arguments['valid_from'] ?? '');
                $update['valid_from'] = $v === '' ? null : $v;
            }

            if (array_key_exists('valid_until', $arguments)) {
                $v = (string)($arguments['valid_until'] ?? '');
                $update['valid_until'] = $v === '' ? null : $v;
            }

            if (!empty($update)) {
                $rule->update($update);
            }
            $rule->refresh();

            return ToolResult::success([
                'id' => $rule->id,
                'commerce_sales_context_id' => $rule->commerce_sales_context_id,
                'commerce_tax_category_id' => $rule->commerce_tax_category_id,
                'tax_rate' => (float)$rule->tax_rate,
                'valid_from' => $rule->valid_from,
                'valid_until' => $rule->valid_until,
                'team_id' => $rule->team_id,
                'message' => 'Steuerregel erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Steuerregel: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'tax_rules', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
