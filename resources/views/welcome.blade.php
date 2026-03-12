<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Get More 5-Star Google Reviews on Autopilot</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="description" content="Automatically collect 5-star Google reviews from happy customers. Send review requests via email or QR code, route feedback smartly, and grow your online reputation.">
    <meta name="keywords" content="Google reviews, review management, review requests, reputation management, QR code reviews, customer feedback">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:title" content="{{ config('app.name') }} — Get More 5-Star Google Reviews on Autopilot">
    <meta property="og:description" content="Send review requests, collect feedback privately, and redirect happy customers to Google Reviews. Simple, fast, effective.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/hero-bg.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }} — Get More 5-Star Google Reviews on Autopilot">
    <meta name="twitter:description" content="Automatically collect 5-star Google reviews from happy customers. Smart routing sends positive reviews to Google and negative feedback to you privately.">
    <meta name="twitter:image" content="{{ asset('images/hero-bg.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => config('app.name'),
        'applicationCategory' => 'BusinessApplication',
        'description' => 'Automatically collect 5-star Google reviews from happy customers. Send review requests via email or QR code and grow your online reputation.',
        'offers' => [
            '@type' => 'Offer',
            'price' => '29',
            'priceCurrency' => 'USD',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
</head>
<body class="antialiased bg-white text-gray-900">

    <!-- Navbar -->
    <nav class="fixed top-0 w-full bg-white/90 backdrop-blur border-b border-gray-100 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <a href="/" class="text-xl font-bold text-indigo-600">{{ config('app.name') }}</a>
            <div class="hidden sm:flex items-center space-x-6 text-sm">
                <a href="#how-it-works" class="text-gray-600 hover:text-gray-900">How it works</a>
                <a href="#pricing" class="text-gray-600 hover:text-gray-900">Pricing</a>
                <a href="#faq" class="text-gray-600 hover:text-gray-900">FAQ</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 font-medium">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Log in</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-500 transition">Start Free Trial</a>
                @endauth
            </div>
            <div class="sm:hidden flex items-center space-x-3 text-sm">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 font-medium">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600">Log in</a>
                    <a href="{{ route('register') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm">Start Free</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="relative pt-32 pb-20 sm:pt-40 sm:pb-28 overflow-hidden">
        <!-- Background image -->
        <div class="absolute inset-0 -z-10">
            <img src="{{ asset('images/hero-bg.jpg') }}" alt="Business growth and success" class="w-full h-full object-cover object-bottom">
            <div class="absolute inset-0 bg-gradient-to-b from-white/60 via-white/50 to-white"></div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-gray-900 drop-shadow-sm">
                Get More 5-Star Google Reviews
                <span class="text-indigo-600">on Autopilot</span>
            </h1>
            <p class="mt-6 text-lg sm:text-xl text-gray-700 max-w-2xl mx-auto font-medium">
                Send review requests via email, collect feedback privately, and redirect happy customers straight to Google Reviews. Simple, fast, effective.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-3 bg-indigo-600 text-white text-lg font-semibold rounded-xl hover:bg-indigo-500 transition shadow-lg shadow-indigo-200">
                    Start Free Trial
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="#how-it-works" class="text-gray-600 hover:text-gray-900 font-medium">See how it works &darr;</a>
            </div>
            <p class="mt-6 text-sm text-gray-400">No credit card required. Set up in under 5 minutes.</p>
        </div>
    </section>

    <!-- How it works -->
    <section id="how-it-works" class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold">How it works</h2>
                <p class="mt-4 text-gray-600 text-lg">Three simple steps to more Google reviews</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-sm font-semibold text-indigo-600 mb-2">Step 1</div>
                    <h3 class="text-xl font-bold mb-2">Send Review Requests</h3>
                    <p class="text-gray-600">Enter your customers' emails or share a QR code. We send a friendly, branded request to rate your business.</p>
                </div>
                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-sm font-semibold text-indigo-600 mb-2">Step 2</div>
                    <h3 class="text-xl font-bold mb-2">Smart Routing</h3>
                    <p class="text-gray-600">Happy customers (4-5 stars) are redirected to leave a Google review. Unhappy ones leave private feedback instead.</p>
                </div>
                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div class="text-sm font-semibold text-indigo-600 mb-2">Step 3</div>
                    <h3 class="text-xl font-bold mb-2">Watch Reviews Grow</h3>
                    <p class="text-gray-600">Track ratings, read private feedback, and see your Google review count climb — all from one dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold">Simple, transparent pricing</h2>
                <p class="mt-4 text-gray-600 text-lg">One plan. Everything included. No surprises.</p>
            </div>
            <div class="bg-white border-2 border-indigo-600 rounded-2xl shadow-xl p-8 sm:p-10 max-w-md mx-auto">
                <div class="text-center">
                    <div class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-2">Pro Plan</div>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-5xl font-extrabold">$29</span>
                        <span class="text-gray-500 text-lg">/month</span>
                    </div>
                    <p class="mt-4 text-gray-600">Everything you need to grow your online reputation</p>
                </div>
                <ul class="mt-8 space-y-3">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Unlimited review requests</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Up to 5 business profiles</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Smart review routing</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>QR codes for in-store use</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Private feedback collection</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Analytics dashboard</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Email notifications</span>
                    </li>
                </ul>
                <a href="{{ route('register') }}" class="mt-8 block w-full text-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-500 transition shadow-lg shadow-indigo-200">
                    Start Free Trial
                </a>
                <p class="text-center text-sm text-gray-400 mt-3">14-day free trial. Cancel anytime.</p>
                <p class="text-center text-xs text-gray-300 mt-2 flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/></svg>
                    Payments by Stripe
                </p>
            </div>
        </div>
    </section>

    <!-- Social Proof -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-lg text-gray-600">
                <span class="font-semibold text-gray-900">Join early adopters</span> who are already growing their Google reviews with {{ config('app.name') }}.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-8 sm:gap-12 text-gray-400">
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900">5 min</div>
                    <div class="text-sm mt-1">Setup time</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900">4.8x</div>
                    <div class="text-sm mt-1">More reviews</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900">90%</div>
                    <div class="text-sm mt-1">Open rate</div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold">Frequently asked questions</h2>
            </div>
            <div class="space-y-4" x-data="{ open: null }">
                <div class="border border-gray-200 rounded-xl">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">How does the review routing work?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 1" x-cloak class="px-6 pb-4 text-gray-600">
                        When a customer rates you 4 or 5 stars, they're automatically redirected to your Google Reviews page. Customers who rate 1-3 stars are shown a private feedback form instead, so you can address their concerns before they go public.
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">What's a QR code used for?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 2" x-cloak class="px-6 pb-4 text-gray-600">
                        Each business profile gets a unique QR code. Print it and place it at your counter, on receipts, or on table cards. Customers scan it with their phone and can leave a rating instantly — no email needed.
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">Can I manage multiple locations?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 3" x-cloak class="px-6 pb-4 text-gray-600">
                        Yes! You can create up to 5 business profiles, each with its own Google Review link, QR code, branding, and customer language settings. Perfect for multi-location businesses.
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">Is this against Google's terms of service?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 4" x-cloak class="px-6 pb-4 text-gray-600">
                        No. We don't filter or gate reviews — all customers can leave a Google review. We simply ask for a quick rating first and route unhappy customers to private feedback. Asking for reviews is encouraged by Google.
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl">
                    <button @click="open = open === 5 ? null : 5" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">Can I cancel anytime?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 5 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 5" x-cloak class="px-6 pb-4 text-gray-600">
                        Absolutely. No contracts, no commitments. Start with a 14-day free trial, and cancel anytime if it's not for you.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-20 bg-indigo-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">Ready to get more 5-star reviews?</h2>
            <p class="mt-4 text-indigo-100 text-lg">Start your free trial today. No credit card required.</p>
            <a href="{{ route('register') }}" class="mt-8 inline-flex items-center px-8 py-3 bg-white text-indigo-600 text-lg font-semibold rounded-xl hover:bg-indigo-50 transition shadow-lg">
                Start Free Trial
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

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
