<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceUnitConversion;

/**
 * Erstellt eine neue Einheiten-Umrechnung.
 */
class CreateUnitConversionTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.unit_conversions.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/unit-conversions - Erstellt eine neue Einheiten-Umrechnung.';
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
                'from_unit_id' => [
                    'type' => 'integer',
                    'description' => 'Quell-Einheit ID (ERFORDERLICH).',
                ],
                'to_unit_id' => [
                    'type' => 'integer',
                    'description' => 'Ziel-Einheit ID (ERFORDERLICH).',
                ],
                'factor' => [
                    'type' => 'number',
                    'description' => 'Umrechnungsfaktor (ERFORDERLICH). from_unit * factor = to_unit.',
                ],
            ],
            'required' => ['from_unit_id', 'to_unit_id', 'factor'],
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

            if (!isset($arguments['from_unit_id'])) {
                return ToolResult::error('VALIDATION_ERROR', 'from_unit_id ist erforderlich.');
            }
            if (!isset($arguments['to_unit_id'])) {
                return ToolResult::error('VALIDATION_ERROR', 'to_unit_id ist erforderlich.');
            }
            if (!isset($arguments['factor'])) {
                return ToolResult::error('VALIDATION_ERROR', 'factor ist erforderlich.');
            }

            $conversion = CommerceUnitConversion::create([
                'team_id' => $team->id,
                'from_unit_id' => (int)$arguments['from_unit_id'],
                'to_unit_id' => (int)$arguments['to_unit_id'],
                'factor' => (float)$arguments['factor'],
            ]);

            return ToolResult::success([
                'id' => $conversion->id,
                'from_unit_id' => $conversion->from_unit_id,
                'to_unit_id' => $conversion->to_unit_id,
                'factor' => $conversion->factor,
                'team_id' => $conversion->team_id,
                'message' => 'Einheiten-Umrechnung erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Einheiten-Umrechnung: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'unit_conversions', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
