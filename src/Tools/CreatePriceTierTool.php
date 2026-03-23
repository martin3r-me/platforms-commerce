<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommercePriceTier;

/**
 * Erstellt eine neue Preisstaffel.
 */
class CreatePriceTierTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.price_tiers.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/price-tiers - Erstellt eine neue Preisstaffel. Nutze zuerst commerce.price_tiers.GET um vorhandene Staffeln zu prüfen.';
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
                'commerce_price_list_id' => [
                    'type' => 'integer',
                    'description' => 'ID der Preisliste (ERFORDERLICH).',
                ],
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Artikels (ERFORDERLICH).',
                ],
                'price' => [
                    'type' => 'number',
                    'description' => 'Preis (ERFORDERLICH).',
                ],
                'min_quantity' => [
                    'type' => 'number',
                    'description' => 'Optional: Mindestmenge. Default: 1.',
                ],
                'max_quantity' => [
                    'type' => 'number',
                    'description' => 'Optional: Höchstmenge.',
                ],
                'discount_percentage' => [
                    'type' => 'number',
                    'description' => 'Optional: Rabattprozentsatz.',
                ],
            ],
            'required' => ['commerce_price_list_id', 'commerce_article_id', 'price'],
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

            if (empty($arguments['commerce_price_list_id'])) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_price_list_id ist erforderlich.');
            }
            if (empty($arguments['commerce_article_id'])) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_article_id ist erforderlich.');
            }
            if (!isset($arguments['price'])) {
                return ToolResult::error('VALIDATION_ERROR', 'price ist erforderlich.');
            }

            $data = [
                'team_id' => $team->id,
                'commerce_price_list_id' => (int)$arguments['commerce_price_list_id'],
                'commerce_article_id' => (int)$arguments['commerce_article_id'],
                'price' => $arguments['price'],
                'min_quantity' => $arguments['min_quantity'] ?? 1,
            ];

            if (array_key_exists('max_quantity', $arguments) && $arguments['max_quantity'] !== null) {
                $data['max_quantity'] = $arguments['max_quantity'];
            }
            if (array_key_exists('discount_percentage', $arguments) && $arguments['discount_percentage'] !== null) {
                $data['discount_percentage'] = $arguments['discount_percentage'];
            }

            $tier = CommercePriceTier::create($data);

            return ToolResult::success([
                'id' => $tier->id,
                'commerce_price_list_id' => $tier->commerce_price_list_id,
                'commerce_article_id' => $tier->commerce_article_id,
                'min_quantity' => $tier->min_quantity,
                'max_quantity' => $tier->max_quantity,
                'price' => $tier->price,
                'discount_percentage' => $tier->discount_percentage,
                'team_id' => $tier->team_id,
                'message' => 'Preisstaffel erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Preisstaffel: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'price_tiers', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
