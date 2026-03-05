<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('business.title') }}
            </h2>
            <a href="{{ route('business-profiles.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                {{ __('business.add_new') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($profiles as $profile)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $profile->name }}</h3>
                            @if($profile->address)
                                <p class="text-sm text-gray-500">{{ $profile->address }}</p>
                            @endif
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('business-profiles.show', $profile->id) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">{{ __('common.view') }}</a>
                            <a href="{{ route('business-profiles.edit', $profile->id) }}" class="text-yellow-600 hover:text-yellow-900 text-sm">{{ __('common.edit') }}</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    {{ __('business.no_profiles') }}
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
