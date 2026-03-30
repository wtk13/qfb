<?php

namespace Tests\Unit\Domain;

use Domain\Campaign\Entity\ReviewRequest;
use Domain\Campaign\ValueObject\ReviewRequestStatus;
use Domain\Campaign\ValueObject\ReviewToken;
use Domain\Shared\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class ReviewRequestTest extends TestCase
{
    private function makeReviewRequest(ReviewRequestStatus $status = ReviewRequestStatus::Pending): ReviewRequest
    {
        return new ReviewRequest(
            id: 'rr-1',
            businessProfileId: 'bp-1',
            recipientEmail: new Email('test@example.com'),
            status: $status,
            token: new ReviewToken,
        );
    }

    public function test_mark_as_sent_from_pending(): void
    {
        $rr = $this->makeReviewRequest(ReviewRequestStatus::Pending);
        $rr->markAsSent();

        $this->assertSame(ReviewRequestStatus::Sent, $rr->status);
        $this->assertNotNull($rr->sentAt);
    }

    public function test_mark_as_clicked_from_sent(): void
    {
        $rr = $this->makeReviewRequest(ReviewRequestStatus::Sent);
        $rr->markAsClicked();

        $this->assertSame(ReviewRequestStatus::Clicked, $rr->status);
    }

    public function test_mark_as_rated_from_clicked(): void
    {
        $rr = $this->makeReviewRequest(ReviewRequestStatus::Clicked);
        $rr->markAsRated();

        $this->assertSame(ReviewRequestStatus::Rated, $rr->status);
    }

    public function test_cannot_mark_as_rated_from_pending(): void
    {
        $rr = $this->makeReviewRequest(ReviewRequestStatus::Pending);

        $this->expectException(\DomainException::class);
        $rr->markAsRated();
    }

    public function test_cannot_mark_as_rated_twice(): void
    {
        $rr = $this->makeReviewRequest(ReviewRequestStatus::Rated);

        $this->expectException(\DomainException::class);
        $rr->markAsRated();
    }

    public function test_cannot_mark_as_sent_from_clicked(): void
    {
        $rr = $this->makeReviewRequest(ReviewRequestStatus::Clicked);

        $this->expectException(\DomainException::class);
        $rr->markAsSent();
    }
}
