<?php

namespace Platform\Commerce\Tools;

use Illuminate\Support\Str;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Enums\CatalogStatus;
use Platform\Commerce\Models\CommerceCatalog;

/**
 * Erstellt einen neuen Katalog.
 */
class CreateCatalogTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.catalogs.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/catalogs - Erstellt einen neuen Katalog. Auto-Slug via Name.';
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
                    'description' => 'Name des Katalogs (ERFORDERLICH). z.B. "FoodBook 2026 DE".',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung des Katalogs.',
                ],
                'slug' => [
                    'type' => 'string',
                    'description' => 'Optional: Slug (URL-freundlich). Wird automatisch aus Name generiert wenn leer.',
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'active', 'archived'],
                    'description' => 'Optional: Status (draft|active|archived). Default: draft.',
                ],
                'valid_from' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültig ab (ISO 8601 Datum).',
                ],
                'valid_until' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültig bis (ISO 8601 Datum).',
                ],
                'cover_image' => [
                    'type' => 'string',
                    'description' => 'Optional: URL oder Pfad zum Cover-Bild.',
                ],
                'metadata' => [
                    'type' => 'object',
                    'description' => 'Optional: Zusätzliche Metadaten (Sprache, Version, etc.).',
                ],
            ],
            'required' => ['name'],
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

            // Status validation
            $status = 'draft';
            if (array_key_exists('status', $arguments) && $arguments['status'] !== null && $arguments['status'] !== '') {
                $statusValue = (string)$arguments['status'];
                $validStatuses = array_map(fn ($s) => $s->value, CatalogStatus::cases());
                if (!in_array($statusValue, $validStatuses, true)) {
                    return ToolResult::error('VALIDATION_ERROR', "Ungültiger Status '{$statusValue}'. Erlaubt: " . implode(', ', $validStatuses));
                }
                $status = $statusValue;
            }

            // Slug generation
            $slug = trim((string)($arguments['slug'] ?? ''));
            if ($slug === '') {
                $slug = Str::slug($name);
            }

            // Ensure slug uniqueness per team
            $baseSlug = $slug;
            $counter = 1;
            while (CommerceCatalog::query()
                ->where('team_id', $team->id)
                ->where('slug', $slug)
                ->whereNull('deleted_at')
                ->exists()
            ) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $catalog = CommerceCatalog::create([
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'slug' => $slug,
                'status' => $status,
                'valid_from' => (array_key_exists('valid_from', $arguments) && $arguments['valid_from'] !== null && $arguments['valid_from'] !== '')
                    ? $arguments['valid_from']
                    : null,
                'valid_until' => (array_key_exists('valid_until', $arguments) && $arguments['valid_until'] !== null && $arguments['valid_until'] !== '')
                    ? $arguments['valid_until']
                    : null,
                'cover_image' => (array_key_exists('cover_image', $arguments) && $arguments['cover_image'] !== '')
                    ? (string)$arguments['cover_image']
                    : null,
                'metadata' => $arguments['metadata'] ?? null,
            ]);

            return ToolResult::success([
                'id' => $catalog->id,
                'name' => $catalog->name,
                'slug' => $catalog->slug,
                'status' => $catalog->status?->value ?? $catalog->status,
                'description' => $catalog->description,
                'valid_from' => $catalog->valid_from?->toIso8601String(),
                'valid_until' => $catalog->valid_until?->toIso8601String(),
                'cover_image' => $catalog->cover_image,
                'metadata' => $catalog->metadata,
                'user_id' => $catalog->user_id,
                'team_id' => $catalog->team_id,
                'message' => 'Katalog erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Katalogs: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'catalogs', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
