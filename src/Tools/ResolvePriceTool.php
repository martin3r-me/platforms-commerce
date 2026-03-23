<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Services\PriceResolver;

class ResolvePriceTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.prices.resolve';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/prices/resolve - Ermittelt den besten Preis für einen Artikel basierend auf Kontext (Kundengruppe, Menge, Kanal, Zeitpunkt). Priorität: Promotional > CustomerGroup > Tier > Time-based > Standard.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer', 'description' => 'Optional: Team-ID.'],
                'commerce_article_id' => ['type' => 'integer', 'description' => 'Artikel-ID (ERFORDERLICH).'],
                'commerce_customer_group_id' => ['type' => 'integer', 'description' => 'Optional: Kundengruppen-ID für gruppenspezifische Preise.'],
                'commerce_sales_context_id' => ['type' => 'integer', 'description' => 'Optional: Sales-Context-ID.'],
                'quantity' => ['type' => 'number', 'description' => 'Optional: Menge für Staffelpreise. Default: 1.'],
                'date' => ['type' => 'string', 'description' => 'Optional: Datum (Y-m-d H:i:s) für zeitbasierte Preise. Default: jetzt.'],
            ],
            'required' => ['commerce_article_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $teamId = $arguments['team_id'] ?? $context->team?->id;
            if ($teamId === 0 || $teamId === '0') { $teamId = null; }
            if (!$teamId) { return ToolResult::error('MISSING_TEAM', 'Kein Team angegeben.'); }
            $team = Team::find((int)$teamId);
            if (!$team) { return ToolResult::error('TEAM_NOT_FOUND', 'Team nicht gefunden.'); }
            if (!$context->user) { return ToolResult::error('AUTH_ERROR', 'Kein User im Kontext.'); }
            $userHasAccess = $context->user->teams()->where('teams.id', $team->id)->exists();
            if (!$userHasAccess) { return ToolResult::error('ACCESS_DENIED', 'Kein Zugriff.'); }

            if (!isset($arguments['commerce_article_id'])) {
                return ToolResult::error('VALIDATION_ERROR', 'commerce_article_id ist erforderlich.');
            }

            $date = isset($arguments['date']) ? new \DateTime($arguments['date']) : null;

            $resolver = new PriceResolver();
            $result = $resolver->resolve(
                articleId: (int)$arguments['commerce_article_id'],
                teamId: $team->id,
                customerGroupId: isset($arguments['commerce_customer_group_id']) ? (int)$arguments['commerce_customer_group_id'] : null,
                salesContextId: isset($arguments['commerce_sales_context_id']) ? (int)$arguments['commerce_sales_context_id'] : null,
                quantity: (float)($arguments['quantity'] ?? 1),
                date: $date,
            );

            return ToolResult::success($result);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler bei Preisermittlung: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return ['category' => 'read', 'tags' => ['commerce', 'prices', 'resolve'], 'read_only' => true, 'requires_auth' => true, 'requires_team' => true, 'risk_level' => 'safe', 'idempotent' => true];
    }
}
