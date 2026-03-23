<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceStockReservation;

/**
 * Listet Bestandsreservierungen für ein Team.
 */
class ListStockReservationsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.stock_reservations.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/stock_reservations - Listet Bestandsreservierungen (id, commerce_article_id, commerce_warehouse_id, quantity, reference_type, reference_id, expires_at). Unterstützt filters/sort/limit/offset.';
    }

    public function getSchema(): array
    {
        return $this->mergeSchemas(
            $this->getStandardGetSchema(),
            [
                'properties' => [
                    'team_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Team-ID. Default: Team aus Kontext.',
                    ],
                    'commerce_article_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Artikel-ID.',
                    ],
                    'commerce_warehouse_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Filter nach Lager-ID.',
                    ],
                ],
            ]
        );
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

            $q = CommerceStockReservation::query()
                ->where('team_id', $team->id);

            if (array_key_exists('commerce_article_id', $arguments) && $arguments['commerce_article_id'] !== null) {
                $q->where('commerce_article_id', (int)$arguments['commerce_article_id']);
            }

            if (array_key_exists('commerce_warehouse_id', $arguments) && $arguments['commerce_warehouse_id'] !== null) {
                $q->where('commerce_warehouse_id', (int)$arguments['commerce_warehouse_id']);
            }

            $this->applyStandardFilters($q, $arguments, ['team_id', 'commerce_article_id', 'commerce_warehouse_id', 'created_at', 'expires_at']);
            $this->applyStandardSort($q, $arguments, ['id', 'created_at', 'expires_at', 'quantity'], 'id', 'desc');

            $result = $this->applyStandardPaginationResult($q, $arguments);
            $items = $result['data']->map(fn ($reservation) => [
                'id' => $reservation->id,
                'team_id' => $reservation->team_id,
                'user_id' => $reservation->user_id,
                'commerce_article_id' => $reservation->commerce_article_id,
                'commerce_warehouse_id' => $reservation->commerce_warehouse_id,
                'quantity' => (float)$reservation->quantity,
                'reference_type' => $reservation->reference_type,
                'reference_id' => $reservation->reference_id,
                'expires_at' => $reservation->expires_at?->toIso8601String(),
                'created_at' => $reservation->created_at?->toIso8601String(),
                'updated_at' => $reservation->updated_at?->toIso8601String(),
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $team->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Reservierungen: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'stock_reservations', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
