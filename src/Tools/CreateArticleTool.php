<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticle;
use Platform\Commerce\Models\CommerceArticleType;

/**
 * Erstellt einen neuen Artikel.
 */
class CreateArticleTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.articles.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/articles - Erstellt einen Artikel (Basiseinheit mit Preis/SKU). WICHTIG: Artikel sind die Grundlage für Produkte. Erst Artikel anlegen, dann über SlotVariants mit Produkten verbinden. Optional vorher: Artikel-Typen (commerce.article_types.GET) und Kategorien (commerce.categories.GET) anlegen.';
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
                    'description' => 'Name des Artikels (ERFORDERLICH).',
                ],
                'sku' => [
                    'type' => 'string',
                    'description' => 'Artikelnummer/SKU (ERFORDERLICH für Identifikation). Muss pro Team eindeutig sein.',
                ],
                'price' => [
                    'type' => 'number',
                    'description' => 'Preis in EUR (ERFORDERLICH).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung des Artikels.',
                ],
                'commerce_article_type_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Artikel-Typ ID. Nutze commerce.article_types.GET um verfügbare Typen zu finden.',
                ],
                'stock_level' => [
                    'type' => 'integer',
                    'description' => 'Optional: Lagerbestand. Default: 0.',
                ],
                'is_available' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Verfügbar. Default: true.',
                ],
            ],
            'required' => ['name', 'sku', 'price'],
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

            $name = trim((string)($arguments['name'] ?? ''));
            if ($name === '') {
                return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
            }

            $sku = trim((string)($arguments['sku'] ?? ''));
            if ($sku === '') {
                return ToolResult::error('VALIDATION_ERROR', 'sku ist erforderlich.');
            }

            if (!array_key_exists('price', $arguments) || $arguments['price'] === null || $arguments['price'] === '') {
                return ToolResult::error('VALIDATION_ERROR', 'price ist erforderlich.');
            }
            $price = (float)$arguments['price'];

            // Check if SKU already exists in team
            $skuExists = CommerceArticle::query()
                ->where('team_id', $team->id)
                ->where('sku', $sku)
                ->whereNull('deleted_at')
                ->exists();
            if ($skuExists) {
                return ToolResult::error('VALIDATION_ERROR', "Artikel mit SKU '{$sku}' existiert bereits in diesem Team.");
            }

            // Validate commerce_article_type_id if provided
            $articleTypeId = null;
            if (array_key_exists('commerce_article_type_id', $arguments) && $arguments['commerce_article_type_id'] !== null && $arguments['commerce_article_type_id'] !== '') {
                $articleTypeId = (int)$arguments['commerce_article_type_id'];
                $typeExists = CommerceArticleType::query()
                    ->where('id', $articleTypeId)
                    ->where('team_id', $team->id)
                    ->whereNull('deleted_at')
                    ->exists();
                if (!$typeExists) {
                    return ToolResult::error('VALIDATION_ERROR', "Artikel-Typ mit ID {$articleTypeId} nicht gefunden in diesem Team.");
                }
            }

            $article = CommerceArticle::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'sku' => $sku,
                'price' => $price,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'commerce_article_type_id' => $articleTypeId,
                'stock_level' => (int)($arguments['stock_level'] ?? 0),
                'is_available' => (bool)($arguments['is_available'] ?? true),
            ]);

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
                'message' => 'Artikel erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Artikels: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'articles', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
