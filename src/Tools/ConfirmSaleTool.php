<?php

namespace Platform\Commerce\Tools;

use Platform\Commerce\Enums\SaleStatus;

/**
 * Bestaetigt einen Verkauf und reserviert dafuer den Bestand.
 */
class ConfirmSaleTool extends AbstractSaleTransitionTool
{
    protected function targetStatus(): SaleStatus
    {
        return SaleStatus::Confirmed;
    }

    protected function verb(): string
    {
        return 'confirm';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/sales/{id}/confirm - Bestaetigt einen Verkauf und reserviert den Bestand der Positionen. Schlaegt fehl, wenn nicht genug verfuegbar ist.';
    }

    protected function successMessage(): string
    {
        return 'Verkauf bestätigt und Bestand reserviert.';
    }
}
