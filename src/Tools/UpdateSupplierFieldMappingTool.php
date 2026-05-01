<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Models\CommerceSupplierFieldMapping;

class UpdateSupplierFieldMappingTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.supplier_field_mappings.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/supplier_field_mappings/{id} - Aktualisiert ein Feldmapping.';
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
                'id' => [
                    'type' => 'integer',
                    'description' => 'ERFORDERLICH: ID des Feldmappings.',
                ],
                'source_key' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Source-Key.',
                ],
                'target_field' => [
                    'type' => 'string',
                    'description' => 'Optional: Neues Zielfeld ("" zum Leeren).',
                ],
                'label' => [
                    'type' => 'string',
                    'description' => 'Optional: Neues Label.',
                ],
                'data_type' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Datentyp.',
                ],
                'transform' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Transform ("" zum Leeren).',
                ],
                'position' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Position.',
                ],
                'is_active' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Aktiv/Inaktiv.',
                ],
            ],
            'required' => ['id'],
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

            $mapping = CommerceSupplierFieldMapping::find($arguments['id']);
            if (!$mapping) {
                return ToolResult::error('NOT_FOUND', 'Feldmapping nicht gefunden.');
            }

            $supplier = CommerceSupplier::where('id', $mapping->commerce_supplier_id)
                ->where('team_id', $team->id)
                ->first();
            if (!$supplier) {
                return ToolResult::error('ACCESS_DENIED', 'Mapping gehört nicht zum Team.');
            }

            $update = [];
            foreach (['source_key', 'label', 'data_type', 'position'] as $field) {
                if (array_key_exists($field, $arguments)) {
                    $update[$field] = $arguments[$field];
                }
            }
            foreach (['target_field', 'transform'] as $field) {
                if (array_key_exists($field, $arguments)) {
                    $v = (string)($arguments[$field] ?? '');
                    $update[$field] = $v === '' ? null : $v;
                }
            }
            if (array_key_exists('is_active', $arguments)) {
                $update['is_active'] = (bool)$arguments['is_active'];
            }

            if (!empty($update)) {
                $mapping->update($update);
            }
            $mapping->refresh();

            return ToolResult::success([
                'id' => $mapping->id,
                'source_key' => $mapping->source_key,
                'target_field' => $mapping->target_field,
                'is_active' => $mapping->is_active,
                'message' => 'Feldmapping aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'suppliers', 'field_mappings', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
