<?php

declare(strict_types = 1);

namespace App\Models\Enum;

enum PostStatusEnum: int
{
    case DRAFT     = 0;
    case PUBLISHED = 1;
    case ARCHIVED  = 2;
}
