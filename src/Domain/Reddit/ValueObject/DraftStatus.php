<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum DraftStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Published = 'published';
    case Failed = 'failed';
}
