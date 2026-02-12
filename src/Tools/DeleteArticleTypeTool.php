<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceArticleType;

/**
 * Löscht einen Artikel-Typ (Soft-Delete).
 */
class DeleteArticleTypeTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.article_types.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /commerce/article-types/{id} - Löscht einen Artikel-Typ (Soft-Delete). Wenn Artikel zugewiesen sind, nutze stattdessen active=false über PATCH.';
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
                    'description' => 'ID des Artikel-Typs (ERFORDERLICH). Nutze commerce.article_types.GET.',
                ],
            ],
            'required' => ['id'],
        ]);
    }

    protected function getAccessAction(): string
    {
        return 'delete';
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
                CommerceArticleType::class,
                'NOT_FOUND',
                'Artikel-Typ nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceArticleType $articleType */
            $articleType = $found['model'];
            if ((int)$articleType->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Artikel-Typ gehört nicht zum angegebenen Team.');
            }

            // Check if articles are assigned to this type
            if ($articleType->articles()->exists()) {
                return ToolResult::error(
                    'VALIDATION_ERROR',
                    'Artikel-Typ hat noch zugewiesene Artikel und kann nicht gelöscht werden. Setze stattdessen active=false über commerce.article_types.PATCH.'
                );
            }

            $articleType->delete();

            return ToolResult::success([
                'id' => $articleType->id,
                'message' => 'Artikel-Typ gelöscht (Soft-Delete).',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Löschen des Artikel-Typs: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'article_types', 'delete'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
