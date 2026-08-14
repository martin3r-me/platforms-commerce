<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Enums\SaleStatus;
use Platform\Commerce\Models\CommerceSale;
use Platform\Commerce\Services\SaleStockService;

/**
 * Basis fuer die expliziten Verkaufs-Statusuebergaenge (confirm/complete/
 * cancel/refund). Jeder Uebergang ist eine bewusste Beleg-Aktion, die den
 * Status setzt UND den Bestand bucht (siehe SaleStockService).
 */
abstract class AbstractSaleTransitionTool implements ToolContract, ToolMetadataContract
{
    /** Zielstatus dieses Uebergangs. */
    abstract protected function targetStatus(): SaleStatus;

    /** Kurzverb fuer Tool-Name/Tag, z.B. "confirm". */
    abstract protected function verb(): string;

    public function getName(): string
    {
        return 'commerce.sales.'.$this->verb();
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
                'sale_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Verkaufs (ERFORDERLICH). Nutze commerce.sales.GET.',
                ],
            ],
            'required' => ['sale_id'],
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

            if (!array_key_exists('sale_id', $arguments) || $arguments['sale_id'] === null) {
                return ToolResult::error('VALIDATION_ERROR', 'sale_id ist erforderlich.');
            }

            $sale = CommerceSale::find((int)$arguments['sale_id']);
            if (!$sale) {
                return ToolResult::error('NOT_FOUND', 'Verkauf nicht gefunden.');
            }
            if ((int)$sale->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Verkauf gehört nicht zum angegebenen Team.');
            }

            /** @var SaleStockService $service */
            $service = app(SaleStockService::class);

            try {
                $sale = $this->transition($service, $sale);
            } catch (\RuntimeException $e) {
                // Fachliche Buchungsfehler (z.B. zu wenig Bestand) -> Status bleibt unveraendert.
                return ToolResult::error('STOCK_ERROR', $e->getMessage());
            }

            return ToolResult::success([
                'id' => $sale->id,
                'status' => $sale->status,
                'team_id' => $sale->team_id,
                'message' => $this->successMessage(),
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Statuswechsel: ' . $e->getMessage());
        }
    }

    private function transition(SaleStockService $service, CommerceSale $sale): CommerceSale
    {
        return match ($this->targetStatus()) {
            SaleStatus::Confirmed => $service->confirm($sale),
            SaleStatus::Completed => $service->complete($sale),
            SaleStatus::Cancelled => $service->cancel($sale),
            SaleStatus::Refunded  => $service->refund($sale),
            default => $sale,
        };
    }

    protected function successMessage(): string
    {
        return 'Verkauf auf Status "'.$this->targetStatus()->value.'" gesetzt.';
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'sales', $this->verb(), 'stock'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
