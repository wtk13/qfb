@props(['items'])

<div class="flex items-center text-sm text-gray-500 mb-1">
    @foreach($items as $item)
        @if(!$loop->first)
            <span class="mx-2">/</span>
        @endif

        @if(isset($item['url']))
            <a href="{{ $item['url'] }}" class="hover:text-indigo-600">{{ $item['label'] }}</a>
        @else
            <span class="text-gray-700">{{ $item['label'] }}</span>
        @endif
    @endforeach
</div>
