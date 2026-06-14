<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceArticle;
use Platform\Commerce\Models\CommerceCostStandard;

class DeleteCostStandardTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.cost_standards.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /commerce/cost-standards/{id} - Löscht einen Personalkostensatz (Soft-Delete). Artikel, die diesen Kostensatz referenzieren, bekommen cost_standard_id auf null.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'id' => ['type' => 'integer', 'description' => 'ERFORDERLICH.'],
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
            $team = Team::find((int) $teamId);
            if (!$team) {
                return ToolResult::error('TEAM_NOT_FOUND', 'Team nicht gefunden.');
            }
            if (!$context->user || !$context->user->teams()->where('teams.id', $team->id)->exists()) {
                return ToolResult::error('ACCESS_DENIED', 'Kein Zugriff auf dieses Team.');
            }

            $id = (int) ($arguments['id'] ?? 0);
            if (!$id) {
                return ToolResult::error('VALIDATION_ERROR', 'id ist erforderlich.');
            }

            $row = CommerceCostStandard::where('team_id', $team->id)->find($id);
            if (!$row) {
                return ToolResult::error('NOT_FOUND', "Kostensatz {$id} nicht in Team {$team->id} gefunden.");
            }

            $referencedCount = CommerceArticle::where('cost_standard_id', $id)->count();
            if ($referencedCount > 0) {
                CommerceArticle::where('cost_standard_id', $id)
                    ->update(['cost_standard_id' => null]);
            }

            $row->delete();

            return ToolResult::success([
                'id' => $id,
                'unlinked_articles' => $referencedCount,
                'message' => "Kostensatz gelöscht. {$referencedCount} Artikel-Referenzen entfernt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return ['category' => 'action', 'tags' => ['commerce', 'cost_standards', 'delete'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => true];
    }
}
