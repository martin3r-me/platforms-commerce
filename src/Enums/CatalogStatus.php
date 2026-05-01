<?php

namespace Platform\Commerce\Enums;

enum CatalogStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
