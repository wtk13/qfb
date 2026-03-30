<?php

namespace Tests\Unit\Domain\Reddit;

use Domain\Reddit\ValueObject\Phase;
use Domain\Reddit\ValueObject\PhasePolicy;
use PHPUnit\Framework\TestCase;

class PhasePolicyTest extends TestCase
{
    public function test_lurk_phase_during_first_14_days(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $now = new \DateTimeImmutable('2026-03-10');

        $this->assertSame(Phase::Lurk, $policy->currentPhase($now));
        $this->assertFalse($policy->canDraft($now));
        $this->assertFalse($policy->canPost($now));
    }

    public function test_comment_phase_between_day_15_and_30(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $now = new \DateTimeImmutable('2026-03-20');

        $this->assertSame(Phase::Comment, $policy->currentPhase($now));
        $this->assertTrue($policy->canDraft($now));
        $this->assertFalse($policy->canPost($now));
    }

    public function test_full_phase_after_day_30(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $now = new \DateTimeImmutable('2026-04-15');

        $this->assertSame(Phase::Full, $policy->currentPhase($now));
        $this->assertTrue($policy->canDraft($now));
        $this->assertTrue($policy->canPost($now));
    }

    public function test_account_age_days(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $this->assertSame(10, $policy->accountAgeDays(new \DateTimeImmutable('2026-03-11')));
    }

    public function test_boundary_day_14_is_lurk(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $this->assertSame(Phase::Lurk, $policy->currentPhase(new \DateTimeImmutable('2026-03-15')));
    }

    public function test_boundary_day_15_is_comment(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $this->assertSame(Phase::Comment, $policy->currentPhase(new \DateTimeImmutable('2026-03-16')));
    }
}
