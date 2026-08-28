<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-700 text-white shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-4 3-7 7-7 11a7 7 0 0014 0c0-4-3-8-7-11z" />
                </svg>
            </div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Farmer Registration &mdash; RSBSA
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- Top intro strip --}}
            <div class="mb-6 overflow-hidden rounded-xl bg-gradient-to-r from-green-700 to-green-600 shadow-sm">
                <div class="flex flex-col gap-2 px-6 py-5 text-white sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-wide text-green-100">EZ-Seed &bull; Registry System for Basic Sectors in Agriculture</p>
                        <p class="mt-1 text-lg font-semibold">Register a new farmer profile</p>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-green-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Fields marked <span class="text-red-200">*</span> are required</span>
                    </div>
                </div>
            </div>

            {{-- Validation summary --}}
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-red-800">Please fix the following before continuing:</p>
                            <ul class="mt-1.5 list-inside list-disc space-y-0.5 text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Main card --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                <form method="POST" action="{{ route('farmer.store') }}" id="registrationForm" novalidate>
                    @csrf

                    {{-- SECTION: Personal Information --}}
                    <div class="border-b border-gray-100 p-6 sm:p-8">
                        <div class="mb-5 flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-green-100 text-sm font-semibold text-green-700">1</span>
                            <h3 class="text-base font-semibold text-gray-800">Personal Information</h3>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label for="rsbsa_number" class="block text-sm font-medium text-gray-700">RSBSA Number</label>
                                <input type="text" name="rsbsa_number" id="rsbsa_number" value="{{ old('rsbsa_number') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('rsbsa_number') border-red-400 @enderror"
                                    placeholder="e.g. 02-14-01-002-000123">
                                @error('rsbsa_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="birth_date" class="block text-sm font-medium text-gray-700">Birth Date <span class="text-red-500">*</span></label>
                                <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('birth_date') border-red-400 @enderror">
                                @error('birth_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="sex" class="block text-sm font-medium text-gray-700">Sex <span class="text-red-500">*</span></label>
                                <select name="sex" id="sex"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('sex') border-red-400 @enderror">
                                    <option value="">Select sex</option>
                                    <option value="Male" @selected(old('sex') == 'Male')>Male</option>
                                    <option value="Female" @selected(old('sex') == 'Female')>Female</option>
                                </select>
                                @error('sex') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('first_name') border-red-400 @enderror">
                                @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
                                <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('middle_name') border-red-400 @enderror">
                                @error('middle_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('last_name') border-red-400 @enderror">
                                @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="suffix" class="block text-sm font-medium text-gray-700">Suffix</label>
                                <select name="suffix" id="suffix"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('suffix') border-red-400 @enderror">
                                    <option value="">None</option>
                                    @foreach (['Jr.', 'Sr.', 'II', 'III', 'IV'] as $suf)
                                        <option value="{{ $suf }}" @selected(old('suffix') == $suf)>{{ $suf }}</option>
                                    @endforeach
                                </select>
                                @error('suffix') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="civil_status" class="block text-sm font-medium text-gray-700">Civil Status <span class="text-red-500">*</span></label>
                                <select name="civil_status" id="civil_status"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('civil_status') border-red-400 @enderror">
                                    <option value="">Select status</option>
                                    @foreach (['Single', 'Married', 'Widowed', 'Separated', 'Divorced'] as $status)
                                        <option value="{{ $status }}" @selected(old('civil_status') == $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                                @error('civil_status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: Contact Information --}}
                    <div class="border-b border-gray-100 p-6 sm:p-8">
                        <div class="mb-5 flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-green-100 text-sm font-semibold text-green-700">2</span>
                            <h3 class="text-base font-semibold text-gray-800">Contact Information</h3>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                                <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('contact_number') border-red-400 @enderror"
                                    placeholder="09XX XXX XXXX">
                                @error('contact_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('email') border-red-400 @enderror"
                                    placeholder="name@example.com">
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: Address (Cascading AJAX) --}}
                    <div class="border-b border-gray-100 p-6 sm:p-8">
                        <div class="mb-5 flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-green-100 text-sm font-semibold text-green-700">3</span>
                            <h3 class="text-base font-semibold text-gray-800">Address</h3>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div>
                                <label for="province_id" class="block text-sm font-medium text-gray-700">Province <span class="text-red-500">*</span></label>
                                <select name="province_id" id="province_id"
                                    data-old="{{ old('province_id') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('province_id') border-red-400 @enderror">
                                    <option value="">Select province</option>
                                    @foreach ($provinces as $province)
                                        <option value="{{ $province->id }}" @selected(old('province_id') == $province->id)>{{ $province->name }}</option>
                                    @endforeach
                                </select>
                                @error('province_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="municipality_id" class="block text-sm font-medium text-gray-700">Municipality <span class="text-red-500">*</span></label>
                                <select name="municipality_id" id="municipality_id" disabled
                                    data-old="{{ old('municipality_id') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('municipality_id') border-red-400 @enderror">
                                    <option value="">Select province first</option>
                                </select>
                                @error('municipality_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="barangay_id" class="block text-sm font-medium text-gray-700">Barangay <span class="text-red-500">*</span></label>
                                <select name="barangay_id" id="barangay_id" disabled
                                    data-old="{{ old('barangay_id') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('barangay_id') border-red-400 @enderror">
                                    <option value="">Select municipality first</option>
                                </select>
                                @error('barangay_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <p id="locationHint" class="mt-2 hidden text-xs text-amber-600">Loading location options&hellip;</p>
                    </div>

                    {{-- SECTION: Farm Details --}}
                    <div class="p-6 sm:p-8">
                        <div class="mb-5 flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-green-100 text-sm font-semibold text-green-700">4</span>
                            <h3 class="text-base font-semibold text-gray-800">Farm Details</h3>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="farm_area" class="block text-sm font-medium text-gray-700">Farm Area (hectares)</label>
                                <div class="relative mt-1">
                                    <input type="number" step="0.01" min="0" name="farm_area" id="farm_area" value="{{ old('farm_area') }}"
                                        class="block w-full rounded-lg border-gray-300 pr-12 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('farm_area') border-red-400 @enderror"
                                        placeholder="0.00">
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-400">ha</span>
                                </div>
                                @error('farm_area') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="tenurial_status" class="block text-sm font-medium text-gray-700">Tenurial Status</label>
                                <select name="tenurial_status" id="tenurial_status"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm @error('tenurial_status') border-red-400 @enderror">
                                    <option value="">Select status</option>
                                    @foreach (['Owner', 'Tenant', 'Lessee', 'Farmworker/Laborer', 'Agrarian Reform Beneficiary'] as $tenure)
                                        <option value="{{ $tenure }}" @selected(old('tenurial_status') == $tenure)>{{ $tenure }}</option>
                                    @endforeach
                                </select>
                                @error('tenurial_status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Action bar --}}
                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-8">
                        <a href="{{ route('landing') }}"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 sm:w-auto">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 sm:w-auto">
                            Proceed to Survey
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{--
        Cascading Province -> Municipality -> Barangay
        Endpoints match: municipalities.byProvince -> /municipalities/{province}
                          barangays.byMunicipality  -> /barangays/{municipality}
    --}}
    @push('scripts')
    <script>
        (function () {
            const provinceSelect     = document.getElementById('province_id');
            const municipalitySelect = document.getElementById('municipality_id');
            const barangaySelect     = document.getElementById('barangay_id');
            const hint                = document.getElementById('locationHint');
            const registrationForm   = document.getElementById('registrationForm');

            const MUNICIPALITIES_URL = (provinceId) => `/municipalities/${provinceId}`;
            const BARANGAYS_URL      = (municipalityId) => `/barangays/${municipalityId}`;

            function resetSelect(select, placeholder, enable = false) {
                select.innerHTML = `<option value="">${placeholder}</option>`;
                select.disabled = !enable;
                select.classList.toggle('bg-gray-50', !enable);
            }

            function populateSelect(select, items, selectedId = null) {
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
                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!response.ok) throw new Error('Request failed');
                    return await response.json();
                } catch (err) {
                    console.error('Location lookup failed:', err);
                    return [];
                } finally {
                    hint.classList.add('hidden');
                }
            }

            async function loadMunicipalities(provinceId, selectedMunicipalityId = null) {
                resetSelect(municipalitySelect, 'Loading…', false);
                resetSelect(barangaySelect, 'Select municipality first', false);

                if (!provinceId) {
                    resetSelect(municipalitySelect, 'Select province first', false);
                    return;
                }

                const municipalities = await fetchJson(MUNICIPALITIES_URL(provinceId));
                resetSelect(municipalitySelect, 'Select municipality', true);
                populateSelect(municipalitySelect, municipalities, selectedMunicipalityId);

                if (selectedMunicipalityId) {
                    await loadBarangays(selectedMunicipalityId, barangaySelect.dataset.old);
                }
            }

            async function loadBarangays(municipalityId, selectedBarangayId = null) {
                resetSelect(barangaySelect, 'Loading…', false);

                if (!municipalityId) {
                    resetSelect(barangaySelect, 'Select municipality first', false);
                    return;
                }

                const barangays = await fetchJson(BARANGAYS_URL(municipalityId));
                resetSelect(barangaySelect, 'Select barangay', true);
                populateSelect(barangaySelect, barangays, selectedBarangayId);
            }

            provinceSelect.addEventListener('change', (e) => {
                loadMunicipalities(e.target.value);
            });

            municipalitySelect.addEventListener('change', (e) => {
                loadBarangays(e.target.value);
            });

            // Re-hydrate selections after a failed validation submit.
            document.addEventListener('DOMContentLoaded', () => {
                const oldProvince = provinceSelect.dataset.old;
                const oldMunicipality = municipalitySelect.dataset.old;
                if (oldProvince) {
                    loadMunicipalities(oldProvince, oldMunicipality);
                }
            });


            registrationForm.addEventListener('submit', () => {
                municipalitySelect.disabled = false;
                barangaySelect.disabled = false;
            });
        })();
    </script>
    @endpush
</x-app-layout>