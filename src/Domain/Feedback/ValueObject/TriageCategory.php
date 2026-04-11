<?php

declare(strict_types=1);

namespace Domain\Feedback\ValueObject;

enum TriageCategory: string
{
    case Staff = 'staff';
    case WaitTime = 'wait_time';
    case ProductQuality = 'product_quality';
    case Pricing = 'pricing';
    case Environment = 'environment';
    case Communication = 'communication';
    case Other = 'other';
}
