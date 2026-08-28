<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Seed Preference Monitoring</h2>
        <p class="text-xs text-gray-500 mt-0.5">Detailed analytics on seed variety preferences</p>
    </x-slot>

    <div class="flex">
        @include('dashboard._sidebar')

        <div class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto space-y-6">

                <form method="GET" action="{{ route('dashboard.seed-monitoring') }}" class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Filter Options</p>
                    <div class="flex flex-wrap gap-3">
                        <select name="season" onchange="this.form.submit()"
                                class="rounded-full border-gray-300 text-sm focus:ring-amber-500 focus:border-amber-500">
                            <option value="">All Seasons</option>
                            <option value="Dry Season" @selected($season === 'Dry Season')>Dry Season</option>
                            <option value="Wet Season" @selected($season === 'Wet Season')>Wet Season</option>
                        </select>
                    </div>
                </form>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5">
                        <p class="text-sm text-gray-600">Total Hybrid Varieties</p>
                        <p class="text-3xl font-bold text-emerald-800 mt-2">{{ $totalHybrid }}</p>
                        <p class="text-xs text-gray-400 mt-1">Reported by farmers</p>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                        <p class="text-sm text-gray-600">Total Inbred Varieties</p>
                        <p class="text-3xl font-bold text-amber-800 mt-2">{{ $totalInbred }}</p>
                        <p class="text-xs text-gray-400 mt-1">Reported by farmers</p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5">
                        <p class="text-sm text-gray-600">Total Preferences Recorded</p>
                        <p class="text-3xl font-bold text-emerald-800 mt-2">{{ $topHybrid->sum('total') + $topInbred->sum('total') }}</p>
                        <p class="text-xs text-gray-400 mt-1">Across all varieties</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Top 5 Hybrid Varieties</h3>
                        @php $hybridColors = ['#0F6B32', '#16803C', '#3FAE68', '#4FAF70', '#6BBF87']; @endphp
                        @forelse ($topHybrid as $i => $v)
                            @php $pct = $topHybrid->sum('total') ? round($v->total / $topHybrid->sum('total') * 100, 1) : 0; @endphp
                            <div class="flex items-center justify-between rounded-xl px-4 py-3 mb-2" style="background-color: {{ $hybridColors[$i % 5] }}1A;">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full text-white text-xs font-semibold flex items-center justify-center" style="background-color: {{ $hybridColors[$i % 5] }};">{{ $i + 1 }}</span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $v->seedVariety->variety_name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-400">{{ $v->total }} farmers</p>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-white px-2.5 py-1 rounded-full" style="background-color: {{ $hybridColors[$i % 5] }};">{{ $pct }}%</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No hybrid preference data yet.</p>
                        @endforelse
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Top 5 Inbred Varieties</h3>
                        @php $inbredColors = ['#0F6B32', '#16803C', '#3FAE68', '#4FAF70', '#6BBF87']; @endphp
                        @forelse ($topInbred as $i => $v)
                            @php $pct = $topInbred->sum('total') ? round($v->total / $topInbred->sum('total') * 100, 1) : 0; @endphp
                            <div class="flex items-center justify-between rounded-xl px-4 py-3 mb-2" style="background-color: {{ $inbredColors[$i % 5] }}1A;">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full text-white text-xs font-semibold flex items-center justify-center" style="background-color: {{ $inbredColors[$i % 5] }};">{{ $i + 1 }}</span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $v->seedVariety->variety_name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-400">{{ $v->total }} farmers</p>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-white px-2.5 py-1 rounded-full" style="background-color: {{ $inbredColors[$i % 5] }};">{{ $pct }}%</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No inbred preference data yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-1">Seed Selection Criteria</h3>
                        <p class="text-xs text-gray-400 mb-4">What farmers prioritize when choosing seeds</p>
                        @if ($criteria->count())
                            <div style="height: 260px;">
                                <canvas id="criteriaChart"></canvas>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No selection criteria recorded yet.</p>
                        @endif
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-1">Common Problems Encountered</h3>
                        <p class="text-xs text-gray-400 mb-4">Excludes farmers who reported "None"</p>
                        @if ($problems->count())
                            <div style="height: 260px;">
                                <canvas id="problemsChart"></canvas>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No problems reported yet.</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    @include('dashboard._chart-colors')
    <script>
        @if ($criteria->count())
        new Chart(document.getElementById('criteriaChart'), {
            type: 'bar',
            data: {
                labels: @json($criteria->keys()),
                datasets: [{
                    data: @json($criteria->values()),
                    backgroundColor: daGreenShades({{ $criteria->count() }}),
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: DA_COLORS.grid } }, y: { grid: { display: false } } }
            }
        });
        @endif

        @if ($problems->count())
        new Chart(document.getElementById('problemsChart'), {
            type: 'bar',
            data: {
                labels: @json($problems->keys()),
                datasets: [{
                    data: @json($problems->values()),
                    backgroundColor: daProblemShades({{ $problems->count() }}),
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: DA_COLORS.grid } }, y: { grid: { display: false } } }
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>