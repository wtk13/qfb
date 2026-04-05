<?php

namespace App\Http\Controllers\Dashboard;

use App\Application\Query\GetBusinessProfiles;
use App\Application\Query\GetDashboardStats;
use App\Application\Query\GetOnboardingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private GetDashboardStats $getDashboardStats,
        private GetBusinessProfiles $getBusinessProfiles,
        private GetOnboardingStatus $getOnboardingStatus,
    ) {}

    public function index(Request $request)
    {
        $stats = $this->getDashboardStats->execute($request->get('tenant_id'));
        $profiles = $this->getBusinessProfiles->execute($request->get('tenant_id'));
        $onboarding = $this->getOnboardingStatus->execute($request->get('tenant_id'));

        return view('dashboard.index', compact('stats', 'profiles', 'onboarding'));
    }
}
