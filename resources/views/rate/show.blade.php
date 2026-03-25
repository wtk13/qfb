<!DOCTYPE html>
<html lang="{{ $profile->locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('rating.page_title', ['business' => $profile->name], $profile->locale) }}</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            @if($profile->logoPath)
                <img src="{{ asset('storage/' . $profile->logoPath) }}" alt="{{ $profile->name }}" class="h-16 mx-auto mb-4">
            @endif

            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $profile->name }}</h1>
            <p class="text-gray-600 mb-8">{{ __('rating.how_was_experience', [], $profile->locale) }}</p>

            <form method="POST" action="{{ route('rate.store', ['slug' => $profile->slug, 'token' => $token]) }}" id="rating-form">
                @csrf

                <div class="flex justify-center space-x-2 mb-6">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button"
                            onclick="submitRating({{ $i }})"
                            class="star-btn w-14 h-14 text-4xl cursor-pointer transition-transform hover:scale-110 focus:outline-none"
                            aria-label="{{ __('rating.star_label', ['count' => $i], $profile->locale) }}">
                            <span class="text-gray-300 hover:text-yellow-400">&#9733;</span>
                        </button>
                    @endfor
                </div>

                <input type="hidden" name="score" id="score-input">

                <div class="flex justify-between text-xs text-gray-400 px-2">
                    <span>{{ __('rating.poor', [], $profile->locale) }}</span>
                    <span>{{ __('rating.excellent', [], $profile->locale) }}</span>
                </div>
            </form>
        </div>

        <p class="text-xs text-gray-400 text-center mt-6">
            Review management by <a href="{{ url('/') }}" target="_blank" rel="noopener" class="text-gray-500 hover:text-indigo-600">QuickFeedback</a>
        </p>
    </div>

    <script>
        function submitRating(score) {
            document.getElementById('score-input').value = score;
            document.getElementById('rating-form').submit();
        }
    </script>
</body>
</html>
