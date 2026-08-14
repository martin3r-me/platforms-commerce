<?php

namespace Platform\Commerce\Tools;

use Platform\Commerce\Enums\SaleStatus;

/**
 * Storniert einen Verkauf und gibt reservierten Bestand wieder frei.
 */
class CancelSaleTool extends AbstractSaleTransitionTool
{
    protected function targetStatus(): SaleStatus
    {
        return SaleStatus::Cancelled;
    }

    protected function verb(): string
    {
        return 'cancel';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/sales/{id}/cancel - Storniert einen Verkauf: gibt reservierten Bestand frei bzw. bucht bereits abgebuchte Ware zurueck.';
    }

    protected function successMessage(): string
    {
        return 'Verkauf storniert, Bestand freigegeben.';
    }
}
