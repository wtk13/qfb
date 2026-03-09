<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('dashboard.title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <a href="{{ route('business-profiles.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition-shadow">
                    <div class="text-sm text-gray-500">{{ __('dashboard.total_businesses') }}</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_businesses'] }}</div>
                </a>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.total_review_requests') }}</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_review_requests'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.total_ratings') }}</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_ratings'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.average_score') }}</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['average_score'] ? number_format($stats['average_score'], 1) : '-' }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">{{ __('dashboard.score_distribution') }}</h3>
                    <div style="height: 200px;">
                        <canvas id="scoreChart"></canvas>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">{{ __('dashboard.ratings_over_time') }}</h3>
                    <div style="height: 200px;">
                        <canvas id="timelineChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.positive_ratings') }}</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['positive_ratings'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.negative_ratings') }}</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['negative_ratings'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.total_feedback') }}</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_feedback'] }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex justify-between items-center border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('business.title') }}</h3>
                    <a href="{{ route('business-profiles.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                        {{ __('business.add_new') }}
                    </a>
                </div>

                @forelse($profiles as $profile)
                    <a href="{{ route('business-profiles.show', $profile->id) }}" class="block p-6 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-medium text-gray-900">{{ $profile->name }}</div>
                                @if($profile->address)
                                    <div class="text-sm text-gray-500">{{ $profile->address }}</div>
                                @endif
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        {{ __('business.no_profiles') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        const scoreData = @json($stats['score_distribution']);
        const timelineData = @json($stats['ratings_over_time']);

        const sharedOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        };

        new Chart(document.getElementById('scoreChart'), {
            type: 'bar',
            data: {
                labels: ['1', '2', '3', '4', '5'],
                datasets: [{
                    data: Object.values(scoreData),
                    backgroundColor: ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e'],
                    borderRadius: 6,
                }]
            },
            options: sharedOptions
        });

        const dates = Object.keys(timelineData);
        const counts = Object.values(timelineData);

        new Chart(document.getElementById('timelineChart'), {
            type: 'line',
            data: {
                labels: dates.map(d => {
                    const date = new Date(d);
                    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    data: counts,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#6366f1',
                }]
            },
            options: sharedOptions
        });
    </script>
    @endpush
</x-app-layout>
