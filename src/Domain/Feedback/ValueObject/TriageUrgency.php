<?php

declare(strict_types=1);

namespace Domain\Feedback\ValueObject;

enum TriageUrgency: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
