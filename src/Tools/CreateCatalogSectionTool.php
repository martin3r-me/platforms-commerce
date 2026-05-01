<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceCatalog;
use Platform\Commerce\Models\CommerceCatalogSection;

/**
 * Erstellt eine neue Katalog-Sektion.
 */
class CreateCatalogSectionTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.catalog_sections.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/catalog-sections - Erstellt eine neue Sektion in einem Katalog.';
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
                'commerce_catalog_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Katalogs (ERFORDERLICH). Nutze commerce.catalogs.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Name der Sektion (ERFORDERLICH). z.B. "Frühstück", "Lunch".',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung der Sektion.',
                ],
                'sort_order' => [
                    'type' => 'integer',
                    'description' => 'Optional: Sortierreihenfolge. Default: 0.',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Farbe der Sektion.',
                ],
                'icon' => [
                    'type' => 'string',
                    'description' => 'Optional: Icon der Sektion.',
                ],
            ],
            'required' => ['commerce_catalog_id', 'name'],
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

            // Validate catalog
            $catalogId = $arguments['commerce_catalog_id'] ?? null;
            if (!$catalogId) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_catalog_id ist erforderlich.');
            }

            $catalog = CommerceCatalog::find((int)$catalogId);
            if (!$catalog) {
                return ToolResult::error('NOT_FOUND', 'Katalog nicht gefunden.');
            }
            if ((int)$catalog->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Katalog gehört nicht zum angegebenen Team.');
            }

            $name = trim((string)($arguments['name'] ?? ''));
            if ($name === '') {
                return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
            }

            $section = CommerceCatalogSection::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'commerce_catalog_id' => $catalog->id,
                'name' => $name,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'sort_order' => (int)($arguments['sort_order'] ?? 0),
                'color' => (array_key_exists('color', $arguments) && $arguments['color'] !== '')
                    ? (string)$arguments['color']
                    : null,
                'icon' => (array_key_exists('icon', $arguments) && $arguments['icon'] !== '')
                    ? (string)$arguments['icon']
                    : null,
            ]);

            return ToolResult::success([
                'id' => $section->id,
                'commerce_catalog_id' => $section->commerce_catalog_id,
                'name' => $section->name,
                'description' => $section->description,
                'sort_order' => $section->sort_order,
                'color' => $section->color,
                'icon' => $section->icon,
                'user_id' => $section->user_id,
                'team_id' => $section->team_id,
                'message' => 'Katalog-Sektion erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Katalog-Sektion: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'catalogs', 'catalog_sections', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
