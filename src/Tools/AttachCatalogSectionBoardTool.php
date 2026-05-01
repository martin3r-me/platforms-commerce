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
 * Verknüpft ein Product-Board mit einer Katalog-Sektion.
 */
class AttachCatalogSectionBoardTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.catalog_sections.boards.ATTACH';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/catalog-sections/{id}/boards/attach - Verknüpft ein Board mit einer Katalog-Sektion.';
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
                    'description' => 'ID der Katalog-Sektion (ERFORDERLICH). Nutze commerce.catalog_sections.GET.',
                ],
                'commerce_product_board_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Product-Boards (ERFORDERLICH). Nutze commerce.product_boards.GET.',
                ],
                'sort_order' => [
                    'type' => 'integer',
                    'description' => 'Optional: Sortierreihenfolge innerhalb der Sektion. Default: 0.',
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
            if ((int)$board->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Product-Board gehört nicht zum angegebenen Team.');
            }

            // Check if already attached
            $alreadyAttached = $section->productBoards()->where('commerce_product_board_id', $board->id)->exists();
            if ($alreadyAttached) {
                return ToolResult::error('ALREADY_EXISTS', "Board '{$board->name}' ist bereits mit Sektion '{$section->name}' verknüpft.");
            }

            // Attach with sort_order
            $sortOrder = (int)($arguments['sort_order'] ?? 0);
            $section->productBoards()->attach($board->id, ['sort_order' => $sortOrder]);

            // Get all attached boards
            $attachedBoards = $section->productBoards()->get()->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'sort_order' => $b->pivot->sort_order,
            ])->values()->toArray();

            return ToolResult::success([
                'commerce_catalog_section_id' => $section->id,
                'section_name' => $section->name,
                'attached_board_id' => $board->id,
                'attached_board_name' => $board->name,
                'sort_order' => $sortOrder,
                'all_attached_boards' => $attachedBoards,
                'message' => "Board '{$board->name}' erfolgreich mit Sektion '{$section->name}' verknüpft.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Verknüpfen: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'catalogs', 'catalog_sections', 'product_boards', 'attach'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
