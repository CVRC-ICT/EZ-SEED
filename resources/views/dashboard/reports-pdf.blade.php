<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Reports</h2>
        <p class="text-xs text-gray-500 mt-0.5">Exportable analytics built from farmer survey submissions · Region 02</p>
    </x-slot>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @include('dashboard._chart-colors')

    <div class="flex">
        @include('dashboard._sidebar')

        <div class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto space-y-6">

                {{-- FILTERS --}}
                <form method="GET" action="{{ route('dashboard.reports') }}"
                      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <input type="hidden" name="type" value="{{ $activeType }}">
                    <p class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <span>Filters &amp; Date Range</span>
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Province</label>
                            <select name="province" class="w-full rounded-lg border-gray-200 text-sm">
                                <option value="">All Provinces</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province->id }}" @selected($filters['province'] == $province->id)>
                                        {{ $province->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Season</label>
                            <select name="season" class="w-full rounded-lg border-gray-200 text-sm">
                                <option value="">All Seasons</option>
                                <option value="wet" @selected($filters['season'] === 'wet')>Wet</option>
                                <option value="dry" @selected($filters['season'] === 'dry')>Dry</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">From</label>
                            <input type="date" name="from" value="{{ $filters['from'] }}"
                                   class="w-full rounded-lg border-gray-200 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">To</label>
                            <input type="date" name="to" value="{{ $filters['to'] }}"
                                   class="w-full rounded-lg border-gray-200 text-sm">
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-3">
                        <button type="submit" class="px-4 py-2 text-sm font-medium rounded-full bg-green-700 text-white hover:bg-green-800">
                            Apply Filters
                        </button>
                        <a href="{{ route('dashboard.reports', ['type' => $activeType]) }}" class="text-sm text-gray-500 hover:text-gray-700">
                            Reset
                        </a>
                        <span class="ml-auto text-xs text-gray-400">{{ $responseCount }} response(s) matched</span>
                    </div>
                </form>

                <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-6">

                    {{-- REPORT TYPE LIST --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-1 h-fit">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-2 mb-2">Report Type</p>
                        @foreach ($types as $key => $label)
                            <a href="{{ route('dashboard.reports', array_merge($filters, ['type' => $key])) }}"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm
                                      {{ $activeType === $key ? 'bg-green-50 text-green-800 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $activeType === $key ? 'bg-green-600' : 'bg-gray-300' }}"></span>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    {{-- ACTIVE REPORT --}}
                    <div class="space-y-6">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                <div>
                                    <h3 class="font-semibold text-gray-800">{{ $activeLabel }}</h3>
                                    <p class="text-xs text-gray-400">Region 02 · Generated just now</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('dashboard.reports.export', array_merge($filters, ['format' => 'csv', 'type' => $activeType])) }}"
                                       class="px-4 py-2 text-sm font-medium rounded-full bg-green-700 text-white hover:bg-green-800">CSV</a>
                                    <a href="{{ route('dashboard.reports.export', array_merge($filters, ['format' => 'excel', 'type' => $activeType])) }}"
                                       class="px-4 py-2 text-sm font-medium rounded-full border border-amber-400 text-amber-700 hover:bg-amber-50">Excel</a>
                                    <a href="{{ route('dashboard.reports.export', array_merge($filters, ['format' => 'pdf', 'type' => $activeType])) }}"
                                       class="px-4 py-2 text-sm font-medium rounded-full border border-gray-300 text-gray-700 hover:bg-gray-50">PDF</a>
                                </div>
                            </div>

                            @if (count($chart['labels']) > 0)
                                <div class="mb-6" style="max-height: 320px;">
                                    <canvas id="reportChart"></canvas>
                                </div>
                            @endif

                            @if ($rows->isNotEmpty())
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-xs text-gray-400 uppercase border-b border-gray-100">
                                            @foreach (array_keys($rows->first()) as $col)
                                                <th class="py-2 pr-4">{{ $col }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @foreach ($rows as $row)
                                            <tr>
                                                @foreach ($row as $val)
                                                    <td class="py-2 pr-4 text-gray-700">{{ $val }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-sm text-gray-500">No data available for this report yet — try widening your filters.</p>
                            @endif
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-800">Download History</h3>
                                <span class="text-xs text-gray-400">{{ $downloadHistory->count() }} files</span>
                            </div>
                            @forelse ($downloadHistory as $log)
                                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                                    <div>
                                        <p class="text-sm text-gray-700">{{ $log->file_name }}</p>
                                        <p class="text-xs text-gray-400">{{ $log->created_at->format('M j, Y') }} · {{ $log->size_kb }} KB</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">{{ $log->format }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No exports yet — download a report above to see it here.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @if (count($chart['labels']) > 0)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('reportChart');
                if (!ctx) return;

                const labels = @json($chart['labels']);
                const data = @json($chart['data']);
                const chartType = @json($chart['type']) === 'donut' ? 'doughnut' : 'bar';
                const activeType = @json($activeType);

                // Vary color family by report type so different donut/bar
                // charts don't visually blend into each other.
                const colorFn = ['reasons-for-selection', 'source-of-income'].includes(activeType)
                    ? daBlueShades
                    : (activeType === 'problems-encountered' ? daProblemShades : daGreenShades);

                new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: @json($activeLabel),
                            data: data,
                            backgroundColor: colorFn(labels.length),
                            borderRadius: chartType === 'bar' ? 6 : 0,
                            borderWidth: chartType === 'doughnut' ? 2 : 0,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: chartType === 'doughnut',
                                position: 'bottom',
                            }
                        },
                        scales: chartType === 'bar' ? {
                            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: DA_COLORS.grid } },
                            x: { grid: { display: false } }
                        } : {}
                    }
                });
            });
        </script>
    @endif
</x-app-layout>