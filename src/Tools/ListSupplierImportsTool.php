<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Models\CommerceSupplierImport;

class ListSupplierImportsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.supplier_imports.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/supplier_imports - Listet Import-Logs eines Lieferanten (status, rows_received/created/updated/skipped, duration_ms).';
    }

    public function getSchema(): array
    {
        return $this->mergeSchemas(
            $this->getStandardGetSchema(),
            [
                'properties' => [
                    'team_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: Team-ID.',
                    ],
                    'commerce_supplier_id' => [
                        'type' => 'integer',
                        'description' => 'ERFORDERLICH: ID des Lieferanten.',
                    ],
                ],
                'required' => ['commerce_supplier_id'],
            ]
        );
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $teamId = $arguments['team_id'] ?? $context->team?->id;
            if (!$teamId) {
                return ToolResult::error('MISSING_TEAM', 'Kein Team angegeben.');
            }

            $team = Team::find((int)$teamId);
            if (!$team || !$context->user?->teams()->where('teams.id', $team->id)->exists()) {
                return ToolResult::error('ACCESS_DENIED', 'Kein Zugriff.');
            }

            $supplier = CommerceSupplier::where('id', $arguments['commerce_supplier_id'])
                ->where('team_id', $team->id)
                ->first();

            if (!$supplier) {
                return ToolResult::error('NOT_FOUND', 'Lieferant nicht gefunden.');
            }

            $q = CommerceSupplierImport::query()
                ->where('commerce_supplier_id', $supplier->id);

            $this->applyStandardSort($q, $arguments, ['created_at', 'id', 'status'], 'created_at', 'desc');
            $result = $this->applyStandardPaginationResult($q, $arguments);

            $items = $result['data']->map(fn ($i) => [
                'id' => $i->id,
                'status' => $i->status,
                'rows_received' => $i->rows_received,
                'rows_created' => $i->rows_created,
                'rows_updated' => $i->rows_updated,
                'rows_skipped' => $i->rows_skipped,
                'duration_ms' => $i->duration_ms,
                'error_log' => $i->error_log,
                'created_at' => $i->created_at?->toIso8601String(),
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $items,
                'pagination' => $result['pagination'] ?? null,
                'commerce_supplier_id' => $supplier->id,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'read',
            'tags' => ['commerce', 'suppliers', 'imports', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
