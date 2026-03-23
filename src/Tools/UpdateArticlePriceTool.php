<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceArticlePrice;

/**
 * Aktualisiert einen bestehenden Artikel-Preis.
 */
class UpdateArticlePriceTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.article_prices.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/article-prices/{id} - Aktualisiert einen Artikel-Preis. Nutze commerce.article_prices.GET um die ID zu finden.';
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
                    'description' => 'ID des Artikel-Preises (ERFORDERLICH). Nutze commerce.article_prices.GET.',
                ],
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Artikel-ID.',
                ],
                'commerce_sales_context_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Verkaufskontext-ID (0 zum Leeren).',
                ],
                'commerce_tax_category_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Steuerkategorie-ID (0 zum Leeren).',
                ],
                'net_price' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Netto-Preis.',
                ],
                'gross_price' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Brutto-Preis.',
                ],
                'tax_rate' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Steuersatz.',
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
                CommerceArticlePrice::class,
                'NOT_FOUND',
                'Artikel-Preis nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceArticlePrice $price */
            $price = $found['model'];
            if ((int)$price->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Artikel-Preis gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('commerce_article_id', $arguments)) {
                $update['commerce_article_id'] = (int)$arguments['commerce_article_id'];
            }
            if (array_key_exists('commerce_sales_context_id', $arguments)) {
                $val = $arguments['commerce_sales_context_id'];
                $update['commerce_sales_context_id'] = ($val === 0 || $val === '0') ? null : (int)$val;
            }
            if (array_key_exists('commerce_tax_category_id', $arguments)) {
                $val = $arguments['commerce_tax_category_id'];
                $update['commerce_tax_category_id'] = ($val === 0 || $val === '0') ? null : (int)$val;
            }
            if (array_key_exists('net_price', $arguments)) {
                $update['net_price'] = $arguments['net_price'];
            }
            if (array_key_exists('gross_price', $arguments)) {
                $update['gross_price'] = $arguments['gross_price'];
            }
            if (array_key_exists('tax_rate', $arguments)) {
                $update['tax_rate'] = $arguments['tax_rate'];
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
                $price->update($update);
            }
            $price->refresh();

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
                'message' => 'Artikel-Preis erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Artikel-Preises: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'article_prices', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
