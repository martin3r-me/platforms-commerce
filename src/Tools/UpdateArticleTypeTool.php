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
 * Aktualisiert einen bestehenden Artikel-Typ.
 */
class UpdateArticleTypeTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.article_types.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/article-types/{id} - Aktualisiert einen Artikel-Typ. Nutze commerce.article_types.GET um die ID zu finden.';
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
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name des Artikel-Typs.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
                'color' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Farbe als Hex-Code ("" zum Leeren).',
                ],
                'active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: aktiv/inaktiv.',
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

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                // Check if name already exists in team (excluding current)
                $exists = CommerceArticleType::query()
                    ->where('team_id', $team->id)
                    ->where('name', $name)
                    ->where('id', '!=', $articleType->id)
                    ->whereNull('deleted_at')
                    ->exists();
                if ($exists) {
                    return ToolResult::error('VALIDATION_ERROR', "Artikel-Typ mit Name '{$name}' existiert bereits in diesem Team.");
                }
                $update['name'] = $name;
            }

            if (array_key_exists('description', $arguments)) {
                $d = (string)($arguments['description'] ?? '');
                $update['description'] = $d === '' ? null : $d;
            }

            if (array_key_exists('color', $arguments)) {
                $c = (string)($arguments['color'] ?? '');
                $update['color'] = $c === '' ? null : $c;
            }

            if (array_key_exists('active', $arguments)) {
                $update['active'] = (bool)$arguments['active'];
            }

            if (!empty($update)) {
                $articleType->update($update);
            }
            $articleType->refresh();

            return ToolResult::success([
                'id' => $articleType->id,
                'name' => $articleType->name,
                'description' => $articleType->description,
                'color' => $articleType->color,
                'active' => (bool)$articleType->active,
                'team_id' => $articleType->team_id,
                'message' => 'Artikel-Typ erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Artikel-Typs: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'article_types', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
