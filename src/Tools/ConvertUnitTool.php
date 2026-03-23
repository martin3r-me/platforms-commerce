<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Services\UnitConverter;

class ConvertUnitTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.units.convert';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/units/convert - Rechnet eine Menge von einer Einheit in eine andere um.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Team-ID.',
                ],
                'quantity' => [
                    'type' => 'number',
                    'description' => 'Menge (ERFORDERLICH).',
                ],
                'from_unit_id' => [
                    'type' => 'integer',
                    'description' => 'Quell-Einheit ID (ERFORDERLICH).',
                ],
                'to_unit_id' => [
                    'type' => 'integer',
                    'description' => 'Ziel-Einheit ID (ERFORDERLICH).',
                ],
            ],
            'required' => ['quantity', 'from_unit_id', 'to_unit_id'],
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
                return ToolResult::error('MISSING_TEAM', 'Kein Team angegeben.');
            }

            $team = Team::find((int)$teamId);
            if (!$team) {
                return ToolResult::error('TEAM_NOT_FOUND', 'Team nicht gefunden.');
            }

            if (!$context->user) {
                return ToolResult::error('AUTH_ERROR', 'Kein User.');
            }
            $userHasAccess = $context->user->teams()->where('teams.id', $team->id)->exists();
            if (!$userHasAccess) {
                return ToolResult::error('ACCESS_DENIED', 'Kein Zugriff.');
            }

            $converter = new UnitConverter();
            $result = $converter->convert(
                (float)$arguments['quantity'],
                (int)$arguments['from_unit_id'],
                (int)$arguments['to_unit_id'],
                $team->id
            );

            return ToolResult::success($result);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler bei Umrechnung: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'units', 'convert'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
