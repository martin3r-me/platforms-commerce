<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceTaxRule;

/**
 * Listet Steuerregeln für ein Team.
 */
class ListTaxRulesTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.tax_rules.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/tax-rules - Listet Steuerregeln (id, commerce_sales_context_id, commerce_tax_category_id, tax_rate, valid_from, valid_until). Unterstützt filters/search/sort/limit/offset.';
    }

    public function getSchema(): array
    {
        return $this->mergeSchemas(
            $this->getStandardGetSchema(),
            [
                'properties' => [
                    'team_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Team-ID. Default: Team aus Kontext.',
                    ],
                    'commerce_sales_context_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Verkaufskontext ID.',
                    ],
                    'commerce_tax_category_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Steuerkategorie ID.',
                    ],
                ],
            ]
        );
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

            $q = CommerceTaxRule::query()
                ->where('team_id', $team->id);

            // Filter by sales context
            if (array_key_exists('commerce_sales_context_id', $arguments) && $arguments['commerce_sales_context_id'] !== null && $arguments['commerce_sales_context_id'] !== '') {
                $q->where('commerce_sales_context_id', (int)$arguments['commerce_sales_context_id']);
            }

            // Filter by tax category
            if (array_key_exists('commerce_tax_category_id', $arguments) && $arguments['commerce_tax_category_id'] !== null && $arguments['commerce_tax_category_id'] !== '') {
                $q->where('commerce_tax_category_id', (int)$arguments['commerce_tax_category_id']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'commerce_sales_context_id', 'commerce_tax_category_id', 'tax_rate', 'created_at']);
            $this->applyStandardSort($q, $arguments, ['id', 'created_at', 'tax_rate'], 'id', 'asc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($rule) => [
                'id' => $rule->id,
                'commerce_sales_context_id' => $rule->commerce_sales_context_id,
                'commerce_tax_category_id' => $rule->commerce_tax_category_id,
                'tax_rate' => (float)$rule->tax_rate,
                'valid_from' => $rule->valid_from,
                'valid_until' => $rule->valid_until,
                'team_id' => $rule->team_id,
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Steuerregeln: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'tax_rules', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
