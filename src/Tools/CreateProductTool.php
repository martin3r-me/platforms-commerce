<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Commerce\Models\CommerceProduct;

/**
 * Erstellt ein neues Produkt.
 */
class CreateProductTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'commerce.products.POST';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/products - Erstellt ein neues Produkt. Nutze zuerst commerce.products.GET um vorhandene Produkte zu prüfen.';
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
                'name' => [
                    'type' => 'string',
                    'description' => 'Name des Produkts (ERFORDERLICH).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung des Produkts.',
                ],
                'commerce_product_board_slot_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Board-Slot-ID.',
                ],
                'order' => [
                    'type' => 'integer',
                    'description' => 'Optional: Sortierreihenfolge.',
                ],
            ],
            'required' => ['name'],
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

            $name = trim((string)($arguments['name'] ?? ''));
            if ($name === '') {
                return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
            }

            $data = [
                'team_id' => $team->id,
                'user_id' => $context->user->id,
                'name' => $name,
                'description' => (array_key_exists('description', $arguments) && $arguments['description'] !== '')
                    ? (string)$arguments['description']
                    : null,
                'commerce_product_board_slot_id' => array_key_exists('commerce_product_board_slot_id', $arguments)
                    ? (int)$arguments['commerce_product_board_slot_id']
                    : null,
                'order' => array_key_exists('order', $arguments) ? (int)$arguments['order'] : null,
            ];

            $product = CommerceProduct::create($data);

            return ToolResult::success([
                'id' => $product->id,
                'uuid' => $product->uuid,
                'name' => $product->name,
                'description' => $product->description,
                'commerce_product_board_slot_id' => $product->commerce_product_board_slot_id,
                'order' => $product->order,
                'team_id' => $product->team_id,
                'message' => 'Produkt erfolgreich erstellt.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Produkts: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'products', 'create'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => false,
        ];
    }
}
