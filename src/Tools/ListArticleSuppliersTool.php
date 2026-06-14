<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticleSupplier;

/**
 * Listet Artikel-Lieferanten-Verknüpfungen, optional gefiltert auf einen Artikel oder Lieferanten.
 */
class ListArticleSuppliersTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.article_suppliers.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/article-suppliers - Listet Artikel↔Lieferanten-Verknüpfungen mit Einkaufspreis, Validity und is_preferred-Flag. Optional Filter nach article_id oder supplier_id.';
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
                    'description' => 'Optional: Nur Verknüpfungen dieses Artikels.',
                ],
                'supplier_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Nur Verknüpfungen dieses Lieferanten.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Optional: Maximale Anzahl Ergebnisse. Default: 50.',
                ],
                'offset' => [
                    'type' => 'integer',
                    'description' => 'Optional: Offset für Pagination. Default: 0.',
                ],
            ],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $teamId = $arguments['team_id'] ?? $context->team?->id;
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

            $limit  = (int) ($arguments['limit'] ?? 50);
            $offset = (int) ($arguments['offset'] ?? 0);

            $query = CommerceArticleSupplier::query()
                ->whereHas('article', fn ($q) => $q->where('team_id', $team->id));

            if (!empty($arguments['article_id'])) {
                $query->where('article_id', (int) $arguments['article_id']);
            }
            if (!empty($arguments['supplier_id'])) {
                $query->where('supplier_id', (int) $arguments['supplier_id']);
            }

            $total = (clone $query)->count();

            $links = $query->with(['article:id,name,sku', 'supplier:id,name'])
                ->orderByDesc('is_preferred')
                ->orderBy('article_id')
                ->orderBy('supplier_id')
                ->skip($offset)
                ->take($limit)
                ->get();

            $data = $links->map(fn ($link) => [
                'id'                => $link->id,
                'article_id'        => $link->article_id,
                'article_name'      => $link->article?->name,
                'article_sku'       => $link->article?->sku,
                'supplier_id'       => $link->supplier_id,
                'supplier_name'     => $link->supplier?->name,
                'external_id'       => $link->external_id,
                'purchase_price'    => $link->purchase_price,
                'purchase_currency' => $link->purchase_currency,
                'valid_from'        => $link->valid_from?->toDateString(),
                'valid_until'       => $link->valid_until?->toDateString(),
                'is_preferred'      => (bool) $link->is_preferred,
                'last_synced_at'    => $link->last_synced_at?->toDateTimeString(),
            ])->all();

            return ToolResult::success([
                'data' => $data,
                'pagination' => [
                    'limit'    => $limit,
                    'offset'   => $offset,
                    'total'    => $total,
                    'returned' => count($data),
                    'has_more' => ($offset + count($data)) < $total,
                ],
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Auflisten der Artikel-Lieferanten-Verknüpfungen: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category'      => 'lookup',
            'tags'          => ['commerce', 'article_suppliers', 'suppliers', 'articles', 'lookup'],
            'read_only'     => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level'    => 'read',
            'idempotent'    => true,
        ];
    }
}
