<?php

namespace App\Listeners;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

// Sent once the address is confirmed: right at signup for Google accounts, after the
// verification link otherwise, so bots registering other people's emails don't trigger it.
class SendWelcomeEmail implements ShouldQueue
{
    public function handleRegistered(Registered $event): void
    {
        if ($event->user instanceof User && $event->user->hasVerifiedEmail()) {
            $this->send($event->user);
        }
    }

    public function handleVerified(Verified $event): void
    {
        if ($event->user instanceof User) {
            $this->send($event->user);
        }
    }

    private function send(User $user): void
    {
        Mail::to($user->email)->send(new WelcomeMail($user));
    }
}
