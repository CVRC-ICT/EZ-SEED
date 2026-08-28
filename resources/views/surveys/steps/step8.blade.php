@extends('surveys.wizard', [
    'currentStep' => 8,
    'totalSteps' => 10,
    'stepTitle' => 'Government Seed Subsidy',
])

@php
    $savedSubsidyHistory = old('subsidy_history', data_get($old_data, 'subsidy_history', []));
    if (empty($savedSubsidyHistory)) {
        $savedSubsidyHistory = [[]];
    }
    $savedSubsidyHistory = array_values($savedSubsidyHistory);
@endphp

@section('content')
<form id="wizardStepForm" method="POST" action="{{ isset($farmer) ? route('surveys.step.store', ['farmer' => $farmer, 'step' => 8]) : url("/survey/local/{$uuid}/step/8") }}" novalidate>
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="bg-da-green-50 border-b border-da-green-100 px-6 sm:px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m-15.432 0A8.959 8.959 0 013 12c0-.778.099-1.533.284-2.253" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Government Seed Subsidy</h2>
                    <p class="text-sm text-gray-600">Part III of the questionnaire — applies to Rice and Corn farmers alike.</p>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7 space-y-8">

            {{-- Q1: Did you receive a seed subsidy --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    1. Did you receive a seed subsidy from the government? <span class="text-red-600">*</span>
                </h3>
                <div class="flex gap-4">
                    <label class="flex-1 sm:flex-none sm:w-40 relative">
                        <input type="radio" name="received_subsidy" value="yes" class="peer sr-only" id="received_subsidy_yes"
                               onchange="toggleSubsidyDetails(true)"
                               {{ old('received_subsidy', data_get($old_data, 'received_subsidy')) == 'yes' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center justify-center gap-2 transition">
                            <span class="font-semibold text-gray-800 text-base">Yes</span>
                        </div>
                    </label>
                    <label class="flex-1 sm:flex-none sm:w-40 relative">
                        <input type="radio" name="received_subsidy" value="no" class="peer sr-only" id="received_subsidy_no"
                               onchange="toggleSubsidyDetails(false)"
                               {{ old('received_subsidy', data_get($old_data, 'received_subsidy')) == 'no' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center justify-center gap-2 transition">
                            <span class="font-semibold text-gray-800 text-base">No</span>
                        </div>
                    </label>
                </div>
                <p class="mt-2 text-xs text-gray-500">If "No," the questions below about subsidy history and preferred variety may be left blank.</p>
                @error('received_subsidy')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </section>

            <div id="subsidyDetailsWrapper" class="{{ old('received_subsidy', data_get($old_data, 'received_subsidy')) == 'no' ? 'hidden' : '' }} space-y-8">

                <hr class="border-gray-200">

                {{-- Q2: Subsidy history table --}}
                <section>
                    <h3 class="text-base font-semibold text-gray-900 mb-2">
                        2. How many times did you receive seed subsidy, and from where?
                    </h3>
                    <p class="text-sm text-gray-500 mb-3">Add one row per year/season you received a subsidy.</p>

                    <div id="subsidyHistoryRows" class="space-y-4">
                        @foreach ($savedSubsidyHistory as $index => $entry)
                            <div class="subsidy-history-row bg-gray-50 border-2 border-gray-200 rounded-xl p-5 relative">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-bold text-da-green-800 bg-da-green-100 px-3 py-1 rounded-full row-number">Entry #{{ $index + 1 }}</span>
                                    <button type="button" onclick="removeSubsidyHistoryRow(this)" class="remove-subsidy-history-row {{ count($savedSubsidyHistory) <= 1 ? 'hidden' : '' }} text-red-500 text-sm font-semibold focus:outline-none">Remove</button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Year</label>
                                        <input type="number" name="subsidy_history[{{ $index }}][year]" min="2000" max="2100" placeholder="e.g. 2022"
                                               value="{{ $entry['year'] ?? '' }}"
                                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Season</label>
                                        <select name="subsidy_history[{{ $index }}][season]"
                                                class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                            <option value="" disabled {{ empty($entry['season'] ?? null) ? 'selected' : '' }}>Select</option>
                                            <option value="dry" {{ ($entry['season'] ?? '') == 'dry' ? 'selected' : '' }}>Dry Season</option>
                                            <option value="wet" {{ ($entry['season'] ?? '') == 'wet' ? 'selected' : '' }}>Wet Season</option>
                                        </select>
                                    </div>
                                </div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Source</label>
                                <div class="flex flex-wrap gap-3">
                                    @foreach (['da_rfo_02' => 'DA RFO 02', 'private' => 'Private', 'plgu' => 'PLGU', 'mlgu' => 'MLGU'] as $key => $label)
                                        <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3.5 py-2 cursor-pointer">
                                            <input type="checkbox" name="subsidy_history[{{ $index }}][source][]" value="{{ $key }}"
                                                   {{ in_array($key, $entry['source'] ?? []) ? 'checked' : '' }}
                                                   class="w-4 h-4 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600">
                                            <span class="text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                    <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3.5 py-2 cursor-pointer">
                                        <input type="checkbox" name="subsidy_history[{{ $index }}][source][]" value="others"
                                               {{ in_array('others', $entry['source'] ?? []) ? 'checked' : '' }}
                                               class="w-4 h-4 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600">
                                        <span class="text-sm text-gray-700">Others</span>
                                    </label>
                                </div>
                                <input type="text" name="subsidy_history[{{ $index }}][source_others]" placeholder="Specify other source"
                                       value="{{ $entry['source_others'] ?? '' }}"
                                       class="mt-3 w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                            </div>
                        @endforeach
                    </div>

                    <button type="button" onclick="addSubsidyHistoryRow()"
                            class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-dashed border-da-green-400 text-da-green-700 text-sm font-semibold hover:bg-da-green-50 focus:outline-none focus:ring-2 focus:ring-da-green-500 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Year/Season
                    </button>
                </section>

                <hr class="border-gray-200">

                {{-- Q3: Most common variety received/bought --}}
                <section>
                    <h3 class="text-base font-semibold text-gray-900 mb-3">
                        3. What is the most common variety of seed that you received or bought?
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Dry Season</p>
                            <div class="space-y-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-500 w-16 flex-shrink-0">Variety {{ $i }}</span>
                                        <input type="text" name="subsidy_variety_ds[{{ $i }}]"
                                               value="{{ old('subsidy_variety_ds.' . $i, data_get($old_data, 'subsidy_variety_ds.' . $i)) }}"
                                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-sm py-2 px-3">
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Wet Season</p>
                            <div class="space-y-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-500 w-16 flex-shrink-0">Variety {{ $i }}</span>
                                        <input type="text" name="subsidy_variety_ws[{{ $i }}]"
                                               value="{{ old('subsidy_variety_ws.' . $i, data_get($old_data, 'subsidy_variety_ws.' . $i)) }}"
                                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-sm py-2 px-3">
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </section>

                <hr class="border-gray-200">

                {{-- Q4: Did you plant/use it --}}
                <section>
                    <h3 class="text-base font-semibold text-gray-900 mb-3">
                        4. Did you plant/use the seed during the said season? <span class="text-red-600">*</span>
                    </h3>
                    <div class="flex gap-4">
                        <label class="flex-1 sm:flex-none sm:w-40 relative">
                            <input type="radio" name="used_subsidized_seed" value="yes" class="peer sr-only"
                                   onchange="document.getElementById('usedSeedReasonField').classList.add('hidden')"
                                   {{ old('used_subsidized_seed', data_get($old_data, 'used_subsidized_seed')) == 'yes' ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center justify-center gap-2 transition">
                                <span class="font-semibold text-gray-800 text-base">Yes</span>
                            </div>
                        </label>
                        <label class="flex-1 sm:flex-none sm:w-40 relative">
                            <input type="radio" name="used_subsidized_seed" value="no" class="peer sr-only"
                                   onchange="document.getElementById('usedSeedReasonField').classList.remove('hidden')"
                                   {{ old('used_subsidized_seed', data_get($old_data, 'used_subsidized_seed')) == 'no' ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center justify-center gap-2 transition">
                                <span class="font-semibold text-gray-800 text-base">No</span>
                            </div>
                        </label>
                    </div>
                    @error('used_subsidized_seed')
                        <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                    <div id="usedSeedReasonField" class="mt-4 {{ old('used_subsidized_seed', data_get($old_data, 'used_subsidized_seed')) == 'no' ? '' : 'hidden' }}">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">If No, why?</label>
                        <textarea name="used_subsidized_seed_reason" rows="2"
                                  class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">{{ old('used_subsidized_seed_reason', data_get($old_data, 'used_subsidized_seed_reason')) }}</textarea>
                    </div>
                </section>

                <hr class="border-gray-200">

                {{-- Q5: Preferred by traders/millers --}}
                <section>
                    <h3 class="text-base font-semibold text-gray-900 mb-3">
                        5. Is your variety planted preferred by traders/millers? <span class="text-red-600">*</span>
                    </h3>
                    <div class="flex gap-4">
                        <label class="flex-1 sm:flex-none sm:w-40 relative">
                            <input type="radio" name="preferred_by_traders" value="yes" class="peer sr-only"
                                   onchange="document.getElementById('tradersReasonField').classList.remove('hidden')"
                                   {{ old('preferred_by_traders', data_get($old_data, 'preferred_by_traders')) == 'yes' ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center justify-center gap-2 transition">
                                <span class="font-semibold text-gray-800 text-base">Yes</span>
                            </div>
                        </label>
                        <label class="flex-1 sm:flex-none sm:w-40 relative">
                            <input type="radio" name="preferred_by_traders" value="no" class="peer sr-only"
                                   onchange="document.getElementById('tradersReasonField').classList.add('hidden')"
                                   {{ old('preferred_by_traders', data_get($old_data, 'preferred_by_traders')) == 'no' ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center justify-center gap-2 transition">
                                <span class="font-semibold text-gray-800 text-base">No</span>
                            </div>
                        </label>
                    </div>
                    @error('preferred_by_traders')
                        <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                    <div id="tradersReasonField" class="mt-4 {{ old('preferred_by_traders', data_get($old_data, 'preferred_by_traders')) == 'yes' ? '' : 'hidden' }}">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">If Yes, why?</label>
                        <textarea name="preferred_by_traders_reason" rows="2"
                                  class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">{{ old('preferred_by_traders_reason', data_get($old_data, 'preferred_by_traders_reason')) }}</textarea>
                    </div>
                </section>

                <hr class="border-gray-200">

                {{-- Q6: Willing to accept other variety --}}
                <section>
                    <h3 class="text-base font-semibold text-gray-900 mb-3">
                        6. Are you willing to accept a new/other variety other than your preferred one? <span class="text-red-600">*</span>
                    </h3>
                    <div class="flex gap-4">
                        <label class="flex-1 sm:flex-none sm:w-40 relative">
                            <input type="radio" name="willing_to_accept_other_variety" value="yes" class="peer sr-only"
                                   {{ old('willing_to_accept_other_variety', data_get($old_data, 'willing_to_accept_other_variety')) == 'yes' ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center justify-center gap-2 transition">
                                <span class="font-semibold text-gray-800 text-base">Yes</span>
                            </div>
                        </label>
                        <label class="flex-1 sm:flex-none sm:w-40 relative">
                            <input type="radio" name="willing_to_accept_other_variety" value="no" class="peer sr-only"
                                   {{ old('willing_to_accept_other_variety', data_get($old_data, 'willing_to_accept_other_variety')) == 'no' ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center justify-center gap-2 transition">
                                <span class="font-semibold text-gray-800 text-base">No</span>
                            </div>
                        </label>
                    </div>
                    @error('willing_to_accept_other_variety')
                        <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Why? (either answer)</label>
                        <textarea name="willing_to_accept_other_variety_reason" rows="2"
                                  class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">{{ old('willing_to_accept_other_variety_reason', data_get($old_data, 'willing_to_accept_other_variety_reason')) }}</textarea>
                    </div>
                </section>

                <hr class="border-gray-200">

                {{-- Q7: Best variety from received seeds --}}
                <section>
                    <h3 class="text-base font-semibold text-gray-900 mb-3">
                        7. From the seeds you received from the government, what is the best variety?
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Dry Season</p>
                            <div class="space-y-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="best_variety_ds[{{ $i }}][variety]" placeholder="Variety"
                                               value="{{ old('best_variety_ds.' . $i . '.variety', data_get($old_data, 'best_variety_ds.' . $i . '.variety')) }}"
                                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-sm py-2 px-3">
                                        <input type="text" name="best_variety_ds[{{ $i }}][reason]" placeholder="Reason"
                                               value="{{ old('best_variety_ds.' . $i . '.reason', data_get($old_data, 'best_variety_ds.' . $i . '.reason')) }}"
                                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-sm py-2 px-3">
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Wet Season</p>
                            <div class="space-y-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="best_variety_ws[{{ $i }}][variety]" placeholder="Variety"
                                               value="{{ old('best_variety_ws.' . $i . '.variety', data_get($old_data, 'best_variety_ws.' . $i . '.variety')) }}"
                                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-sm py-2 px-3">
                                        <input type="text" name="best_variety_ws[{{ $i }}][reason]" placeholder="Reason"
                                               value="{{ old('best_variety_ws.' . $i . '.reason', data_get($old_data, 'best_variety_ws.' . $i . '.reason')) }}"
                                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-sm py-2 px-3">
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        Reasons: High Yield, Good Grain Quality, Resistance to Pest/Diseases, Adoptable in the Area,
                        Early Maturing, Low Cost, Resistance to Calamities, Palatability, Higher Market Price,
                        Available in the Area, Preferred by Traders.
                    </p>
                </section>

                <hr class="border-gray-200">

                {{-- Q7b: Problems encountered on subsidy --}}
                <section>
                    <h3 class="text-base font-semibold text-gray-900 mb-3">
                        Problems encountered on government seed subsidy
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ([
                            'poor_germination' => 'Poor Seed Germination',
                            'low_yield' => 'Low Yield',
                            'not_adoptable' => 'Not Adoptable in the Area',
                            'susceptible_pest' => 'Susceptible to Pest and Diseases',
                            'late_delivery' => 'Late Delivery',
                        ] as $key => $label)
                            <label class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" name="subsidy_problems[]" value="{{ $key }}"
                                       {{ in_array($key, old('subsidy_problems', data_get($old_data, 'subsidy_problems', []))) ? 'checked' : '' }}
                                       class="w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                                <span class="text-sm sm:text-base text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                        <label class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition">
                            <input type="checkbox" name="subsidy_problems[]" value="others"
                                   onchange="document.getElementById('subsidyProblemsOthersField').classList.toggle('hidden', !this.checked)"
                                   {{ in_array('others', old('subsidy_problems', data_get($old_data, 'subsidy_problems', []))) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                            <span class="text-sm sm:text-base text-gray-700">Others (specify)</span>
                        </label>
                    </div>
                    <div id="subsidyProblemsOthersField" class="mt-4 {{ in_array('others', old('subsidy_problems', data_get($old_data, 'subsidy_problems', []))) ? '' : 'hidden' }}">
                        <input type="text" name="subsidy_problems_others" placeholder="Specify other problem"
                               value="{{ old('subsidy_problems_others', data_get($old_data, 'subsidy_problems_others')) }}"
                               class="w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                    </div>
                </section>
            </div>

            <hr class="border-gray-200">

            {{-- Q8: Recommendation --}}
            <section>
                <label for="subsidy_recommendation" class="block text-base font-semibold text-gray-900 mb-2">
                    8. Recommendation to improve seed subsidy
                </label>
                <textarea id="subsidy_recommendation" name="subsidy_recommendation" rows="3"
                          class="w-full rounded-xl border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-3.5 px-4">{{ old('subsidy_recommendation', data_get($old_data, 'subsidy_recommendation')) }}</textarea>
            </section>

            <hr class="border-gray-200">

            {{-- Q9: Capable to buy own seed --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    9. Are you capable of buying your own seed once there is no subsidy given? <span class="text-red-600">*</span>
                </h3>
                <div class="flex gap-4">
                    <label class="flex-1 sm:flex-none sm:w-40 relative">
                        <input type="radio" name="capable_to_buy_own_seed" value="yes" class="peer sr-only"
                               {{ old('capable_to_buy_own_seed', data_get($old_data, 'capable_to_buy_own_seed')) == 'yes' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center justify-center gap-2 transition">
                            <span class="font-semibold text-gray-800 text-base">Yes</span>
                        </div>
                    </label>
                    <label class="flex-1 sm:flex-none sm:w-40 relative">
                        <input type="radio" name="capable_to_buy_own_seed" value="no" class="peer sr-only"
                               {{ old('capable_to_buy_own_seed', data_get($old_data, 'capable_to_buy_own_seed')) == 'no' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center justify-center gap-2 transition">
                            <span class="font-semibold text-gray-800 text-base">No</span>
                        </div>
                    </label>
                </div>
                @error('capable_to_buy_own_seed')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </section>
        </div>

        {{-- Navigation --}}
        <div class="bg-gray-50 border-t border-gray-200 px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ isset($farmer) ? route('surveys.step.show', ['farmer' => $farmer, 'step' => 7]) : url("/survey/local/{$uuid}/step/7") }}"
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
    function toggleSubsidyDetails(receivedSubsidy) {
        const wrapper = document.getElementById('subsidyDetailsWrapper');
        wrapper.classList.toggle('hidden', !receivedSubsidy);
    }

    let subsidyHistoryIndex = document.querySelectorAll('.subsidy-history-row').length;

    function addSubsidyHistoryRow() {
        const container = document.getElementById('subsidyHistoryRows');
        const index = subsidyHistoryIndex++;
        const row = document.createElement('div');
        row.className = 'subsidy-history-row bg-gray-50 border-2 border-gray-200 rounded-xl p-5 relative';
        row.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-bold text-da-green-800 bg-da-green-100 px-3 py-1 rounded-full row-number">Entry #${index + 1}</span>
                <button type="button" onclick="removeSubsidyHistoryRow(this)" class="remove-subsidy-history-row text-red-500 text-sm font-semibold focus:outline-none">Remove</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Year</label>
                    <input type="number" name="subsidy_history[${index}][year]" min="2000" max="2100" placeholder="e.g. 2022"
                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Season</label>
                    <select name="subsidy_history[${index}][season]" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                        <option value="" disabled selected>Select</option>
                        <option value="dry">Dry Season</option>
                        <option value="wet">Wet Season</option>
                    </select>
                </div>
            </div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Source</label>
            <div class="flex flex-wrap gap-3">
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3.5 py-2 cursor-pointer">
                    <input type="checkbox" name="subsidy_history[${index}][source][]" value="da_rfo_02" class="w-4 h-4 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600">
                    <span class="text-sm text-gray-700">DA RFO 02</span>
                </label>
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3.5 py-2 cursor-pointer">
                    <input type="checkbox" name="subsidy_history[${index}][source][]" value="private" class="w-4 h-4 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600">
                    <span class="text-sm text-gray-700">Private</span>
                </label>
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3.5 py-2 cursor-pointer">
                    <input type="checkbox" name="subsidy_history[${index}][source][]" value="plgu" class="w-4 h-4 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600">
                    <span class="text-sm text-gray-700">PLGU</span>
                </label>
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3.5 py-2 cursor-pointer">
                    <input type="checkbox" name="subsidy_history[${index}][source][]" value="mlgu" class="w-4 h-4 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600">
                    <span class="text-sm text-gray-700">MLGU</span>
                </label>
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3.5 py-2 cursor-pointer">
                    <input type="checkbox" name="subsidy_history[${index}][source][]" value="others" class="w-4 h-4 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600">
                    <span class="text-sm text-gray-700">Others</span>
                </label>
            </div>
            <input type="text" name="subsidy_history[${index}][source_others]" placeholder="Specify other source"
                   class="mt-3 w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
        `;
        container.appendChild(row);
        updateSubsidyHistoryRemoveButtons();
    }

    function removeSubsidyHistoryRow(button) {
        button.closest('.subsidy-history-row').remove();
        updateSubsidyHistoryRemoveButtons();
    }

    function updateSubsidyHistoryRemoveButtons() {
        const rows = document.querySelectorAll('.subsidy-history-row');
        rows.forEach((row) => {
            row.querySelector('.remove-subsidy-history-row').classList.toggle('hidden', rows.length <= 1);
        });
    }

    updateSubsidyHistoryRemoveButtons();
</script>

@if (isset($farmer))
@include('surveys.partials.offline-sync-init', [
    'step' => 8,
    'nextUrl' => route('surveys.step.show', ['farmer' => $farmer, 'step' => 9]),
    'hasServerData' => !empty($old_data),
])
@else
@include('surveys.partials.local-first-init', [
    'uuid' => $uuid,
    'step' => 8,
])
@endif
@endpush
@endsection