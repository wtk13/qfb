<?php

namespace App\Providers;

use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Listeners\NotifyOwnerOnNegativeFeedback;
use Domain\Feedback\Event\FeedbackTriaged;
use Domain\Feedback\Event\NegativeFeedbackReceived;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Cashier::useCustomerModel(TenantModel::class);
        Event::listen(NegativeFeedbackReceived::class, \App\Listeners\TriageNegativeFeedback::class);
        Event::listen(FeedbackTriaged::class, NotifyOwnerOnNegativeFeedback::class);
    }
}
