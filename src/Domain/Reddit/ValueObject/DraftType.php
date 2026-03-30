<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum DraftType: string
{
    case Comment = 'comment';
    case Post = 'post';
}
