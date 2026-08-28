@extends('surveys.wizard', [
    'currentStep' => 4,
    'totalSteps' => 10,
    'stepTitle' => 'Training, Information Sources & Finance',
])

@php
    $savedTrainings = old('trainings_attended', data_get($old_data, 'trainings_attended', []));
    if (empty($savedTrainings)) {
        $savedTrainings = [[]];
    }
    $savedTrainings = array_values($savedTrainings);

    $savedFinanceSources = old('finance_sources', data_get($old_data, 'finance_sources', []));
    if (empty($savedFinanceSources)) {
        $savedFinanceSources = [[]];
    }
    $savedFinanceSources = array_values($savedFinanceSources);

    $incomeOptions = [
        'farming' => 'Farming',
        'livestock' => 'Livestock / Poultry Raising',
        'fishing' => 'Fishing',
        'business' => 'Business / Trading',
        'employment' => 'Employment (Salary/Wage)',
        'ofw_remittance' => 'OFW Remittance',
        'pension' => 'Pension',
        'others' => 'Others',
    ];

    $financeSourceOptions = [
        'banks' => 'Banks',
        'cooperative' => 'Cooperative / Association',
        'private' => 'Private',
        'own' => 'Own',
        'others' => 'Others',
    ];
@endphp

@section('content')
<form id="wizardStepForm" method="POST" action="{{ isset($farmer) ? route('surveys.step.store', ['farmer' => $farmer, 'step' => 4]) : url("/survey/local/{$uuid}/step/4") }}" novalidate>
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="bg-da-green-50 border-b border-da-green-100 px-6 sm:px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Training, Information Sources &amp; Finance</h2>
                    <p class="text-sm text-gray-600">Learning access, communication channels, and funding sources.</p>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7 space-y-8">

            {{-- Trainings Attended — repeatable table, matches "13. Trainings/Briefing" --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-2">Trainings / Briefing Attended (past 5 years)</h3>
                <p class="text-sm text-gray-500 mb-3">Leave blank and skip if none attended.</p>

                <div id="trainingRows" class="space-y-3">
                    @foreach ($savedTrainings as $index => $training)
                        <div class="training-row grid grid-cols-1 sm:grid-cols-[2fr_2fr_1fr_auto] gap-3 items-start">
                            <input type="text" name="trainings_attended[{{ $index }}][title]" placeholder="Title of Training"
                                   value="{{ $training['title'] ?? '' }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                            <input type="text" name="trainings_attended[{{ $index }}][conducted_by]" placeholder="Who Conducted"
                                   value="{{ $training['conducted_by'] ?? '' }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                            <input type="text" name="trainings_attended[{{ $index }}][month_year]" placeholder="Month/Year"
                                   value="{{ $training['month_year'] ?? '' }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                            <button type="button" onclick="removeTrainingRow(this)"
                                    class="remove-training-row {{ count($savedTrainings) <= 1 ? 'hidden' : '' }} text-red-500 hover:text-red-700 focus:outline-none px-2 py-2.5" aria-label="Remove row">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="addTrainingRow()"
                        class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-dashed border-da-green-400 text-da-green-700 text-sm font-semibold hover:bg-da-green-50 focus:outline-none focus:ring-2 focus:ring-da-green-500 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Training
                </button>
            </section>

            <hr class="border-gray-200">

            {{-- Information Sources --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    Sources of Farming Information <span class="text-red-600">*</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ([
                        'social_media' => 'Social Media',
                        'facebook' => 'Facebook',
                        'youtube' => 'Youtube',
                        'brochure' => 'Brochure / Pamphlets',
                        'radio' => 'Radio',
                        'television' => 'Television',
                        'co_farmer' => 'Co-farmer',
                        'da_rfo' => 'DA RFO',
                        'mlgu' => 'MLGU',
                        'plgu' => 'PLGU',
                        'private_company' => 'Private Company',
                        'fca' => 'FCA',
                    ] as $key => $label)
                        <label class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition">
                            <input type="checkbox" name="information_sources[]" value="{{ $key }}"
                                   {{ in_array($key, old('information_sources', data_get($old_data, 'information_sources', []))) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                            <span class="text-sm sm:text-base text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach

                    <label class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition sm:col-span-2">
                        <input type="checkbox" name="information_sources[]" value="others"
                               onchange="document.getElementById('infoSourcesOthersField').classList.toggle('hidden', !this.checked)"
                               {{ in_array('others', old('information_sources', data_get($old_data, 'information_sources', []))) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                        <span class="text-sm sm:text-base text-gray-700">Others (specify)</span>
                    </label>
                </div>
                @error('information_sources')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror

                <div id="infoSourcesOthersField" class="mt-4 {{ in_array('others', old('information_sources', data_get($old_data, 'information_sources', []))) ? '' : 'hidden' }}">
                    <label for="information_sources_others" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Please specify other source
                    </label>
                    <input type="text" id="information_sources_others" name="information_sources_others"
                           value="{{ old('information_sources_others', data_get($old_data, 'information_sources_others')) }}"
                           class="w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('information_sources_others') border-red-500 @enderror">
                    @error('information_sources_others')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="no_information_source_reason" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        If none selected above, why? (optional)
                    </label>
                    <input type="text" id="no_information_source_reason" name="no_information_source_reason"
                           value="{{ old('no_information_source_reason', data_get($old_data, 'no_information_source_reason')) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Preferred IEC Materials --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    Preferred IEC Materials <span class="text-red-600">*</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ([
                        'prints' => 'Prints',
                        'video' => 'Video',
                        'radio' => 'Radio',
                        'television' => 'Television',
                    ] as $key => $label)
                        <label class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition">
                            <input type="checkbox" name="preferred_iec_materials[]" value="{{ $key }}"
                                   {{ in_array($key, old('preferred_iec_materials', data_get($old_data, 'preferred_iec_materials', []))) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                            <span class="text-sm sm:text-base text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach

                    <label class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition sm:col-span-2">
                        <input type="checkbox" name="preferred_iec_materials[]" value="others"
                               onchange="document.getElementById('iecOthersField').classList.toggle('hidden', !this.checked)"
                               {{ in_array('others', old('preferred_iec_materials', data_get($old_data, 'preferred_iec_materials', []))) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                        <span class="text-sm sm:text-base text-gray-700">Others (specify)</span>
                    </label>
                </div>
                @error('preferred_iec_materials')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror

                <div id="iecOthersField" class="mt-4 {{ in_array('others', old('preferred_iec_materials', data_get($old_data, 'preferred_iec_materials', []))) ? '' : 'hidden' }}">
                    <label for="preferred_iec_materials_others" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Please specify other IEC material
                    </label>
                    <input type="text" id="preferred_iec_materials_others" name="preferred_iec_materials_others"
                           value="{{ old('preferred_iec_materials_others', data_get($old_data, 'preferred_iec_materials_others')) }}"
                           class="w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('preferred_iec_materials_others') border-red-500 @enderror">
                    @error('preferred_iec_materials_others')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Source of Income — Primary / Secondary, with cascading Farming breakdown --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-1">Source of Income</h3>
                <p class="text-sm text-gray-500 mb-4">Primary and secondary income sources</p>

                <div class="space-y-4">
                    <div class="bg-da-green-50 border border-da-green-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-da-green-800 mb-3">Primary Source of Income</h4>
                        <label for="income_source_primary" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Source Type <span class="text-red-600">*</span>
                        </label>
                        <select id="income_source_primary" name="income_source_primary" required
                                onchange="toggleFarmingIncome('primary', this.value)"
                                class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('income_source_primary') border-red-500 @enderror">
                            <option value="" disabled {{ old('income_source_primary', data_get($old_data, 'income_source_primary')) ? '' : 'selected' }}>Select primary source</option>
                            @foreach ($incomeOptions as $key => $label)
                                <option value="{{ $key }}" {{ old('income_source_primary', data_get($old_data, 'income_source_primary')) == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('income_source_primary')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror

                        <div id="farmingIncomePrimary" class="mt-4 bg-white rounded-xl p-4 space-y-4 {{ old('income_source_primary', data_get($old_data, 'income_source_primary')) === 'farming' ? '' : 'hidden' }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dry Season Income (₱)</label>
                                    <input type="number" step="0.01" min="0" name="income_primary_dry"
                                           value="{{ old('income_primary_dry', data_get($old_data, 'income_primary_dry')) }}" placeholder="0.00"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Wet Season Income (₱)</label>
                                    <input type="number" step="0.01" min="0" name="income_primary_wet"
                                           value="{{ old('income_primary_wet', data_get($old_data, 'income_primary_wet')) }}" placeholder="0.00"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estimated Monthly Income (₱)</label>
                                <input type="number" step="0.01" min="0" name="income_primary_monthly"
                                       value="{{ old('income_primary_monthly', data_get($old_data, 'income_primary_monthly')) }}" placeholder="0.00"
                                       class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-amber-800 mb-3">Secondary Source of Income</h4>
                        <label for="income_source_secondary" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Source Type
                        </label>
                        <select id="income_source_secondary" name="income_source_secondary"
                                onchange="toggleFarmingIncome('secondary', this.value)"
                                class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('income_source_secondary') border-red-500 @enderror">
                            <option value="" {{ old('income_source_secondary', data_get($old_data, 'income_source_secondary')) ? '' : 'selected' }}>Select secondary source (optional)</option>
                            @foreach ($incomeOptions as $key => $label)
                                <option value="{{ $key }}" {{ old('income_source_secondary', data_get($old_data, 'income_source_secondary')) == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('income_source_secondary')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror

                        <div id="farmingIncomeSecondary" class="mt-4 bg-white rounded-xl p-4 space-y-4 {{ old('income_source_secondary', data_get($old_data, 'income_source_secondary')) === 'farming' ? '' : 'hidden' }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dry Season Income (₱)</label>
                                    <input type="number" step="0.01" min="0" name="income_secondary_dry"
                                           value="{{ old('income_secondary_dry', data_get($old_data, 'income_secondary_dry')) }}" placeholder="0.00"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Wet Season Income (₱)</label>
                                    <input type="number" step="0.01" min="0" name="income_secondary_wet"
                                           value="{{ old('income_secondary_wet', data_get($old_data, 'income_secondary_wet')) }}" placeholder="0.00"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estimated Monthly Income (₱)</label>
                                <input type="number" step="0.01" min="0" name="income_secondary_monthly"
                                       value="{{ old('income_secondary_monthly', data_get($old_data, 'income_secondary_monthly')) }}" placeholder="0.00"
                                       class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Source of Finance — repeatable cards --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-1">
                    Source of Finance <span class="text-red-600">*</span>
                </h3>
                <p class="text-sm text-gray-500 mb-4">Financing sources for farming activities</p>

                <div id="financeRows" class="space-y-4">
                    @foreach ($savedFinanceSources as $index => $finance)
                        <div class="finance-row bg-amber-50 border border-amber-200 rounded-xl p-5">
                            <div class="flex items-center justify-between mb-3">
                                <span class="finance-row-label text-sm font-bold text-amber-800">Source #{{ $index + 1 }}</span>
                                <button type="button" onclick="removeFinanceRow(this)"
                                        class="remove-finance-row {{ count($savedFinanceSources) <= 1 ? 'hidden' : '' }} text-red-500 hover:text-red-700 text-sm font-semibold flex items-center gap-1 focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    Remove
                                </button>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Source <span class="text-red-600">*</span>
                                </label>
                                <select name="finance_sources[{{ $index }}][source]"
                                        class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                    <option value="" disabled {{ empty($finance['source'] ?? null) ? 'selected' : '' }}>Select finance source</option>
                                    @foreach ($financeSourceOptions as $key => $label)
                                        <option value="{{ $key }}" {{ ($finance['source'] ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Amount (₱)</label>
                                    <input type="number" step="0.01" min="0" name="finance_sources[{{ $index }}][amount]"
                                           value="{{ $finance['amount'] ?? '' }}" placeholder="0.00"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Interest Rate (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="finance_sources[{{ $index }}][interest]"
                                           value="{{ $finance['interest'] ?? '' }}" placeholder="0.00"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('finance_sources')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror

                <button type="button" onclick="addFinanceRow()"
                        class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-dashed border-amber-400 text-amber-700 text-sm font-semibold hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Source
                </button>
            </section>
        </div>

        {{-- Navigation --}}
        <div class="bg-gray-50 border-t border-gray-200 px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ isset($farmer) ? route('surveys.step.show', ['farmer' => $farmer, 'step' => 3]) : url("/survey/local/{$uuid}/step/3") }}"
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
    let trainingIndex = document.querySelectorAll('.training-row').length;

    function addTrainingRow() {
        const container = document.getElementById('trainingRows');
        const index = trainingIndex++;
        const row = document.createElement('div');
        row.className = 'training-row grid grid-cols-1 sm:grid-cols-[2fr_2fr_1fr_auto] gap-3 items-start';
        row.innerHTML = `
            <input type="text" name="trainings_attended[${index}][title]" placeholder="Title of Training"
                   class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
            <input type="text" name="trainings_attended[${index}][conducted_by]" placeholder="Who Conducted"
                   class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
            <input type="text" name="trainings_attended[${index}][month_year]" placeholder="Month/Year"
                   class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
            <button type="button" onclick="removeTrainingRow(this)"
                    class="remove-training-row text-red-500 hover:text-red-700 focus:outline-none px-2 py-2.5" aria-label="Remove row">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;
        container.appendChild(row);
        updateTrainingRemoveButtons();
    }

    function removeTrainingRow(button) {
        button.closest('.training-row').remove();
        updateTrainingRemoveButtons();
    }

    function updateTrainingRemoveButtons() {
        const rows = document.querySelectorAll('.training-row');
        rows.forEach((row) => {
            const btn = row.querySelector('.remove-training-row');
            btn.classList.toggle('hidden', rows.length <= 1);
        });
    }

    updateTrainingRemoveButtons();

    // --- Source of Finance repeatable rows ---
    let financeIndex = document.querySelectorAll('.finance-row').length;
    const financeOptionsHtml = `
        <option value="" disabled selected>Select finance source</option>
        <option value="banks">Banks</option>
        <option value="cooperative">Cooperative / Association</option>
        <option value="private">Private</option>
        <option value="own">Own</option>
        <option value="others">Others</option>
    `;

    function addFinanceRow() {
        const container = document.getElementById('financeRows');
        const index = financeIndex++;
        const row = document.createElement('div');
        row.className = 'finance-row bg-amber-50 border border-amber-200 rounded-xl p-5';
        row.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <span class="finance-row-label text-sm font-bold text-amber-800">Source #${index + 1}</span>
                <button type="button" onclick="removeFinanceRow(this)"
                        class="remove-finance-row text-red-500 hover:text-red-700 text-sm font-semibold flex items-center gap-1 focus:outline-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Remove
                </button>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Source <span class="text-red-600">*</span></label>
                <select name="finance_sources[${index}][source]" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                    ${financeOptionsHtml}
                </select>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Amount (\u20b1)</label>
                    <input type="number" step="0.01" min="0" name="finance_sources[${index}][amount]" placeholder="0.00"
                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Interest Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="finance_sources[${index}][interest]" placeholder="0.00"
                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                </div>
            </div>
        `;
        container.appendChild(row);
        updateFinanceRemoveButtons();
    }

    function removeFinanceRow(button) {
        button.closest('.finance-row').remove();
        renumberFinanceRows();
        updateFinanceRemoveButtons();
    }

    function renumberFinanceRows() {
        document.querySelectorAll('.finance-row').forEach((row, i) => {
            row.querySelector('.finance-row-label').textContent = `Source #${i + 1}`;
        });
    }

    function updateFinanceRemoveButtons() {
        const rows = document.querySelectorAll('.finance-row');
        rows.forEach((row) => {
            const btn = row.querySelector('.remove-finance-row');
            btn.classList.toggle('hidden', rows.length <= 1);
        });
    }

    updateFinanceRemoveButtons();

    // --- Farming income cascade ---
    function toggleFarmingIncome(which, value) {
        const el = document.getElementById(which === 'primary' ? 'farmingIncomePrimary' : 'farmingIncomeSecondary');
        el.classList.toggle('hidden', value !== 'farming');
    }
</script>

@if (isset($farmer))
@include('surveys.partials.offline-sync-init', [
    'step' => 4,
    'nextUrl' => route('surveys.step.show', ['farmer' => $farmer, 'step' => 5]),
    'hasServerData' => !empty($old_data),
])
@else
@include('surveys.partials.local-first-init', [
    'uuid' => $uuid,
    'step' => 4,
])
@endif
@endpush
@endsection