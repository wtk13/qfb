<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Draft #{{ $draft->id }}</h2>
            <a href="{{ route('admin.reddit.drafts.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; All Drafts</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Thread Context --}}
            @if($draft->thread)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Thread Context</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="font-medium text-gray-600">Title:</span>
                            <a href="{{ $draft->thread->url }}" target="_blank" class="text-indigo-600 hover:underline ml-1">{{ $draft->thread->title }}</a>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Subreddit:</span>
                            <span class="ml-1">r/{{ $draft->subreddit?->name }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Author:</span>
                            <span class="ml-1">u/{{ $draft->thread->author }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Score:</span>
                            <span class="ml-1">{{ $draft->thread->score ?? 'N/A' }}</span>
                        </div>
                        @if($draft->thread->body)
                            <div class="mt-3 p-3 bg-gray-50 rounded-lg text-gray-700 whitespace-pre-wrap">{{ $draft->thread->body }}</div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Draft Metadata --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex flex-wrap gap-4 items-center">
                    @php
                        $statusColor = match($draft->status) {
                            'pending' => 'bg-amber-100 text-amber-800',
                            'approved' => 'bg-blue-100 text-blue-800',
                            'published' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-gray-100 text-gray-800',
                            'failed' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                        $catColor = match($draft->content_category) {
                            'value' => 'bg-indigo-100 text-indigo-800',
                            'discussion' => 'bg-cyan-100 text-cyan-800',
                            'brand' => 'bg-orange-100 text-orange-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                    @endphp
                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $statusColor }}">{{ ucfirst($draft->status) }}</span>
                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $catColor }}">{{ ucfirst($draft->content_category) }}</span>
                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-700">{{ ucfirst($draft->type) }}</span>
                </div>

                {{-- Timeline --}}
                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-500">
                    <span>Created: {{ $draft->created_at->format('M d, Y H:i') }}</span>
                    @if($draft->approved_at)
                        <span>Approved: {{ $draft->approved_at->format('M d, Y H:i') }}</span>
                    @endif
                    @if($draft->rejected_at)
                        <span>Rejected: {{ $draft->rejected_at->format('M d, Y H:i') }}</span>
                    @endif
                    @if($draft->published_at)
                        <span>Published: {{ $draft->published_at->format('M d, Y H:i') }}</span>
                    @endif
                </div>

                @if($draft->rejection_reason)
                    <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                        <span class="font-medium">Rejection reason:</span> {{ $draft->rejection_reason }}
                    </div>
                @endif
            </div>

            {{-- Subreddit Rules --}}
            @if($draft->subreddit?->rules_json)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Subreddit Rules</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        @foreach($draft->subreddit->rules_json as $rule)
                            <li>{{ $rule }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Draft Body (Editable) --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Draft Body</h3>
                <form method="POST" action="{{ route('admin.reddit.drafts.update', $draft->id) }}">
                    @csrf
                    @method('PATCH')
                    <textarea name="body" rows="10" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('body', $draft->body) }}</textarea>
                    @error('body')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-3">
                @if($draft->status === 'pending')
                    <form method="POST" action="{{ route('admin.reddit.drafts.approve', $draft->id) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                            Approve
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.reddit.drafts.reject', $draft->id) }}" x-data="{ showReason: false }">
                        @csrf
                        <div class="flex items-center gap-2">
                            <button type="submit" @click.prevent="showReason = !showReason" x-show="!showReason" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                                Reject
                            </button>
                            <div x-show="showReason" class="flex items-center gap-2">
                                <input type="text" name="reason" placeholder="Reason (optional)" class="rounded-lg border-gray-300 text-sm" />
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                                    Confirm Reject
                                </button>
                            </div>
                        </div>
                    </form>
                @elseif($draft->status === 'failed')
                    <form method="POST" action="{{ route('admin.reddit.drafts.retry', $draft->id) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            Retry Publishing
                        </button>
                    </form>
                @endif

                @if($draft->reddit_thing_id)
                    <a href="https://reddit.com/{{ $draft->reddit_thing_id }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        View on Reddit
                    </a>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
