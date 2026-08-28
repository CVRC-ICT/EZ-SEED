<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Farmer Profiles</h2>
        <p class="text-xs text-gray-500 mt-0.5">Search, view, and export farmer records</p>
    </x-slot>

    <div class="flex">
        @include('dashboard._sidebar')

        <div class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto space-y-6">

                <div class="flex justify-end">
                    <a href="{{ route('dashboard.reports', ['type' => 'variety-by-province']) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-700 text-sm font-medium rounded-full hover:bg-green-50 transition">
                        ⬇ Export All
                    </a>
                </div>

                {{-- RSBSA TABS --}}
                <div class="flex gap-2 border-b border-gray-200">
                    @php
                        $tabs = [
                            '' => 'All Farmers',
                            'registered' => 'RSBSA Registered',
                            'not_registered' => 'Not Registered',
                        ];
                    @endphp
                    @foreach ($tabs as $value => $label)
                        <a href="{{ route('dashboard.farmers', array_merge(request()->except('page'), ['rsbsa' => $value])) }}"
                           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition
                                  {{ $selectedRsbsa === $value ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                            {{ $label }}
                            <span class="ml-1 text-xs text-gray-400">({{ $rsbsaCounts[$value === '' ? 'all' : $value] ?? 0 }})</span>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('dashboard.farmers') }}" class="flex flex-col sm:flex-row gap-3">
                    <input type="hidden" name="rsbsa" value="{{ $selectedRsbsa }}">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Search by name, RSBSA number..."
                           class="flex-1 rounded-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-green-600 focus:border-green-600">

                    <select name="province" class="rounded-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-green-600 focus:border-green-600">
                        <option value="">All Provinces</option>
                        @foreach ($provinces as $p)
                            <option value="{{ $p->id }}" @selected($selectedProvince == $p->id)>
                                {{ str_replace(['ñ','Ñ'],['n','N'], \App\Support\PlaceName::clean($p->name)) }}
                            </option>
                        @endforeach
                    </select>

                    <label class="inline-flex items-center gap-2 px-4 rounded-full border border-gray-300 dark:border-gray-600 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                        <input type="checkbox" name="portal_only" value="1" @checked($portalOnly) class="rounded text-green-600 focus:ring-green-600">
                        Portal submissions only
                    </label>

                    <button type="submit" class="px-5 py-2 bg-green-700 text-white text-sm font-medium rounded-full hover:bg-green-800 transition">
                        Search
                    </button>
                </form>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $farmers->total() }} Farmer Records</p>
                    </div>

                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Farmer</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Farm Size</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">RSBSA</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Responses</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date Filled</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Source</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($farmers as $farmer)
                                @php
                                    $fullName = trim("{$farmer->first_name} {$farmer->last_name}");
                                    // Most recent survey submission date for this farmer.
                                    // Requires $farmer->surveys to be eager-loaded with the
                                    // submitted_at column selected (see controller note).
                                    $lastSubmitted = $farmer->surveys->max('submitted_at');
                                @endphp
                                <tr>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-green-100 text-green-800 text-xs font-semibold flex items-center justify-center">
                                                {{ strtoupper(substr($farmer->first_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $fullName }}</p>
                                                <p class="text-xs text-gray-400">{{ $farmer->rsbsa_number ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $farmer->municipality_name ?? '—' }}<br>
                                        <span class="text-xs text-gray-400">{{ $farmer->province_name ?? '' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $farmer->farm_area ? $farmer->farm_area . ' ha' : '—' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        @if (!empty($farmer->rsbsa_number))
                                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700">Registered</span>
                                        @else
                                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">Not Registered</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-800 text-xs font-semibold">
                                            {{ $farmer->surveys_count }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $lastSubmitted ? \Carbon\Carbon::parse($lastSubmitted)->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        @php $assisted = $farmer->surveys->contains('assisted_by_da_personnel', true); @endphp
                                        @if ($assisted)
                                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">DA Staff</span>
                                        @else
                                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-sky-100 text-sky-700">From Portal</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('farmers.show', $farmer) }}" class="text-green-700 hover:text-green-900 mr-3" title="View">👁️</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-6 text-center text-sm text-gray-500">No farmers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-5 py-4">
                        {{ $farmers->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>