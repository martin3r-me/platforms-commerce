<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticleSupplier;

/**
 * Löst eine Artikel-Lieferanten-Verknüpfung wieder auf.
 */
class DeleteArticleSupplierTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.article_suppliers.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /commerce/article-suppliers/{id} - Entfernt eine Artikel↔Lieferanten-Verknüpfung. Das Pivot-Modell hat kein Soft-Delete — die Verknüpfung wird endgültig entfernt.';
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

            $articleId  = $link->article_id;
            $supplierId = $link->supplier_id;

            $link->delete();

            return ToolResult::success([
                'id'          => $id,
                'article_id'  => $articleId,
                'supplier_id' => $supplierId,
                'message'     => 'Artikel-Lieferanten-Verknüpfung entfernt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Entfernen der Verknüpfung: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category'      => 'action',
            'tags'          => ['commerce', 'article_suppliers', 'delete'],
            'read_only'     => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level'    => 'write',
            'idempotent'    => true,
        ];
    }
}
