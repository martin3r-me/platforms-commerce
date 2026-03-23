<?php

namespace Platform\Commerce\Enums;

enum PriceType: string
{
    case Standard = 'standard';
    case Tier = 'tier';
    case TimeBased = 'time_based';
    case CustomerGroup = 'customer_group';
    case Promotional = 'promotional';
}
