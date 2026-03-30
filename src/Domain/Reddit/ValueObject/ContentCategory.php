<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum ContentCategory: string
{
    case Value = 'value';
    case Discussion = 'discussion';
    case Brand = 'brand';
}
