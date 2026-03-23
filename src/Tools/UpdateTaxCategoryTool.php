<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceTaxCategory;

/**
 * Aktualisiert eine bestehende Steuerkategorie.
 */
class UpdateTaxCategoryTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.tax_categories.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/tax-categories/{id} - Aktualisiert eine Steuerkategorie. Nutze commerce.tax_categories.GET um die ID zu finden.';
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
                    'description' => 'ID der Steuerkategorie (ERFORDERLICH). Nutze commerce.tax_categories.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name der Steuerkategorie.',
                ],
                'default_rate' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Standard-Steuersatz.',
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
                CommerceTaxCategory::class,
                'NOT_FOUND',
                'Steuerkategorie nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceTaxCategory $category */
            $category = $found['model'];
            if ((int)$category->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Steuerkategorie gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            if (array_key_exists('default_rate', $arguments)) {
                if ($arguments['default_rate'] === null || $arguments['default_rate'] === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'default_rate darf nicht leer sein.');
                }
                $update['default_rate'] = (float)$arguments['default_rate'];
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
                $category->update($update);
            }
            $category->refresh();

            return ToolResult::success([
                'id' => $category->id,
                'name' => $category->name,
                'default_rate' => (float)$category->default_rate,
                'valid_from' => $category->valid_from,
                'valid_until' => $category->valid_until,
                'team_id' => $category->team_id,
                'message' => 'Steuerkategorie erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Steuerkategorie: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'tax_categories', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
