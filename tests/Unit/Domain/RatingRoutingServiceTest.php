<?php

namespace Tests\Unit\Domain;

use Domain\Feedback\Service\RatingRoutingService;
use Domain\Feedback\ValueObject\Score;
use PHPUnit\Framework\TestCase;

class RatingRoutingServiceTest extends TestCase
{
    private RatingRoutingService $service;

    protected function setUp(): void
    {
        $this->service = new RatingRoutingService();
    }

    public function test_high_score_routes_to_google(): void
    {
        $this->assertSame(RatingRoutingService::ROUTE_GOOGLE, $this->service->determineRoute(new Score(4)));
        $this->assertSame(RatingRoutingService::ROUTE_GOOGLE, $this->service->determineRoute(new Score(5)));
    }

    public function test_low_score_routes_to_feedback(): void
    {
        $this->assertSame(RatingRoutingService::ROUTE_FEEDBACK, $this->service->determineRoute(new Score(1)));
        $this->assertSame(RatingRoutingService::ROUTE_FEEDBACK, $this->service->determineRoute(new Score(2)));
        $this->assertSame(RatingRoutingService::ROUTE_FEEDBACK, $this->service->determineRoute(new Score(3)));
    }
}
