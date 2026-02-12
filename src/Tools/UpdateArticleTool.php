<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceArticle;
use Platform\Commerce\Models\CommerceArticleType;

/**
 * Aktualisiert einen bestehenden Artikel.
 */
class UpdateArticleTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.articles.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/articles/{id} - Aktualisiert einen Artikel. Nutze commerce.articles.GET um die ID zu finden.';
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
                    'description' => 'ID des Artikels (ERFORDERLICH). Nutze commerce.articles.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name des Artikels.',
                ],
                'sku' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Artikelnummer/SKU. Muss pro Team eindeutig sein.',
                ],
                'price' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Preis in EUR.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
                'commerce_article_type_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Artikel-Typ ID (null zum Leeren).',
                ],
                'stock_level' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neuer Lagerbestand.',
                ],
                'is_available' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Neue Verfügbarkeit.',
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
                CommerceArticle::class,
                'NOT_FOUND',
                'Artikel nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceArticle $article */
            $article = $found['model'];
            if ((int)$article->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Artikel gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            if (array_key_exists('sku', $arguments)) {
                $sku = trim((string)($arguments['sku'] ?? ''));
                if ($sku === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'sku darf nicht leer sein.');
                }
                // Check uniqueness if SKU changed
                if ($sku !== $article->sku) {
                    $skuExists = CommerceArticle::query()
                        ->where('team_id', $team->id)
                        ->where('sku', $sku)
                        ->where('id', '!=', $article->id)
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($skuExists) {
                        return ToolResult::error('VALIDATION_ERROR', "Artikel mit SKU '{$sku}' existiert bereits in diesem Team.");
                    }
                }
                $update['sku'] = $sku;
            }

            if (array_key_exists('price', $arguments)) {
                if ($arguments['price'] === null || $arguments['price'] === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'price darf nicht leer sein.');
                }
                $update['price'] = (float)$arguments['price'];
            }

            if (array_key_exists('description', $arguments)) {
                $d = (string)($arguments['description'] ?? '');
                $update['description'] = $d === '' ? null : $d;
            }

            if (array_key_exists('commerce_article_type_id', $arguments)) {
                $articleTypeId = $arguments['commerce_article_type_id'];
                if ($articleTypeId === null || $articleTypeId === '' || $articleTypeId === 0) {
                    $update['commerce_article_type_id'] = null;
                } else {
                    $articleTypeId = (int)$articleTypeId;
                    $typeExists = CommerceArticleType::query()
                        ->where('id', $articleTypeId)
                        ->where('team_id', $team->id)
                        ->whereNull('deleted_at')
                        ->exists();
                    if (!$typeExists) {
                        return ToolResult::error('VALIDATION_ERROR', "Artikel-Typ mit ID {$articleTypeId} nicht gefunden in diesem Team.");
                    }
                    $update['commerce_article_type_id'] = $articleTypeId;
                }
            }

            if (array_key_exists('stock_level', $arguments)) {
                $update['stock_level'] = $arguments['stock_level'] !== null ? (int)$arguments['stock_level'] : 0;
            }

            if (array_key_exists('is_available', $arguments)) {
                $update['is_available'] = (bool)$arguments['is_available'];
            }

            if (!empty($update)) {
                $article->update($update);
            }
            $article->refresh();

            return ToolResult::success([
                'id' => $article->id,
                'name' => $article->name,
                'sku' => $article->sku,
                'price' => (float)$article->price,
                'description' => $article->description,
                'commerce_article_type_id' => $article->commerce_article_type_id,
                'stock_level' => (int)$article->stock_level,
                'is_available' => (bool)$article->is_available,
                'team_id' => $article->team_id,
                'message' => 'Artikel erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Artikels: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'articles', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
