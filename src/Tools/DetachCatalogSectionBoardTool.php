<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceCatalogSection;
use Platform\Commerce\Models\CommerceProductBoard;

/**
 * Trennt ein Product-Board von einer Katalog-Sektion.
 */
class DetachCatalogSectionBoardTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.catalog_sections.boards.DETACH';
    }

    public function getDescription(): string
    {
        return 'DELETE /commerce/catalog-sections/{id}/boards/detach - Trennt ein Board von einer Katalog-Sektion.';
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
                'commerce_catalog_section_id' => [
                    'type' => 'integer',
                    'description' => 'ID der Katalog-Sektion (ERFORDERLICH).',
                ],
                'commerce_product_board_id' => [
                    'type' => 'integer',
                    'description' => 'ID des zu trennenden Product-Boards (ERFORDERLICH).',
                ],
            ],
            'required' => ['commerce_catalog_section_id', 'commerce_product_board_id'],
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

            // Validate section
            $sectionId = $arguments['commerce_catalog_section_id'] ?? null;
            if (!$sectionId) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_catalog_section_id ist erforderlich.');
            }

            $section = CommerceCatalogSection::find((int)$sectionId);
            if (!$section) {
                return ToolResult::error('NOT_FOUND', 'Katalog-Sektion nicht gefunden.');
            }
            if ((int)$section->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Katalog-Sektion gehört nicht zum angegebenen Team.');
            }

            // Validate board
            $boardId = $arguments['commerce_product_board_id'] ?? null;
            if (!$boardId) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_product_board_id ist erforderlich.');
            }

            $board = CommerceProductBoard::find((int)$boardId);
            if (!$board) {
                return ToolResult::error('NOT_FOUND', 'Product-Board nicht gefunden.');
            }

            // Check if attached
            $isAttached = $section->productBoards()->where('commerce_product_board_id', $board->id)->exists();
            if (!$isAttached) {
                return ToolResult::error('NOT_FOUND', "Board '{$board->name}' ist nicht mit Sektion '{$section->name}' verknüpft.");
            }

            // Detach
            $section->productBoards()->detach($board->id);

            // Get remaining boards
            $remainingBoards = $section->productBoards()->get()->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'sort_order' => $b->pivot->sort_order,
            ])->values()->toArray();

            return ToolResult::success([
                'commerce_catalog_section_id' => $section->id,
                'section_name' => $section->name,
                'detached_board_id' => $board->id,
                'detached_board_name' => $board->name,
                'remaining_boards' => $remainingBoards,
                'message' => "Board '{$board->name}' erfolgreich von Sektion '{$section->name}' getrennt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Trennen: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'catalogs', 'catalog_sections', 'product_boards', 'detach'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
