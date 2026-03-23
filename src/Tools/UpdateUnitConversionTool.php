<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceUnitConversion;

/**
 * Aktualisiert eine bestehende Einheiten-Umrechnung.
 */
class UpdateUnitConversionTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.unit_conversions.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/unit-conversions/{id} - Aktualisiert eine Einheiten-Umrechnung. Nutze commerce.unit_conversions.GET um die ID zu finden.';
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
                    'description' => 'ID der Einheiten-Umrechnung (ERFORDERLICH). Nutze commerce.unit_conversions.GET.',
                ],
                'from_unit_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Quell-Einheit ID.',
                ],
                'to_unit_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Ziel-Einheit ID.',
                ],
                'factor' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Umrechnungsfaktor.',
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
                CommerceUnitConversion::class,
                'NOT_FOUND',
                'Einheiten-Umrechnung nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceUnitConversion $conversion */
            $conversion = $found['model'];
            if ((int)$conversion->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Einheiten-Umrechnung gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('from_unit_id', $arguments)) {
                $update['from_unit_id'] = (int)$arguments['from_unit_id'];
            }

            if (array_key_exists('to_unit_id', $arguments)) {
                $update['to_unit_id'] = (int)$arguments['to_unit_id'];
            }

            if (array_key_exists('factor', $arguments)) {
                $update['factor'] = (float)$arguments['factor'];
            }

            if (!empty($update)) {
                $conversion->update($update);
            }
            $conversion->refresh();

            return ToolResult::success([
                'id' => $conversion->id,
                'from_unit_id' => $conversion->from_unit_id,
                'to_unit_id' => $conversion->to_unit_id,
                'factor' => $conversion->factor,
                'team_id' => $conversion->team_id,
                'message' => 'Einheiten-Umrechnung erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Einheiten-Umrechnung: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'unit_conversions', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
