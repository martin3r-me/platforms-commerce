<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceSalesContext;

/**
 * Erstellt einen neuen Verkaufskontext.
 */
class CreateSalesContextTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.sales_contexts.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/sales-contexts - Erstellt einen neuen Verkaufskontext.';
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
                    'description' => 'Name des Verkaufskontexts (ERFORDERLICH).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung des Verkaufskontexts.',
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
            'required' => ['name'],
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

            $salesContext = CommerceSalesContext::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'valid_from' => (array_key_exists('valid_from', $arguments) && $arguments['valid_from'] !== '')
                    ? (string)$arguments['valid_from']
                    : null,
                'valid_until' => (array_key_exists('valid_until', $arguments) && $arguments['valid_until'] !== '')
                    ? (string)$arguments['valid_until']
                    : null,
            ]);

            return ToolResult::success([
                'id' => $salesContext->id,
                'name' => $salesContext->name,
                'description' => $salesContext->description,
                'valid_from' => $salesContext->valid_from,
                'valid_until' => $salesContext->valid_until,
                'team_id' => $salesContext->team_id,
                'message' => 'Verkaufskontext erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Verkaufskontexts: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'sales_contexts', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
