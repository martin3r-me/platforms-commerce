<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticleAvailability;
use Platform\Commerce\Models\CommerceArticle;

class CheckAvailabilityTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.sales_contexts.availability_check';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/sales_contexts/availability_check - Prüft ob ein Artikel in einem Sales-Context verfügbar ist.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Team-ID.',
                ],
                'commerce_article_id' => [
                    'type' => 'integer',
                    'description' => 'Artikel-ID (ERFORDERLICH).',
                ],
                'commerce_sales_context_id' => [
                    'type' => 'integer',
                    'description' => 'Sales-Context-ID (ERFORDERLICH).',
                ],
                'quantity' => [
                    'type' => 'number',
                    'description' => 'Optional: Gewünschte Menge.',
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
                return ToolResult::error('MISSING_TEAM', 'Kein Team angegeben.');
            }

            $team = Team::find((int)$teamId);
            if (!$team) {
                return ToolResult::error('TEAM_NOT_FOUND', 'Team nicht gefunden.');
            }

            if (!$context->user) {
                return ToolResult::error('AUTH_ERROR', 'Kein User.');
            }
            $userHasAccess = $context->user->teams()->where('teams.id', $team->id)->exists();
            if (!$userHasAccess) {
                return ToolResult::error('ACCESS_DENIED', 'Kein Zugriff.');
            }

            $articleId = (int)$arguments['commerce_article_id'];
            $contextId = (int)$arguments['commerce_sales_context_id'];
            $requestedQty = isset($arguments['quantity']) ? (float)$arguments['quantity'] : null;

            $article = CommerceArticle::where('id', $articleId)->where('team_id', $team->id)->first();
            if (!$article) {
                return ToolResult::error('NOT_FOUND', 'Artikel nicht gefunden.');
            }

            $availability = CommerceArticleAvailability::where('commerce_article_id', $articleId)
                ->where('commerce_sales_context_id', $contextId)
                ->where('team_id', $team->id)
                ->first();

            $now = now();
            $isAvailable = true;
            $reasons = [];

            // Check article-level availability
            if (!$article->is_available) {
                $isAvailable = false;
                $reasons[] = 'Article is not available.';
            }

            if ($availability) {
                if (!$availability->is_available) {
                    $isAvailable = false;
                    $reasons[] = 'Not available in this sales context.';
                }
                if ($availability->available_from && $now < $availability->available_from) {
                    $isAvailable = false;
                    $reasons[] = 'Availability period has not started.';
                }
                if ($availability->available_until && $now > $availability->available_until) {
                    $isAvailable = false;
                    $reasons[] = 'Availability period has ended.';
                }
                if ($requestedQty !== null && $availability->max_quantity !== null && $requestedQty > (float)$availability->max_quantity) {
                    $isAvailable = false;
                    $reasons[] = "Requested quantity {$requestedQty} exceeds max {$availability->max_quantity}.";
                }
            }

            return ToolResult::success([
                'is_available' => $isAvailable,
                'reasons' => $reasons,
                'article_id' => $articleId,
                'sales_context_id' => $contextId,
                'availability' => $availability ? [
                    'is_available' => (bool)$availability->is_available,
                    'available_from' => $availability->available_from?->toIso8601String(),
                    'available_until' => $availability->available_until?->toIso8601String(),
                    'max_quantity' => $availability->max_quantity ? (float)$availability->max_quantity : null,
                ] : null,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'availability', 'check'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
