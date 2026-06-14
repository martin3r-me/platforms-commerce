<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticleSupplier;

/**
 * Aktualisiert eine bestehende Artikel-Lieferanten-Verknüpfung.
 */
class UpdateArticleSupplierTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.article_suppliers.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/article-suppliers/{id} - Aktualisiert eine Artikel↔Lieferanten-Verknüpfung. Wenn is_preferred=true gesetzt wird, verlieren andere Verknüpfungen desselben Artikels den Flag.';
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
                'id' => [
                    'type' => 'integer',
                    'description' => 'ID der Artikel-Lieferanten-Verknüpfung (ERFORDERLICH).',
                ],
                'external_id' => [
                    'type' => 'string',
                    'description' => 'Optional: Externe SKU/Artikelnummer beim Lieferanten.',
                ],
                'purchase_price' => [
                    'type' => 'number',
                    'description' => 'Optional: Einkaufspreis pro Basiseinheit.',
                ],
                'purchase_currency' => [
                    'type' => 'string',
                    'description' => 'Optional: ISO-Währungscode.',
                ],
                'valid_from' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültigkeitsbeginn (YYYY-MM-DD).',
                ],
                'valid_until' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültigkeitsende (YYYY-MM-DD).',
                ],
                'is_preferred' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Bevorzugte Quelle für diesen Artikel.',
                ],
            ],
            'required' => ['id'],
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

            $id = (int) ($arguments['id'] ?? 0);
            if (!$id) {
                return ToolResult::error('VALIDATION_ERROR', 'id ist erforderlich.');
            }

            $link = CommerceArticleSupplier::with('article')->find($id);
            if (!$link || ($link->article && $link->article->team_id !== $team->id)) {
                return ToolResult::error('NOT_FOUND', "Verknüpfung {$id} nicht in Team {$team->id} gefunden.");
            }

            $updates = [];
            foreach (['external_id', 'purchase_price', 'purchase_currency', 'valid_from', 'valid_until', 'is_preferred'] as $field) {
                if (array_key_exists($field, $arguments)) {
                    $updates[$field] = $arguments[$field];
                }
            }

            if (!empty($updates['is_preferred'])) {
                CommerceArticleSupplier::where('article_id', $link->article_id)
                    ->where('id', '!=', $link->id)
                    ->where('is_preferred', true)
                    ->update(['is_preferred' => false]);
            }

            $link->fill($updates)->save();
            $link->refresh();

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
                'message'           => 'Artikel-Lieferanten-Verknüpfung erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Verknüpfung: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category'      => 'action',
            'tags'          => ['commerce', 'article_suppliers', 'update'],
            'read_only'     => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level'    => 'write',
            'idempotent'    => true,
        ];
    }
}
