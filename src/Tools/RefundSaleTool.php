<?php

namespace Platform\Commerce\Tools;

use Platform\Commerce\Enums\SaleStatus;

/**
 * Erstattet einen abgeschlossenen Verkauf und bucht die Ware zurueck ins Lager.
 */
class RefundSaleTool extends AbstractSaleTransitionTool
{
    protected function targetStatus(): SaleStatus
    {
        return SaleStatus::Refunded;
    }

    protected function verb(): string
    {
        return 'refund';
    }

    public function getDescription(): string
    {
        return 'POST /commerce/sales/{id}/refund - Erstattet einen abgeschlossenen Verkauf und bucht die abgebuchte Ware zurueck ins Lager.';
    }

    protected function successMessage(): string
    {
        return 'Verkauf erstattet, Ware zurueck ins Lager gebucht.';
    }
}
