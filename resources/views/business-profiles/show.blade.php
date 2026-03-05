<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $profile->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('business-profiles.edit', $profile->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400">
                    {{ __('common.edit') }}
                </a>
                <form method="POST" action="{{ route('business-profiles.destroy', $profile->id) }}" onsubmit="return confirm('{{ __('common.confirm_delete') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                        {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.total_ratings') }}</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_ratings'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.average_score') }}</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['average_score'] ? number_format($stats['average_score'], 1) : '-' }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.total_review_requests') }}</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_review_requests'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('dashboard.total_feedback') }}</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_feedback'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Send Review Request -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('campaign.send_request') }}</h3>
                    <form method="POST" action="{{ route('review-requests.store', $profile->id) }}">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="recipient_email" :value="__('campaign.recipient_email')" />
                            <x-text-input id="recipient_email" name="recipient_email" type="email" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('recipient_email')" class="mt-2" />
                        </div>
                        <x-primary-button>{{ __('campaign.send') }}</x-primary-button>
                    </form>
                </div>

                <!-- Bulk Import -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('campaign.bulk_import') }}</h3>
                    <form method="POST" action="{{ route('review-requests.bulk', $profile->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="csv_file" :value="__('campaign.csv_file')" />
                            <input id="csv_file" name="csv_file" type="file" accept=".csv,.txt" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('csv_file')" class="mt-2" />
                        </div>
                        <x-primary-button>{{ __('campaign.upload') }}</x-primary-button>
                    </form>
                </div>
            </div>

            <!-- QR Code & Feedback Links -->
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('business.qr_code') }}</h3>
                    <a href="{{ route('qr-code.show', $profile->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('business.view_qr') }}</a>
                    <span class="mx-2">|</span>
                    <a href="{{ route('qr-code.download', $profile->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('business.download_qr') }}</a>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('feedback.title') }}</h3>
                    <a href="{{ route('feedback.index', $profile->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('feedback.view_all') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
