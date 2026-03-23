<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceUnit;

/**
 * Aktualisiert eine bestehende Einheit.
 */
class UpdateUnitTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.units.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/units/{id} - Aktualisiert eine Einheit. Nutze commerce.units.GET um die ID zu finden.';
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
                    'description' => 'ID der Einheit (ERFORDERLICH). Nutze commerce.units.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name der Einheit.',
                ],
                'symbol' => [
                    'type' => 'string',
                    'description' => 'Optional: Neues Symbol der Einheit.',
                ],
                'type' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Typ der Einheit.',
                ],
                'is_base_unit' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ist dies eine Basis-Einheit?',
                ],
                'base_unit_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: ID der Basis-Einheit (null zum Entfernen).',
                ],
                'factor_to_base' => [
                    'type' => 'number',
                    'description' => 'Optional: Umrechnungsfaktor zur Basis-Einheit.',
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
                CommerceUnit::class,
                'NOT_FOUND',
                'Einheit nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceUnit $unit */
            $unit = $found['model'];
            if ((int)$unit->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Einheit gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            if (array_key_exists('symbol', $arguments)) {
                $symbol = trim((string)($arguments['symbol'] ?? ''));
                if ($symbol === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'symbol darf nicht leer sein.');
                }
                $update['symbol'] = $symbol;
            }

            if (array_key_exists('type', $arguments)) {
                $type = trim((string)($arguments['type'] ?? ''));
                if ($type === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'type darf nicht leer sein.');
                }
                $update['type'] = $type;
            }

            if (array_key_exists('is_base_unit', $arguments)) {
                $update['is_base_unit'] = (bool)$arguments['is_base_unit'];
            }

            if (array_key_exists('base_unit_id', $arguments)) {
                $update['base_unit_id'] = $arguments['base_unit_id'] !== null ? (int)$arguments['base_unit_id'] : null;
            }

            if (array_key_exists('factor_to_base', $arguments)) {
                $update['factor_to_base'] = $arguments['factor_to_base'] !== null ? (float)$arguments['factor_to_base'] : null;
            }

            if (!empty($update)) {
                $unit->update($update);
            }
            $unit->refresh();

            return ToolResult::success([
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'type' => $unit->type,
                'is_base_unit' => (bool)$unit->is_base_unit,
                'base_unit_id' => $unit->base_unit_id,
                'factor_to_base' => $unit->factor_to_base,
                'team_id' => $unit->team_id,
                'message' => 'Einheit erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Einheit: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'units', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
