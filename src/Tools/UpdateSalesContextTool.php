<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceSalesContext;

/**
 * Aktualisiert einen bestehenden Verkaufskontext.
 */
class UpdateSalesContextTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.sales_contexts.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/sales-contexts/{id} - Aktualisiert einen Verkaufskontext. Nutze commerce.sales_contexts.GET um die ID zu finden.';
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
                    'description' => 'ID des Verkaufskontexts (ERFORDERLICH). Nutze commerce.sales_contexts.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name des Verkaufskontexts.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
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
                CommerceSalesContext::class,
                'NOT_FOUND',
                'Verkaufskontext nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceSalesContext $salesContext */
            $salesContext = $found['model'];
            if ((int)$salesContext->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Verkaufskontext gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            if (array_key_exists('description', $arguments)) {
                $d = (string)($arguments['description'] ?? '');
                $update['description'] = $d === '' ? null : $d;
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
                $salesContext->update($update);
            }
            $salesContext->refresh();

            return ToolResult::success([
                'id' => $salesContext->id,
                'name' => $salesContext->name,
                'description' => $salesContext->description,
                'valid_from' => $salesContext->valid_from,
                'valid_until' => $salesContext->valid_until,
                'team_id' => $salesContext->team_id,
                'message' => 'Verkaufskontext erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Verkaufskontexts: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'sales_contexts', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
