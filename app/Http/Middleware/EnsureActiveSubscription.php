<?php

namespace App\Http\Middleware;

use Closure;
use Domain\Billing\Port\SubscriptionServiceInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function __construct(
        private SubscriptionServiceInterface $subscriptionService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->user()?->tenant_id;

        if ($tenantId && !$this->subscriptionService->isActive($tenantId)) {
            return redirect()->route('billing.index')
                ->with('warning', __('billing.subscription_required'));
        }

        return $next($request);
    }
}
