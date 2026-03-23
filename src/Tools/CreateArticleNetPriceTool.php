<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticleNetPrice;

/**
 * Erstellt einen neuen Artikel-Netto-Preis.
 */
class CreateArticleNetPriceTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.article_net_prices.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/article-net-prices - Erstellt einen neuen Artikel-Netto-Preis. Nutze zuerst commerce.article_net_prices.GET um vorhandene Netto-Preise zu prüfen.';
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
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Artikels (ERFORDERLICH).',
                ],
                'net_price' => [
                    'type' => 'number',
                    'description' => 'Netto-Preis (ERFORDERLICH).',
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
            'required' => ['commerce_article_id', 'net_price'],
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

            if (empty($arguments['commerce_article_id'])) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_article_id ist erforderlich.');
            }
            if (!isset($arguments['net_price'])) {
                return ToolResult::error('VALIDATION_ERROR', 'net_price ist erforderlich.');
            }

            $data = [
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'commerce_article_id' => (int)$arguments['commerce_article_id'],
                'net_price' => $arguments['net_price'],
            ];

            if (array_key_exists('valid_from', $arguments) && $arguments['valid_from'] !== '' && $arguments['valid_from'] !== null) {
                $data['valid_from'] = $arguments['valid_from'];
            }
            if (array_key_exists('valid_until', $arguments) && $arguments['valid_until'] !== '' && $arguments['valid_until'] !== null) {
                $data['valid_until'] = $arguments['valid_until'];
            }

            $netPrice = CommerceArticleNetPrice::create($data);

            return ToolResult::success([
                'id' => $netPrice->id,
                'commerce_article_id' => $netPrice->commerce_article_id,
                'net_price' => $netPrice->net_price,
                'valid_from' => $netPrice->valid_from?->toIso8601String(),
                'valid_until' => $netPrice->valid_until?->toIso8601String(),
                'user_id' => $netPrice->user_id,
                'team_id' => $netPrice->team_id,
                'message' => 'Artikel-Netto-Preis erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Artikel-Netto-Preises: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'article_net_prices', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
