<?php

namespace App\Http\Controllers\Dashboard;

use App\Application\Query\GetDashboardStats;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private GetDashboardStats $getDashboardStats,
    ) {}

    public function index(Request $request)
    {
        $stats = $this->getDashboardStats->execute($request->get('tenant_id'));

        return view('dashboard.index', compact('stats'));
    }
}
