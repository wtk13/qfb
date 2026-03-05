<?php

declare(strict_types=1);

namespace Domain\Campaign\ValueObject;

enum ReviewRequestStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Clicked = 'clicked';
    case Rated = 'rated';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending => $next === self::Sent,
            self::Sent => $next === self::Clicked,
            self::Clicked => $next === self::Rated,
            self::Rated => false,
        };
    }
}
