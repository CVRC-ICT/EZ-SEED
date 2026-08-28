@extends('surveys.wizard', [
    'currentStep' => 3,
    'totalSteps' => 10,
    'stepTitle' => 'Farm Information',
])

@section('content')
<form id="wizardStepForm" method="POST" action="{{ isset($farmer) ? route('surveys.step.store', ['farmer' => $farmer, 'step' => 3]) : url("/survey/local/{$uuid}/step/3") }}" novalidate>
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="bg-da-green-50 border-b border-da-green-100 px-6 sm:px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Farm Information</h2>
                    <p class="text-sm text-gray-600">Location, land, and economic profile of the farm.</p>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7 space-y-8">

            {{-- Farm Location — real cascading Province -> Municipality -> Barangay --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Farm Location</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label for="farm_province_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Province <span class="text-red-600">*</span>
                        </label>
                        <select id="farm_province_id" name="farm_province_id" required
                                data-old="{{ old('farm_province_id', data_get($old_data, 'farm_province_id')) }}"
                                class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('farm_province_id') border-red-500 @enderror">
                            <option value="" disabled {{ old('farm_province_id', data_get($old_data, 'farm_province_id')) ? '' : 'selected' }}>Select Province</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}" {{ old('farm_province_id', data_get($old_data, 'farm_province_id')) == $province->id ? 'selected' : '' }}>
                                    {{ \App\Support\PlaceName::clean($province->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('farm_province_id')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="farm_municipality_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Municipality <span class="text-red-600">*</span>
                        </label>
                        <select id="farm_municipality_id" name="farm_municipality_id" disabled required
                                data-old="{{ old('farm_municipality_id', data_get($old_data, 'farm_municipality_id')) }}"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('farm_municipality_id') border-red-500 @enderror">
                            <option value="">Select province first</option>
                        </select>
                        @error('farm_municipality_id')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="farm_barangay_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Barangay <span class="text-red-600">*</span>
                        </label>
                        <select id="farm_barangay_id" name="farm_barangay_id" disabled required
                                data-old="{{ old('farm_barangay_id', data_get($old_data, 'farm_barangay_id')) }}"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('farm_barangay_id') border-red-500 @enderror">
                            <option value="">Select municipality first</option>
                        </select>
                        @error('farm_barangay_id')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p id="farmLocationHint" class="mt-2 hidden text-xs text-amber-600">Loading location options&hellip;</p>
            </section>

            <hr class="border-gray-200">

            {{-- Farm Area breakdown — Total is manual & first, Rice/Corn split by season, HVC flat --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-1">Farm Area</h3>
                <p class="text-sm text-gray-500 mb-4">Land area by season and crop</p>

                <div class="mb-5">
                    <label for="farm_area" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Total Farm Area (hectares) <span class="text-red-600">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" id="farm_area" name="farm_area"
                           value="{{ old('farm_area', data_get($old_data, 'farm_area')) }}" required
                           placeholder="0.00"
                           class="w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 font-semibold @error('farm_area') border-red-500 @enderror">
                    @error('farm_area')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-amber-800 mb-4">Dry Season</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="farm_area_rice_dry" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Rice Area (hectares)
                                </label>
                                <input type="number" step="0.01" min="0" id="farm_area_rice_dry" name="farm_area_rice_dry"
                                       value="{{ old('farm_area_rice_dry', data_get($old_data, 'farm_area_rice_dry')) }}"
                                       placeholder="0.00" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                            </div>
                            <div>
                                <label for="farm_area_corn_dry" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Corn Area (hectares)
                                </label>
                                <input type="number" step="0.01" min="0" id="farm_area_corn_dry" name="farm_area_corn_dry"
                                       value="{{ old('farm_area_corn_dry', data_get($old_data, 'farm_area_corn_dry')) }}"
                                       placeholder="0.00" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                            </div>
                        </div>
                    </div>

                    <div class="bg-da-green-50 border border-da-green-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-da-green-800 mb-4">Wet Season</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="farm_area_rice_wet" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Rice Area (hectares)
                                </label>
                                <input type="number" step="0.01" min="0" id="farm_area_rice_wet" name="farm_area_rice_wet"
                                       value="{{ old('farm_area_rice_wet', data_get($old_data, 'farm_area_rice_wet')) }}"
                                       placeholder="0.00" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                            </div>
                            <div>
                                <label for="farm_area_corn_wet" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Corn Area (hectares)
                                </label>
                                <input type="number" step="0.01" min="0" id="farm_area_corn_wet" name="farm_area_corn_wet"
                                       value="{{ old('farm_area_corn_wet', data_get($old_data, 'farm_area_corn_wet')) }}"
                                       placeholder="0.00" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="farm_area_hvc" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        HVC / Others Area (hectares)
                    </label>
                    <input type="number" step="0.01" min="0" id="farm_area_hvc" name="farm_area_hvc"
                           value="{{ old('farm_area_hvc', data_get($old_data, 'farm_area_hvc')) }}"
                           placeholder="0.00" class="w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                    <p class="mt-1 text-xs text-gray-400">Not split by season</p>
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Land & Tenure --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Land Tenure &amp; Experience</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="tenurial_status" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Tenurial Status <span class="text-red-600">*</span>
                        </label>
                        <select id="tenurial_status" name="tenurial_status" required
                                class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('tenurial_status') border-red-500 @enderror">
                            <option value="" disabled {{ old('tenurial_status', data_get($old_data, 'tenurial_status')) ? '' : 'selected' }}>Select Status</option>
                            @foreach (['Owner', 'Tenant', 'Lessee', 'Farm Worker', 'CLOA Holder', 'Others'] as $status)
                                <option value="{{ $status }}" {{ old('tenurial_status', data_get($old_data, 'tenurial_status')) == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        @error('tenurial_status')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="years_farming" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Years in Farming <span class="text-red-600">*</span>
                        </label>
                        <input type="number" min="0" max="100" id="years_farming" name="years_farming"
                               value="{{ old('years_farming', data_get($old_data, 'years_farming')) }}" required
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('years_farming') border-red-500 @enderror">
                        @error('years_farming')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Household & Economic --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Household &amp; Economic Profile</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="household_size" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Household Size <span class="text-red-600">*</span>
                        </label>
                        <input type="number" min="1" max="30" id="household_size" name="household_size"
                               value="{{ old('household_size', data_get($old_data, 'household_size')) }}" required
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('household_size') border-red-500 @enderror">
                        @error('household_size')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="occupation" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Primary Occupation <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="occupation" name="occupation"
                               value="{{ old('occupation', data_get($old_data, 'occupation')) }}" required
                               placeholder="e.g. Farmer, Farm Owner-Operator"
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('occupation') border-red-500 @enderror">
                        @error('occupation')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="annual_income" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Annual Income (PHP) <span class="text-red-600">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" id="annual_income" name="annual_income"
                               value="{{ old('annual_income', data_get($old_data, 'annual_income')) }}" required
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('annual_income') border-red-500 @enderror">
                        @error('annual_income')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Organization Membership <span class="text-red-600">*</span>
                        </label>
                        <div class="flex gap-4 mt-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="organization_membership" value="yes"
                                       onchange="document.getElementById('orgDetailsFields').classList.remove('hidden')"
                                       {{ old('organization_membership', data_get($old_data, 'organization_membership')) == 'yes' ? 'checked' : '' }}
                                       class="w-5 h-5 text-da-green-600 border-gray-400 focus:ring-da-green-600">
                                <span class="text-base text-gray-800">Yes</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="organization_membership" value="no"
                                       onchange="document.getElementById('orgDetailsFields').classList.add('hidden')"
                                       {{ old('organization_membership', data_get($old_data, 'organization_membership')) == 'no' ? 'checked' : '' }}
                                       class="w-5 h-5 text-da-green-600 border-gray-400 focus:ring-da-green-600">
                                <span class="text-base text-gray-800">No</span>
                            </label>
                        </div>
                        @error('organization_membership')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div id="orgDetailsFields" class="mt-5 space-y-5 {{ old('organization_membership', data_get($old_data, 'organization_membership')) == 'yes' ? '' : 'hidden' }}">
                    <div>
                        <label for="organization_name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Name of Organization / Association
                        </label>
                        <input type="text" id="organization_name" name="organization_name"
                               value="{{ old('organization_name', data_get($old_data, 'organization_name')) }}"
                               placeholder="e.g. Barangay Farmers Association"
                               class="w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('organization_name') border-red-500 @enderror">
                        @error('organization_name')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status of Organization</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="organization_status" value="registered"
                                       {{ old('organization_status', data_get($old_data, 'organization_status')) == 'registered' ? 'checked' : '' }}
                                       class="w-5 h-5 text-da-green-600 border-gray-400 focus:ring-da-green-600">
                                <span class="text-base text-gray-800">Registered</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="organization_status" value="not_registered"
                                       {{ old('organization_status', data_get($old_data, 'organization_status')) == 'not_registered' ? 'checked' : '' }}
                                       class="w-5 h-5 text-da-green-600 border-gray-400 focus:ring-da-green-600">
                                <span class="text-base text-gray-800">Not Registered</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Registered With</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach (['SEC', 'DOLE', 'CDA'] as $reg)
                                <label class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3.5 py-2 cursor-pointer">
                                    <input type="checkbox" name="organization_registered_with[]" value="{{ $reg }}"
                                           {{ in_array($reg, old('organization_registered_with', data_get($old_data, 'organization_registered_with', []))) ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600">
                                    <span class="text-sm text-gray-700">{{ $reg }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Navigation --}}
        <div class="bg-gray-50 border-t border-gray-200 px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ isset($farmer) ? route('surveys.step.show', ['farmer' => $farmer, 'step' => 2]) : url("/survey/local/{$uuid}/step/2") }}"
               class="w-full sm:w-auto text-center px-6 py-3.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-base hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back
            </a>
            <button type="submit"
                    class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-da-green-600 text-white font-semibold text-base shadow-sm hover:bg-da-green-700 focus:outline-none focus:ring-2 focus:ring-da-green-500 focus:ring-offset-2 transition flex items-center justify-center gap-2">
                Save &amp; Continue
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    (function () {
        const provinceSelect = document.getElementById('farm_province_id');
        const municipalitySelect = document.getElementById('farm_municipality_id');
        const barangaySelect = document.getElementById('farm_barangay_id');
        const hint = document.getElementById('farmLocationHint');
        const form = provinceSelect.closest('form');

        function resetSelect(select, placeholder, enable) {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.disabled = !enable;
            select.classList.toggle('bg-gray-50', !enable);
        }

        function populateSelect(select, items, selectedId) {
            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                if (selectedId && String(item.id) === String(selectedId)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        async function fetchJson(url) {
            hint.classList.remove('hidden');
            try {
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error('Request failed');
                return await response.json();
            } catch (err) {
                console.error('Location lookup failed:', err);
                return [];
            } finally {
                hint.classList.add('hidden');
            }
        }

        async function loadMunicipalities(provinceId, selectedMunicipalityId) {
            resetSelect(municipalitySelect, 'Loading…', false);
            resetSelect(barangaySelect, 'Select municipality first', false);
            if (!provinceId) {
                resetSelect(municipalitySelect, 'Select province first', false);
                return;
            }
            const municipalities = await fetchJson(`/municipalities/${provinceId}`);
            resetSelect(municipalitySelect, 'Select municipality', true);
            populateSelect(municipalitySelect, municipalities, selectedMunicipalityId);
            if (selectedMunicipalityId) {
                await loadBarangays(selectedMunicipalityId, barangaySelect.dataset.old);
            }
        }

        async function loadBarangays(municipalityId, selectedBarangayId) {
            resetSelect(barangaySelect, 'Loading…', false);
            if (!municipalityId) {
                resetSelect(barangaySelect, 'Select municipality first', false);
                return;
            }
            const barangays = await fetchJson(`/barangays/${municipalityId}`);
            resetSelect(barangaySelect, 'Select barangay', true);
            populateSelect(barangaySelect, barangays, selectedBarangayId);
        }

        provinceSelect.addEventListener('change', (e) => loadMunicipalities(e.target.value));
        municipalitySelect.addEventListener('change', (e) => loadBarangays(e.target.value));

        document.addEventListener('DOMContentLoaded', () => {
            const oldProvince = provinceSelect.dataset.old;
            const oldMunicipality = municipalitySelect.dataset.old;
            if (oldProvince) {
                loadMunicipalities(oldProvince, oldMunicipality);
            }
        });

        form.addEventListener('submit', () => {
            municipalitySelect.disabled = false;
            barangaySelect.disabled = false;
        });
    })();
</script>

@if (isset($farmer))
@include('surveys.partials.offline-sync-init', [
    'step' => 3,
    'nextUrl' => route('surveys.step.show', ['farmer' => $farmer, 'step' => 4]),
    'hasServerData' => !empty($old_data),
])
@else
@include('surveys.partials.local-first-init', [
    'uuid' => $uuid,
    'step' => 3,
])
@endif
@endpush
@endsection