<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommercePriceList;

/**
 * Erstellt eine neue Preisliste.
 */
class CreatePriceListTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.price_lists.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/price-lists - Erstellt eine neue Preisliste. Nutze zuerst commerce.price_lists.GET um vorhandene Preislisten zu prüfen.';
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
                    'description' => 'Name der Preisliste (ERFORDERLICH).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung der Preisliste.',
                ],
                'price_type' => [
                    'type' => 'string',
                    'description' => 'Optional: Preistyp. Default: "standard".',
                ],
                'priority' => [
                    'type' => 'integer',
                    'description' => 'Optional: Priorität der Preisliste.',
                ],
                'is_active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ob die Preisliste aktiv ist.',
                ],
                'valid_from' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültig ab (ISO 8601 Datum).',
                ],
                'valid_until' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültig bis (ISO 8601 Datum).',
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

            if (empty($arguments['name'])) {
                return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
            }

            $data = [
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $arguments['name'],
            ];

            if (array_key_exists('description', $arguments) && $arguments['description'] !== null) {
                $data['description'] = $arguments['description'];
            }
            if (array_key_exists('price_type', $arguments) && $arguments['price_type'] !== null) {
                $data['price_type'] = $arguments['price_type'];
            }
            if (array_key_exists('priority', $arguments)) {
                $data['priority'] = (int)$arguments['priority'];
            }
            if (array_key_exists('is_active', $arguments)) {
                $data['is_active'] = (bool)$arguments['is_active'];
            }
            if (array_key_exists('valid_from', $arguments) && $arguments['valid_from'] !== '' && $arguments['valid_from'] !== null) {
                $data['valid_from'] = $arguments['valid_from'];
            }
            if (array_key_exists('valid_until', $arguments) && $arguments['valid_until'] !== '' && $arguments['valid_until'] !== null) {
                $data['valid_until'] = $arguments['valid_until'];
            }

            $priceList = CommercePriceList::create($data);

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
                'message' => 'Preisliste erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Preisliste: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'price_lists', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
