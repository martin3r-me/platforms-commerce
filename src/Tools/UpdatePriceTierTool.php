<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommercePriceTier;

/**
 * Aktualisiert eine bestehende Preisstaffel.
 */
class UpdatePriceTierTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.price_tiers.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/price-tiers/{id} - Aktualisiert eine Preisstaffel. Nutze commerce.price_tiers.GET um die ID zu finden.';
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
                    'description' => 'ID der Preisstaffel (ERFORDERLICH). Nutze commerce.price_tiers.GET.',
                ],
                'commerce_price_list_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Preislisten-ID.',
                ],
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Artikel-ID.',
                ],
                'min_quantity' => [
                    'type' => 'number',
                    'description' => 'Optional: Neue Mindestmenge.',
                ],
                'max_quantity' => [
                    'type' => 'number',
                    'description' => 'Optional: Neue Höchstmenge (0 zum Leeren).',
                ],
                'price' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Preis.',
                ],
                'discount_percentage' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Rabattprozentsatz (0 zum Leeren).',
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
                CommercePriceTier::class,
                'NOT_FOUND',
                'Preisstaffel nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommercePriceTier $tier */
            $tier = $found['model'];
            if ((int)$tier->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Preisstaffel gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('commerce_price_list_id', $arguments)) {
                $update['commerce_price_list_id'] = (int)$arguments['commerce_price_list_id'];
            }
            if (array_key_exists('commerce_article_id', $arguments)) {
                $update['commerce_article_id'] = (int)$arguments['commerce_article_id'];
            }
            if (array_key_exists('min_quantity', $arguments)) {
                $update['min_quantity'] = $arguments['min_quantity'];
            }
            if (array_key_exists('max_quantity', $arguments)) {
                $val = $arguments['max_quantity'];
                $update['max_quantity'] = ($val === 0 || $val === '0') ? null : $val;
            }
            if (array_key_exists('price', $arguments)) {
                $update['price'] = $arguments['price'];
            }
            if (array_key_exists('discount_percentage', $arguments)) {
                $val = $arguments['discount_percentage'];
                $update['discount_percentage'] = ($val === 0 || $val === '0') ? null : $val;
            }

            if (!empty($update)) {
                $tier->update($update);
            }
            $tier->refresh();

            return ToolResult::success([
                'id' => $tier->id,
                'commerce_price_list_id' => $tier->commerce_price_list_id,
                'commerce_article_id' => $tier->commerce_article_id,
                'min_quantity' => $tier->min_quantity,
                'max_quantity' => $tier->max_quantity,
                'price' => $tier->price,
                'discount_percentage' => $tier->discount_percentage,
                'team_id' => $tier->team_id,
                'message' => 'Preisstaffel erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Preisstaffel: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'price_tiers', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
