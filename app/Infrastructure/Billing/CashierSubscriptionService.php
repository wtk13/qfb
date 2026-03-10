<?php

namespace App\Infrastructure\Billing;

use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Domain\Billing\Port\SubscriptionServiceInterface;

class CashierSubscriptionService implements SubscriptionServiceInterface
{
    private const SUBSCRIPTION_NAME = 'default';

    public function startTrial(string $tenantId): void
    {
        $tenant = $this->findTenant($tenantId);
        $tenant->trial_ends_at = now()->addDays(14);
        $tenant->save();
    }

    public function createCheckoutSession(string $tenantId, string $successUrl, string $cancelUrl): string
    {
        $tenant = $this->findTenant($tenantId);

        $checkout = $tenant->newSubscription(self::SUBSCRIPTION_NAME, config('cashier.pro_price_id'))
            ->checkout([
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

        return $checkout->url;
    }

    public function cancelSubscription(string $tenantId): void
    {
        $tenant = $this->findTenant($tenantId);
        $subscription = $tenant->subscription(self::SUBSCRIPTION_NAME);

        if ($subscription && !$subscription->cancelled()) {
            $subscription->cancel();
        }
    }

    public function resumeSubscription(string $tenantId): void
    {
        $tenant = $this->findTenant($tenantId);
        $subscription = $tenant->subscription(self::SUBSCRIPTION_NAME);

        if ($subscription && $subscription->cancelled() && $subscription->onGracePeriod()) {
            $subscription->resume();
        }
    }

    public function isActive(string $tenantId): bool
    {
        $tenant = $this->findTenant($tenantId);

        return $tenant->onTrial() || $tenant->subscribed(self::SUBSCRIPTION_NAME);
    }

    public function isOnTrial(string $tenantId): bool
    {
        $tenant = $this->findTenant($tenantId);

        return $tenant->onTrial() && !$tenant->subscribed(self::SUBSCRIPTION_NAME);
    }

    public function isCancelled(string $tenantId): bool
    {
        $tenant = $this->findTenant($tenantId);
        $subscription = $tenant->subscription(self::SUBSCRIPTION_NAME);

        return $subscription && $subscription->cancelled();
    }

    public function getStatus(string $tenantId): array
    {
        $tenant = $this->findTenant($tenantId);
        $subscription = $tenant->subscription(self::SUBSCRIPTION_NAME);
        $isSubscribed = $tenant->subscribed(self::SUBSCRIPTION_NAME);

        return [
            'is_active' => $tenant->onTrial() || $isSubscribed,
            'is_on_trial' => $tenant->onTrial() && !$isSubscribed,
            'trial_ends_at' => $tenant->trial_ends_at,
            'is_subscribed' => $isSubscribed,
            'is_cancelled' => $subscription?->cancelled() ?? false,
            'ends_at' => $subscription?->ends_at,
        ];
    }

    private function findTenant(string $tenantId): TenantModel
    {
        return TenantModel::findOrFail($tenantId);
    }
}
