<?php

namespace Platform\Commerce\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Models\Team;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Commerce\Models\CommerceProduct;

/**
 * Aktualisiert ein bestehendes Produkt.
 */
class UpdateProductTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;

    public function getName(): string
    {
        return 'commerce.products.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /commerce/products/{id} - Aktualisiert ein Produkt. Nutze commerce.products.GET um die ID zu finden.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Team-ID. Default: Team aus Kontext.',
                ],
                'id' => [
                    'type' => 'integer',
                    'description' => 'ID des Produkts (ERFORDERLICH). Nutze commerce.products.GET.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional: Neuer Name des Produkts.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Neue Beschreibung ("" zum Leeren).',
                ],
                'commerce_product_board_slot_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Board-Slot-ID (null zum Leeren).',
                ],
                'order' => [
                    'type' => 'integer',
                    'description' => 'Optional: Neue Sortierreihenfolge.',
                ],
            ],
            'required' => ['id'],
        ]);
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

            $found = $this->validateAndFindModel(
                $arguments,
                $context,
                'id',
                CommerceProduct::class,
                'NOT_FOUND',
                'Produkt nicht gefunden.'
            );
            if ($found['error']) {
                return $found['error'];
            }

            /** @var CommerceProduct $product */
            $product = $found['model'];
            if ((int)$product->team_id !== (int)$team->id) {
                return ToolResult::error('ACCESS_DENIED', 'Produkt gehört nicht zum angegebenen Team.');
            }

            $update = [];

            if (array_key_exists('name', $arguments)) {
                $name = trim((string)($arguments['name'] ?? ''));
                if ($name === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'name darf nicht leer sein.');
                }
                $update['name'] = $name;
            }

            if (array_key_exists('description', $arguments)) {
                $d = (string)($arguments['description'] ?? '');
                $update['description'] = $d === '' ? null : $d;
            }

            if (array_key_exists('commerce_product_board_slot_id', $arguments)) {
                $slotId = $arguments['commerce_product_board_slot_id'];
                $update['commerce_product_board_slot_id'] = ($slotId === null || $slotId === '' || $slotId === 0)
                    ? null
                    : (int)$slotId;
            }

            if (array_key_exists('order', $arguments)) {
                $update['order'] = $arguments['order'] !== null ? (int)$arguments['order'] : null;
            }

            if (!empty($update)) {
                $product->update($update);
            }
            $product->refresh();

            return ToolResult::success([
                'id' => $product->id,
                'uuid' => $product->uuid,
                'name' => $product->name,
                'description' => $product->description,
                'commerce_product_board_slot_id' => $product->commerce_product_board_slot_id,
                'order' => $product->order,
                'team_id' => $product->team_id,
                'message' => 'Produkt erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Produkts: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action',
            'tags' => ['commerce', 'products', 'update'],
            'read_only' => false,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'write',
            'idempotent' => true,
        ];
    }
}
