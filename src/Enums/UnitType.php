<?php

namespace Platform\Commerce\Enums;

enum UnitType: string
{
    case Piece = 'piece';
    case Weight = 'weight';
    case Volume = 'volume';
    case Length = 'length';
    case Area = 'area';
    case Time = 'time';
    case Package = 'package';
}
