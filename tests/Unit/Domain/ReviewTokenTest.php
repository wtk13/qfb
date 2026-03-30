<?php

namespace Tests\Unit\Domain;

use Domain\Campaign\ValueObject\ReviewToken;
use PHPUnit\Framework\TestCase;

class ReviewTokenTest extends TestCase
{
    public function test_generates_unique_token(): void
    {
        $token1 = new ReviewToken;
        $token2 = new ReviewToken;

        $this->assertNotEmpty($token1->value);
        $this->assertNotEquals($token1->value, $token2->value);
    }

    public function test_accepts_existing_value(): void
    {
        $token = new ReviewToken('abc123');
        $this->assertSame('abc123', $token->value);
    }

    public function test_token_length(): void
    {
        $token = new ReviewToken;
        $this->assertSame(64, strlen($token->value));
    }

    public function test_equals(): void
    {
        $token1 = new ReviewToken('same-value');
        $token2 = new ReviewToken('same-value');
        $this->assertTrue($token1->equals($token2));
    }
}
