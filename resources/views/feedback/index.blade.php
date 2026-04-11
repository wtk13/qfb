<x-app-layout>
    <x-slot name="header">
        <x-breadcrumbs :items="[
            ['label' => __('business.title'), 'url' => route('business-profiles.index')],
            ['label' => $profile->name, 'url' => route('business-profiles.show', $profile->id)],
            ['label' => __('feedback.title')],
        ]" />
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('feedback.title') }} — {{ $profile->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @forelse($feedbackList as $feedback)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 p-6">
                    <div class="flex items-start justify-between">
                        <p class="text-gray-900">{{ $feedback->comment }}</p>
                        @if($feedback->score)
                            <span class="shrink-0 ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $feedback->score >= 4 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $feedback->score }}/5
                            </span>
                        @endif
                    </div>
                    @if($feedback->contactEmail)
                        <p class="text-sm text-gray-500 mt-2">{{ __('feedback.contact') }}: {{ $feedback->contactEmail->value }}</p>
                    @endif

                    @if($feedback->triage)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ ucfirst(str_replace('_', ' ', $feedback->triage->category->value)) }}
                                </span>
                                @php
                                    $urgencyColors = [
                                        'low' => 'bg-green-100 text-green-800',
                                        'medium' => 'bg-yellow-100 text-yellow-800',
                                        'high' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $urgencyColors[$feedback->triage->urgency->value] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($feedback->triage->urgency->value) }}
                                </span>
                            </div>
                            <div x-data="{ copied: false }" class="bg-gray-50 rounded-md p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-medium text-gray-500">Suggested response</span>
                                    <button
                                        @click="navigator.clipboard.writeText($refs.response.textContent); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="text-xs text-indigo-600 hover:text-indigo-800"
                                    >
                                        <span x-show="!copied">Copy</span>
                                        <span x-show="copied" x-cloak>Copied!</span>
                                    </button>
                                </div>
                                <p x-ref="response" class="text-sm text-gray-700">{{ $feedback->triage->suggestedResponse }}</p>
                            </div>
                        </div>
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
