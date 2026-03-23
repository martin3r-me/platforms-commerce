<?php

namespace Platform\Commerce\Enums;

enum StockMovementType: string
{
    case Inbound = 'inbound';
    case Outbound = 'outbound';
    case Transfer = 'transfer';
    case Adjustment = 'adjustment';
    case Reservation = 'reservation';
    case ReservationRelease = 'reservation_release';
}
