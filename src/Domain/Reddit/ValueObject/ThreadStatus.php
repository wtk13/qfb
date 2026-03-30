<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum ThreadStatus: string
{
    case New = 'new';
    case Drafting = 'drafting';
    case Drafted = 'drafted';
    case Skipped = 'skipped';
    case Stale = 'stale';
}
