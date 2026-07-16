<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceTaxCategory;

/**
 * Erstellt eine neue Steuerkategorie.
 */
class CreateTaxCategoryTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.tax_categories.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/tax-categories - Erstellt eine neue Steuerkategorie. Steuerkategorien (z.B. "Normalsatz 19%", "Ermäßigt 7%") werden Artikeln über TaxRules zugewiesen (commerce.tax_rules.POST).';
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
                'name' => [
                    'type' => 'string',
                    'description' => 'Name der Steuerkategorie (ERFORDERLICH).',
                ],
                'default_rate' => [
                    'type' => 'number',
                    'description' => 'Standard-Steuersatz (ERFORDERLICH). Z.B. 19.0 für 19%.',
                ],
                'revenue_account' => [
                    'type' => 'string',
                    'description' => 'Optional: Standard-Erlöskonto (Fibu-/Sachkonto) für Umsätze dieser Steuerkategorie. Z.B. SKR03 "8400" (Erlöse 19%) oder "8300" (Erlöse 7%). Artikel erben dieses Konto, sofern sie kein eigenes revenue_account gesetzt haben.',
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
            'required' => ['name', 'default_rate'],
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

            $name = trim((string)($arguments['name'] ?? ''));
            if ($name === '') {
                return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
            }

            if (!array_key_exists('default_rate', $arguments) || $arguments['default_rate'] === null || $arguments['default_rate'] === '') {
                return ToolResult::error('VALIDATION_ERROR', 'default_rate ist erforderlich.');
            }
            $defaultRate = (float)$arguments['default_rate'];

            $category = CommerceTaxCategory::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'default_rate' => $defaultRate,
                'revenue_account' => (array_key_exists('revenue_account', $arguments) && trim((string)$arguments['revenue_account']) !== '')
                    ? trim((string)$arguments['revenue_account'])
                    : null,
                'valid_from' => (array_key_exists('valid_from', $arguments) && $arguments['valid_from'] !== '')
                    ? (string)$arguments['valid_from']
                    : now()->toDateString(),
                'valid_until' => (array_key_exists('valid_until', $arguments) && $arguments['valid_until'] !== '')
                    ? (string)$arguments['valid_until']
                    : null,
            ]);

            return ToolResult::success([
                'id' => $category->id,
                'name' => $category->name,
                'default_rate' => (float)$category->default_rate,
                'revenue_account' => $category->revenue_account,
                'valid_from' => $category->valid_from,
                'valid_until' => $category->valid_until,
                'team_id' => $category->team_id,
                'message' => 'Steuerkategorie erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Steuerkategorie: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'tax_categories', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
