<?php

namespace Tests\Unit\Domain;

use Domain\Shared\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_valid_email(): void
    {
        $email = new Email('test@example.com');
        $this->assertSame('test@example.com', $email->value);
    }

    public function test_invalid_email_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Email('not-an-email');
    }

    public function test_empty_email_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Email('');
    }

    public function test_equals(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $this->assertTrue($email1->equals($email2));
    }
}
