<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use App\Infrastructure\Persistence\Eloquent\RedditStrategyReportModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use Domain\Reddit\ValueObject\PhasePolicy;

class RedditDashboardController extends Controller
{
    public function index()
    {
        $accountCreatedAt = config('reddit.account_created_at');
        $phasePolicy = $accountCreatedAt ? new PhasePolicy(new \DateTimeImmutable($accountCreatedAt)) : null;

        $stats = RedditDraftModel::query()
            ->selectRaw('COUNT(*) as total_drafts')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed")
            ->first();

        $threadsThisWeek = RedditThreadModel::where('created_at', '>=', now()->startOfWeek())->count();

        // Content ratio for the last 30 days
        $ratioRaw = RedditDraftModel::where('created_at', '>=', now()->subDays(30))
            ->whereIn('status', ['pending', 'approved', 'published'])
            ->selectRaw('content_category, COUNT(*) as count')
            ->groupBy('content_category')
            ->pluck('count', 'content_category')
            ->toArray();

        $contentRatio = [
            'value' => $ratioRaw['value'] ?? 0,
            'discussion' => $ratioRaw['discussion'] ?? 0,
            'brand' => $ratioRaw['brand'] ?? 0,
        ];

        $latestReport = RedditStrategyReportModel::orderByDesc('period_end')->first();

        $recentDrafts = RedditDraftModel::with('subreddit', 'thread')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.reddit.dashboard', compact(
            'phasePolicy',
            'stats',
            'threadsThisWeek',
            'contentRatio',
            'latestReport',
            'recentDrafts',
        ));
    }
}
