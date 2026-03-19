<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $title }} — {{ config('app.name') }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }} — {{ config('app.name') }}">
    <meta name="twitter:description" content="{{ $description }}">

    @if(isset($jsonLd))
        <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|lora:400,400i,700" rel="stylesheet" />

    @vite(['resources/css/app.css'])

    <style>
        .blog-content {
            font-family: 'Lora', Georgia, serif;
        }
        .blog-content h1,
        .blog-content h2,
        .blog-content h3,
        .blog-content h4 {
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* Reading progress bar */
        .reading-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #4f46e5, #818cf8);
            z-index: 50;
            transition: width 50ms linear;
        }
    </style>
</head>
<body class="antialiased bg-white text-gray-900" style="font-family: 'Inter', system-ui, sans-serif;">

    <div class="reading-progress" id="reading-progress"></div>

    <nav class="border-b border-gray-100 sticky top-0 bg-white/95 backdrop-blur-sm z-40">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <a href="/" class="text-xl font-bold text-indigo-600">{{ config('app.name') }}</a>
            <div class="flex items-center gap-6 text-sm">
                <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-gray-900">Blog</a>
                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-500 transition">Start Free Trial</a>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 blog-content">
        {{ $slot }}
    </main>

    <footer class="py-8 border-t border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center gap-6 text-sm text-gray-400">
            <a href="{{ route('blog.index') }}" class="hover:text-gray-600">Blog</a>
            <a href="{{ route('privacy') }}" class="hover:text-gray-600">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-gray-600">Terms of Service</a>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function () {
            var el = document.getElementById('reading-progress');
            var scrollTop = window.scrollY;
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            el.style.width = docHeight > 0 ? (scrollTop / docHeight * 100) + '%' : '0%';
        });
    </script>

</body>
</html>
