<!DOCTYPE html>
<html lang="{{ $profile->locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('rating.thank_you_title', [], $profile->locale) }}</title>
    @if($googleRedirect)
        <meta http-equiv="refresh" content="3;url={{ $googleRedirect }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            @if($profile->logoPath)
                <img src="{{ asset('storage/' . $profile->logoPath) }}" alt="{{ $profile->name }}" class="h-16 mx-auto mb-4">
            @endif

            <div class="text-5xl mb-4">&#10004;</div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('rating.thank_you', [], $profile->locale) }}</h1>
            <p class="text-gray-600">{{ __('rating.thank_you_message', ['business' => $profile->name], $profile->locale) }}</p>

            @if($googleRedirect)
                <p class="text-sm text-gray-400 mt-4">{{ __('rating.redirecting_google', [], $profile->locale) }}</p>
                <a href="{{ $googleRedirect }}" class="inline-block mt-2 text-indigo-600 hover:text-indigo-900">
                    {{ __('rating.leave_google_review', [], $profile->locale) }}
                </a>
            @endif
        </div>
    </div>
</body>
</html>
