<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Subreddits</h2>
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

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subreddit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Posts/Week</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Comments/Week</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Published This Week</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($subreddits as $sub)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="https://reddit.com/r/{{ $sub->name }}" target="_blank" class="text-indigo-600 hover:underline font-medium">r/{{ $sub->name }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $tierColor = match($sub->tier) {
                                            1 => 'bg-green-100 text-green-800',
                                            2 => 'bg-blue-100 text-blue-800',
                                            3 => 'bg-amber-100 text-amber-800',
                                            4 => 'bg-purple-100 text-purple-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $tierColor }}">Tier {{ $sub->tier }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input form="sub-{{ $sub->id }}" type="number" name="max_posts_per_week" value="{{ $sub->max_posts_per_week }}" min="0" max="20" class="w-20 rounded-lg border-gray-300 text-sm" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input form="sub-{{ $sub->id }}" type="number" name="max_comments_per_week" value="{{ $sub->max_comments_per_week }}" min="0" max="50" class="w-20 rounded-lg border-gray-300 text-sm" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $sub->published_this_week }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input form="sub-{{ $sub->id }}" type="hidden" name="is_active" value="0" />
                                    <input form="sub-{{ $sub->id }}" type="checkbox" name="is_active" value="1" @checked($sub->is_active) class="rounded border-gray-300 text-indigo-600" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form id="sub-{{ $sub->id }}" method="POST" action="{{ route('admin.reddit.subreddits.update', $sub->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-sm text-indigo-600 hover:underline">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No subreddits configured. Run <code>db:seed --class=RedditSubredditSeeder</code> to load defaults.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
