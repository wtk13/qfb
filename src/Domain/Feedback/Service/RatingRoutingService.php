<?php

declare(strict_types=1);

namespace Domain\Feedback\Service;

use Domain\Feedback\ValueObject\Score;

final class RatingRoutingService
{
    public const string ROUTE_GOOGLE = 'google';
    public const string ROUTE_FEEDBACK = 'feedback';

    public function determineRoute(Score $score): string
    {
        return $score->isPositive() ? self::ROUTE_GOOGLE : self::ROUTE_FEEDBACK;
    }
}
