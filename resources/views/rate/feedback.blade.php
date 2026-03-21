<!DOCTYPE html>
<html lang="{{ $profile->locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('feedback.page_title', [], $profile->locale) }}</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            @if($profile->logoPath)
                <img src="{{ asset('storage/' . $profile->logoPath) }}" alt="{{ $profile->name }}" class="h-16 mx-auto mb-4">
            @endif

            <h1 class="text-xl font-bold text-gray-900 mb-2 text-center">{{ __('feedback.sorry_header', [], $profile->locale) }}</h1>
            <p class="text-gray-600 mb-6 text-center">{{ __('feedback.tell_us_more', [], $profile->locale) }}</p>

            <form method="POST" action="{{ route('rate.feedback.store', ['slug' => $profile->slug, 'token' => $token]) }}">
                @csrf
                <input type="hidden" name="rating_id" value="{{ $ratingId }}">

                <div class="mb-4">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">{{ __('feedback.comment', [], $profile->locale) }}</label>
                    <textarea id="comment" name="comment" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>{{ old('comment') }}</textarea>
                    @error('comment')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('feedback.contact_email', [], $profile->locale) }}</label>
                    <input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('contact_email') }}">
                    <p class="text-xs text-gray-400 mt-1">{{ __('feedback.email_optional', [], $profile->locale) }}</p>
                </div>

                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-500">
                    {{ __('feedback.submit', [], $profile->locale) }}
                </button>
            </form>
        </div>
    </div>
</body>
</html>
