<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticlePrice;

/**
 * Erstellt einen neuen Artikel-Preis.
 */
class CreateArticlePriceTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.article_prices.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/article-prices - Erstellt einen neuen Artikel-Preis. Nutze zuerst commerce.article_prices.GET um vorhandene Preise zu prüfen.';
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
                'gross_price' => [
                    'type' => 'number',
                    'description' => 'Brutto-Preis (ERFORDERLICH).',
                ],
                'tax_rate' => [
                    'type' => 'number',
                    'description' => 'Steuersatz in Prozent (ERFORDERLICH).',
                ],
                'commerce_sales_context_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: ID des Verkaufskontexts.',
                ],
                'commerce_tax_category_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: ID der Steuerkategorie.',
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
            'required' => ['commerce_article_id', 'net_price', 'gross_price', 'tax_rate'],
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
            if (!isset($arguments['gross_price'])) {
                return ToolResult::error('VALIDATION_ERROR', 'gross_price ist erforderlich.');
            }
            if (!isset($arguments['tax_rate'])) {
                return ToolResult::error('VALIDATION_ERROR', 'tax_rate ist erforderlich.');
            }

            $data = [
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'commerce_article_id' => (int)$arguments['commerce_article_id'],
                'net_price' => $arguments['net_price'],
                'gross_price' => $arguments['gross_price'],
                'tax_rate' => $arguments['tax_rate'],
            ];

            if (!empty($arguments['commerce_sales_context_id'])) {
                $data['commerce_sales_context_id'] = (int)$arguments['commerce_sales_context_id'];
            }
            if (!empty($arguments['commerce_tax_category_id'])) {
                $data['commerce_tax_category_id'] = (int)$arguments['commerce_tax_category_id'];
            }
            if (array_key_exists('valid_from', $arguments) && $arguments['valid_from'] !== '' && $arguments['valid_from'] !== null) {
                $data['valid_from'] = $arguments['valid_from'];
            }
            if (array_key_exists('valid_until', $arguments) && $arguments['valid_until'] !== '' && $arguments['valid_until'] !== null) {
                $data['valid_until'] = $arguments['valid_until'];
            }

            $price = CommerceArticlePrice::create($data);

            return ToolResult::success([
                'id' => $price->id,
                'commerce_article_id' => $price->commerce_article_id,
                'commerce_sales_context_id' => $price->commerce_sales_context_id,
                'commerce_tax_category_id' => $price->commerce_tax_category_id,
                'net_price' => $price->net_price,
                'gross_price' => $price->gross_price,
                'tax_rate' => $price->tax_rate,
                'valid_from' => $price->valid_from?->toIso8601String(),
                'valid_until' => $price->valid_until?->toIso8601String(),
                'user_id' => $price->user_id,
                'team_id' => $price->team_id,
                'message' => 'Artikel-Preis erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Artikel-Preises: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'article_prices', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
