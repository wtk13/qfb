<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:title" content="{{ $title }} — {{ config('app.name') }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title }} — {{ config('app.name') }}">
    <meta name="twitter:description" content="{{ $description }}">
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-white text-gray-900">

    <nav class="border-b border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <a href="/" class="text-xl font-bold text-indigo-600">{{ config('app.name') }}</a>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {{ $slot }}
    </main>

    <footer class="py-8 border-t border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center gap-6 text-sm text-gray-400">
            <a href="{{ route('privacy') }}" class="hover:text-gray-600">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-gray-600">Terms of Service</a>
        </div>
    </footer>

</body>
</html>
