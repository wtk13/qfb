<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Outreach Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('output'))
                <details class="bg-gray-50 border border-gray-200 rounded-lg">
                    <summary class="px-4 py-2 cursor-pointer text-sm text-gray-600">Command output</summary>
                    <pre class="px-4 py-3 text-xs text-gray-700 overflow-x-auto whitespace-pre-wrap">{{ session('output') }}</pre>
                </details>
            @endif

            {{-- Pipeline Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-gray-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_leads'] }}</div>
                            <div class="text-sm text-gray-500">Total Leads</div>
                        </div>
                        <div class="text-gray-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-indigo-600">{{ $stats['queue'] }}</div>
                            <div class="text-sm text-gray-500">Ready to Send</div>
                        </div>
                        <div class="text-indigo-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-blue-600">{{ $stats['sent'] }}</div>
                            <div class="text-sm text-gray-500">Sent</div>
                        </div>
                        <div class="text-blue-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-green-600">{{ $stats['replied'] }}</div>
                            <div class="text-sm text-gray-500">Replied</div>
                        </div>
                        <div class="text-green-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-emerald-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-emerald-600">{{ $stats['converted'] }}</div>
                            <div class="text-sm text-gray-500">Converted</div>
                        </div>
                        <div class="text-emerald-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-orange-600">{{ $stats['bounced'] }}</div>
                            <div class="text-sm text-gray-500">Bounced</div>
                        </div>
                        <div class="text-orange-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-red-600">{{ $stats['unsubscribed'] }}</div>
                            <div class="text-sm text-gray-500">Unsubscribed</div>
                        </div>
                        <div class="text-red-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-violet-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-violet-600">
                                {{ $stats['sent'] > 0 ? round(($stats['replied'] / $stats['sent']) * 100, 1) . '%' : '-' }}
                            </div>
                            <div class="text-sm text-gray-500">Reply Rate</div>
                        </div>
                        <div class="text-violet-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts Row 1: Funnel + Status Donut --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Pipeline Funnel Chart --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pipeline Funnel</h3>
                    <div class="relative" style="height: 280px;">
                        <canvas id="funnelChart"></canvas>
                    </div>
                </div>

                {{-- Status Breakdown Donut --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Lead Status Distribution</h3>
                    <div class="relative" style="height: 280px;">
                        <canvas id="statusDonutChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Charts Row 2: Daily Volume + Campaign Performance --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Daily Send Volume --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Send Volume (Last 14 Days)</h3>
                    <div class="relative" style="height: 280px;">
                        <canvas id="dailySendChart"></canvas>
                    </div>
                </div>

                {{-- Campaign Performance --}}
                @if(count($campaignChartData) > 0)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Campaign Performance</h3>
                    <div class="relative" style="height: 280px;">
                        <canvas id="campaignChart"></canvas>
                    </div>
                </div>
                @endif
            </div>

            {{-- Resend Budget --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Resend Budget</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Today</span>
                            <span class="font-medium">{{ $stats['sent_today'] }} / {{ config('outreach.daily_cap') }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, ($stats['sent_today'] / config('outreach.daily_cap')) * 100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">This Month</span>
                            <span class="font-medium">{{ $stats['sent_this_month'] }} / {{ number_format(config('outreach.monthly_cap')) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, ($stats['sent_this_month'] / config('outreach.monthly_cap')) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Run Weekly Scrape --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold mb-3">Weekly Scrape</h3>
                    <p class="text-sm text-gray-500 mb-4">Scrape next niche from rotation, verify emails, prepare leads.</p>
                    <form method="POST" action="{{ route('admin.outreach.run-weekly') }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-500 transition">
                            Run Weekly Rotation
                        </button>
                    </form>
                </div>

                {{-- Send Batch --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold mb-3">Send Batch</h3>
                    <p class="text-sm text-gray-500 mb-4">Send outreach emails to verified leads.</p>
                    <form method="POST" action="{{ route('admin.outreach.send-batch') }}" class="space-y-3">
                        @csrf
                        <div class="flex gap-2">
                            <input type="number" name="limit" value="10" min="1" max="50" class="w-20 rounded-md border-gray-300 text-sm">
                            <input type="text" name="sender_name" placeholder="Your name" value="{{ old('sender_name', 'Mike') }}" class="flex-1 rounded-md border-gray-300 text-sm">
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-500 transition">
                            Send Emails
                        </button>
                    </form>
                </div>

                {{-- Send Test --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold mb-3">Send Test Email</h3>
                    <p class="text-sm text-gray-500 mb-4">Send a test outreach email to yourself.</p>
                    <form method="POST" action="{{ route('admin.outreach.send-test') }}" class="space-y-3">
                        @csrf
                        <div class="flex gap-2">
                            <input type="email" name="email" placeholder="your@email.com" required class="flex-1 rounded-md border-gray-300 text-sm">
                            <input type="text" name="sender_name" value="{{ old('sender_name', 'Mike') }}" class="w-28 rounded-md border-gray-300 text-sm">
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold hover:bg-gray-500 transition">
                            Send Test
                        </button>
                    </form>
                </div>
            </div>

            {{-- Campaign Breakdown --}}
            @if($campaigns->isNotEmpty())
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Campaigns</h3>
                    <a href="{{ route('admin.outreach.leads') }}" class="text-sm text-indigo-600 hover:text-indigo-500">View all leads</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Category</th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">City</th>
                                <th class="text-right py-2 pr-4 font-medium text-gray-500">Leads</th>
                                <th class="text-right py-2 pr-4 font-medium text-gray-500">Verified</th>
                                <th class="text-right py-2 pr-4 font-medium text-gray-500">Sent</th>
                                <th class="text-right py-2 pr-4 font-medium text-gray-500">Replies</th>
                                <th class="text-right py-2 pr-4 font-medium text-gray-500">Conv.</th>
                                <th class="text-right py-2 pr-4 font-medium text-gray-500">Reply %</th>
                                <th class="text-right py-2 font-medium text-gray-500">Scraped</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns as $campaign)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2 pr-4 capitalize">{{ $campaign->category }}</td>
                                <td class="py-2 pr-4">{{ $campaign->city }}</td>
                                <td class="py-2 pr-4 text-right">{{ $campaign->leads_scraped }}</td>
                                <td class="py-2 pr-4 text-right">{{ $campaign->emails_verified }}</td>
                                <td class="py-2 pr-4 text-right">{{ $campaign->emails_sent }}</td>
                                <td class="py-2 pr-4 text-right">{{ $campaign->replies }}</td>
                                <td class="py-2 pr-4 text-right">{{ $campaign->conversions }}</td>
                                <td class="py-2 pr-4 text-right">
                                    {{ $campaign->emails_sent > 0 ? round(($campaign->replies / $campaign->emails_sent) * 100, 1) . '%' : '-' }}
                                </td>
                                <td class="py-2 text-right text-gray-500">{{ $campaign->scraped_at?->diffForHumans() ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Recent Activity --}}
            @if($recentSent->isNotEmpty())
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Business</th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Email</th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Category</th>
                                <th class="text-right py-2 pr-4 font-medium text-gray-500">Reviews</th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Status</th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Sent</th>
                                <th class="text-left py-2 font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSent as $lead)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2 pr-4 max-w-[200px] truncate">{{ $lead->business_name }}</td>
                                <td class="py-2 pr-4 text-gray-600">{{ $lead->email }}</td>
                                <td class="py-2 pr-4 capitalize">{{ $lead->category }}</td>
                                <td class="py-2 pr-4 text-right">{{ $lead->reviews }}</td>
                                <td class="py-2 pr-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ match($lead->outreach_status) {
                                            'sent' => 'bg-blue-100 text-blue-800',
                                            'replied' => 'bg-green-100 text-green-800',
                                            'converted' => 'bg-emerald-100 text-emerald-800',
                                            'bounced' => 'bg-orange-100 text-orange-800',
                                            'unsubscribed' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        } }}">
                                        {{ $lead->outreach_status }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4 text-gray-500">{{ $lead->sent_at?->diffForHumans() ?? '-' }}</td>
                                <td class="py-2">
                                    @if($lead->outreach_status === 'sent')
                                    <form method="POST" action="{{ route('admin.outreach.update-status', $lead->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="replied">
                                        <button type="submit" class="text-xs text-green-600 hover:text-green-800 font-medium">Mark Replied</button>
                                    </form>
                                    <span class="text-gray-300 mx-1">|</span>
                                    <form method="POST" action="{{ route('admin.outreach.update-status', $lead->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="converted">
                                        <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">Mark Converted</button>
                                    </form>
                                    @elseif($lead->outreach_status === 'replied')
                                    <form method="POST" action="{{ route('admin.outreach.update-status', $lead->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="converted">
                                        <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">Mark Converted</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fontFamily = "'Inter', 'Segoe UI', system-ui, sans-serif";
            Chart.defaults.font.family = fontFamily;
            Chart.defaults.font.size = 12;

            // -- Pipeline Funnel (horizontal bar) --
            const funnelData = @js($funnelData);
            const funnelLabels = Object.keys(funnelData);
            const funnelValues = Object.values(funnelData);
            const funnelColors = ['#6366f1', '#3b82f6', '#0ea5e9', '#10b981', '#059669'];

            new Chart(document.getElementById('funnelChart'), {
                type: 'bar',
                data: {
                    labels: funnelLabels,
                    datasets: [{
                        data: funnelValues,
                        backgroundColor: funnelColors,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const total = funnelValues[0];
                                    const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                    return ctx.raw.toLocaleString() + ' (' + pct + '% of total)';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: { color: '#6b7280' },
                        },
                        y: {
                            grid: { display: false },
                            ticks: { color: '#374151', font: { weight: '500' } },
                        }
                    }
                }
            });

            // -- Status Donut --
            const statusData = @js($statusBreakdown);
            const statusLabels = Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1));
            const statusValues = Object.values(statusData);
            const statusColors = [
                '#9ca3af', // new (gray)
                '#3b82f6', // sent (blue)
                '#22c55e', // replied (green)
                '#059669', // converted (emerald)
                '#f97316', // bounced (orange)
                '#ef4444', // unsubscribed (red)
            ];

            new Chart(document.getElementById('statusDonutChart'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: statusColors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '55%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 16,
                                usePointStyle: true,
                                pointStyleWidth: 10,
                                color: '#374151',
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const total = statusValues.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                    return ctx.label + ': ' + ctx.raw.toLocaleString() + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // -- Daily Send Volume (line chart) --
            const dailyData = @js($dailySendVolume);
            const dailyLabels = Object.keys(dailyData).map(d => {
                const date = new Date(d + 'T00:00:00');
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const dailyValues = Object.values(dailyData);

            new Chart(document.getElementById('dailySendChart'), {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Emails Sent',
                        data: dailyValues,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.08)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#6b7280', maxRotation: 45 },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                color: '#6b7280',
                                stepSize: 1,
                                precision: 0,
                            },
                        }
                    }
                }
            });

            // -- Campaign Performance (stacked bar) --
            const campaignData = @js($campaignChartData);
            if (campaignData.length > 0) {
                const campaignLabels = campaignData.map(c => {
                    return c.label.length > 25 ? c.label.substring(0, 25) + '...' : c.label;
                });

                new Chart(document.getElementById('campaignChart'), {
                    type: 'bar',
                    data: {
                        labels: campaignLabels,
                        datasets: [
                            {
                                label: 'Sent',
                                data: campaignData.map(c => c.sent),
                                backgroundColor: '#93c5fd',
                                borderRadius: { topLeft: 0, topRight: 0 },
                            },
                            {
                                label: 'Replied',
                                data: campaignData.map(c => c.replied),
                                backgroundColor: '#86efac',
                                borderRadius: 0,
                            },
                            {
                                label: 'Converted',
                                data: campaignData.map(c => c.converted),
                                backgroundColor: '#6ee7b7',
                                borderRadius: { topLeft: 4, topRight: 4 },
                            },
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    pointStyleWidth: 10,
                                    padding: 16,
                                    color: '#374151',
                                }
                            },
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: { display: false },
                                ticks: {
                                    color: '#6b7280',
                                    maxRotation: 45,
                                    font: { size: 11 },
                                },
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' },
                                ticks: { color: '#6b7280', precision: 0 },
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
