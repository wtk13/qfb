<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\OutreachCampaignModel;
use App\Infrastructure\Persistence\Eloquent\OutreachLeadModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class OutreachController extends Controller
{
    public function index()
    {
        // Single query for all status counts (PostgreSQL-compatible with CASE casts)
        $counts = OutreachLeadModel::query()
            ->selectRaw('COUNT(*) as total_leads')
            ->selectRaw("COUNT(*) FILTER (WHERE email_status = 'verified') as verified")
            ->selectRaw("COUNT(*) FILTER (WHERE outreach_status = 'new') as new_count")
            ->selectRaw("COUNT(*) FILTER (WHERE outreach_status = 'sent') as sent")
            ->selectRaw("COUNT(*) FILTER (WHERE outreach_status = 'replied') as replied")
            ->selectRaw("COUNT(*) FILTER (WHERE outreach_status = 'converted') as converted")
            ->selectRaw("COUNT(*) FILTER (WHERE outreach_status = 'bounced') as bounced")
            ->selectRaw("COUNT(*) FILTER (WHERE outreach_status = 'unsubscribed') as unsubscribed")
            ->selectRaw('COUNT(*) FILTER (WHERE sent_at >= ?) as sent_today', [today()])
            ->selectRaw('COUNT(*) FILTER (WHERE sent_at >= ?) as sent_this_month', [now()->startOfMonth()])
            ->first();

        $stats = [
            'total_leads' => (int) $counts->total_leads,
            'verified' => (int) $counts->verified,
            'queue' => OutreachLeadModel::sendable()->count(),
            'sent' => (int) $counts->sent,
            'replied' => (int) $counts->replied,
            'converted' => (int) $counts->converted,
            'bounced' => (int) $counts->bounced,
            'unsubscribed' => (int) $counts->unsubscribed,
            'sent_today' => (int) $counts->sent_today,
            'sent_this_month' => (int) $counts->sent_this_month,
        ];

        $campaigns = OutreachCampaignModel::orderByDesc('scraped_at')->get();

        $recentSent = OutreachLeadModel::where('outreach_status', '!=', 'new')
            ->orderByDesc('sent_at')
            ->limit(20)
            ->get();

        // Chart data: status breakdown for donut chart
        $statusBreakdown = [
            'new' => (int) $counts->new_count,
            'sent' => $stats['sent'],
            'replied' => $stats['replied'],
            'converted' => $stats['converted'],
            'bounced' => $stats['bounced'],
            'unsubscribed' => $stats['unsubscribed'],
        ];

        // Chart data: funnel stages (each stage includes downstream statuses)
        $funnelData = [
            'Total Leads' => $stats['total_leads'],
            'Verified' => $stats['verified'],
            'Sent' => $stats['sent'] + $stats['replied'] + $stats['converted'],
            'Replied' => $stats['replied'] + $stats['converted'],
            'Converted' => $stats['converted'],
        ];

        // Chart data: campaign performance (top 10 by emails sent)
        $campaignChartData = $campaigns
            ->sortByDesc('emails_sent')
            ->take(10)
            ->map(fn ($c) => [
                'label' => ucfirst($c->category).' - '.$c->city,
                'sent' => $c->emails_sent,
                'replied' => $c->replies,
                'converted' => $c->conversions,
            ])
            ->values()
            ->toArray();

        // Chart data: daily send volume (last 14 days)
        $dailySends = OutreachLeadModel::whereNotNull('sent_at')
            ->where('sent_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(sent_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $dailySendVolume = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailySendVolume[$date] = $dailySends[$date] ?? 0;
        }

        return view('admin.outreach.index', compact(
            'stats',
            'campaigns',
            'recentSent',
            'statusBreakdown',
            'funnelData',
            'campaignChartData',
            'dailySendVolume',
        ));
    }

    public function leads(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:new,sent,replied,converted,bounced,unsubscribed',
            'category' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:200',
        ]);

        $query = OutreachLeadModel::query();

        if ($request->filled('status')) {
            $query->where('outreach_status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->filled('search')) {
            $query->where('business_name', 'like', '%'.$request->search.'%');
        }

        $leads = $query->orderByDesc('created_at')->paginate(50);

        $categories = OutreachLeadModel::distinct()->pluck('category')->filter()->sort();
        $cities = OutreachLeadModel::distinct()->pluck('city')->filter()->sort();

        return view('admin.outreach.leads', compact('leads', 'categories', 'cities'));
    }

    public function runWeekly()
    {
        Artisan::queue('outreach:weekly');

        return back()->with('success', 'Weekly rotation queued. Results will appear shortly.');
    }

    public function sendBatch(Request $request)
    {
        $request->validate([
            'limit' => 'required|integer|min:1|max:50',
        ]);

        $dailyCap = config('outreach.daily_cap');
        $monthlyCap = config('outreach.monthly_cap');

        $sentToday = OutreachLeadModel::whereNotNull('sent_at')
            ->whereDate('sent_at', today())->count();

        if ($sentToday >= $dailyCap) {
            return back()->with('error', "Daily cap reached ({$sentToday}/{$dailyCap}). Try again tomorrow.");
        }

        $sentThisMonth = OutreachLeadModel::whereNotNull('sent_at')
            ->whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)->count();

        if ($sentThisMonth >= $monthlyCap) {
            return back()->with('error', "Monthly cap reached ({$sentThisMonth}/{$monthlyCap}).");
        }

        $limit = min(
            $request->integer('limit'),
            $dailyCap - $sentToday,
            $monthlyCap - $sentThisMonth,
        );

        Artisan::queue('outreach:daily-send', [
            '--limit' => $limit,
            '--delay' => 5,
            '--sender-name' => $request->input('sender_name', 'Mike'),
            '--sender-title' => 'Founder, QuickFeedback',
        ]);

        return back()->with('success', "Batch of {$limit} emails queued for sending.");
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:new,sent,replied,bounced,unsubscribed,converted',
        ]);

        $lead = OutreachLeadModel::findOrFail($id);
        $lead->update(['outreach_status' => $request->status]);

        OutreachCampaignModel::refreshStats($lead->category, $lead->city);

        return back()->with('success', "{$lead->business_name} marked as {$request->status}.");
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'sender_name' => 'required|string|max:100',
        ]);

        Artisan::call('outreach:test', [
            'email' => $request->email,
            '--sender-name' => $request->sender_name,
        ]);

        return back()->with('success', "Test email sent to {$request->email}.");
    }
}
