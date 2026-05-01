<?php

namespace Platform\Commerce\Tools;

use Illuminate\Support\Str;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Enums\CatalogStatus;
use Platform\Commerce\Models\CommerceCatalog;

/**
 * Aktualisiert einen bestehenden Katalog.
 */
class UpdateCatalogTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.catalogs.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/catalogs/{id} - Aktualisiert einen Katalog. Nutze commerce.catalogs.GET um die ID zu finden.';
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
                    'description' => 'ID des Katalogs (ERFORDERLICH). Nutze commerce.catalogs.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
                'slug' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Slug. Muss pro Team eindeutig sein.',
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'active', 'archived'],
                    'description' => 'Optional: Neuer Status (draft|active|archived).',
                ],
                'valid_from' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültig ab (ISO 8601 Datum, null zum Leeren).',
                ],
                'valid_until' => [
                    'type' => 'string',
                    'description' => 'Optional: Gültig bis (ISO 8601 Datum, null zum Leeren).',
                ],
                'cover_image' => [
                    'type' => 'string',
                    'description' => 'Optional: Cover-Bild ("" zum Leeren).',
                ],
                'metadata' => [
                    'type' => 'object',
                    'description' => 'Optional: Metadaten (null zum Leeren).',
                ],
            ],
            'required' => ['id'],
        ]);
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
                CommerceCatalog::class,
                'NOT_FOUND',
                'Katalog nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceCatalog $catalog */
            $catalog = $found['model'];
            if ((int)$catalog->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Katalog gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            if (array_key_exists('description', $arguments)) {
                $d = (string)($arguments['description'] ?? '');
                $update['description'] = $d === '' ? null : $d;
            }

            if (array_key_exists('slug', $arguments)) {
                $slug = trim((string)($arguments['slug'] ?? ''));
                if ($slug === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'slug darf nicht leer sein.');
                }
                if ($slug !== $catalog->slug) {
                    $slugExists = CommerceCatalog::query()
                        ->where('team_id', $team->id)
                        ->where('slug', $slug)
                        ->where('id', '!=', $catalog->id)
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($slugExists) {
                        return ToolResult::error('VALIDATION_ERROR', "Katalog mit Slug '{$slug}' existiert bereits in diesem Team.");
                    }
                }
                $update['slug'] = $slug;
            }

            if (array_key_exists('status', $arguments)) {
                $statusValue = (string)($arguments['status'] ?? '');
                $validStatuses = array_map(fn ($s) => $s->value, CatalogStatus::cases());
                if (!in_array($statusValue, $validStatuses, true)) {
                    return ToolResult::error('VALIDATION_ERROR', "Ungültiger Status '{$statusValue}'. Erlaubt: " . implode(', ', $validStatuses));
                }
                $update['status'] = $statusValue;
            }

            if (array_key_exists('valid_from', $arguments)) {
                $update['valid_from'] = ($arguments['valid_from'] !== null && $arguments['valid_from'] !== '')
                    ? $arguments['valid_from'] : null;
            }

            if (array_key_exists('valid_until', $arguments)) {
                $update['valid_until'] = ($arguments['valid_until'] !== null && $arguments['valid_until'] !== '')
                    ? $arguments['valid_until'] : null;
            }

            if (array_key_exists('cover_image', $arguments)) {
                $ci = (string)($arguments['cover_image'] ?? '');
                $update['cover_image'] = $ci === '' ? null : $ci;
            }

            if (array_key_exists('metadata', $arguments)) {
                $update['metadata'] = $arguments['metadata'];
            }

            if (!empty($update)) {
                $catalog->update($update);
            }
            $catalog->refresh();

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
                'message' => 'Katalog erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Katalogs: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'catalogs', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
