<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Free Google Review Link Generator — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="description" content="Generate your direct Google review link instantly. Free tool — enter your Place ID and get a shareable link in seconds. No signup required.">
    <link rel="canonical" href="{{ route('tools.google-review-link-generator') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4F46E5">
    <meta property="og:title" content="Free Google Review Link Generator — {{ config('app.name') }}">
    <meta property="og:description" content="Generate your direct Google review link instantly. Free tool — enter your Place ID and get a shareable link in seconds. No signup required.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('tools.google-review-link-generator') }}">
    <meta property="og:image" content="{{ asset('images/hero-bg.jpg') }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Free Google Review Link Generator — {{ config('app.name') }}">
    <meta name="twitter:description" content="Generate your direct Google review link instantly. Free tool — enter your Place ID and get a shareable link in seconds. No signup required.">
    <meta name="twitter:image" content="{{ asset('images/hero-bg.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => 'Google Review Link Generator',
        'url' => route('tools.google-review-link-generator'),
        'description' => 'Generate your direct Google review link instantly. Enter your Place ID and get a shareable link in seconds.',
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Web',
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'USD',
        ],
        'author' => [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            ['@type' => 'Question', 'name' => 'What is a Google Place ID?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'A Google Place ID is a unique identifier for any place in Google\'s database. It looks like a string of letters and numbers (e.g., ChIJN1t_tDeuEmsRUsoyG83frY4). You need it to generate your direct Google review link.']],
            ['@type' => 'Question', 'name' => 'Is this tool really free?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, completely free. No signup, no email required, no limits. Generate as many review links as you need.']],
            ['@type' => 'Question', 'name' => 'How do I use my Google review link?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Share it via email, SMS, your website, or social media. When someone clicks the link, they land directly on your Google review form.']],
            ['@type' => 'Question', 'name' => 'Can I automate sending this link to customers?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. QuickFeedback automatically sends your Google review link to every customer after each job or appointment, follows up with non-responders, and routes unhappy customers to private feedback.']],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
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
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">Google Review Link Generator</h1>
            <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Enter your Google Place ID and get a direct review link — free, instantly, no signup required.</p>
        </div>
    </section>

    <!-- Tool -->
    <section class="pb-16" x-data="reviewLinkGenerator()">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Input -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">
                <label for="place-id" class="block text-sm font-medium text-gray-700 mb-2">Your Google Place ID</label>
                <div class="flex gap-3">
                    <input
                        type="text"
                        id="place-id"
                        x-model="placeId"
                        @keydown.enter="generate()"
                        placeholder="e.g. ChIJN1t_tDeuEmsRUsoyG83frY4"
                        class="flex-1 rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base"
                    >
                    <button
                        @click="generate()"
                        :disabled="!placeId.trim()"
                        class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-500 transition disabled:opacity-40 disabled:cursor-not-allowed text-sm sm:text-base whitespace-nowrap"
                    >
                        Generate
                    </button>
                </div>
                <p class="mt-3 text-sm text-gray-500">
                    Don't know your Place ID? Follow these steps:
                </p>
                <ol class="mt-2 text-sm text-gray-500 list-decimal list-inside space-y-1">
                    <li>Go to <a href="https://www.google.com/maps" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-500 underline">Google Maps</a> and search for your business</li>
                    <li>Click on your business listing</li>
                    <li>Copy the full URL and paste it here — we'll extract the Place ID automatically</li>
                    <li>Or use <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-500 underline">Google's Place ID Finder</a> — search your business name, copy the ID (starts with <code class="bg-gray-100 px-1 rounded text-xs">ChIJ...</code>)</li>
                </ol>
                <div x-show="error" x-cloak class="mt-3 text-sm text-red-600 bg-red-50 rounded-lg p-3" x-text="error"></div>
            </div>

            <!-- Results -->
            <div x-show="generated" x-cloak x-transition class="mt-8 space-y-6">

                <!-- Review Link -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-bold mb-3">Your Google Review Link</h2>
                    <div class="flex items-center gap-2 bg-gray-50 rounded-xl p-3">
                        <code class="flex-1 text-sm text-gray-700 break-all" x-text="reviewLink"></code>
                        <button
                            @click="copyToClipboard(reviewLink, 'link')"
                            aria-label="Copy review link to clipboard"
                            class="shrink-0 px-3 py-1.5 text-sm font-medium rounded-lg transition"
                            :class="copied.link ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'"
                        >
                            <span x-text="copied.link ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                </div>

                <!-- Email Capture -->
                <div x-show="!emailSent" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h3 class="text-lg font-bold mb-1">Want this link emailed to you with a ready-to-send customer template?</h3>
                    <p class="text-sm text-gray-500 mb-4">We'll send you the link plus a copy-paste email template you can use right away.</p>
                    <div class="flex gap-3">
                        <input
                            type="email"
                            x-model="email"
                            @keydown.enter="submitEmail()"
                            placeholder="you@example.com"
                            class="flex-1 rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base"
                        >
                        <button
                            @click="submitEmail()"
                            :disabled="!email.trim() || emailSending"
                            class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-500 transition disabled:opacity-40 disabled:cursor-not-allowed text-sm sm:text-base whitespace-nowrap"
                        >
                            <span x-show="!emailSending">Send it to me</span>
                            <span x-show="emailSending" x-cloak>Sending...</span>
                        </button>
                    </div>
                </div>

                <div x-show="emailSent" x-cloak class="bg-white border border-green-200 rounded-2xl shadow-sm p-6 text-center">
                    <p class="text-green-700 font-semibold">Check your inbox!</p>
                </div>

                <!-- CTA -->
                <div class="bg-indigo-600 rounded-2xl p-8 text-center">
                    <h3 class="text-2xl font-bold text-white">You have the link. Now automate sending it.</h3>
                    <p class="text-indigo-100 mt-2 max-w-lg mx-auto">{{ config('app.name') }} sends your review link to every customer automatically after each job, follows up with non-responders, and routes unhappy customers to private feedback.</p>
                    <a href="{{ route('register') }}" class="mt-6 inline-flex items-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-xl hover:bg-indigo-50 transition shadow-lg">
                        Start Your Free 14-Day Trial
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <p class="text-indigo-200 text-sm mt-3">No credit card required.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-center mb-10">Frequently asked questions</h2>
            <div class="space-y-4" x-data="{ open: null }">
                <div class="border border-gray-200 rounded-xl bg-white">
                    <button @click="open = open === 1 ? null : 1" :aria-expanded="open === 1" aria-controls="faq-1" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">What is a Google Place ID?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 1" x-cloak id="faq-1" role="region" class="px-6 pb-4 text-gray-600">
                        A Google Place ID is a unique identifier for any place in Google's database. It's a string of letters and numbers (e.g., <code class="text-sm bg-gray-100 px-1 rounded">ChIJN1t_tDeuEmsRUsoyG83frY4</code>). You can find yours using <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener" class="text-indigo-600 underline hover:text-indigo-500">Google's Place ID Finder</a> — just search your business name and copy the ID.
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl bg-white">
                    <button @click="open = open === 2 ? null : 2" :aria-expanded="open === 2" aria-controls="faq-2" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">Is this tool really free?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 2" x-cloak id="faq-2" role="region" class="px-6 pb-4 text-gray-600">
                        Yes, completely free. No signup, no email required, no limits. Generate as many review links as you need.
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl bg-white">
                    <button @click="open = open === 3 ? null : 3" :aria-expanded="open === 3" aria-controls="faq-3" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">How do I use my Google review link?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 3" x-cloak id="faq-3" role="region" class="px-6 pb-4 text-gray-600">
                        Share it everywhere: in emails, text messages, on your website, and in social media bios. Check out our <a href="{{ route('blog.show', 'google-review-link') }}" class="text-indigo-600 underline hover:text-indigo-500">complete guide to using your Google review link</a> for detailed strategies.
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl bg-white">
                    <button @click="open = open === 4 ? null : 4" :aria-expanded="open === 4" aria-controls="faq-4" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">Can I automate sending this link to customers?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 4" x-cloak id="faq-4" role="region" class="px-6 pb-4 text-gray-600">
                        Yes — that's exactly what <a href="{{ route('register') }}" class="text-indigo-600 underline hover:text-indigo-500">{{ config('app.name') }}</a> does. It sends your review link to every customer automatically after each job or appointment, follows up with those who don't respond, and routes unhappy customers to a private feedback form instead of a public review.
                    </div>
                </div>
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

    <script>
    function reviewLinkGenerator() {
        return {
            placeId: '',
            generated: false,
            reviewLink: '',
            error: '',
            copied: { link: false },
            email: '',
            emailSent: false,
            emailSending: false,

            extractPlaceId(input) {
                input = input.trim();

                // Already a Place ID (starts with ChIJ)
                if (/^ChIJ[A-Za-z0-9_-]+$/.test(input)) return input;

                // Google Maps URL — try to extract Place ID
                try {
                    const url = new URL(input);
                    if (url.hostname.includes('google.com') || url.hostname.includes('goo.gl') || url.hostname.includes('g.page')) {
                        const placeParam = url.searchParams.get('place_id') || url.searchParams.get('placeid');
                        if (placeParam && placeParam.startsWith('ChIJ')) return placeParam;

                        const dataMatch = input.match(/!1s(ChIJ[A-Za-z0-9_-]+)/);
                        if (dataMatch) return dataMatch[1];

                        const ftid = url.searchParams.get('ftid');
                        if (ftid && ftid.startsWith('ChIJ')) return ftid;
                    }
                } catch (e) {
                    // Not a URL, continue
                }

                return null;
            },

            generate() {
                this.error = '';
                const input = this.placeId.trim();
                if (!input) return;

                const id = this.extractPlaceId(input);
                if (!id) {
                    this.error = 'Could not find a Place ID. Please enter a valid Google Place ID (starts with ChIJ...) or paste a Google Maps URL for your business.';
                    this.generated = false;
                    return;
                }

                this.reviewLink = 'https://search.google.com/local/writereview?placeid=' + encodeURIComponent(id);
                this.generated = true;
                this.error = '';
                this.copied = { link: false };
                this.emailSent = false;
                this.emailSending = false;
            },

            copyToClipboard(text, key) {
                const onSuccess = () => {
                    this.copied[key] = true;
                    setTimeout(() => { this.copied[key] = false; }, 2000);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(onSuccess).catch(() => {
                        this.fallbackCopy(text);
                        onSuccess();
                    });
                } else {
                    this.fallbackCopy(text);
                    onSuccess();
                }
            },

            async submitEmail() {
                if (!this.email.trim() || this.emailSending) return;
                this.emailSending = true;
                try {
                    const url = new URL(this.reviewLink);
                    const placeId = url.searchParams.get('placeid');
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch('{{ route("tools.capture-email") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({ email: this.email, place_id: placeId }),
                    });
                    if (res.ok) {
                        this.emailSent = true;
                    } else {
                        this.error = 'Something went wrong. Please try again.';
                    }
                } catch (e) {
                    this.error = 'Something went wrong. Please try again.';
                } finally {
                    this.emailSending = false;
                }
            },

            fallbackCopy(text) {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            },
        };
    }
    </script>

</body>
</html>
