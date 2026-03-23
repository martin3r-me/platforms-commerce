<?php

namespace Platform\Commerce\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Archived = 'archived';
    case Discontinued = 'discontinued';
}
