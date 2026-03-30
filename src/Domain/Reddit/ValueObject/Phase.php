<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum Phase: string
{
    case Lurk = 'lurk';
    case Comment = 'comment';
    case Full = 'full';
}
