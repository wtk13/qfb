<?php

namespace Tests\Unit\Domain\Feedback;

use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;
use PHPUnit\Framework\TestCase;

class FeedbackTriageEntityTest extends TestCase
{
    public function test_can_be_constructed(): void
    {
        $triagedAt = new \DateTimeImmutable('2026-04-11 12:00:00');

        $triage = new FeedbackTriage(
            id: 'triage-1',
            feedbackId: 'feedback-1',
            category: TriageCategory::Staff,
            urgency: TriageUrgency::High,
            suggestedResponse: 'We apologize for the experience.',
            rawLlmResponse: '{"category":"staff","urgency":"high","suggested_response":"We apologize for the experience."}',
            modelUsed: 'claude-haiku-4-5-20251001',
            triagedAt: $triagedAt,
        );

        $this->assertSame('triage-1', $triage->id);
        $this->assertSame('feedback-1', $triage->feedbackId);
        $this->assertSame(TriageCategory::Staff, $triage->category);
        $this->assertSame(TriageUrgency::High, $triage->urgency);
        $this->assertSame('We apologize for the experience.', $triage->suggestedResponse);
        $this->assertSame('claude-haiku-4-5-20251001', $triage->modelUsed);
        $this->assertSame($triagedAt, $triage->triagedAt);
    }
}
