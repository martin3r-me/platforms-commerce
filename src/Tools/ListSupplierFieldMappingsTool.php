<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Models\CommerceSupplierFieldMapping;

class ListSupplierFieldMappingsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;

    public function getName(): string
    {
        return 'commerce.supplier_field_mappings.GET';
    }

    public function getDescription(): string
    {
        return 'GET /commerce/supplier_field_mappings - Listet Feldmappings eines Lieferanten (source_key, target_field, data_type, transform).';
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
                return ToolResult::error('MISSING_TEAM', 'Kein Team angegeben und kein Team im Kontext gefunden.');
            }

            $team = Team::find((int)$teamId);
            if (!$team || !$context->user?->teams()->where('teams.id', $team->id)->exists()) {
                return ToolResult::error('ACCESS_DENIED', 'Kein Zugriff auf dieses Team.');
            }

            $supplier = CommerceSupplier::where('id', $arguments['commerce_supplier_id'])
                ->where('team_id', $team->id)
                ->first();

            if (!$supplier) {
                return ToolResult::error('NOT_FOUND', 'Lieferant nicht gefunden.');
            }

            $q = CommerceSupplierFieldMapping::query()
                ->where('commerce_supplier_id', $supplier->id);

            $this->applyStandardSort($q, $arguments, ['position', 'source_key', 'id'], 'position', 'asc');
            $result = $this->applyStandardPaginationResult($q, $arguments);

            $items = $result['data']->map(fn ($m) => [
                'id' => $m->id,
                'source_key' => $m->source_key,
                'target_field' => $m->target_field,
                'label' => $m->label,
                'data_type' => $m->data_type,
                'transform' => $m->transform,
                'position' => $m->position,
                'is_active' => $m->is_active,
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
            'tags' => ['commerce', 'suppliers', 'field_mappings', 'lookup'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'safe',
            'idempotent' => true,
        ];
    }
}
