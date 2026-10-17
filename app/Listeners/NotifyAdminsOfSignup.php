<?php

namespace App\Listeners;

use App\Mail\NewSignupMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotifyAdminsOfSignup implements ShouldQueue
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        User::where('is_admin', true)
            ->where('id', '!=', $user->id)
            ->each(fn (User $admin) => Mail::to($admin->email)->send(new NewSignupMail($user)));
    }
}
