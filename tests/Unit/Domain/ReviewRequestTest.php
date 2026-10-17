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

    private function sentDaysAgo(int $days, ReviewRequestStatus $status = ReviewRequestStatus::Sent): ReviewRequest
    {
        return new ReviewRequest(
            id: 'rr-1',
            businessProfileId: 'bp-1',
            recipientEmail: new Email('test@example.com'),
            status: $status,
            token: new ReviewToken,
            sentAt: new \DateTimeImmutable("-{$days} days"),
        );
    }

    public function test_unanswered_request_needs_a_reminder_after_three_days(): void
    {
        $now = new \DateTimeImmutable;

        $this->assertFalse($this->sentDaysAgo(2)->needsReminder($now));
        $this->assertTrue($this->sentDaysAgo(4)->needsReminder($now));
    }

    public function test_clicked_or_rated_request_needs_no_reminder(): void
    {
        $now = new \DateTimeImmutable;

        $this->assertFalse($this->sentDaysAgo(4, ReviewRequestStatus::Clicked)->needsReminder($now));
        $this->assertFalse($this->sentDaysAgo(4, ReviewRequestStatus::Rated)->needsReminder($now));
    }

    public function test_old_request_needs_no_reminder(): void
    {
        $this->assertFalse($this->sentDaysAgo(15)->needsReminder(new \DateTimeImmutable));
    }

    public function test_reminder_is_sent_only_once(): void
    {
        $rr = $this->sentDaysAgo(4);
        $rr->markReminderSent();

        $this->assertNotNull($rr->reminderSentAt);
        $this->assertFalse($rr->needsReminder(new \DateTimeImmutable));
    }

    public function test_cannot_mark_as_sent_from_clicked(): void
    {
        $rr = $this->makeReviewRequest(ReviewRequestStatus::Clicked);

        $this->expectException(\DomainException::class);
        $rr->markAsSent();
    }
}
