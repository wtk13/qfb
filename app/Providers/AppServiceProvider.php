<?php

namespace App\Providers;

use App\Listeners\NotifyOwnerOnNegativeFeedback;
use Domain\Feedback\Event\NegativeFeedbackReceived;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(NegativeFeedbackReceived::class, NotifyOwnerOnNegativeFeedback::class);
    }
}
