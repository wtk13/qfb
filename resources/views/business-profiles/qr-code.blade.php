<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('business.qr_code') }} — {{ $profile->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <div class="mb-4">
                    {!! $svg !!}
                </div>
                <p class="text-sm text-gray-500 mb-4">{{ $url }}</p>
                <a href="{{ route('qr-code.download', $profile->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    {{ __('business.download_qr') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
