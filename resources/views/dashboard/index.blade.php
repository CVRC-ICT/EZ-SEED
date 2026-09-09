<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard Overview</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $isAdmin ? 'Region 02 · Real-time seed preference analytics · Updated just now' : 'Your surveys and farmers · Updated just now' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.export-pdf', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 bg-white px-3 py-1.5 rounded-full border border-gray-200 hover:bg-gray-50">
                    📄 Export PDF
                </a>
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-50 px-3 py-1 rounded-full border border-green-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Live
                </span>
            </div>
        </div>
    </x-slot>

    <div class="flex">
        @include('dashboard._sidebar')

        <div class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto space-y-6">

                {{-- FILTER BAR --}}
                <form method="GET" action="{{ route('dashboard') }}" id="dashboardFilters"
                      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

                    {{-- NEW: Crop toggle — All / Rice / Corn. Hidden input holds
                         the actual value submitted with the form; clicking a
                         button sets it and resubmits immediately. --}}
                    <div class="flex items-center gap-2 mb-4 pb-4 border-b border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 uppercase mr-1">Crop:</span>
                        <input type="hidden" name="crop" id="filterCropInput" value="{{ $filters['crop'] }}">
                        <button type="button" onclick="setCropFilter('')"
                                class="crop-filter-btn px-4 py-1.5 rounded-full text-sm font-semibold border transition {{ $filters['crop'] === null || $filters['crop'] === '' ? 'bg-green-700 text-white border-green-700' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                            All Crops
                        </button>
                        <button type="button" onclick="setCropFilter('rice')"
                                class="crop-filter-btn px-4 py-1.5 rounded-full text-sm font-semibold border transition {{ $filters['crop'] === 'rice' ? 'bg-green-700 text-white border-green-700' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                            🌾 Rice
                        </button>
                        <button type="button" onclick="setCropFilter('corn')"
                                class="crop-filter-btn px-4 py-1.5 rounded-full text-sm font-semibold border transition {{ $filters['crop'] === 'corn' ? 'bg-green-700 text-white border-green-700' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                            🌽 Corn
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                        <select name="province" id="filterProvince" onchange="onProvinceChange(this.value)"
                                class="rounded-lg border-gray-300 text-sm">
                            <option value="">All Provinces</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}" @selected($filters['province'] == $province->id)>{{ $province->name }}</option>
                            @endforeach
                        </select>

                        <select name="municipality" id="filterMunicipality" class="rounded-lg border-gray-300 text-sm" {{ $filters['province'] ? '' : 'disabled' }}>
                            <option value="">All Municipalities</option>
                            @foreach ($municipalities as $municipality)
                                <option value="{{ $municipality->id }}" @selected($filters['municipality'] == $municipality->id)>{{ $municipality->name }}</option>
                            @endforeach
                        </select>

                        <select name="season" class="rounded-lg border-gray-300 text-sm">
                            <option value="">All Seasons</option>
                            <option value="Dry Season" @selected($filters['season'] === 'Dry Season')>Dry Season</option>
                            <option value="Wet Season" @selected($filters['season'] === 'Wet Season')>Wet Season</option>
                        </select>

                        <select name="seed_type" class="rounded-lg border-gray-300 text-sm">
                            <option value="">All Seed Types</option>
                            <option value="Hybrid" @selected($filters['seed_type'] === 'Hybrid')>Hybrid</option>
                            <option value="Inbred" @selected($filters['seed_type'] === 'Inbred')>Inbred</option>
                        </select>

                        <select name="year" class="rounded-lg border-gray-300 text-sm">
                            <option value="">All Years</option>
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}" @selected($filters['year'] == $year)>{{ $year }}</option>
                            @endforeach
                        </select>

                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium rounded-lg bg-green-700 text-white hover:bg-green-800">Apply</button>
                            <a href="{{ route('dashboard') }}" class="flex-1 text-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Reset</a>
                        </div>
                    </div>
                </form>

                {{-- KPI CARDS --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <x-dashboard.kpi-card label="Total Farmers" :value="$totalFarmers" icon="🧑‍🌾" color="green" sub="Registered Farmers" />
                    <x-dashboard.kpi-card label="Completed Surveys" :value="$submittedSurveys" icon="📋" color="green"
                        :sub="$totalSurveys ? round(($submittedSurveys / max($totalSurveys,1)) * 100, 1) . '% completion rate' : 'No surveys yet'" />
                    <x-dashboard.kpi-card label="Hybrid Preference" :value="$hybridPct !== null ? $hybridPct . '%' : '—'" icon="🌾" color="amber"
                        sub="Of recorded seed preferences" />
                    <x-dashboard.kpi-card label="Inbred Preference" :value="$inbredPct !== null ? $inbredPct . '%' : '—'" icon="🌱" color="amber"
                        sub="Of recorded seed preferences" />
                    <x-dashboard.kpi-card label="Top Variety" :value="$topVarietyName" icon="⭐" color="green"
                        :sub="$topVarietyCount . ' preferences'" />
                    <x-dashboard.kpi-card label="Top Province" :value="$topProvinceName" icon="📍" color="blue"
                        :sub="$topProvinceCount . ' participating farmers'" />
                </div>

                {{-- AVERAGE FARM SIZE BANNER --}}
                <div class="bg-green-950 rounded-2xl p-6 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-green-800 flex items-center justify-center text-lg">📈</div>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-green-300 mb-1">Average Farm Size</p>
                            <p class="text-2xl font-bold">{{ $averageFarmArea !== null ? $averageFarmArea . ' ha' : '—' }}</p>
                            <p class="text-xs text-green-300">Based on registered farmers</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold">{{ $totalFarmers }} Farmers</p>
                        <p class="text-xs text-green-300">{{ $submittedSurveys }} Completed Surveys</p>
                    </div>
                </div>

                {{-- TOP VARIETIES + FARMERS BY PROVINCE --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">
                            Top Preferred {{ $filters['crop'] ? ucfirst($filters['crop']) . ' ' : '' }}Varieties
                        </h3>
                        <p class="text-xs text-gray-400 mb-4">
                            Based on all recorded preference entries (ranking is no longer captured in the survey)
                        </p>
                        @if ($topVarieties->count())
                            <canvas id="varietyChart" height="220"></canvas>
                        @else
                            <p class="text-sm text-gray-500">No seed preference data available for the selected filters.</p>
                        @endif
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Farmers by Province</h3>
                        <p class="text-xs text-gray-400 mb-4">Participating farmers per province</p>
                        @if ($farmersByProvince->count())
                            <canvas id="provinceChart" height="220"></canvas>
                        @else
                            <p class="text-sm text-gray-500">No province data available.</p>
                        @endif
                    </div>
                </div>

                {{-- SEASONAL + HYBRID/INBRED --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Seasonal Seed Preference</h3>
                        <p class="text-xs text-gray-400 mb-4">Top varieties, Dry vs Wet Season</p>
                        @if ($seasonalBreakdown->count())
                            <canvas id="seasonalChart" height="220"></canvas>
                        @else
                            <p class="text-sm text-gray-500">No wet or dry season responses yet.</p>
                        @endif
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Seed Type Preference</h3>
                        <p class="text-xs text-gray-400 mb-4">
                            {{ $totalPreferences ? "Based on {$totalPreferences} preferences" : 'No data' }}
                        </p>
                        @if ($totalPreferences)
                            <canvas id="seedTypeChart" height="220"></canvas>
                        @else
                            <p class="text-sm text-gray-500">No seed preference data available for the selected filters.</p>
                        @endif
                    </div>
                </div>

                {{-- REASONS + PROBLEMS --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Why Farmers Choose Their Varieties</h3>
                        <p class="text-xs text-gray-400 mb-4">Survey responses{{ $isAdmin ? ' across all provinces' : '' }}</p>
                        @if ($reasonBreakdown->count())
                            <canvas id="reasonChart" height="220"></canvas>
                        @else
                            <p class="text-sm text-gray-500">No categorized reasons recorded yet.</p>
                        @endif
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Common Problems Reported</h3>
                        <p class="text-xs text-gray-400 mb-4">Excludes farmers who reported "None"</p>
                        @if ($problemsTally->count())
                            <canvas id="problemsChart" height="220"></canvas>
                        @else
                            <p class="text-sm text-gray-500">No reported problems yet.</p>
                        @endif
                    </div>
                </div>

                {{-- KEY INSIGHTS --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Key Insights</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($insights as $insight)
                            <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-4">
                                <span class="text-xl">{{ $insight['icon'] }}</span>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ $insight['label'] }}</p>
                                    <p class="text-sm text-gray-800">{{ $insight['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- RECENT ACTIVITY + QUICK REPORTS --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Recent Activity</h3>
                        @forelse ($recentActivity as $activity)
                            <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                                <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-800">{{ $activity['type'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $activity['place'] }}</p>
                                </div>
                                <span class="text-xs text-gray-400">{{ optional($activity['at'])->diffForHumans() }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No recent activity yet.</p>
                        @endforelse
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Quick Reports</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('dashboard.reports', ['type' => 'variety-by-province']) }}"
                               class="px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 text-center">
                                Seed Preference Report
                            </a>
                            <a href="{{ route('dashboard.reports', ['type' => 'variety-by-province']) }}"
                               class="px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 text-center">
                                Provincial Report
                            </a>
                            <a href="{{ route('dashboard.reports', ['type' => 'demographic-profiles']) }}"
                               class="px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 text-center">
                                Farmer Report
                            </a>
                            <a href="{{ route('dashboard.seasonal-analysis') }}"
                               class="px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 text-center">
                                Seasonal Report
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    @include('dashboard._chart-colors')
    <script>
        // NEW: Crop filter buttons — set the hidden input and resubmit the
        // filter form immediately, same as changing any other dropdown.
        function setCropFilter(crop) {
            document.getElementById('filterCropInput').value = crop;
            document.getElementById('dashboardFilters').submit();
        }

        async function onProvinceChange(provinceId) {
            const muniSelect = document.getElementById('filterMunicipality');
            if (!provinceId) {
                muniSelect.innerHTML = '<option value="">All Municipalities</option>';
                muniSelect.disabled = true;
                return;
            }
            muniSelect.disabled = false;
            muniSelect.innerHTML = '<option value="">Loading…</option>';
            try {
                const res = await fetch(`/municipalities/${provinceId}`, { headers: { Accept: 'application/json' } });
                const municipalities = await res.json();
                muniSelect.innerHTML = '<option value="">All Municipalities</option>' +
                    municipalities.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
            } catch (e) {
                muniSelect.innerHTML = '<option value="">All Municipalities</option>';
            }
        }

        @if ($topVarieties->count())
        new Chart(document.getElementById('varietyChart'), {
            type: 'bar',
            data: {
                labels: @json($topVarieties->map(fn ($v) => $v->seedVariety->variety_name ?? 'Unknown')),
                datasets: [{ data: @json($topVarieties->pluck('total')), backgroundColor: daGreenShades({{ $topVarieties->count() }}), borderRadius: 6 }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: DA_COLORS.grid } }, y: { grid: { display: false } } }
            }
        });
        @endif

        @if ($farmersByProvince->count())
        new Chart(document.getElementById('provinceChart'), {
            type: 'bar',
            data: {
                labels: @json($farmersByProvince->pluck('name')),
                datasets: [{ data: @json($farmersByProvince->pluck('total')), backgroundColor: daGreenShades({{ $farmersByProvince->count() }}), borderRadius: 6 }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: DA_COLORS.grid } }, y: { grid: { display: false } } }
            }
        });
        @endif

        @if ($seasonalBreakdown->count())
        new Chart(document.getElementById('seasonalChart'), {
            type: 'bar',
            data: {
                labels: @json($seasonalBreakdown->pluck('name')),
                datasets: [
                    { label: 'Dry Season', data: @json($seasonalBreakdown->pluck('dry')), backgroundColor: DA_COLORS.warning, borderRadius: 4 },
                    { label: 'Wet Season', data: @json($seasonalBreakdown->pluck('wet')), backgroundColor: DA_COLORS.primaryGreen, borderRadius: 4 },
                ]
            },
            options: {
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: DA_COLORS.grid } }, x: { grid: { display: false } } }
            }
        });
        @endif

        @if ($totalPreferences)
        new Chart(document.getElementById('seedTypeChart'), {
            type: 'doughnut',
            data: {
                labels: ['Hybrid', 'Inbred'],
                datasets: [{ data: [{{ $hybridCount }}, {{ $inbredCount }}], backgroundColor: daGreenShades(2) }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
        @endif

        @if ($reasonBreakdown->count())
        new Chart(document.getElementById('reasonChart'), {
            type: 'doughnut',
            data: {
                labels: @json($reasonBreakdown->pluck('reason_category')),
                datasets: [{ data: @json($reasonBreakdown->pluck('total')), backgroundColor: daBlueShades({{ $reasonBreakdown->count() }}) }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
        @endif

        @if ($problemsTally->count())
        new Chart(document.getElementById('problemsChart'), {
            type: 'bar',
            data: {
                labels: @json($problemsTally->keys()),
                datasets: [{ data: @json($problemsTally->values()), backgroundColor: daProblemShades({{ $problemsTally->count() }}), borderRadius: 6 }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: DA_COLORS.grid } }, y: { grid: { display: false } } }
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>