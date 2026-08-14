<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceSale;

/**
 * Erstellt einen neuen Verkauf.
 */
class CreateSaleTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.sales.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/sales - Erstellt einen neuen Verkauf. Nutze zuerst commerce.sales.GET um vorhandene Verkäufe zu prüfen.';
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
                'total_amount' => [
                    'type' => 'number',
                    'description' => 'Gesamtbetrag des Verkaufs (ERFORDERLICH).',
                ],
                'status' => [
                    'type' => 'string',
                    'description' => 'Optional: Start-Status (default: "draft"). Nur draft oder pending. Gebuchte Zustaende (confirmed/completed/...) NICHT hier setzen, sondern ueber commerce.sales.confirm/complete/cancel/refund (die den Bestand buchen).',
                    'enum' => ['draft', 'pending'],
                ],
                'paid_at' => [
                    'type' => 'string',
                    'description' => 'Optional: Bezahldatum (ISO 8601).',
                ],
            ],
            'required' => ['total_amount'],
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

            if (!array_key_exists('total_amount', $arguments) || $arguments['total_amount'] === null) {
                return ToolResult::error('VALIDATION_ERROR', 'total_amount ist erforderlich.');
            }

            $status = array_key_exists('status', $arguments) && $arguments['status'] !== null
                ? (string)$arguments['status']
                : 'draft';
            if (!in_array($status, ['draft', 'pending'], true)) {
                return ToolResult::error(
                    'VALIDATION_ERROR',
                    'Ein Verkauf kann nur als "draft" oder "pending" angelegt werden. Fuer gebuchte Zustaende nutze commerce.sales.confirm/complete/cancel/refund (buchen den Bestand).'
                );
            }

            $data = [
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'total_amount' => (float)$arguments['total_amount'],
                'status' => $status,
                'paid_at' => array_key_exists('paid_at', $arguments) && $arguments['paid_at'] !== null
                    ? (string)$arguments['paid_at']
                    : null,
            ];

            $sale = CommerceSale::create($data);

            return ToolResult::success([
                'id' => $sale->id,
                'user_id' => $sale->user_id,
                'team_id' => $sale->team_id,
                'total_amount' => $sale->total_amount,
                'paid_at' => $sale->paid_at?->toIso8601String(),
                'status' => $sale->status,
                'created_at' => $sale->created_at?->toIso8601String(),
                'message' => 'Verkauf erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Verkaufs: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'sales', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
