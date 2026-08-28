<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Province Maps</h2>
        <p class="text-xs text-gray-500 mt-0.5">Geographic distribution of farmer survey response activity</p>
    </x-slot>

    <div class="flex">
        @include('dashboard._sidebar')

        <div class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto space-y-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-1">
                        <div>
                            <h3 class="font-semibold text-gray-800">Response Intensity</h3>
                            <p class="text-xs text-gray-400" id="breadcrumb">Farmer survey activity across Region II</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-gray-200 inline-block"></span> No Data</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-100 inline-block"></span> Low</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-400 inline-block"></span> Medium</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-800 inline-block"></span> High</span>
                        </div>
                    </div>

                    {{-- FILTERS --}}
                    <div class="flex flex-wrap items-center gap-3 mt-4 mb-2">
                        <select id="filterProvince" class="rounded-lg border-gray-300 text-sm">
                            <option value="">All Provinces (Region II)</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}" data-name="{{ $province->name }}">{{ $province->name }}</option>
                            @endforeach
                        </select>
                        <select id="filterYear" class="rounded-lg border-gray-300 text-sm">
                            <option value="">All Years</option>
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <button id="backToRegionBtn" type="button" class="hidden text-sm font-medium text-green-700 hover:text-green-900">
                            ← Back to Region II
                        </button>
                    </div>

                    {{-- MAP + ANALYTICS PANEL --}}
                    <div class="grid grid-cols-1 lg:grid-cols-[1fr_280px] gap-5 mt-4">
                        <div>
                            <div id="provinceMap" style="height: 460px; border-radius: 0.75rem;"></div>
                            <p id="mapNote" class="text-xs text-gray-400 mt-3"></p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4 space-y-4" id="analyticsPanel">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase" id="analyticsScope">Region II</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Farmers</p>
                                <p class="text-xl font-bold text-gray-800" id="statFarmers">—</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Responses</p>
                                <p class="text-xl font-bold text-gray-800" id="statResponses">—</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Response Rate</p>
                                <p class="text-xl font-bold text-gray-800" id="statRate">—</p>
                            </div>
                            <hr class="border-gray-200">
                            <div>
                                <p class="text-xs text-gray-500">High Intensity</p>
                                <p class="text-sm font-semibold text-red-800" id="statHigh">—</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Medium Intensity</p>
                                <p class="text-sm font-semibold text-red-500" id="statMedium">—</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Low Intensity</p>
                                <p class="text-sm font-semibold text-red-300" id="statLow">—</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- HIGHEST RESPONSE AREAS --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-1">Highest Response Areas</h3>
                    <p class="text-xs text-gray-400 mb-4" id="rankingScope">Ranked by response rate · Region II provinces</p>
                    <div id="rankingList" class="space-y-2">
                        <p class="text-sm text-gray-500">Loading…</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const REGION_GEOJSON_URL = 'https://raw.githubusercontent.com/faeldon/philippines-json-maps/master/2023/geojson/regions/lowres/provdists-region-200000000.0.001.json';
        const PROVINCE_PSGC = {
            'Batanes': '200900000',
            'Cagayan': '201500000',
            'Isabela': '203100000',
            'Nueva Vizcaya': '205000000',
            'Quirino': '205700000',
        };
        const dataEndpoint = @json(route('dashboard.province-maps.data'));

        function normalizeName(name) {
            return (name || '')
                .toLowerCase()
                .replace(/^city of /, '')
                .replace(/ city$/, '')
                .replace(/[^a-z]/g, '');
        }

        function colorFor(intensity) {
            switch (intensity) {
                case 'high': return '#991b1b';
                case 'medium': return '#f87171';
                case 'low': return '#fecaca';
                default: return '#e5e7eb'; // no data
            }
        }

        function intensityLabel(intensity) {
            return { high: 'HIGH', medium: 'MEDIUM', low: 'LOW', none: 'NO DATA' }[intensity] || 'NO DATA';
        }

        const map = L.map('provinceMap', { scrollWheelZoom: false });

        // CartoDB Positron: a lighter, less-labeled basemap than default OSM,
        // to reduce clutter from neighboring regions' city/town labels.
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
            maxZoom: 14,
        }).addTo(map);

        let currentLayer = null;
        let currentLevel = 'province'; // 'province' | 'municipality'
        let currentProvinceId = null;
        let currentProvinceName = null;
        let statsById = {}; // keyed by feature match name (normalized) -> stats row

        function setBreadcrumb() {
            const el = document.getElementById('breadcrumb');
            const backBtn = document.getElementById('backToRegionBtn');
            if (currentLevel === 'municipality') {
                el.textContent = `Region II > ${currentProvinceName}`;
                backBtn.classList.remove('hidden');
            } else {
                el.textContent = 'Farmer survey activity across Region II';
                backBtn.classList.add('hidden');
            }
        }

        function updateAnalyticsPanel(rows, scopeLabel) {
            document.getElementById('analyticsScope').textContent = scopeLabel;

            const totalFarmers = rows.reduce((s, r) => s + r.farmers, 0);
            const totalResponses = rows.reduce((s, r) => s + r.responses, 0);
            const rate = totalFarmers > 0 ? ((totalResponses / totalFarmers) * 100).toFixed(1) + '%' : '—';

            document.getElementById('statFarmers').textContent = totalFarmers.toLocaleString();
            document.getElementById('statResponses').textContent = totalResponses.toLocaleString();
            document.getElementById('statRate').textContent = rate;

            const counts = { high: 0, medium: 0, low: 0 };
            rows.forEach(r => { if (counts[r.intensity] !== undefined) counts[r.intensity]++; });

            const unitLabel = currentLevel === 'municipality' ? 'municipalities' : 'provinces';
            document.getElementById('statHigh').textContent = `${counts.high} ${unitLabel}`;
            document.getElementById('statMedium').textContent = `${counts.medium} ${unitLabel}`;
            document.getElementById('statLow').textContent = `${counts.low} ${unitLabel}`;
        }

        function updateRanking(rows, scopeLabel) {
            document.getElementById('rankingScope').textContent = `Ranked by response rate · ${scopeLabel}`;
            const list = document.getElementById('rankingList');

            const ranked = rows
                .filter(r => r.response_rate !== null)
                .sort((a, b) => b.response_rate - a.response_rate)
                .slice(0, 5);

            if (ranked.length === 0) {
                list.innerHTML = '<p class="text-sm text-gray-500">No response data available for this view yet.</p>';
                return;
            }

            list.innerHTML = ranked.map((r, i) => `
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="flex items-center gap-2 text-sm text-gray-700">
                        <span class="w-6 h-6 rounded-full bg-green-100 text-green-800 text-xs font-semibold flex items-center justify-center">${i + 1}</span>
                        ${r.name}
                    </span>
                    <span class="text-sm font-semibold text-gray-800">${r.response_rate}%</span>
                </div>
            `).join('');
        }

        function buildTooltip(row) {
            const rate = row.response_rate !== null ? row.response_rate + '%' : '—';
            return `
                <div style="min-width:160px">
                    <strong>${row.name.toUpperCase()}</strong><br>
                    Farmers: ${row.farmers}<br>
                    Responses: ${row.responses}<br>
                    Response Rate: ${rate}<br>
                    Intensity: ${intensityLabel(row.intensity)}
                </div>
            `;
        }

        async function fetchStats(level, provinceId, year) {
            const params = new URLSearchParams({ level });
            if (provinceId) params.set('province_id', provinceId);
            if (year) params.set('year', year);
            const res = await fetch(`${dataEndpoint}?${params.toString()}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('Stats fetch failed: ' + res.status);
            return (await res.json()).rows;
        }

        function renderLayer(geojson, nameKey, rows, onFeatureClick) {
            if (currentLayer) {
                map.removeLayer(currentLayer);
            }

            statsById = {};
            rows.forEach(r => { statsById[normalizeName(r.name)] = r; });

            let matched = 0;

            currentLayer = L.geoJSON(geojson, {
                style: (feature) => {
                    const rawName = feature.properties[nameKey] || '';
                    const row = statsById[normalizeName(rawName)];
                    if (row) matched++;
                    return {
                        fillColor: colorFor(row ? row.intensity : 'none'),
                        fillOpacity: 0.85,
                        color: '#7f1d1d',
                        weight: 1,
                    };
                },
                onEachFeature: (feature, layer) => {
                    const rawName = feature.properties[nameKey] || 'Unknown';
                    const row = statsById[normalizeName(rawName)] || { name: rawName, farmers: 0, responses: 0, response_rate: null, intensity: 'none' };

                    layer.bindTooltip(buildTooltip(row));

                    layer.on('mouseover', () => layer.setStyle({ weight: 2, color: '#450a0a' }));
                    layer.on('mouseout', () => layer.setStyle({ weight: 1, color: '#7f1d1d' }));

                    if (onFeatureClick) {
                        layer.on('click', () => onFeatureClick(row, feature.properties));
                    }
                },
            }).addTo(map);

            map.fitBounds(currentLayer.getBounds());

            const note = document.getElementById('mapNote');
            note.textContent = matched === 0
                ? "Map loaded, but names didn't match your database — check the browser console."
                : '';
        }

        async function loadProvinceLevel() {
            currentLevel = 'province';
            currentProvinceId = null;
            currentProvinceName = null;
            setBreadcrumb();

            const year = document.getElementById('filterYear').value;

            try {
                const [rows, geojson] = await Promise.all([
                    fetchStats('province', null, year),
                    fetch(REGION_GEOJSON_URL).then(r => {
                        if (!r.ok) throw new Error('GeoJSON fetch failed: ' + r.status);
                        return r.json();
                    }),
                ]);

                renderLayer(geojson, 'adm2_en', rows, (row) => {
                    const provinceId = document.querySelector(`#filterProvince option[data-name="${row.name}"]`)?.value;
                    if (provinceId) {
                        document.getElementById('filterProvince').value = provinceId;
                        loadMunicipalityLevel(provinceId, row.name);
                    }
                });

                updateAnalyticsPanel(rows, 'Region II');
                updateRanking(rows, 'Region II provinces');
            } catch (err) {
                document.getElementById('mapNote').textContent = 'Could not load the province boundary file (' + err.message + ').';
                console.error(err);
            }
        }

        async function loadMunicipalityLevel(provinceId, provinceName) {
            currentLevel = 'municipality';
            currentProvinceId = provinceId;
            currentProvinceName = provinceName;
            setBreadcrumb();

            const year = document.getElementById('filterYear').value;
            const psgc = PROVINCE_PSGC[provinceName];

            if (!psgc) {
                document.getElementById('mapNote').textContent = `No municipality boundary file mapped for ${provinceName}.`;
                return;
            }

            const muniGeoUrl = `https://raw.githubusercontent.com/faeldon/philippines-json-maps/master/2023/geojson/provdists/lowres/municities-provdist-${psgc}.0.001.json`;

            try {
                const [rows, geojson] = await Promise.all([
                    fetchStats('municipality', provinceId, year),
                    fetch(muniGeoUrl).then(r => {
                        if (!r.ok) throw new Error('Municipality GeoJSON fetch failed: ' + r.status);
                        return r.json();
                    }),
                ]);

                renderLayer(geojson, 'adm3_en', rows, null);

                updateAnalyticsPanel(rows, provinceName);
                updateRanking(rows, `${provinceName} municipalities`);
            } catch (err) {
                document.getElementById('mapNote').textContent = 'Could not load municipality boundaries (' + err.message + ').';
                console.error(err);
            }
        }

        document.getElementById('filterProvince').addEventListener('change', (e) => {
            const opt = e.target.selectedOptions[0];
            if (e.target.value) {
                loadMunicipalityLevel(e.target.value, opt.dataset.name);
            } else {
                loadProvinceLevel();
            }
        });

        document.getElementById('filterYear').addEventListener('change', () => {
            if (currentLevel === 'municipality') {
                loadMunicipalityLevel(currentProvinceId, currentProvinceName);
            } else {
                loadProvinceLevel();
            }
        });

        document.getElementById('backToRegionBtn').addEventListener('click', () => {
            document.getElementById('filterProvince').value = '';
            loadProvinceLevel();
        });

        loadProvinceLevel();
    </script>
    @endpush
</x-app-layout>