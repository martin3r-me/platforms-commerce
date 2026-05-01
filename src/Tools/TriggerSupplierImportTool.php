<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Services\SupplierImportService;

class TriggerSupplierImportTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.supplier_imports.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/supplier_imports - Triggert einen manuellen Import mit JSON-Payload für einen aktiven Lieferanten.';
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
                'commerce_supplier_id' => [
                    'type' => 'integer',
                    'description' => 'ERFORDERLICH: ID des Lieferanten.',
                ],
                'payload' => [
                    'type' => ['array', 'object'],
                    'description' => 'ERFORDERLICH: JSON-Payload mit den zu importierenden Daten. Array von Objekten oder einzelnes Objekt.',
                ],
            ],
            'required' => ['commerce_supplier_id', 'payload'],
        ];
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

            if (!$supplier->isActive()) {
                return ToolResult::error('NOT_ACTIVE', 'Lieferant ist nicht aktiv. Status: ' . ($supplier->status?->value ?? 'unbekannt'));
            }

            $payload = $arguments['payload'] ?? null;
            if (empty($payload)) {
                return ToolResult::error('VALIDATION_ERROR', 'payload ist erforderlich.');
            }

            $importService = app(SupplierImportService::class);
            $import = $importService->importFromPayload($supplier, $payload);

            return ToolResult::success([
                'import_id' => $import->id,
                'status' => $import->status,
                'rows_received' => $import->rows_received,
                'rows_created' => $import->rows_created,
                'rows_updated' => $import->rows_updated,
                'rows_skipped' => $import->rows_skipped,
                'duration_ms' => $import->duration_ms,
                'errors' => $import->error_log,
                'message' => 'Import durchgeführt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'suppliers', 'imports', 'trigger'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
