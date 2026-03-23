<?php

namespace Platform\Commerce\Enums;

enum RuleType: string
{
    case QuantityLimit = 'quantity_limit';
    case OrderValue = 'order_value';
    case SalePeriod = 'sale_period';
    case Dependency = 'dependency';
    case Exclusion = 'exclusion';
    case MandatoryField = 'mandatory_field';
}
