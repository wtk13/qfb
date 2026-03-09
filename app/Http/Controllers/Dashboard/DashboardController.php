<?php

namespace App\Http\Controllers\Dashboard;

use App\Application\Query\GetBusinessProfiles;
use App\Application\Query\GetDashboardStats;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private GetDashboardStats $getDashboardStats,
        private GetBusinessProfiles $getBusinessProfiles,
    ) {}

    public function index(Request $request)
    {
        $stats = $this->getDashboardStats->execute($request->get('tenant_id'));
        $profiles = $this->getBusinessProfiles->execute($request->get('tenant_id'));

        return view('dashboard.index', compact('stats', 'profiles'));
    }
}
