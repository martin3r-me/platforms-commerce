<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommercePriceList;

/**
 * Aktualisiert eine bestehende Preisliste.
 */
class UpdatePriceListTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.price_lists.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/price-lists/{id} - Aktualisiert eine Preisliste. Nutze commerce.price_lists.GET um die ID zu finden.';
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
                    'description' => 'ID der Preisliste (ERFORDERLICH). Nutze commerce.price_lists.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
                'price_type' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Preistyp.',
                ],
                'priority' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Priorität.',
                ],
                'is_active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Aktiv/Inaktiv setzen.',
                ],
                'valid_from' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültig ab (ISO 8601 Datum, "" zum Leeren).',
                ],
                'valid_until' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültig bis (ISO 8601 Datum, "" zum Leeren).',
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
                CommercePriceList::class,
                'NOT_FOUND',
                'Preisliste nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommercePriceList $priceList */
            $priceList = $found['model'];
            if ((int)$priceList->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Preisliste gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $update['name'] = $arguments['name'];
            }
            if (array_key_exists('description', $arguments)) {
                $v = (string)($arguments['description'] ?? '');
                $update['description'] = $v === '' ? null : $v;
            }
            if (array_key_exists('price_type', $arguments)) {
                $update['price_type'] = $arguments['price_type'];
            }
            if (array_key_exists('priority', $arguments)) {
                $update['priority'] = (int)$arguments['priority'];
            }
            if (array_key_exists('is_active', $arguments)) {
                $update['is_active'] = (bool)$arguments['is_active'];
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
                $priceList->update($update);
            }
            $priceList->refresh();

            return ToolResult::success([
                'id' => $priceList->id,
                'name' => $priceList->name,
                'description' => $priceList->description,
                'price_type' => $priceList->price_type,
                'priority' => $priceList->priority,
                'is_active' => $priceList->is_active,
                'valid_from' => $priceList->valid_from?->toIso8601String(),
                'valid_until' => $priceList->valid_until?->toIso8601String(),
                'user_id' => $priceList->user_id,
                'team_id' => $priceList->team_id,
                'message' => 'Preisliste erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Preisliste: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'price_lists', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
