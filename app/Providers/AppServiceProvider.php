<?php

namespace App\Providers;

use App\Infrastructure\Persistence\Eloquent\TenantModel;
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
        // Listeners in app/Listeners are auto-discovered; registering them here too ran each one twice.
    }
}
