<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('billing.title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="mb-4 p-4 bg-yellow-50 text-yellow-700 rounded-lg">{{ session('warning') }}</div>
            @endif
            @if(request('checkout') === 'success')
                <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg">{{ __('billing.checkout_success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                {{-- Current Status --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4">{{ __('billing.current_plan') }}</h3>

                    @if($status['is_on_trial'])
                        <div class="flex items-center gap-3 mb-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-700">{{ __('billing.free_trial') }}</span>
                        </div>
                        <p class="text-gray-600">
                            {{ __('billing.trial_ends', ['date' => $status['trial_ends_at']->format('M j, Y')]) }}
                        </p>
                    @elseif($status['is_subscribed'] && !$status['is_cancelled'])
                        <div class="flex items-center gap-3 mb-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">{{ __('billing.active') }}</span>
                            <span class="text-gray-600">{{ __('billing.pro_plan') }} — $29/{{ __('billing.month') }}</span>
                        </div>
                    @elseif($status['is_cancelled'])
                        <div class="flex items-center gap-3 mb-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-700">{{ __('billing.cancelled_status') }}</span>
                        </div>
                        @if($status['ends_at'])
                            <p class="text-gray-600">{{ __('billing.access_until', ['date' => $status['ends_at']->format('M j, Y')]) }}</p>
                        @endif
                    @else
                        <div class="flex items-center gap-3 mb-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">{{ __('billing.inactive') }}</span>
                        </div>
                        <p class="text-gray-600">{{ __('billing.no_active_subscription') }}</p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="border-t pt-6 space-y-3">
                    @if(!$status['is_subscribed'] || (!$status['is_active'] && !$status['is_cancelled']))
                        <form method="POST" action="{{ route('billing.checkout') }}">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-500 transition">
                                {{ __('billing.subscribe_now') }} — $29/{{ __('billing.month') }}
                            </button>
                        </form>
                    @endif

                    @if($status['is_cancelled'] && $status['is_active'])
                        <form method="POST" action="{{ route('billing.resume') }}">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-500 transition">
                                {{ __('billing.resume_subscription') }}
                            </button>
                        </form>
                    @endif

                    @if($status['is_subscribed'] && !$status['is_cancelled'])
                        <a href="{{ route('billing.portal') }}" class="block w-full text-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition">
                            {{ __('billing.manage_payment') }}
                        </a>
                        <form method="POST" action="{{ route('billing.cancel') }}">
                            @csrf
                            <button type="submit" class="w-full text-center px-6 py-3 text-red-600 hover:text-red-700 text-sm font-medium"
                                    onclick="return confirm('{{ __('billing.cancel_confirm') }}')">
                                {{ __('billing.cancel_subscription') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
