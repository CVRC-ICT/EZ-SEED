<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Seed Preference Monitoring
        </h2>
        <p class="text-xs text-gray-500 mt-0.5">Detailed analytics on seed variety preferences</p>
    </x-slot>

    <div class="flex">
        @include('dashboard._sidebar')

        <div class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto space-y-6">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- SEED TYPE DONUT --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">Seed Type Distribution</h3>
                        @if ($seedTypes->count())
                            <canvas id="seedTypeChart" height="220"></canvas>
                        @else
                            <p class="text-sm text-gray-500">No seed preference data available.</p>
                        @endif
                    </div>

                    {{-- SEASON DONUT --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">Season Analysis</h3>
                        @if ($seasons->count())
                            <canvas id="seasonChart" height="220"></canvas>
                        @else
                            <p class="text-sm text-gray-500">No season data available.</p>
                        @endif
                    </div>

                    {{-- TOP VARIETIES BAR --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">Top 5 Preferred Varieties</h3>
                        @if ($topVarieties->count())
                            <canvas id="varietyChart" height="220"></canvas>
                        @else
                            <p class="text-sm text-gray-500">No variety data available yet.</p>
                        @endif
                    </div>

                    {{-- PROVINCE DISTRIBUTION (Admin/Supervisor only) --}}
                    @if ($isAdmin)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">Farmers by Province</h3>
                            @if ($provinceData->count())
                                <canvas id="provinceChart" height="220"></canvas>
                            @else
                                <p class="text-sm text-gray-500">No farmer data available.</p>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        @if ($seedTypes->count())
        new Chart(document.getElementById('seedTypeChart'), {
            type: 'doughnut',
            data: {
                labels: @json($seedTypes->pluck('seed_type')),
                datasets: [{
                    data: @json($seedTypes->pluck('total')),
                    backgroundColor: ['#15803d', '#f59e0b', '#0ea5e9'],
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
        @endif

        @if ($seasons->count())
        new Chart(document.getElementById('seasonChart'), {
            type: 'doughnut',
            data: {
                labels: @json($seasons->pluck('season')),
                datasets: [{
                    data: @json($seasons->pluck('total')),
                    backgroundColor: ['#f59e0b', '#0ea5e9', '#15803d'],
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
        @endif

        @if ($topVarieties->count())
        new Chart(document.getElementById('varietyChart'), {
            type: 'bar',
            data: {
                labels: @json($topVarieties->map(fn ($v) => $v->seedVariety->variety_name ?? 'Unknown')),
                datasets: [{
                    data: @json($topVarieties->pluck('total')),
                    backgroundColor: '#15803d',
                    borderRadius: 6,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
        @endif

        @if ($isAdmin && $provinceData->count())
        new Chart(document.getElementById('provinceChart'), {
            type: 'bar',
            data: {
                labels: @json($provinceData->pluck('name')),
                datasets: [{
                    data: @json($provinceData->pluck('farmers_count')),
                    backgroundColor: '#0ea5e9',
                    borderRadius: 6,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>
