<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $lead->business_name }} — Get More Google Reviews with {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#4F46E5">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="antialiased bg-white text-gray-900">

    <!-- Navbar -->
    <nav aria-label="Main navigation" class="fixed top-0 w-full bg-white/90 backdrop-blur border-b border-gray-100 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <a href="/" class="text-xl font-bold text-indigo-600">{{ config('app.name') }}</a>
            <div class="flex items-center space-x-4 text-sm">
                <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-gray-900 hidden sm:inline">Blog</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 font-medium">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Log in</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-500 transition">Start Free Trial</a>
                @endauth
            </div>
        </div>
    </nav>

    <main>

    <!-- Hero -->
    <section class="pt-28 pb-12 sm:pt-32 sm:pb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">{{ $lead->business_name }}, here's how to get more Google reviews</h1>
            <p class="mt-6 text-lg text-gray-600">You currently have <span class="font-semibold text-gray-900">{{ $lead->reviews }} {{ Str::plural('review', $lead->reviews) }}</span> at <span class="font-semibold text-gray-900">{{ $lead->rating }} stars</span> on Google.</p>
        </div>
    </section>

    <!-- The Gap -->
    <section class="pb-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">
                <h2 class="text-xl font-bold mb-4">Where you stand</h2>

                @if(count($competitors) > 0)
                    @php($maxReviews = max(array_merge(array_column($competitors, 'reviews'), [$lead->reviews, 1])))
                    <p class="text-sm text-gray-500 mb-4">Top Google results for &ldquo;{{ $lead->category }} in {{ $lead->city }}&rdquo;, compared with you.</p>
                    <div class="space-y-3 mb-6">
                        @foreach($competitors as $competitor)
                            <div>
                                <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                                    <span class="truncate pr-4">{{ $competitor['name'] }}</span>
                                    <span class="shrink-0">{{ number_format($competitor['reviews']) }}</span>
                                </div>
                                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gray-400 rounded-full" style="width: {{ round($competitor['reviews'] / $maxReviews * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <div class="flex items-center justify-between text-sm font-semibold text-gray-900 mb-1">
                                <span class="truncate pr-4">{{ $lead->business_name }} (you)</span>
                                <span class="shrink-0">{{ number_format($lead->reviews) }}</span>
                            </div>
                            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-600 rounded-full" style="width: {{ max(round($lead->reviews / $maxReviews * 100), 1) }}%"></div>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-gray-600 mb-6">{{ $lead->business_name }} has <strong>{{ $lead->reviews }} {{ Str::plural('review', $lead->reviews) }}</strong> at {{ $lead->rating }} stars on Google.</p>
                @endif

                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                    <p class="text-indigo-900 font-medium">More reviews is rarely about better service.</p>
                    <p class="text-indigo-800 text-sm mt-1">Most happy customers simply never get asked at the right moment. A direct link that opens your Google review form, sent right after the visit, removes every extra step.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Review Link -->
    <section class="pb-16" x-data="{ copied: false }">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">
                <h2 class="text-xl font-bold mb-2">Your Google review link</h2>
                <p class="text-gray-600 text-sm mb-4">This is your direct Google review link. Share it with customers and they land straight on your review form.</p>

                @php($reviewUrl = 'https://search.google.com/local/writereview?placeid=' . urlencode($lead->place_id))
                <div class="flex items-center gap-2 bg-gray-50 rounded-xl p-3">
                    <code class="flex-1 text-sm text-gray-700 break-all">{{ $reviewUrl }}</code>
                    <button
                        @click="
                            const text = @js($reviewUrl);
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                navigator.clipboard.writeText(text).then(() => { copied = true; setTimeout(() => copied = false, 2000); }).catch(() => {
                                    const ta = document.createElement('textarea'); ta.value = text; ta.style.position = 'fixed'; ta.style.left = '-9999px'; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); copied = true; setTimeout(() => copied = false, 2000);
                                });
                            } else {
                                const ta = document.createElement('textarea'); ta.value = text; ta.style.position = 'fixed'; ta.style.left = '-9999px'; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); copied = true; setTimeout(() => copied = false, 2000);
                            }
                        "
                        aria-label="Copy review link to clipboard"
                        class="shrink-0 px-3 py-1.5 text-sm font-medium rounded-lg transition"
                        :class="copied ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'"
                    >
                        <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="pb-20">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-indigo-600 rounded-2xl p-8 sm:p-10 text-center">
                <h2 class="text-2xl sm:text-3xl font-bold text-white">Want to automate this?</h2>
                <p class="text-indigo-100 mt-4 max-w-lg mx-auto">{{ config('app.name') }} sends your review link to customers by email or QR code, reminds the ones who don't respond, and alerts you privately when someone had a bad experience, so you can make it right.</p>
                <a href="{{ route('register') }}" class="mt-6 inline-flex items-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-xl hover:bg-indigo-50 transition shadow-lg">
                    Start Your Free 14-Day Trial
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <p class="text-indigo-200 text-sm mt-3">No credit card required.</p>
            </div>
        </div>
    </section>

    </main>

    <!-- Footer -->
    <footer class="py-8 border-t border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-400">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
            <div class="flex gap-6">
                <a href="{{ route('blog.index') }}" class="hover:text-gray-600">Blog</a>
                <a href="mailto:support@quickfeedback.app" class="hover:text-gray-600">Support</a>
                <a href="{{ route('privacy') }}" class="hover:text-gray-600">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="hover:text-gray-600">Terms of Service</a>
            </div>
        </div>
    </footer>

</body>
</html>
