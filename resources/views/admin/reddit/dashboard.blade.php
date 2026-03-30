<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reddit Marketing Dashboard</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.reddit.drafts.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    Review Drafts
                </a>
                <a href="{{ route('admin.reddit.subreddits.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    Manage Subreddits
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Phase Indicator --}}
            @if($phasePolicy)
                @php
                    $phase = $phasePolicy->currentPhase(new \DateTimeImmutable());
                    $ageDays = $phasePolicy->accountAgeDays(new \DateTimeImmutable());
                    $phaseColor = match($phase) {
                        \Domain\Reddit\ValueObject\Phase::Lurk => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                        \Domain\Reddit\ValueObject\Phase::Comment => 'bg-blue-100 text-blue-800 border-blue-300',
                        \Domain\Reddit\ValueObject\Phase::Full => 'bg-green-100 text-green-800 border-green-300',
                    };
                @endphp
                <div class="border rounded-lg p-4 {{ $phaseColor }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-lg">Phase: {{ $phase->value }}</span>
                            <span class="ml-2 text-sm">(Account age: {{ $ageDays }} days)</span>
                        </div>
                        <div class="text-sm">
                            @if($phase === \Domain\Reddit\ValueObject\Phase::Lurk)
                                Comments unlock at day 15 ({{ 15 - $ageDays }} days remaining)
                            @elseif($phase === \Domain\Reddit\ValueObject\Phase::Comment)
                                Full posting unlocks at day 31 ({{ 31 - $ageDays }} days remaining)
                            @else
                                All capabilities unlocked
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="border rounded-lg p-4 bg-gray-100 text-gray-600 border-gray-300">
                    <span class="font-semibold">Phase: Unknown</span>
                    <span class="ml-2 text-sm">-- Set <code>REDDIT_ACCOUNT_CREATED_AT</code> in .env to enable phase tracking</span>
                </div>
            @endif

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-gray-400">
                    <div class="text-2xl font-bold text-gray-900">{{ $threadsThisWeek }}</div>
                    <div class="text-sm text-gray-500">Threads This Week</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-amber-500">
                    <div class="text-2xl font-bold text-amber-600">{{ $stats->pending ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Pending Review</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats->approved ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Approved</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-green-500">
                    <div class="text-2xl font-bold text-green-600">{{ $stats->published ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Published</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-red-500">
                    <div class="text-2xl font-bold text-red-600">{{ $stats->failed ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Failed</div>
                </div>
            </div>

            {{-- Content Ratio --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Content Mix (Last 30 Days)</h3>
                @php
                    $total = $contentRatio['value'] + $contentRatio['discussion'] + $contentRatio['brand'];
                @endphp
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-600">{{ $contentRatio['value'] }}</div>
                        <div class="text-sm text-gray-500">Value (target 70%)</div>
                        @if($total > 0)
                            <div class="text-xs text-gray-400">{{ round(($contentRatio['value'] / $total) * 100) }}% actual</div>
                        @endif
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-cyan-600">{{ $contentRatio['discussion'] }}</div>
                        <div class="text-sm text-gray-500">Discussion (target 20%)</div>
                        @if($total > 0)
                            <div class="text-xs text-gray-400">{{ round(($contentRatio['discussion'] / $total) * 100) }}% actual</div>
                        @endif
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">{{ $contentRatio['brand'] }}</div>
                        <div class="text-sm text-gray-500">Brand (target 10%)</div>
                        @if($total > 0)
                            <div class="text-xs text-gray-400">{{ round(($contentRatio['brand'] / $total) * 100) }}% actual</div>
                        @endif
                    </div>
                </div>
                @if($total > 0)
                    <div class="mt-4 h-3 rounded-full bg-gray-200 overflow-hidden flex">
                        <div class="bg-indigo-500" style="width: {{ round(($contentRatio['value'] / $total) * 100) }}%"></div>
                        <div class="bg-cyan-500" style="width: {{ round(($contentRatio['discussion'] / $total) * 100) }}%"></div>
                        <div class="bg-orange-500" style="width: {{ round(($contentRatio['brand'] / $total) * 100) }}%"></div>
                    </div>
                @endif
            </div>

            {{-- Latest Strategy Report --}}
            @if($latestReport)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Latest Strategy Report</h3>
                        <a href="{{ route('admin.reddit.reports.show', $latestReport->id) }}" class="text-sm text-indigo-600 hover:underline">View full report</a>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">
                        Period: {{ $latestReport->period_start->format('M d') }} -- {{ $latestReport->period_end->format('M d, Y') }}
                    </div>
                    @if($latestReport->report_json && isset($latestReport->report_json['summary']))
                        <p class="text-gray-700">{{ $latestReport->report_json['summary'] }}</p>
                    @endif
                </div>
            @endif

            {{-- Recent Drafts --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Drafts</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subreddit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thread</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentDrafts as $draft)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColor = match($draft->status) {
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            'approved' => 'bg-blue-100 text-blue-800',
                                            'published' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-gray-100 text-gray-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">{{ $draft->status }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    r/{{ $draft->subreddit?->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">
                                    {{ $draft->thread?->title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $draft->content_category }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $draft->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('admin.reddit.drafts.show', $draft->id) }}" class="text-indigo-600 hover:underline">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No drafts yet. Run <code>reddit:scout</code> and <code>reddit:draft</code> to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
