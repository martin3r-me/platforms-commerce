<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticleAvailability;

/**
 * Erstellt eine neue Artikel-Verfügbarkeit.
 */
class CreateArticleAvailabilityTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.article_availabilities.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/article-availabilities - Erstellt eine neue Artikel-Verfügbarkeit für einen Sales-Context. Voraussetzung: Artikel (commerce.articles.GET) und Sales-Context (commerce.sales_contexts.GET) müssen existieren.';
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
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'Artikel-ID (ERFORDERLICH).',
                ],
                'commerce_sales_context_id' => [
                    'type' => 'integer',
                    'description' => 'Sales-Context-ID (ERFORDERLICH).',
                ],
                'is_available' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Ist der Artikel verfügbar? Default: true.',
                ],
                'available_from' => [
                    'type' => 'string',
                    'description' => 'Optional: Verfügbar ab (ISO 8601 Datum).',
                ],
                'available_until' => [
                    'type' => 'string',
                    'description' => 'Optional: Verfügbar bis (ISO 8601 Datum).',
                ],
                'max_quantity' => [
                    'type' => 'number',
                    'description' => 'Optional: Maximale bestellbare Menge.',
                ],
            ],
            'required' => ['commerce_article_id', 'commerce_sales_context_id'],
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

            if (!isset($arguments['commerce_article_id'])) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_article_id ist erforderlich.');
            }
            if (!isset($arguments['commerce_sales_context_id'])) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_sales_context_id ist erforderlich.');
            }

            $data = [
                'team_id' => $team->id,
                'commerce_article_id' => (int)$arguments['commerce_article_id'],
                'commerce_sales_context_id' => (int)$arguments['commerce_sales_context_id'],
                'is_available' => (bool)($arguments['is_available'] ?? true),
            ];

            if (array_key_exists('available_from', $arguments) && $arguments['available_from'] !== null) {
                $data['available_from'] = $arguments['available_from'];
            }
            if (array_key_exists('available_until', $arguments) && $arguments['available_until'] !== null) {
                $data['available_until'] = $arguments['available_until'];
            }
            if (array_key_exists('max_quantity', $arguments) && $arguments['max_quantity'] !== null) {
                $data['max_quantity'] = (float)$arguments['max_quantity'];
            }

            $availability = CommerceArticleAvailability::create($data);

            return ToolResult::success([
                'id' => $availability->id,
                'commerce_article_id' => $availability->commerce_article_id,
                'commerce_sales_context_id' => $availability->commerce_sales_context_id,
                'is_available' => (bool)$availability->is_available,
                'available_from' => $availability->available_from?->toIso8601String(),
                'available_until' => $availability->available_until?->toIso8601String(),
                'max_quantity' => $availability->max_quantity,
                'team_id' => $availability->team_id,
                'message' => 'Artikel-Verfügbarkeit erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Artikel-Verfügbarkeit: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'article_availabilities', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
