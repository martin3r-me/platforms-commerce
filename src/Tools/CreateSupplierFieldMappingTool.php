<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Models\CommerceSupplierFieldMapping;

class CreateSupplierFieldMappingTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.supplier_field_mappings.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/supplier_field_mappings - Erstellt ein neues Feldmapping für einen Lieferanten.';
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
                'commerce_supplier_id' => [
                    'type' => 'integer',
                    'description' => 'ERFORDERLICH: ID des Lieferanten.',
                ],
                'source_key' => [
                    'type' => 'string',
                    'description' => 'ERFORDERLICH: Feldname aus Quelldaten.',
                ],
                'target_field' => [
                    'type' => 'string',
                    'description' => 'Optional: Zielfeld in CommerceArticle (z.B. name, sku, price).',
                ],
                'label' => [
                    'type' => 'string',
                    'description' => 'Optional: Anzeige-Label.',
                ],
                'data_type' => [
                    'type' => 'string',
                    'description' => 'Optional: Datentyp (string, integer, decimal, boolean, date, datetime). Default: string.',
                ],
                'transform' => [
                    'type' => 'string',
                    'description' => 'Optional: Transform (trim, lowercase, uppercase, cast_german_decimal, strip_tags, to_integer, to_boolean).',
                ],
                'position' => [
                    'type' => 'integer',
                    'description' => 'Optional: Sortier-Position. Default: 0.',
                ],
            ],
            'required' => ['commerce_supplier_id', 'source_key'],
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

            $mapping = CommerceSupplierFieldMapping::create([
                'commerce_supplier_id' => $supplier->id,
                'source_key' => $arguments['source_key'],
                'target_field' => $arguments['target_field'] ?? null,
                'label' => $arguments['label'] ?? null,
                'data_type' => $arguments['data_type'] ?? 'string',
                'transform' => $arguments['transform'] ?? null,
                'position' => $arguments['position'] ?? 0,
                'is_active' => true,
            ]);

            return ToolResult::success([
                'id' => $mapping->id,
                'source_key' => $mapping->source_key,
                'target_field' => $mapping->target_field,
                'message' => 'Feldmapping erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'suppliers', 'field_mappings', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
