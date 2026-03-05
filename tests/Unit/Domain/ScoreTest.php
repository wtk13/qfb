<?php

namespace Tests\Unit\Domain;

use Domain\Feedback\ValueObject\Score;
use PHPUnit\Framework\TestCase;

class ScoreTest extends TestCase
{
    public function test_valid_scores(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $score = new Score($i);
            $this->assertSame($i, $score->value);
        }
    }

    public function test_score_below_range_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Score(0);
    }

    public function test_score_above_range_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Score(6);
    }

    public function test_positive_score(): void
    {
        $this->assertTrue((new Score(4))->isPositive());
        $this->assertTrue((new Score(5))->isPositive());
        $this->assertFalse((new Score(3))->isPositive());
    }

    public function test_negative_score(): void
    {
        $this->assertTrue((new Score(1))->isNegative());
        $this->assertTrue((new Score(2))->isNegative());
        $this->assertTrue((new Score(3))->isNegative());
        $this->assertFalse((new Score(4))->isNegative());
    }
}
