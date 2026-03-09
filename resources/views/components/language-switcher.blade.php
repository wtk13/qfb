@props(['mobile' => false])

<div class="flex items-center {{ $mobile ? 'space-x-2 px-4 pt-2' : 'space-x-1 text-sm' }}">
    @foreach(config('locales.labels') as $code => $label)
        @if(app()->getLocale() === $code)
            <span class="{{ $mobile ? 'px-3 py-1 text-sm font-medium text-indigo-600 bg-indigo-50 rounded' : 'px-2 py-1 font-medium text-indigo-600' }}">{{ $label }}</span>
        @else
            <form method="POST" action="{{ route('locale.switch', $code) }}" class="inline">
                @csrf
                <button type="submit" class="{{ $mobile ? 'px-3 py-1 text-sm text-gray-400 hover:text-gray-700' : 'px-2 py-1 text-gray-400 hover:text-gray-700' }}">{{ $label }}</button>
            </form>
        @endif
    @endforeach
</div>
