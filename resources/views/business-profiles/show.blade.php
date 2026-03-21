<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <x-breadcrumbs :items="[
                    ['label' => __('business.title'), 'url' => route('business-profiles.index')],
                    ['label' => $profile->name],
                ]" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $profile->name }}
                </h2>
            </div>
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

            <!-- Review Link Toolkit -->
            @if($profile->googleReviewLink)
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" x-data="{
                reviewLink: '{{ $profile->googleReviewLink->value }}',
                businessName: '{{ addslashes($profile->name) }}',
                copied: { link: false, email: false, sms: false },
                copyToClipboard(text, key) {
                    const onSuccess = () => {
                        this.copied[key] = true;
                        setTimeout(() => { this.copied[key] = false; }, 2000);
                    };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(onSuccess).catch(() => onSuccess());
                    } else {
                        const ta = document.createElement('textarea');
                        ta.value = text;
                        ta.style.position = 'fixed';
                        ta.style.left = '-9999px';
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        document.body.removeChild(ta);
                        onSuccess();
                    }
                },
                get emailTemplate() {
                    return `Subject: How did we do, [First Name]?\n\nHi [First Name],\n\nThanks for choosing ${this.businessName}! We hope you had a great experience.\n\nIf you have 30 seconds, a quick Google review would mean the world to us:\n\n${this.reviewLink}\n\nThank you!\n${this.businessName}`;
                },
                get smsTemplate() {
                    return `Hi [First Name]! Thanks for choosing ${this.businessName}. If you had a great experience, would you leave us a quick Google review? It takes 30 seconds: ${this.reviewLink}`;
                }
            }">
                <h3 class="text-lg font-semibold mb-4">{{ __('business.review_link_toolkit') ?? 'Review Link Toolkit' }}</h3>

                <!-- Google Review Link -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Your Google Review Link</label>
                    <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-3">
                        <code class="flex-1 text-sm text-gray-700 break-all" x-text="reviewLink"></code>
                        <button
                            @click="copyToClipboard(reviewLink, 'link')"
                            aria-label="Copy review link"
                            class="shrink-0 px-3 py-1.5 text-xs font-semibold rounded-md uppercase tracking-widest transition"
                            :class="copied.link ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'"
                        >
                            <span x-text="copied.link ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                </div>

                <!-- Email Template -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Email Template</label>
                    <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 whitespace-pre-line max-h-40 overflow-y-auto" x-text="emailTemplate"></div>
                    <button
                        @click="copyToClipboard(emailTemplate, 'email')"
                        aria-label="Copy email template"
                        class="mt-2 px-3 py-1.5 text-xs font-semibold rounded-md uppercase tracking-widest transition"
                        :class="copied.email ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'"
                    >
                        <span x-text="copied.email ? 'Copied!' : 'Copy Template'"></span>
                    </button>
                </div>

                <!-- SMS Template -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">SMS Template</label>
                    <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700" x-text="smsTemplate"></div>
                    <button
                        @click="copyToClipboard(smsTemplate, 'sms')"
                        aria-label="Copy SMS template"
                        class="mt-2 px-3 py-1.5 text-xs font-semibold rounded-md uppercase tracking-widest transition"
                        :class="copied.sms ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'"
                    >
                        <span x-text="copied.sms ? 'Copied!' : 'Copy Template'"></span>
                    </button>
                </div>
            </div>
            @else
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">{{ __('business.review_link_toolkit') ?? 'Review Link Toolkit' }}</h3>
                <p class="text-sm text-gray-500 mb-3">Add your Google review link to unlock copy-paste email and SMS templates personalized with your business name.</p>
                <a href="{{ route('business-profiles.edit', $profile->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    Add Google Review Link
                </a>
            </div>
            @endif

            <!-- QR Code & Feedback -->
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('business.qr_code') }}</h3>
                    <div class="flex items-start space-x-6">
                        <div class="w-32 h-32 shrink-0 [&>svg]:w-full [&>svg]:h-full">
                            {!! $qrSvg !!}
                        </div>
                        <div class="min-w-0 pt-1">
                            <p class="text-sm text-gray-500 mb-3 truncate">{{ $qrUrl }}</p>
                            <a href="{{ route('qr-code.download', $profile->id) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                {{ __('business.download_qr') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('feedback.title') }}</h3>
                    <a href="{{ route('feedback.index', $profile->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('feedback.view_all') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
