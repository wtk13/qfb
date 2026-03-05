<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('dashboard.title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.total_businesses') }}</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_businesses'] }}</div>
                </div>
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

            <div class="mt-8">
                <a href="{{ route('business-profiles.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    {{ __('business.add_new') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
