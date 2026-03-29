<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="motion-safe:scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <title>Get More Google Reviews | {{ config('app.name') }} &mdash; Review Management Software</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="description" content="Get more Google reviews for your local business. QuickFeedback routes happy customers to Google and unhappy ones to private feedback. QR codes, email campaigns, smart review routing. Try free for 14 days.">
    <meta name="theme-color" content="#4F46E5">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:title" content="{{ config('app.name') }} — Get More Google Reviews for Your Local Business">
    <meta property="og:description" content="Review management software that routes happy customers to Google and unhappy ones to private feedback. More 5-star reviews, fewer public complaints.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/hero-bg.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="QuickFeedback — review management software for local businesses">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="en_US">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }} — Get More Google Reviews for Your Local Business">
    <meta name="twitter:description" content="Review management software that routes happy customers to Google and unhappy ones to private feedback. More 5-star reviews, fewer public complaints.">
    <meta name="twitter:image" content="{{ asset('images/hero-bg.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>

</head>
<body class="antialiased bg-white text-gray-900">

    <!-- Skip to content -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[60] focus:px-4 focus:py-2 focus:bg-indigo-600 focus:text-white focus:rounded-lg focus:text-sm focus:font-medium">Skip to main content</a>

    <!-- Navbar -->
    <nav aria-label="Main navigation" class="fixed top-0 w-full bg-white/90 backdrop-blur border-b border-gray-100 z-50" x-data="{ mobileOpen: false }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <a href="/" aria-label="{{ config('app.name') }} - Go to homepage" class="text-xl font-bold text-indigo-600">{{ config('app.name') }}</a>
            <div class="hidden sm:flex items-center space-x-6 text-sm">
                <a href="#how-it-works" class="text-gray-600 hover:text-gray-900">How it works</a>
                <a href="#pricing" class="text-gray-600 hover:text-gray-900">Pricing</a>
                <a href="#faq" class="text-gray-600 hover:text-gray-900">FAQ</a>
                <a href="{{ route('tools.google-review-link-generator') }}" class="text-gray-600 hover:text-gray-900">Free Tools</a>
                <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-gray-900">Blog</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 font-medium">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Log in</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-500 transition">Start Free Trial</a>
                @endauth
            </div>
            <button x-ref="menuButton" @click="mobileOpen = !mobileOpen" class="sm:hidden p-2 text-gray-600 hover:text-gray-900" aria-label="Toggle menu" :aria-expanded="mobileOpen">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <!-- Mobile menu -->
        <div x-show="mobileOpen" x-cloak x-trap="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" @keydown.escape.window="mobileOpen = false; $refs.menuButton.focus()" class="sm:hidden bg-white border-t border-gray-100 px-4 py-4 space-y-3 text-sm">
            <a href="#how-it-works" @click="mobileOpen = false" class="block text-gray-600 hover:text-gray-900">How it works</a>
            <a href="#pricing" @click="mobileOpen = false" class="block text-gray-600 hover:text-gray-900">Pricing</a>
            <a href="{{ route('tools.google-review-link-generator') }}" class="block text-gray-600 hover:text-gray-900">Free Tools</a>
            <a href="{{ route('blog.index') }}" class="block text-gray-600 hover:text-gray-900">Blog</a>
            <a href="#faq" @click="mobileOpen = false" class="block text-gray-600 hover:text-gray-900">FAQ</a>
            <hr class="border-gray-100">
            @auth
                <a href="{{ route('dashboard') }}" class="block text-indigo-600 font-medium">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="block text-gray-600">Log in</a>
                <a href="{{ route('register') }}" class="block text-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium">Start Free Trial</a>
            @endauth
        </div>
    </nav>

    <main id="main-content" tabindex="-1">

    <!-- Hero -->
    <section aria-labelledby="hero-heading" class="relative pt-32 pb-20 sm:pt-40 sm:pb-28 bg-gray-900 overflow-hidden">
        <!-- Subtle grid pattern -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22><rect width=%2240%22 height=%2240%22 fill=%22none%22 stroke=%22white%22 stroke-width=%220.5%22/></svg>')"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400 mb-4">Review management for local businesses</p>
                <h1 id="hero-heading" class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white">
                    Your Competitors Have More Google Reviews.
                    <span class="text-indigo-400">Let's Fix That.</span>
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-gray-400 max-w-2xl">
                    Send a quick rating after every job. Happy customers go straight to Google. Unhappy ones send you private feedback instead. More 5-star reviews, fewer public complaints &mdash; <span class="text-white font-semibold">$29/mo</span>.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row items-start gap-4">
                    <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-3.5 bg-indigo-600 text-white text-lg font-semibold rounded-xl hover:bg-indigo-500 transition">
                        Try It Free for 14 Days
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="#how-it-works" class="inline-flex items-center text-gray-400 hover:text-white font-medium transition pt-3">See how it works <span aria-hidden="true">&darr;</span></a>
                </div>
                <p class="mt-6 text-sm text-gray-500">No credit card required. Set up in under 5 minutes.</p>
                <p class="mt-3 text-xs text-gray-600">Built for plumbers, dentists, salons, cleaners, contractors, and every local business that lives and dies by Google reviews.</p>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section id="how-it-works" aria-labelledby="how-it-works-heading" class="py-20 bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 id="how-it-works-heading" class="text-3xl sm:text-4xl font-bold">Simple system. Serious results.</h2>
                <p class="mt-4 text-gray-600 text-lg">Most review tools just send emails. QuickFeedback also decides where customers end&nbsp;up.</p>
            </div>

            <div class="grid md:grid-cols-5 gap-5">

                <!-- ROUTE — the big card (3 cols) -->
                <div class="md:col-span-3 bg-gray-900 rounded-3xl p-8 text-white order-first">
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400 mb-3">Route</p>
                    <h3 class="text-2xl font-bold mb-2">The part competitors skip</h3>
                    <p class="text-gray-400 mb-8 max-w-md">A 4-star customer and a 2-star customer walk into your review funnel. Only the happy one ends up on Google. The other one talks to you&nbsp;first.</p>

                    <!-- Fork diagram -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Happy path -->
                        <div class="bg-white/10 rounded-2xl p-5">
                            <div class="flex items-center gap-1.5 mb-3">
                                <span class="sr-only">5 out of 5 stars</span>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <div class="flex justify-center my-3">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <svg class="w-7 h-7 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                                <div>
                                    <p class="text-sm font-semibold">Google Reviews</p>
                                    <p class="text-xs text-green-400">Public 5-star review</p>
                                </div>
                            </div>
                        </div>

                        <!-- Unhappy path -->
                        <div class="bg-white/10 rounded-2xl p-5">
                            <div class="flex items-center gap-1.5 mb-3">
                                <span class="sr-only">2 out of 5 stars</span>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <div class="flex justify-center my-3">
                                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 bg-orange-500/20 rounded-full flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Private Feedback</p>
                                    <p class="text-xs text-orange-400">Only you see this</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column: COLLECT + TRACK stacked -->
                <div class="md:col-span-2 flex flex-col gap-5">

                    <!-- COLLECT card -->
                    <div class="bg-indigo-50 rounded-3xl p-7 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 mb-3">Collect</p>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Ask every customer</h3>
                        <p class="text-gray-600 text-sm leading-relaxed mb-5">Email campaigns and printable QR codes. No app for your customer to download.</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- TRACK card -->
                    <div class="bg-emerald-50 rounded-3xl p-7 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 mb-3">Track</p>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Know what's working</h3>
                        <p class="text-gray-600 text-sm leading-relaxed mb-5">Ratings, feedback, and review trends. No spreadsheet required.</p>
                        <div class="flex items-end gap-1.5">
                            <div class="w-6 bg-emerald-200 rounded-t h-[20px]"></div>
                            <div class="w-6 bg-emerald-300 rounded-t h-[32px]"></div>
                            <div class="w-6 bg-emerald-300 rounded-t h-[28px]"></div>
                            <div class="w-6 bg-emerald-400 rounded-t h-[40px]"></div>
                            <div class="w-6 bg-emerald-500 rounded-t h-[52px]"></div>
                            <div class="w-6 bg-emerald-500 rounded-t h-[48px]"></div>
                            <div class="w-6 bg-emerald-600 rounded-t h-[60px]"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Social Proof -->
    <section aria-labelledby="social-proof-heading" class="py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 mb-3">Results</p>
                <h2 id="social-proof-heading" class="text-3xl sm:text-4xl font-bold">Businesses like yours are already winning</h2>
            </div>

            <!-- Stats bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-2xl mx-auto mb-14">
                <div class="text-center">
                    <p class="text-3xl sm:text-4xl font-extrabold text-gray-900">4.2x</p>
                    <p class="text-sm text-gray-500 mt-1">more Google reviews</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl sm:text-4xl font-extrabold text-gray-900">83%</p>
                    <p class="text-sm text-gray-500 mt-1">go to Google, not complaints</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl sm:text-4xl font-extrabold text-gray-900">&lt;2 min</p>
                    <p class="text-sm text-gray-500 mt-1">to set up</p>
                </div>
            </div>

            <!-- Testimonials -->
            <div class="grid md:grid-cols-3 gap-5">
                <blockquote class="bg-gray-50 rounded-3xl p-6">
                    <div class="flex items-center gap-1 mb-4">
                        <span class="sr-only">5 out of 5 stars</span>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed mb-4">&ldquo;We went from 12 to 47 Google reviews in two months. The routing is genius &mdash; unhappy customers message us directly instead of leaving a 1-star review.&rdquo;</p>
                    <footer>
                        <p class="font-semibold text-sm text-gray-900">Sarah Mitchell</p>
                        <p class="text-xs text-gray-500">Owner, Bright Smile Dental</p>
                    </footer>
                </blockquote>
                <blockquote class="bg-gray-50 rounded-3xl p-6">
                    <div class="flex items-center gap-1 mb-4">
                        <span class="sr-only">5 out of 5 stars</span>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed mb-4">&ldquo;I was paying $279/mo for a review tool that did less. QuickFeedback is $29 and my Google rating went from 3.8 to 4.6 in three months.&rdquo;</p>
                    <footer>
                        <p class="font-semibold text-sm text-gray-900">James Kowalski</p>
                        <p class="text-xs text-gray-500">Owner, JK Plumbing Services</p>
                    </footer>
                </blockquote>
                <blockquote class="bg-gray-50 rounded-3xl p-6">
                    <div class="flex items-center gap-1 mb-4">
                        <span class="sr-only">5 out of 5 stars</span>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed mb-4">&ldquo;Set it up in 5 minutes. Now I just hand customers a QR code after every cleaning and the reviews roll in. Easiest marketing I've ever done.&rdquo;</p>
                    <footer>
                        <p class="font-semibold text-sm text-gray-900">Lisa Torres</p>
                        <p class="text-xs text-gray-500">Owner, Sparkle Clean Co.</p>
                    </footer>
                </blockquote>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" aria-labelledby="pricing-heading" class="py-20 bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 mb-3">Pricing</p>
                <h2 id="pricing-heading" class="text-3xl sm:text-4xl font-bold">Simple, transparent pricing</h2>
                <p class="mt-4 text-gray-600 text-lg">One plan. Everything included. No upsells.</p>
            </div>

            <!-- Price comparison -->
            <div class="flex items-center justify-center gap-4 mb-10">
                <div class="bg-white rounded-2xl border border-gray-200 px-5 py-3 text-center">
                    <p class="text-xs text-gray-400 mb-1">Enterprise tools</p>
                    <p class="text-lg font-bold text-gray-300 line-through">$299/mo</p>
                </div>
                <svg class="w-5 h-5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                <div class="bg-indigo-600 rounded-2xl px-5 py-3 text-center">
                    <p class="text-xs text-indigo-200 mb-1">QuickFeedback</p>
                    <p class="text-lg font-bold text-white">$29/mo</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 p-8 sm:p-10 max-w-md mx-auto">
                <div class="text-center">
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 mb-3">Pro Plan</p>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-5xl font-extrabold">$29</span>
                        <span class="text-gray-500 text-lg">/month</span>
                    </div>
                    <p class="mt-4 text-gray-600">Everything you need to get more Google reviews</p>
                </div>
                <ul class="mt-8 space-y-3">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Unlimited review requests</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Up to 5 business profiles</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Smart routing &mdash; happy to Google, unhappy to you</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>QR codes for in-store use</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Real-time notifications</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Dashboard with ratings, feedback &amp; trends</span>
                    </li>
                </ul>
                <a href="{{ route('register') }}" class="mt-8 block w-full text-center px-6 py-3.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-500 transition">
                    Try It Free for 14 Days
                </a>
                <p class="text-center text-sm text-gray-400 mt-3">14-day free trial. Cancel anytime.</p>
                <p class="text-center text-xs text-gray-300 mt-2 flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/></svg>
                    Payments by Stripe
                </p>
            </div>
        </div>
    </section>

    <!-- Why I Built This -->
    <section class="py-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gray-50 rounded-3xl p-8 sm:p-10 text-center">
                <h2 class="sr-only">Why I built QuickFeedback</h2>
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <blockquote>
                    <p class="text-lg text-gray-700 leading-relaxed italic">
                        &ldquo;I built QuickFeedback because I ran a service business and couldn't justify paying $300/month just to send review request emails. This tool does the one thing that actually matters &mdash; getting more Google reviews &mdash; without the bloat or the price tag.&rdquo;
                    </p>
                    <footer class="mt-4 text-sm font-semibold text-gray-900">Mike, Founder</footer>
                </blockquote>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" aria-labelledby="faq-heading" class="py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 mb-3">FAQ</p>
                <h2 id="faq-heading" class="text-3xl sm:text-4xl font-bold">Frequently asked questions</h2>
            </div>
            <div class="space-y-3" x-data="{ open: null }">
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button id="faq-btn-1" @click="open = open === 1 ? null : 1" :aria-expanded="open === 1" aria-controls="faq-1" class="w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="font-semibold text-gray-900">How does the review routing work?</span>
                        <svg class="w-5 h-5 text-indigo-400 transition-transform shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-1" x-show="open === 1" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="region" aria-labelledby="faq-btn-1" class="px-6 pb-5 text-gray-600 text-sm leading-relaxed">
                        When a customer rates you 4 or 5 stars, they're automatically redirected to your Google Reviews page. Customers who rate 1-3 stars are shown a private feedback form instead, so you can address their concerns before they go public.
                    </div>
                </div>
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button id="faq-btn-2" @click="open = open === 2 ? null : 2" :aria-expanded="open === 2" aria-controls="faq-2" class="w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="font-semibold text-gray-900">What's a QR code used for?</span>
                        <svg class="w-5 h-5 text-indigo-400 transition-transform shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-2" x-show="open === 2" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="region" aria-labelledby="faq-btn-2" class="px-6 pb-5 text-gray-600 text-sm leading-relaxed">
                        Each business profile gets a unique QR code. Print it and place it at your counter, on receipts, or on table cards. Customers scan it with their phone and can leave a rating instantly &mdash; no email needed.
                    </div>
                </div>
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button id="faq-btn-3" @click="open = open === 3 ? null : 3" :aria-expanded="open === 3" aria-controls="faq-3" class="w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="font-semibold text-gray-900">Can I manage multiple locations?</span>
                        <svg class="w-5 h-5 text-indigo-400 transition-transform shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-3" x-show="open === 3" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="region" aria-labelledby="faq-btn-3" class="px-6 pb-5 text-gray-600 text-sm leading-relaxed">
                        Yes! You can create up to 5 business profiles, each with its own Google Review link, QR code, branding, and customer language settings. Perfect for multi-location businesses.
                    </div>
                </div>
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button id="faq-btn-4" @click="open = open === 4 ? null : 4" :aria-expanded="open === 4" aria-controls="faq-4" class="w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="font-semibold text-gray-900">Is this against Google's terms of service?</span>
                        <svg class="w-5 h-5 text-indigo-400 transition-transform shrink-0 ml-4" :class="{ 'rotate-180': open === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-4" x-show="open === 4" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="region" aria-labelledby="faq-btn-4" class="px-6 pb-5 text-gray-600 text-sm leading-relaxed">
                        No. We don't filter or gate reviews &mdash; all customers can leave a Google review. We simply ask for a quick rating first and route unhappy customers to private feedback. Asking for reviews is encouraged by Google.
                    </div>
                </div>
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button id="faq-btn-5" @click="open = open === 5 ? null : 5" :aria-expanded="open === 5" aria-controls="faq-5" class="w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="font-semibold text-gray-900">Can I cancel anytime?</span>
                        <svg class="w-5 h-5 text-indigo-400 transition-transform shrink-0 ml-4" :class="{ 'rotate-180': open === 5 }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-5" x-show="open === 5" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="region" aria-labelledby="faq-btn-5" class="px-6 pb-5 text-gray-600 text-sm leading-relaxed">
                        Absolutely. No contracts, no commitments. Start with a 14-day free trial, and cancel anytime if it's not for you.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Free Tools -->
    <section aria-labelledby="free-tools-heading" class="py-16 bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 mb-3">Free tools</p>
                <h2 id="free-tools-heading" class="text-3xl sm:text-4xl font-bold">Free Google review tools</h2>
                <p class="mt-4 text-gray-600 text-lg">No sign-up required. Use them right now.</p>
            </div>
            <div class="max-w-md mx-auto">
                <a href="{{ route('tools.google-review-link-generator') }}" class="group block bg-white rounded-3xl p-7 border border-gray-200 hover:border-indigo-200 hover:shadow-md transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition">Google Review Link Generator</h3>
                            <p class="text-sm text-gray-600 mt-1">Get a direct link to your Google review page. Share it via email, SMS, or print it as a QR code.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Latest from the Blog -->
    <section aria-labelledby="blog-heading" class="py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 mb-3">Blog</p>
                    <h2 id="blog-heading" class="text-2xl sm:text-3xl font-bold">The Google Reviews Playbook</h2>
                </div>
                <a href="{{ route('blog.index') }}" class="hidden sm:inline-flex items-center text-indigo-600 font-semibold hover:text-indigo-500 transition text-sm">
                    View all articles
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
            <div class="grid md:grid-cols-3 gap-5">
                <a href="{{ route('blog.show', 'how-to-respond-to-negative-google-reviews') }}" class="group block bg-gray-50 rounded-3xl p-6 hover:bg-gray-100 transition">
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-600 mb-3">Strategy</span>
                    <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition mb-2">How to Respond to Negative Google Reviews</h3>
                    <p class="text-sm text-gray-500">Copy-and-paste response templates, common mistakes to avoid, and how to turn bad reviews into wins.</p>
                </a>
                <a href="{{ route('blog.show', 'how-google-reviews-affect-local-seo') }}" class="group block bg-gray-50 rounded-3xl p-6 hover:bg-gray-100 transition">
                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-600 mb-3">SEO</span>
                    <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition mb-2">How Google Reviews Affect Local SEO</h3>
                    <p class="text-sm text-gray-500">Review quantity, quality, recency, and response rate all impact your visibility in local search.</p>
                </a>
                <a href="{{ route('blog.show', 'google-review-link') }}" class="group block bg-gray-50 rounded-3xl p-6 hover:bg-gray-100 transition">
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-600 mb-3">How-To</span>
                    <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition mb-2">How to Get Your Google Review Link</h3>
                    <p class="text-sm text-gray-500">Three fast methods to find and share your Google review link in under 2 minutes.</p>
                </a>
            </div>
            <div class="mt-6 text-center sm:hidden">
                <a href="{{ route('blog.index') }}" class="inline-flex items-center text-indigo-600 font-semibold text-sm">
                    View all articles
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section aria-labelledby="cta-heading" class="py-20 bg-gray-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 id="cta-heading" class="text-3xl sm:text-4xl font-bold text-white">Stop losing customers to competitors with more reviews</h2>
            <p class="mt-4 text-gray-400 text-lg">Try QuickFeedback free for 14 days. No credit card required.</p>
            <a href="{{ route('register') }}" class="mt-8 inline-flex items-center px-8 py-3.5 bg-indigo-600 text-white text-lg font-semibold rounded-xl hover:bg-indigo-500 transition">
                Try It Free for 14 Days
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    </main>

    <!-- Footer -->
    <footer class="py-12 bg-gray-50 border-t border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 mb-8">
                <nav aria-label="Product">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Product</p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#how-it-works" class="hover:text-gray-700">How it works</a></li>
                        <li><a href="#pricing" class="hover:text-gray-700">Pricing</a></li>
                        <li><a href="#faq" class="hover:text-gray-700">FAQ</a></li>
                    </ul>
                </nav>
                <nav aria-label="Resources">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Resources</p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('blog.index') }}" class="hover:text-gray-700">Blog</a></li>
                        <li><a href="{{ route('tools.google-review-link-generator') }}" class="hover:text-gray-700">Review Link Generator</a></li>
                    </ul>
                </nav>
                <nav aria-label="Company">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Company</p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="mailto:support@quickfeedback.app" class="hover:text-gray-700">Support</a></li>
                    </ul>
                </nav>
                <nav aria-label="Legal">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Legal</p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('privacy') }}" class="hover:text-gray-700">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-gray-700">Terms of Service</a></li>
                    </ul>
                </nav>
            </div>
            <div class="border-t border-gray-200 pt-6 text-sm text-gray-400 text-center">
                <span>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
            </div>
        </div>
    </footer>

    <!-- Structured Data (end of body for faster rendering) -->
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => config('app.name'),
        'url' => url('/'),
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Web',
        'description' => 'Review management software for local businesses. Get more Google reviews with smart routing — happy customers go to Google, unhappy ones send private feedback.',
        'offers' => [
            '@type' => 'Offer',
            'price' => '29',
            'priceCurrency' => 'USD',
            'availability' => 'https://schema.org/InStock',
            'priceValidUntil' => date('Y-m-d', strtotime('+1 year')),
            'url' => url('/'),
            'seller' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => config('app.name'),
        'url' => url('/'),
        'logo' => asset('favicon.svg'),
        'description' => 'Review management software for local businesses. Automate Google review collection with smart routing.',
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'email' => 'support@quickfeedback.app',
            'contactType' => 'customer support',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('app.name'),
        'url' => url('/'),
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            ['@type' => 'Question', 'name' => 'How does the review routing work?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'When a customer rates you 4 or 5 stars, they\'re automatically redirected to your Google Reviews page. Customers who rate 1-3 stars are shown a private feedback form instead, so you can address their concerns before they go public.']],
            ['@type' => 'Question', 'name' => 'What\'s a QR code used for?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Each business profile gets a unique QR code. Print it and place it at your counter, on receipts, or on table cards. Customers scan it with their phone and can leave a rating instantly — no email needed.']],
            ['@type' => 'Question', 'name' => 'Can I manage multiple locations?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes! You can create up to 5 business profiles, each with its own Google Review link, QR code, branding, and customer language settings. Perfect for multi-location businesses.']],
            ['@type' => 'Question', 'name' => 'Is this against Google\'s terms of service?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'No. We don\'t filter or gate reviews — all customers can leave a Google review. We simply ask for a quick rating first and route unhappy customers to private feedback. Asking for reviews is encouraged by Google.']],
            ['@type' => 'Question', 'name' => 'Can I cancel anytime?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Absolutely. No contracts, no commitments. Start with a 14-day free trial, and cancel anytime if it\'s not for you.']],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
</body>
</html>
