<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reddit Drafts</h2>
            <a href="{{ route('admin.reddit.dashboard') }}" class="text-sm text-indigo-600 hover:underline">&larr; Dashboard</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filters --}}
            <div class="bg-white shadow-sm rounded-lg p-4">
                <form method="GET" action="{{ route('admin.reddit.drafts.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select name="status" class="rounded-lg border-gray-300 text-sm">
                            <option value="">All</option>
                            @foreach(['pending', 'approved', 'published', 'rejected', 'failed'] as $s)
                                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Subreddit</label>
                        <select name="subreddit" class="rounded-lg border-gray-300 text-sm">
                            <option value="">All</option>
                            @foreach(\App\Infrastructure\Persistence\Eloquent\RedditSubredditModel::orderBy('name')->pluck('name', 'id') as $id => $name)
                                <option value="{{ $id }}" @selected(request('subreddit') == $id)>r/{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                        <select name="content_category" class="rounded-lg border-gray-300 text-sm">
                            <option value="">All</option>
                            @foreach(['value', 'discussion', 'brand'] as $c)
                                <option value="{{ $c }}" @selected(request('content_category') === $c)>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                        Filter
                    </button>
                    @if(request()->hasAny(['status', 'subreddit', 'content_category']))
                        <a href="{{ route('admin.reddit.drafts.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Drafts Table --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subreddit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thread</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preview</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($drafts as $draft)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColor = match($draft->status) {
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            'approved' => 'bg-blue-100 text-blue-800',
                                            'published' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-gray-100 text-gray-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">{{ $draft->status }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    r/{{ $draft->subreddit?->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">
                                    <a href="{{ $draft->thread?->url }}" target="_blank" class="hover:underline">{{ Str::limit($draft->thread?->title, 50) }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $catColor = match($draft->content_category) {
                                            'value' => 'bg-indigo-100 text-indigo-800',
                                            'discussion' => 'bg-cyan-100 text-cyan-800',
                                            'brand' => 'bg-orange-100 text-orange-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $catColor }}">{{ $draft->content_category }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ Str::limit($draft->body, 100) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $draft->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <a href="{{ route('admin.reddit.drafts.show', $draft->id) }}" class="text-indigo-600 hover:underline">View</a>
                                    @if($draft->status === 'pending')
                                        <form method="POST" action="{{ route('admin.reddit.drafts.approve', $draft->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:underline">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.reddit.drafts.reject', $draft->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:underline">Reject</button>
                                        </form>
                                    @elseif($draft->status === 'failed')
                                        <form method="POST" action="{{ route('admin.reddit.drafts.retry', $draft->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:underline">Retry</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No drafts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($drafts->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $drafts->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
