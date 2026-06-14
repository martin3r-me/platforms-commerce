<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticle;
use Platform\Commerce\Models\CommerceArticleSupplier;
use Platform\Commerce\Models\CommerceSupplier;

/**
 * Verknüpft einen Artikel mit einem Lieferanten (n:m) inklusive Einkaufspreis,
 * Validity-Range und Preferred-Flag (Make-or-Buy-Pattern).
 */
class CreateArticleSupplierTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.article_suppliers.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/article-suppliers - Verknüpft einen Artikel mit einem Lieferanten (n:m). Trägt optional Einkaufspreis, Währung, Validity-Range und is_preferred-Flag (Make-or-Buy-Pattern). Nutze zuerst commerce.article_suppliers.GET um vorhandene Verknüpfungen zu prüfen.';
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
                'article_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Artikels (ERFORDERLICH).',
                ],
                'supplier_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Lieferanten (ERFORDERLICH).',
                ],
                'external_id' => [
                    'type' => 'string',
                    'description' => 'Optional: Externe SKU/Artikelnummer beim Lieferanten.',
                ],
                'purchase_price' => [
                    'type' => 'number',
                    'description' => 'Optional: Einkaufspreis pro Basiseinheit beim Lieferanten.',
                ],
                'purchase_currency' => [
                    'type' => 'string',
                    'description' => 'Optional: ISO-Währungscode des EK-Preises. Default: EUR.',
                ],
                'valid_from' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültigkeitsbeginn des EK-Preises (YYYY-MM-DD).',
                ],
                'valid_until' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültigkeitsende des EK-Preises (YYYY-MM-DD).',
                ],
                'is_preferred' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Markiert diesen Lieferanten als bevorzugte Quelle für den Artikel.',
                ],
            ],
            'required' => ['article_id', 'supplier_id'],
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

            $team = Team::find((int) $teamId);
            if (!$team) {
                return ToolResult::error('TEAM_NOT_FOUND', 'Team nicht gefunden.');
            }

            if (!$context->user) {
                return ToolResult::error('AUTH_ERROR', 'Kein User im Kontext gefunden.');
            }
            if (!$context->user->teams()->where('teams.id', $team->id)->exists()) {
                return ToolResult::error('ACCESS_DENIED', 'Du hast keinen Zugriff auf dieses Team.');
            }

            $articleId  = (int) ($arguments['article_id'] ?? 0);
            $supplierId = (int) ($arguments['supplier_id'] ?? 0);

            if (!$articleId || !$supplierId) {
                return ToolResult::error('VALIDATION_ERROR', 'article_id und supplier_id sind erforderlich.');
            }

            $article  = CommerceArticle::where('team_id', $team->id)->find($articleId);
            $supplier = CommerceSupplier::where('team_id', $team->id)->find($supplierId);

            if (!$article) {
                return ToolResult::error('NOT_FOUND', "Artikel {$articleId} nicht in Team {$team->id} gefunden.");
            }
            if (!$supplier) {
                return ToolResult::error('NOT_FOUND', "Lieferant {$supplierId} nicht in Team {$team->id} gefunden.");
            }

            $existing = CommerceArticleSupplier::where('article_id', $articleId)
                ->where('supplier_id', $supplierId)
                ->first();
            if ($existing) {
                return ToolResult::error('DUPLICATE', "Verknüpfung existiert bereits (id={$existing->id}). Nutze commerce.article_suppliers.PATCH zum Aktualisieren.");
            }

            $data = [
                'article_id'  => $articleId,
                'supplier_id' => $supplierId,
            ];

            foreach (['external_id', 'purchase_price', 'purchase_currency', 'valid_from', 'valid_until', 'is_preferred'] as $field) {
                if (array_key_exists($field, $arguments) && $arguments[$field] !== null && $arguments[$field] !== '') {
                    $data[$field] = $arguments[$field];
                }
            }

            // is_preferred: wenn true, allen anderen Verknüpfungen dieses Artikels den Flag entziehen
            if (!empty($data['is_preferred'])) {
                CommerceArticleSupplier::where('article_id', $articleId)
                    ->where('is_preferred', true)
                    ->update(['is_preferred' => false]);
            }

            $link = CommerceArticleSupplier::create($data);

            return ToolResult::success([
                'id'                => $link->id,
                'article_id'        => $link->article_id,
                'supplier_id'       => $link->supplier_id,
                'external_id'       => $link->external_id,
                'purchase_price'    => $link->purchase_price,
                'purchase_currency' => $link->purchase_currency,
                'valid_from'        => $link->valid_from?->toDateString(),
                'valid_until'       => $link->valid_until?->toDateString(),
                'is_preferred'      => (bool) $link->is_preferred,
                'message'           => "Artikel '{$article->name}' mit Lieferant '{$supplier->name}' verknüpft.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Verknüpfen von Artikel und Lieferant: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category'      => 'action',
            'tags'          => ['commerce', 'article_suppliers', 'suppliers', 'articles', 'create'],
            'read_only'     => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level'    => 'write',
            'idempotent'    => false,
        ];
    }
}
