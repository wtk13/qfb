<?php

namespace Tests\Feature\Onboarding;

use App\Mail\NewSignupMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SignupEmailsTest extends TestCase
{
    use RefreshDatabase;

    private function register(): User
    {
        $this->post('/register', [
            'name' => 'Dana Plumber',
            'email' => 'dana@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        return User::where('email', 'dana@example.com')->firstOrFail();
    }

    public function test_admins_are_told_about_every_signup(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['is_admin' => false, 'email' => 'not-admin@example.com']);

        $this->register();

        Mail::assertSent(NewSignupMail::class, fn (NewSignupMail $mail) => $mail->hasTo($admin->email)
            && str_contains($mail->render(), 'dana@example.com'));
        Mail::assertNotSent(NewSignupMail::class, fn (NewSignupMail $mail) => $mail->hasTo('not-admin@example.com'));
    }

    public function test_email_signup_gets_welcome_only_after_verifying(): void
    {
        Mail::fake();

        $user = $this->register();
        Mail::assertNotSent(WelcomeMail::class);

        $user->markEmailAsVerified();
        event(new Verified($user));

        Mail::assertSent(WelcomeMail::class, fn (WelcomeMail $mail) => $mail->hasTo('dana@example.com'));
    }

    public function test_already_verified_signup_gets_welcome_right_away(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'google-user@example.com']);

        event(new Registered($user));

        Mail::assertSent(WelcomeMail::class, 1);
    }

    public function test_welcome_replies_go_to_the_support_address(): void
    {
        config(['company.support_email' => 'support@quickfeedback.test']);
        $user = User::factory()->create(['name' => 'Dana Plumber']);

        $mail = new WelcomeMail($user);

        $this->assertTrue($mail->hasReplyTo('support@quickfeedback.test'));
        $this->assertStringContainsString('Hi Dana', $mail->render());
    }
}
