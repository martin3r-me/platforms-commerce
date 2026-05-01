<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceUnit;

/**
 * Erstellt eine neue Einheit.
 */
class CreateUnitTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.units.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/units - Erstellt eine neue Einheit. Einheiten werden für Artikelmengen genutzt (kg, Stück, Liter). Definiere zuerst Basis-Einheiten (is_base_unit=true), dann abgeleitete.';
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
                    'description' => 'Name der Einheit (ERFORDERLICH).',
                ],
                'symbol' => [
                    'type' => 'string',
                    'description' => 'Symbol der Einheit, z.B. "kg", "m" (ERFORDERLICH).',
                ],
                'type' => [
                    'type' => 'string',
                    'description' => 'Typ der Einheit (ERFORDERLICH). Erlaubte Werte: piece, weight, volume, length, area, time, package.',
                    'enum' => ['piece', 'weight', 'volume', 'length', 'area', 'time', 'package'],
                ],
                'is_base_unit' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ist dies eine Basis-Einheit? Default: false.',
                ],
                'base_unit_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: ID der Basis-Einheit (falls keine Basis-Einheit).',
                ],
                'factor_to_base' => [
                    'type' => 'number',
                    'description' => 'Optional: Umrechnungsfaktor zur Basis-Einheit.',
                ],
            ],
            'required' => ['name', 'symbol', 'type'],
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

            $symbol = trim((string)($arguments['symbol'] ?? ''));
            if ($symbol === '') {
                return ToolResult::error('VALIDATION_ERROR', 'symbol ist erforderlich.');
            }

            $type = trim((string)($arguments['type'] ?? ''));
            if ($type === '') {
                return ToolResult::error('VALIDATION_ERROR', 'type ist erforderlich.');
            }

            $unit = CommerceUnit::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'symbol' => $symbol,
                'type' => $type,
                'is_base_unit' => (bool)($arguments['is_base_unit'] ?? false),
                'base_unit_id' => isset($arguments['base_unit_id']) ? (int)$arguments['base_unit_id'] : null,
                'factor_to_base' => isset($arguments['factor_to_base']) ? (float)$arguments['factor_to_base'] : null,
            ]);

            return ToolResult::success([
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'type' => $unit->type,
                'is_base_unit' => (bool)$unit->is_base_unit,
                'base_unit_id' => $unit->base_unit_id,
                'factor_to_base' => $unit->factor_to_base,
                'team_id' => $unit->team_id,
                'message' => 'Einheit erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Einheit: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'units', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
