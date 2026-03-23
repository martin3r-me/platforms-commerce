<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceSale;

/**
 * Aktualisiert einen bestehenden Verkauf.
 */
class UpdateSaleTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.sales.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/sales/{id} - Aktualisiert einen Verkauf. Nutze commerce.sales.GET um die ID zu finden.';
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
                    'description' => 'ID des Verkaufs (ERFORDERLICH). Nutze commerce.sales.GET.',
                ],
                'total_amount' => [
                    'type' => 'number',
                    'description' => 'Optional: Neuer Gesamtbetrag.',
                ],
                'status' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Status.',
                ],
                'paid_at' => [
                    'type' => 'string',
                    'description' => 'Optional: Neues Bezahldatum (ISO 8601, "" zum Leeren).',
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
                CommerceSale::class,
                'NOT_FOUND',
                'Verkauf nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceSale $sale */
            $sale = $found['model'];
            if ((int)$sale->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Verkauf gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('total_amount', $arguments)) {
                $update['total_amount'] = (float)$arguments['total_amount'];
            }

            if (array_key_exists('status', $arguments)) {
                $update['status'] = (string)$arguments['status'];
            }

            if (array_key_exists('paid_at', $arguments)) {
                $v = $arguments['paid_at'];
                $update['paid_at'] = ($v === null || $v === '') ? null : (string)$v;
            }

            if (!empty($update)) {
                $sale->update($update);
            }
            $sale->refresh();

            return ToolResult::success([
                'id' => $sale->id,
                'user_id' => $sale->user_id,
                'team_id' => $sale->team_id,
                'total_amount' => $sale->total_amount,
                'paid_at' => $sale->paid_at?->toIso8601String(),
                'status' => $sale->status,
                'created_at' => $sale->created_at?->toIso8601String(),
                'message' => 'Verkauf erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Verkaufs: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'sales', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
