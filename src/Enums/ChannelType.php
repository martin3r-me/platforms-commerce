<?php

namespace Platform\Commerce\Enums;

enum ChannelType: string
{
    case Online = 'online';
    case Store = 'store';
    case Delivery = 'delivery';
    case Wholesale = 'wholesale';
    case Marketplace = 'marketplace';
}
