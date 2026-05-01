<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Models\CommerceSupplierFieldMapping;

class DeleteSupplierFieldMappingTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.supplier_field_mappings.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /commerce/supplier_field_mappings/{id} - Löscht ein Feldmapping.';
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

            $mapping->delete();

            return ToolResult::success([
                'id' => (int)$arguments['id'],
                'message' => 'Feldmapping gelöscht.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'suppliers', 'field_mappings', 'delete'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
