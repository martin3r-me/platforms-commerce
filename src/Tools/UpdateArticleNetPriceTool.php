<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceArticleNetPrice;

/**
 * Aktualisiert einen bestehenden Artikel-Netto-Preis.
 */
class UpdateArticleNetPriceTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.article_net_prices.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/article-net-prices/{id} - Aktualisiert einen Artikel-Netto-Preis. Nutze commerce.article_net_prices.GET um die ID zu finden.';
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
                    'description' => 'ID des Artikel-Netto-Preises (ERFORDERLICH). Nutze commerce.article_net_prices.GET.',
                ],
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Artikel-ID.',
                ],
                'net_price' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Netto-Preis.',
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
                CommerceArticleNetPrice::class,
                'NOT_FOUND',
                'Artikel-Netto-Preis nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceArticleNetPrice $netPrice */
            $netPrice = $found['model'];
            if ((int)$netPrice->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Artikel-Netto-Preis gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('commerce_article_id', $arguments)) {
                $update['commerce_article_id'] = (int)$arguments['commerce_article_id'];
            }
            if (array_key_exists('net_price', $arguments)) {
                $update['net_price'] = $arguments['net_price'];
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
                $netPrice->update($update);
            }
            $netPrice->refresh();

            return ToolResult::success([
                'id' => $netPrice->id,
                'commerce_article_id' => $netPrice->commerce_article_id,
                'net_price' => $netPrice->net_price,
                'valid_from' => $netPrice->valid_from?->toIso8601String(),
                'valid_until' => $netPrice->valid_until?->toIso8601String(),
                'user_id' => $netPrice->user_id,
                'team_id' => $netPrice->team_id,
                'message' => 'Artikel-Netto-Preis erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Artikel-Netto-Preises: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'article_net_prices', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
