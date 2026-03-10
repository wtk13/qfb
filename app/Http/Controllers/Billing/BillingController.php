<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Domain\Billing\Port\SubscriptionServiceInterface;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(
        private SubscriptionServiceInterface $subscriptionService,
    ) {}

    public function index(Request $request)
    {
        $status = $this->subscriptionService->getStatus($request->user()->tenant_id);

        return view('billing.index', compact('status'));
    }

    public function checkout(Request $request)
    {
        $url = $this->subscriptionService->createCheckoutSession(
            tenantId: $request->user()->tenant_id,
            successUrl: route('billing.index') . '?checkout=success',
            cancelUrl: route('billing.index') . '?checkout=cancelled',
        );

        return redirect($url);
    }

    public function cancel(Request $request)
    {
        $this->subscriptionService->cancelSubscription($request->user()->tenant_id);

        return redirect()->route('billing.index')
            ->with('success', __('billing.cancelled'));
    }

    public function resume(Request $request)
    {
        $this->subscriptionService->resumeSubscription($request->user()->tenant_id);

        return redirect()->route('billing.index')
            ->with('success', __('billing.resumed'));
    }

    public function portal(Request $request)
    {
        $tenant = $request->user()->tenant;

        return $tenant->redirectToBillingPortal(route('billing.index'));
    }
}
