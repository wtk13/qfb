<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Strategy Report</h2>
            <a href="{{ route('admin.reddit.dashboard') }}" class="text-sm text-indigo-600 hover:underline">&larr; Dashboard</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Period --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Report Period</h3>
                <p class="text-gray-600">{{ $report->period_start->format('M d, Y') }} -- {{ $report->period_end->format('M d, Y') }}</p>
                <p class="text-xs text-gray-400 mt-1">Generated {{ $report->created_at->diffForHumans() }}</p>
            </div>

            @php $data = $report->report_json ?? []; @endphp

            {{-- Summary --}}
            @if(isset($data['summary']))
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Summary</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $data['summary'] }}</p>
                </div>
            @endif

            {{-- Content Ratio --}}
            @if(isset($data['content_ratio']))
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Content Mix Analysis</h3>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        @foreach(['value' => 'Value (70%)', 'discussion' => 'Discussion (20%)', 'brand' => 'Brand (10%)'] as $key => $label)
                            <div>
                                <div class="text-2xl font-bold text-gray-900">{{ $data['content_ratio'][$key] ?? 0 }}</div>
                                <div class="text-sm text-gray-500">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Phase Assessment --}}
            @if(isset($data['phase_assessment']))
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Phase Assessment</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $data['phase_assessment'] }}</p>
                </div>
            @endif

            {{-- Top Performing Drafts --}}
            @if(isset($data['top_performing']) && count($data['top_performing']) > 0)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Top Performing Drafts</h3>
                    <ul class="space-y-2">
                        @foreach($data['top_performing'] as $item)
                            <li class="text-sm text-gray-700 p-3 bg-gray-50 rounded-lg">
                                @if(is_array($item))
                                    <span class="font-medium">{{ $item['subreddit'] ?? '' }}</span> --
                                    {{ $item['title'] ?? $item['body'] ?? json_encode($item) }}
                                @else
                                    {{ $item }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Recommendations --}}
            @if(isset($data['recommendations']) && count($data['recommendations']) > 0)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Recommendations</h3>
                    <ul class="list-disc list-inside space-y-2 text-sm text-gray-700">
                        @foreach($data['recommendations'] as $rec)
                            <li>{{ is_array($rec) ? json_encode($rec) : $rec }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Raw JSON (collapsible) --}}
            <details class="bg-white shadow-sm rounded-lg">
                <summary class="px-6 py-4 cursor-pointer text-sm font-medium text-gray-500 hover:text-gray-700">View raw report data</summary>
                <pre class="px-6 py-4 text-xs text-gray-600 overflow-x-auto border-t border-gray-200">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </details>

        </div>
    </div>
</x-app-layout>
