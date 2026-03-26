<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Outreach Leads</h2>
            <a href="{{ route('admin.outreach.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Back to Dashboard</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filters --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="GET" action="{{ route('admin.outreach.leads') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">All</option>
                            @foreach(['new', 'sent', 'replied', 'converted', 'bounced', 'unsubscribed'] as $status)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">All</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <select name="city" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">All</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Business name..." class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-500 transition">Filter</button>
                    </div>
                </form>
            </div>

            {{-- Results --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">{{ $leads->total() }} leads</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Business</th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Email</th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Category</th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">City</th>
                                <th class="text-right py-2 pr-4 font-medium text-gray-500">
                                    <a href="{{ route('admin.outreach.leads', array_merge(request()->query(), ['sort' => 'rating', 'direction' => request('sort') === 'rating' && request('direction') === 'desc' ? 'asc' : 'desc'])) }}" class="hover:text-gray-900">
                                        Rating {!! request('sort') === 'rating' ? (request('direction') === 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                                    </a>
                                </th>
                                <th class="text-right py-2 pr-4 font-medium text-gray-500">
                                    <a href="{{ route('admin.outreach.leads', array_merge(request()->query(), ['sort' => 'reviews', 'direction' => request('sort') === 'reviews' && request('direction') === 'desc' ? 'asc' : 'desc'])) }}" class="hover:text-gray-900">
                                        Reviews {!! request('sort') === 'reviews' ? (request('direction') === 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                                    </a>
                                </th>
                                <th class="text-right py-2 pr-4 font-medium text-gray-500">
                                    <a href="{{ route('admin.outreach.leads', array_merge(request()->query(), ['sort' => 'landing_clicks', 'direction' => request('sort') === 'landing_clicks' && request('direction') === 'desc' ? 'asc' : 'desc'])) }}" class="hover:text-gray-900">
                                        Clicks {!! request('sort') === 'landing_clicks' ? (request('direction') === 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                                    </a>
                                </th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Email Status</th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Outreach</th>
                                <th class="text-left py-2 pr-4 font-medium text-gray-500">Sent</th>
                                <th class="text-left py-2 font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $lead)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2 pr-4 max-w-[180px] truncate">
                                    @if($lead->google_maps_url)
                                        <a href="{{ $lead->google_maps_url }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-500">{{ $lead->business_name }}</a>
                                    @else
                                        {{ $lead->business_name }}
                                    @endif
                                </td>
                                <td class="py-2 pr-4 text-gray-600 max-w-[180px] truncate">{{ $lead->email ?? '-' }}</td>
                                <td class="py-2 pr-4 capitalize">{{ $lead->category }}</td>
                                <td class="py-2 pr-4">{{ $lead->city }}</td>
                                <td class="py-2 pr-4 text-right">{{ $lead->rating ?? '-' }}</td>
                                <td class="py-2 pr-4 text-right">{{ $lead->reviews ?? '-' }}</td>
                                <td class="py-2 pr-4 text-right">
                                    @if($lead->landing_clicks > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cyan-100 text-cyan-800">{{ $lead->landing_clicks }}</span>
                                    @else
                                        <span class="text-gray-300">0</span>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ match($lead->email_status) {
                                            'verified' => 'bg-green-100 text-green-800',
                                            'invalid' => 'bg-red-100 text-red-800',
                                            'catch_all' => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        } }}">
                                        {{ $lead->email_status }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ match($lead->outreach_status) {
                                            'sent' => 'bg-blue-100 text-blue-800',
                                            'replied' => 'bg-green-100 text-green-800',
                                            'converted' => 'bg-emerald-100 text-emerald-800',
                                            'bounced' => 'bg-orange-100 text-orange-800',
                                            'unsubscribed' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        } }}">
                                        {{ $lead->outreach_status }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4 text-gray-500">{{ $lead->sent_at?->diffForHumans() ?? '-' }}</td>
                                <td class="py-2">
                                    @if(in_array($lead->outreach_status, ['sent', 'replied']))
                                        <div class="flex items-center gap-1">
                                            @if($lead->outreach_status === 'sent')
                                            <form method="POST" action="{{ route('admin.outreach.update-status', $lead->id) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="replied">
                                                <button type="submit" class="text-xs text-green-600 hover:text-green-800 font-medium">Replied</button>
                                            </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.outreach.update-status', $lead->id) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="converted">
                                                <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">Converted</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="py-8 text-center text-gray-500">No leads found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $leads->withQueryString()->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
