<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('feedback.title') }} — {{ $profile->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @forelse($feedbackList as $feedback)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 p-6">
                    <p class="text-gray-900">{{ $feedback->comment }}</p>
                    @if($feedback->contactEmail)
                        <p class="text-sm text-gray-500 mt-2">{{ __('feedback.contact') }}: {{ $feedback->contactEmail->value }}</p>
                    @endif
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    {{ __('feedback.no_feedback') }}
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
