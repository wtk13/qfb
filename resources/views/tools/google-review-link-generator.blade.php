<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Free Google Review Link Generator — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="description" content="Generate your direct Google review link instantly. Free tool — get your link, QR code, and ready-to-use email and SMS templates in seconds.">
    <link rel="canonical" href="{{ route('tools.google-review-link-generator') }}">
    <meta name="theme-color" content="#4F46E5">
    <meta property="og:title" content="Free Google Review Link Generator — {{ config('app.name') }}">
    <meta property="og:description" content="Generate your direct Google review link instantly. Free tool — get your link, QR code, and ready-to-use templates in seconds.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('tools.google-review-link-generator') }}">
    <meta property="og:image" content="{{ asset('images/hero-bg.jpg') }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Free Google Review Link Generator — {{ config('app.name') }}">
    <meta name="twitter:description" content="Generate your direct Google review link instantly. Free tool — get your link, QR code, and ready-to-use templates in seconds.">
    <meta name="twitter:image" content="{{ asset('images/hero-bg.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => 'Google Review Link Generator',
        'url' => route('tools.google-review-link-generator'),
        'description' => 'Generate your direct Google review link, QR code, and ready-to-use templates instantly.',
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
            ['@type' => 'Question', 'name' => 'How do I use my Google review link?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Share it via email, SMS, your website, social media, or print it as a QR code on business cards and receipts. When someone clicks the link, they land directly on your Google review form.']],
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

    <!-- Hero -->
    <section class="pt-28 pb-12 sm:pt-32 sm:pb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">Google Review Link Generator</h1>
            <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Enter your Google Place ID and get a direct review link, QR code, and ready-to-use templates — free, instantly, no signup required.</p>
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
                <p class="mt-3 text-sm text-gray-400">
                    Don't know your Place ID?
                    <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-500 underline">Find it here</a>
                    — search your business name, copy the Place ID.
                </p>
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
                            class="shrink-0 px-3 py-1.5 text-sm font-medium rounded-lg transition"
                            :class="copied.link ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'"
                        >
                            <span x-text="copied.link ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-bold mb-3">QR Code</h2>
                    <p class="text-sm text-gray-500 mb-4">Print this on business cards, receipts, or in-store signage.</p>
                    <div class="flex flex-col items-center gap-4">
                        <div id="qr-code" class="bg-white p-4 rounded-xl border border-gray-100"></div>
                        <button
                            @click="downloadQr()"
                            class="px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                        >
                            Download QR Code
                        </button>
                    </div>
                </div>

                <!-- Email Template -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-bold mb-3">Email Template</h2>
                    <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-700 whitespace-pre-line" x-text="emailTemplate"></div>
                    <button
                        @click="copyToClipboard(emailTemplate, 'email')"
                        class="mt-3 px-4 py-2 text-sm font-medium rounded-lg transition"
                        :class="copied.email ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'"
                    >
                        <span x-text="copied.email ? 'Copied!' : 'Copy Template'"></span>
                    </button>
                </div>

                <!-- SMS Template -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-bold mb-3">SMS Template</h2>
                    <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-700" x-text="smsTemplate"></div>
                    <button
                        @click="copyToClipboard(smsTemplate, 'sms')"
                        class="mt-3 px-4 py-2 text-sm font-medium rounded-lg transition"
                        :class="copied.sms ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'"
                    >
                        <span x-text="copied.sms ? 'Copied!' : 'Copy Template'"></span>
                    </button>
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
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">What is a Google Place ID?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 1" x-cloak class="px-6 pb-4 text-gray-600">
                        A Google Place ID is a unique identifier for any place in Google's database. It's a string of letters and numbers (e.g., <code class="text-sm bg-gray-100 px-1 rounded">ChIJN1t_tDeuEmsRUsoyG83frY4</code>). You can find yours using <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener" class="text-indigo-600 underline hover:text-indigo-500">Google's Place ID Finder</a> — just search your business name and copy the ID.
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl bg-white">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">Is this tool really free?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 2" x-cloak class="px-6 pb-4 text-gray-600">
                        Yes, completely free. No signup, no email required, no limits. Generate as many review links and QR codes as you need.
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl bg-white">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">How do I use my Google review link?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 3" x-cloak class="px-6 pb-4 text-gray-600">
                        Share it everywhere: in emails, text messages, on your website, in social media bios, and as a QR code on printed materials. Check out our <a href="{{ route('blog.show', 'google-review-link') }}" class="text-indigo-600 underline hover:text-indigo-500">complete guide to using your Google review link</a> for detailed strategies.
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl bg-white">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium">Can I automate sending this link to customers?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 4" x-cloak class="px-6 pb-4 text-gray-600">
                        Yes — that's exactly what <a href="{{ route('register') }}" class="text-indigo-600 underline hover:text-indigo-500">{{ config('app.name') }}</a> does. It sends your review link to every customer automatically after each job or appointment, follows up with those who don't respond, and routes unhappy customers to a private feedback form instead of a public review.
                    </div>
                </div>
            </div>
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

    <script>
    /**
     * Minimal QR Code generator (numeric mode, Version 2, ECC L)
     * Generates an SVG QR code entirely client-side.
     */
    function generateQRCode(text, size) {
        // Use a canvas-based approach via the QR matrix
        const modules = qrEncode(text);
        const n = modules.length;
        const cellSize = Math.floor(size / (n + 8));
        const padding = cellSize * 4;
        const totalSize = cellSize * n + padding * 2;

        let svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${totalSize} ${totalSize}" width="${size}" height="${size}">`;
        svg += `<rect width="${totalSize}" height="${totalSize}" fill="white"/>`;

        for (let y = 0; y < n; y++) {
            for (let x = 0; x < n; x++) {
                if (modules[y][x]) {
                    svg += `<rect x="${padding + x * cellSize}" y="${padding + y * cellSize}" width="${cellSize}" height="${cellSize}" fill="black"/>`;
                }
            }
        }
        svg += '</svg>';
        return svg;
    }

    /**
     * QR Code encoder - generates module matrix for given text
     * Supports byte mode, version auto-select (1-10), ECC level L
     */
    function qrEncode(text) {
        const data = new TextEncoder().encode(text);
        const version = getMinVersion(data.length);
        const size = version * 4 + 17;

        // Create module grid
        const modules = Array.from({ length: size }, () => Array(size).fill(null));
        const isFunction = Array.from({ length: size }, () => Array(size).fill(false));

        // Place finder patterns
        placeFinder(modules, isFunction, 0, 0);
        placeFinder(modules, isFunction, size - 7, 0);
        placeFinder(modules, isFunction, 0, size - 7);

        // Place alignment patterns
        const alignPos = getAlignmentPositions(version);
        for (const ay of alignPos) {
            for (const ax of alignPos) {
                if ((ax < 8 && ay < 8) || (ax < 8 && ay > size - 9) || (ax > size - 9 && ay < 8)) continue;
                placeAlignment(modules, isFunction, ax, ay);
            }
        }

        // Timing patterns
        for (let i = 8; i < size - 8; i++) {
            setModule(modules, isFunction, 6, i, i % 2 === 0);
            setModule(modules, isFunction, i, 6, i % 2 === 0);
        }

        // Dark module
        setModule(modules, isFunction, 8, (4 * version) + 9, true);

        // Reserve format info areas
        for (let i = 0; i < 8; i++) {
            setFunctionModule(modules, isFunction, 8, i === 6 ? 7 : i);
            setFunctionModule(modules, isFunction, i === 6 ? 7 : i, 8);
            setFunctionModule(modules, isFunction, 8, size - 1 - i);
            setFunctionModule(modules, isFunction, size - 1 - i, 8);
        }
        setFunctionModule(modules, isFunction, 8, 8);

        // Version info
        if (version >= 7) {
            const vInfo = getVersionInfo(version);
            for (let i = 0; i < 18; i++) {
                const bit = (vInfo >> i) & 1;
                const a = Math.floor(i / 3);
                const b = i % 3 + size - 11;
                setModule(modules, isFunction, b, a, bit === 1);
                setModule(modules, isFunction, a, b, bit === 1);
            }
        }

        // Encode data
        const codewords = encodeData(data, version);

        // Place data
        placeData(modules, isFunction, codewords, size);

        // Apply best mask
        const bestMask = applyBestMask(modules, isFunction, size);

        // Place format info
        placeFormatInfo(modules, isFunction, size, bestMask);

        return modules;
    }

    function getMinVersion(dataLen) {
        // Byte mode capacity for ECC level L
        const caps = [0, 17, 32, 53, 78, 106, 134, 154, 192, 230, 271];
        for (let v = 1; v <= 10; v++) {
            const overhead = v <= 9 ? 2 : 3; // mode + char count bytes
            if (dataLen + overhead <= caps[v]) return v;
        }
        return 10;
    }

    function getAlignmentPositions(version) {
        if (version <= 1) return [];
        const positions = [6];
        const size = version * 4 + 17;
        const last = size - 7;
        const count = Math.floor(version / 7) + 2;
        const step = count === 2 ? last - 6 : Math.ceil((last - 6) / (count - 1) / 2) * 2;
        for (let p = last; positions.length < count; p -= step) positions.splice(1, 0, p);
        return positions;
    }

    function setModule(modules, isFunction, row, col, val) {
        modules[row][col] = val;
        isFunction[row][col] = true;
    }

    function setFunctionModule(modules, isFunction, row, col) {
        isFunction[row][col] = true;
    }

    function placeFinder(modules, isFunction, row, col) {
        for (let dy = -1; dy <= 7; dy++) {
            for (let dx = -1; dx <= 7; dx++) {
                const r = row + dy, c = col + dx;
                if (r < 0 || r >= modules.length || c < 0 || c >= modules.length) continue;
                const val = (dy >= 0 && dy <= 6 && (dx === 0 || dx === 6)) ||
                           (dx >= 0 && dx <= 6 && (dy === 0 || dy === 6)) ||
                           (dy >= 2 && dy <= 4 && dx >= 2 && dx <= 4);
                setModule(modules, isFunction, r, c, val);
            }
        }
    }

    function placeAlignment(modules, isFunction, cx, cy) {
        for (let dy = -2; dy <= 2; dy++) {
            for (let dx = -2; dx <= 2; dx++) {
                const val = Math.max(Math.abs(dx), Math.abs(dy)) !== 1;
                setModule(modules, isFunction, cy + dy, cx + dx, val);
            }
        }
    }

    function encodeData(data, version) {
        const totalCodewords = getTotalCodewords(version);
        const ecCodewords = getEcCodewords(version);
        const dataCodewords = totalCodewords - ecCodewords;

        // Build bit stream: mode (0100=byte), char count, data, terminator, padding
        let bits = '';
        bits += '0100'; // byte mode
        bits += (version <= 9 ? toBin(data.length, 8) : toBin(data.length, 16));
        for (const b of data) bits += toBin(b, 8);
        bits += '0000'.slice(0, Math.min(4, dataCodewords * 8 - bits.length));
        while (bits.length % 8 !== 0) bits += '0';
        while (bits.length < dataCodewords * 8) {
            bits += '11101100';
            if (bits.length < dataCodewords * 8) bits += '00010001';
        }

        const dataBytes = [];
        for (let i = 0; i < bits.length; i += 8) dataBytes.push(parseInt(bits.slice(i, i + 8), 2));

        // RS error correction
        const ecBytes = reedSolomon(dataBytes.slice(0, dataCodewords), ecCodewords);
        return [...dataBytes.slice(0, dataCodewords), ...ecBytes];
    }

    function toBin(val, len) { return val.toString(2).padStart(len, '0'); }

    function getTotalCodewords(version) {
        const total = [0, 26, 44, 70, 100, 134, 172, 196, 242, 292, 346];
        return total[version];
    }

    function getEcCodewords(version) {
        // ECC level L
        const ec = [0, 7, 10, 15, 20, 26, 18, 20, 24, 30, 18];
        return ec[version];
    }

    function reedSolomon(data, ecLen) {
        const gf256Exp = new Uint8Array(512);
        const gf256Log = new Uint8Array(256);
        let val = 1;
        for (let i = 0; i < 255; i++) {
            gf256Exp[i] = val;
            gf256Log[val] = i;
            val = (val << 1) ^ (val >= 128 ? 0x11d : 0);
        }
        for (let i = 255; i < 512; i++) gf256Exp[i] = gf256Exp[i - 255];

        const gfMul = (a, b) => a === 0 || b === 0 ? 0 : gf256Exp[gf256Log[a] + gf256Log[b]];

        // Build generator polynomial
        let gen = [1];
        for (let i = 0; i < ecLen; i++) {
            const newGen = new Array(gen.length + 1).fill(0);
            for (let j = 0; j < gen.length; j++) {
                newGen[j] ^= gfMul(gen[j], gf256Exp[i]);
                newGen[j + 1] ^= gen[j];
            }
            gen = newGen;
        }

        const result = new Uint8Array(ecLen);
        const msg = new Uint8Array(data.length + ecLen);
        msg.set(data);

        for (let i = 0; i < data.length; i++) {
            const coef = msg[i];
            if (coef !== 0) {
                for (let j = 0; j < gen.length; j++) {
                    msg[i + j] ^= gfMul(gen[j], coef);
                }
            }
        }

        for (let i = 0; i < ecLen; i++) result[i] = msg[data.length + i];
        return result;
    }

    function placeData(modules, isFunction, codewords, size) {
        let bitIdx = 0;
        const totalBits = codewords.length * 8;

        for (let right = size - 1; right >= 1; right -= 2) {
            if (right === 6) right = 5;
            for (let vert = 0; vert < size; vert++) {
                for (let j = 0; j < 2; j++) {
                    const x = right - j;
                    const upward = ((right + 1) & 2) === 0;
                    const y = upward ? size - 1 - vert : vert;
                    if (!isFunction[y][x] && bitIdx < totalBits) {
                        modules[y][x] = ((codewords[bitIdx >> 3] >> (7 - (bitIdx & 7))) & 1) === 1;
                        bitIdx++;
                    }
                }
            }
        }
    }

    function applyBestMask(modules, isFunction, size) {
        let bestPenalty = Infinity;
        let bestMask = 0;
        const original = modules.map(r => [...r]);

        for (let mask = 0; mask < 8; mask++) {
            // Apply mask
            for (let y = 0; y < size; y++) {
                for (let x = 0; x < size; x++) {
                    if (!isFunction[y][x]) {
                        let invert = false;
                        switch (mask) {
                            case 0: invert = (y + x) % 2 === 0; break;
                            case 1: invert = y % 2 === 0; break;
                            case 2: invert = x % 3 === 0; break;
                            case 3: invert = (y + x) % 3 === 0; break;
                            case 4: invert = (Math.floor(y/2) + Math.floor(x/3)) % 2 === 0; break;
                            case 5: invert = (y*x)%2 + (y*x)%3 === 0; break;
                            case 6: invert = ((y*x)%2 + (y*x)%3) % 2 === 0; break;
                            case 7: invert = ((y+x)%2 + (y*x)%3) % 2 === 0; break;
                        }
                        if (invert) modules[y][x] = !modules[y][x];
                    }
                }
            }

            const penalty = calculatePenalty(modules, size);
            if (penalty < bestPenalty) {
                bestPenalty = penalty;
                bestMask = mask;
            }

            // Restore
            for (let y = 0; y < size; y++) {
                for (let x = 0; x < size; x++) {
                    modules[y][x] = original[y][x];
                }
            }
        }

        // Apply best mask
        for (let y = 0; y < size; y++) {
            for (let x = 0; x < size; x++) {
                if (!isFunction[y][x]) {
                    let invert = false;
                    switch (bestMask) {
                        case 0: invert = (y + x) % 2 === 0; break;
                        case 1: invert = y % 2 === 0; break;
                        case 2: invert = x % 3 === 0; break;
                        case 3: invert = (y + x) % 3 === 0; break;
                        case 4: invert = (Math.floor(y/2) + Math.floor(x/3)) % 2 === 0; break;
                        case 5: invert = (y*x)%2 + (y*x)%3 === 0; break;
                        case 6: invert = ((y*x)%2 + (y*x)%3) % 2 === 0; break;
                        case 7: invert = ((y+x)%2 + (y*x)%3) % 2 === 0; break;
                    }
                    if (invert) modules[y][x] = !modules[y][x];
                }
            }
        }

        return bestMask;
    }

    function calculatePenalty(modules, size) {
        let penalty = 0;
        // Rule 1: consecutive same-color modules in row/col
        for (let y = 0; y < size; y++) {
            let run = 1;
            for (let x = 1; x < size; x++) {
                if (modules[y][x] === modules[y][x-1]) { run++; }
                else { if (run >= 5) penalty += run - 2; run = 1; }
            }
            if (run >= 5) penalty += run - 2;
        }
        for (let x = 0; x < size; x++) {
            let run = 1;
            for (let y = 1; y < size; y++) {
                if (modules[y][x] === modules[y-1][x]) { run++; }
                else { if (run >= 5) penalty += run - 2; run = 1; }
            }
            if (run >= 5) penalty += run - 2;
        }
        return penalty;
    }

    function placeFormatInfo(modules, isFunction, size, mask) {
        const formatBits = getFormatBits(mask);
        for (let i = 0; i < 15; i++) {
            const bit = (formatBits >> (14 - i)) & 1 === 1;
            // Around top-left finder
            if (i < 6) modules[8][i] = bit;
            else if (i < 8) modules[8][i + 1] = bit;
            else modules[14 - i][8] = bit;

            // Around other finders
            if (i < 8) modules[size - 1 - i][8] = bit;
            else modules[8][size - 15 + i] = bit;
        }
    }

    function getFormatBits(mask) {
        // ECC level L = 01, mask pattern
        const data = (1 << 3) | mask; // 01xxx
        let bits = data;
        for (let i = 0; i < 10; i++) {
            if (bits & (1 << (i + 4))) bits ^= 0x537 << i;
        }
        // Actually, compute BCH properly
        let rem = data << 10;
        const gen = 0x537;
        for (let i = 4; i >= 0; i--) {
            if (rem & (1 << (i + 10))) rem ^= gen << i;
        }
        const result = ((data << 10) | rem) ^ 0x5412;
        return result;
    }

    function getVersionInfo(version) {
        let rem = version;
        for (let i = 0; i < 12; i++) {
            rem = (rem << 1) ^ ((rem >> 11) * 0x1F25);
        }
        return (version << 12) | rem;
    }

    function reviewLinkGenerator() {
        return {
            placeId: '',
            generated: false,
            reviewLink: '',
            copied: { link: false, email: false, sms: false },

            generate() {
                const id = this.placeId.trim();
                if (!id) return;
                this.reviewLink = 'https://search.google.com/local/writereview?placeid=' + encodeURIComponent(id);
                this.generated = true;
                this.copied = { link: false, email: false, sms: false };

                this.$nextTick(() => {
                    const container = document.getElementById('qr-code');
                    if (container) {
                        container.innerHTML = generateQRCode(this.reviewLink, 200);
                    }
                });
            },

            copyToClipboard(text, key) {
                navigator.clipboard.writeText(text).then(() => {
                    this.copied[key] = true;
                    setTimeout(() => { this.copied[key] = false; }, 2000);
                });
            },

            get emailTemplate() {
                return `Subject: How did we do?\n\nHi [First Name],\n\nThanks for choosing [Your Business Name]! We hope you had a great experience.\n\nIf you have 30 seconds, a quick Google review would mean the world to us. It helps other people in your area find us.\n\n${this.reviewLink}\n\nThank you!\n[Your Name]`;
            },

            get smsTemplate() {
                return `Hi [First Name]! Thanks for choosing [Your Business]. If you had a great experience, would you leave us a quick Google review? It takes 30 seconds: ${this.reviewLink}`;
            },

            downloadQr() {
                const container = document.getElementById('qr-code');
                const svg = container.querySelector('svg');
                if (!svg) return;
                const svgData = new XMLSerializer().serializeToString(svg);
                const blob = new Blob([svgData], { type: 'image/svg+xml' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'google-review-qr-code.svg';
                a.click();
                URL.revokeObjectURL(url);
            }
        };
    }
    </script>

</body>
</html>
