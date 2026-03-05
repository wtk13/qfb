<?php

namespace Tests\Unit\Domain;

use Domain\Campaign\ValueObject\ReviewRequestStatus;
use PHPUnit\Framework\TestCase;

class ReviewRequestStatusTest extends TestCase
{
    public function test_pending_to_sent(): void
    {
        $this->assertTrue(ReviewRequestStatus::Pending->canTransitionTo(ReviewRequestStatus::Sent));
    }

    public function test_sent_to_clicked(): void
    {
        $this->assertTrue(ReviewRequestStatus::Sent->canTransitionTo(ReviewRequestStatus::Clicked));
    }

    public function test_clicked_to_rated(): void
    {
        $this->assertTrue(ReviewRequestStatus::Clicked->canTransitionTo(ReviewRequestStatus::Rated));
    }

    public function test_invalid_transitions(): void
    {
        $this->assertFalse(ReviewRequestStatus::Pending->canTransitionTo(ReviewRequestStatus::Clicked));
        $this->assertFalse(ReviewRequestStatus::Pending->canTransitionTo(ReviewRequestStatus::Rated));
        $this->assertFalse(ReviewRequestStatus::Sent->canTransitionTo(ReviewRequestStatus::Rated));
        $this->assertFalse(ReviewRequestStatus::Rated->canTransitionTo(ReviewRequestStatus::Pending));
    }
}
