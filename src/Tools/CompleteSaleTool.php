<?php

namespace Platform\Commerce\Tools;

use Platform\Commerce\Enums\SaleStatus;

/**
 * Schliesst einen Verkauf ab und bucht den Bestand ab (Outbound).
 */
class CompleteSaleTool extends AbstractSaleTransitionTool
{
    protected function targetStatus(): SaleStatus
    {
        return SaleStatus::Completed;
    }

    protected function verb(): string
    {
        return 'complete';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/sales/{id}/complete - Schliesst einen Verkauf ab: loest eine evtl. Reservierung auf und bucht den Bestand ab.';
    }

    protected function successMessage(): string
    {
        return 'Verkauf abgeschlossen und Bestand abgebucht.';
    }
}
