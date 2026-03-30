<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\RedditStrategyReportModel;

class RedditStrategyReportController extends Controller
{
    public function show(int $id)
    {
        $report = RedditStrategyReportModel::findOrFail($id);

        return view('admin.reddit.reports.show', compact('report'));
    }
}
