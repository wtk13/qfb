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

                <!-- Visual comparison bar -->
                <div class="mb-6">
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
                        <span>Your reviews</span>
                        <span>Top 3 local benchmark (50)</span>
                    </div>
                    <div class="relative h-4 bg-gray-100 rounded-full overflow-hidden">
                        <div class="absolute inset-y-0 left-0 bg-indigo-600 rounded-full transition-all duration-500" style="width: {{ min(($lead->reviews / 50) * 100, 100) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-400 mt-1">
                        <span>{{ $lead->reviews }}</span>
                        <span>50</span>
                    </div>
                </div>

                <p class="text-gray-600 mb-4">Businesses ranking in Google's top 3 local results typically have <strong>30&ndash;50+ reviews</strong>.</p>

                @if($lead->reviews == 0)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <p class="text-amber-800 font-medium">You don't have any reviews yet — that's actually a huge opportunity.</p>
                        <p class="text-amber-700 text-sm mt-1">Every review you collect from here moves the needle. Your competitors may have a head start, but customers trust businesses that actively collect fresh reviews.</p>
                    </div>
                @elseif($lead->reviews < 10)
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <p class="text-blue-800 font-medium">You're close to breaking through.</p>
                        <p class="text-blue-700 text-sm mt-1">A few more reviews could change your visibility in local search. Businesses that cross the 10-review mark see a noticeable bump in trust and click-through rates.</p>
                    </div>
                @elseif($lead->reviews <= 30)
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <p class="text-green-800 font-medium">You have a solid foundation.</p>
                        <p class="text-green-700 text-sm mt-1">Consistent new reviews will push you higher in local rankings. Google rewards businesses that collect reviews steadily over time, not just in bursts.</p>
                    </div>
                @else
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <p class="text-green-800 font-medium">You're in great shape — now it's about staying ahead.</p>
                        <p class="text-green-700 text-sm mt-1">Your competitors are collecting reviews too. Automating your review requests keeps you consistently at the top of local search results.</p>
                    </div>
                @endif
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
                <p class="text-indigo-100 mt-4 max-w-lg mx-auto">{{ config('app.name') }} sends your review link to every customer automatically, follows up with non-responders, and routes unhappy customers to private feedback instead of a public review.</p>
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
