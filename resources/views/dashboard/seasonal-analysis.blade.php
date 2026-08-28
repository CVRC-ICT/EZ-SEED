<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Seasonal Analysis</h2>
        <p class="text-xs text-gray-500 mt-0.5">Compare dry and wet season performance</p>
    </x-slot>

    <div class="flex">
        @include('dashboard._sidebar')

        <div class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto space-y-6">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                        <p class="text-sm text-gray-600">Dry Season Responses</p>
                        <p class="text-3xl font-bold text-amber-800 mt-2">{{ $dryTotal }}</p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5">
                        <p class="text-sm text-gray-600">Wet Season Responses</p>
                        <p class="text-3xl font-bold text-emerald-800 mt-2">{{ $wetTotal }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-1">Top Varieties by Season</h3>
                    <p class="text-xs text-gray-400 mb-4">Dry vs Wet Season, by variety</p>
                    @if ($varietySeasonal->count())
                        <div style="height: 320px;">
                            <canvas id="varietySeasonalChart"></canvas>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No seed preference data available yet to compare seasons.</p>
                    @endif
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-1">Response Trends by Month</h3>
                    <p class="text-xs text-gray-400 mb-4">
                        @if ($monthly->count() === 1)
                            Only one month of submissions so far — this will fill in as more surveys are submitted over time.
                        @else
                            Submitted surveys per month, split by season
                        @endif
                    </p>
                    @if ($monthly->count())
                        <div style="height: 300px;">
                            <canvas id="seasonTrendChart"></canvas>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Not enough submitted surveys yet to chart monthly trends.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    @include('dashboard._chart-colors')
    <script>
        @if ($varietySeasonal->count())
        new Chart(document.getElementById('varietySeasonalChart'), {
            type: 'bar',
            data: {
                labels: @json($varietySeasonal->pluck('name')),
                datasets: [
                    { label: 'Dry Season', data: @json($varietySeasonal->pluck('dry')), backgroundColor: DA_COLORS.warning, borderRadius: 4 },
                    { label: 'Wet Season', data: @json($varietySeasonal->pluck('wet')), backgroundColor: DA_COLORS.primaryGreen, borderRadius: 4 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: DA_COLORS.grid } }, x: { grid: { display: false } } }
            }
        });
        @endif

        @if ($monthly->count())
        const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const monthlyData = @json($monthly);
        const labels = monthlyData.map(m => monthNames[m.month - 1]);

        new Chart(document.getElementById('seasonTrendChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Dry Season', data: monthlyData.map(m => m.dry_total), backgroundColor: DA_COLORS.warning, borderRadius: 4 },
                    { label: 'Wet Season', data: monthlyData.map(m => m.wet_total), backgroundColor: DA_COLORS.primaryGreen, borderRadius: 4 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: DA_COLORS.grid } }, x: { grid: { display: false } } }
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>