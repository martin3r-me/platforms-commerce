<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Services\RuleEngine;

class EvaluateRulesTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.rules.evaluate';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/rules/evaluate - Evaluiert alle aktiven Regeln für ein Produkt/Artikel mit gegebenem Kontext. Regeltypen: Mengenlimit (max_quantity), Mindestbestellwert (min_order_value), Verkaufszeitraum (sale_period), Pflichtfelder (mandatory_field), Produktabhängigkeit (requires_product).';
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
                'target_id' => [
                    'type' => 'integer',
                    'description' => 'Produkt- oder Artikel-ID (ERFORDERLICH).',
                ],
                'target_type' => [
                    'type' => 'string',
                    'description' => 'Typ: "product" oder "article" (ERFORDERLICH).',
                    'enum' => ['product', 'article'],
                ],
                'quantity' => [
                    'type' => 'number',
                    'description' => 'Optional: Bestellmenge.',
                ],
                'order_value' => [
                    'type' => 'number',
                    'description' => 'Optional: Bestellwert.',
                ],
                'cart_product_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'integer'],
                    'description' => 'Optional: Produkt-IDs im Warenkorb.',
                ],
                'data' => [
                    'type' => 'object',
                    'description' => 'Optional: Key-Value-Objekt mit Felddaten für Pflichtfeld-Regeln (mandatory_field). Beispiel: {"allergens": "Nüsse", "origin": "DE"}.',
                ],
            ],
            'required' => ['target_id', 'target_type'],
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

            $targetTypeMap = [
                'product' => \Platform\Commerce\Models\CommerceProduct::class,
                'article' => \Platform\Commerce\Models\CommerceArticle::class,
            ];
            $targetType = $targetTypeMap[$arguments['target_type']] ?? $arguments['target_type'];

            $engine = new RuleEngine();
            $results = $engine->evaluate(
                (int)$arguments['target_id'],
                $targetType,
                [
                    'quantity' => $arguments['quantity'] ?? null,
                    'order_value' => $arguments['order_value'] ?? null,
                    'cart_product_ids' => $arguments['cart_product_ids'] ?? [],
                    'data' => $arguments['data'] ?? [],
                ],
                $team->id,
            );

            $allPassed = true;
            $resultData = [];
            foreach ($results as $r) {
                $resultData[] = $r->toArray();
                if (!$r->passed) {
                    $allPassed = false;
                }
            }

            return ToolResult::success([
                'all_passed' => $allPassed,
                'results' => $resultData,
                'count' => count($resultData),
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler bei Regel-Evaluierung: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'rules', 'evaluate'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
