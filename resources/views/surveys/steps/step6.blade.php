@extends('surveys.wizard', [
    'currentStep' => 6,
    'totalSteps' => 10,
    'stepTitle' => 'Seed Variety Preference',
])

@php
    // NEW STRUCTURE: Crop (Rice/Corn) -> Season (Dry/Wet) -> Seed Type ->
    // up to 5 variety cards. Rice's seed types are Hybrid/Inbred; Corn's
    // are Hybrid/OPV. Data is stored as preferences[crop][season][type][index].
    // The seed type KEY used in form field names / payload is always
    // 'hybrid' or 'inbred' for Rice, and 'hybrid' or 'opv' for Corn.
    $structure = [
        'rice' => [
            'label' => 'Rice',
            'icon' => '🌾',
            'seasons' => [
                'dry' => [
                    'label' => 'Dry Season',
                    'types' => ['hybrid' => 'Hybrid', 'inbred' => 'Inbred'],
                ],
                'wet' => [
                    'label' => 'Wet Season',
                    'types' => ['hybrid' => 'Hybrid', 'inbred' => 'Inbred'],
                ],
            ],
        ],
        'corn' => [
            'label' => 'Corn',
            'icon' => '🌽',
            'seasons' => [
                'dry' => [
                    'label' => 'Dry Season',
                    'types' => ['hybrid' => 'Hybrid', 'opv' => 'OPV'],
                ],
                'wet' => [
                    'label' => 'Wet Season',
                    'types' => ['hybrid' => 'Hybrid', 'opv' => 'OPV'],
                ],
            ],
        ],
    ];

    $savedPreferences = old('preferences', data_get($old_data, 'preferences', []));

    $reasonOptions = [
        'high_yield' => 'High Yield',
        'good_grain_quality' => 'Good Grain Quality',
        'pest_resistant' => 'Resistance to Insect Pest',
        'disease_resistant' => 'Resistance to Disease',
        'adaptable' => 'Adaptable in Area',
        'early_maturing' => 'Early Maturing',
        'low_cost' => 'Low Cost',
        'higher_market_price' => 'Higher Market Price',
        'calamity_resistant' => 'Resistance to Calamities',
        'available_supply' => 'Available Seed Supply',
        'preferred_by_traders' => 'Preferred by Traders',
        'others' => 'Others',
    ];

    $problemOptions = [
        'pest_susceptible' => 'Susceptible to Insect Pest',
        'disease_susceptible' => 'Susceptible to Disease',
        'poor_germination' => 'Poor Germination',
        'costly' => 'Costly',
        'poor_milling_recovery' => 'Poor Milling Recovery',
        'not_available' => 'Not Available',
        'none' => 'None',
        'others' => 'Others',
    ];

    // Variety Name dropdown suggestions, keyed by crop + seed type key.
    // OPV corn varieties live under corn.opv; everything else unchanged
    // from the previous list.
    $varietyOptions = [
        'rice' => [
            'hybrid' => [
                'PSB Rc 26H (Magat)', 'PSB Rc 72H (Mestizo 1)', 'PSB Rc 76H (Panay)',
                'NSIC Rc 114H (Mestizo 2)', 'NSIC Rc 116H (Mestizo 3)', 'NSIC Rc 124H (Mestizo 4)',
                'NSIC Rc 126H (Mestizo 5)', 'NSIC Rc 132H (Mestizo 6)', 'NSIC Rc 162H', 'NSIC Rc 164H',
                'NSIC Rc 202H (Mestiso 19)', 'NSIC Rc 204H (Mestiso 20)', 'NSIC Rc 240H (Mestiso 22)',
                'NSIC Rc 244H (Mestiso 29)', 'NSIC Rc 262H (Mestiso 38)', 'NSIC Rc 518H', 'NSIC Rc 522H',
                'NSIC Rc 550H', 'NSIC Rc 552H', 'NSIC Rc 586H', 'NSIC Rc 588H', 'NSIC Rc 614H',
                'NSIC Rc 616H', 'NSIC Rc 618H',
            ],
            'inbred' => [
                'NSIC Rc 18', 'NSIC Rc 160', 'NSIC Rc 216', 'NSIC Rc 218', 'NSIC Rc 222', 'NSIC Rc 238',
                'NSIC Rc 300', 'NSIC Rc 302', 'NSIC Rc 308', 'NSIC Rc 352', 'NSIC Rc 354 (Tubigan 28)',
                'NSIC Rc 356', 'NSIC Rc 358 (Tubigan 30)', 'NSIC Rc 394', 'NSIC Rc 400', 'NSIC Rc 402',
                'NSIC Rc 480', 'NSIC Rc 506', 'NSIC Rc 508', 'NSIC Rc 512', 'NSIC Rc 534', 'NSIC Rc 558',
                'NSIC Rc 560', 'NSIC Rc 562', 'NSIC Rc 564', 'NSIC Rc 568', 'NSIC Rc 572', 'NSIC Rc 574',
                'NSIC Rc 578', 'NSIC Rc 580', 'NSIC Rc 582', 'NSIC Rc 584', 'NSIC Rc 592', 'NSIC Rc 594',
                'NSIC Rc 600', 'NSIC Rc 602', 'NSIC Rc 604', 'NSIC Rc 622', 'NSIC Rc 624', 'NSIC Rc 626',
                'NSIC Rc 628', 'NSIC Rc 630', 'NSIC Rc 632', 'NSIC Rc 634', 'NSIC Rc 636',
                'NSIC Rc 638 SR (Special-purpose/pigmented)', 'NSIC Rc 640 SR (Special-purpose/pigmented)',
                'NSIC Rc 642 SR (Special-purpose/pigmented)', 'NSIC Rc 644 SR (Special-purpose/pigmented)',
                'NSIC Rc 646 SR (Special-purpose/pigmented)', 'NSIC Rc 648 (Zinc-biofortified)',
                'NSIC Rc 650 (Rainfed lowland)',
            ],
        ],
        'corn' => [
            'hybrid' => [
                'CW 851', 'TSG 398', 'TSG 361', 'TSG 81', 'P30B80', 'P30T80', 'P3482YR', 'PAC 105',
                'Healer 101', 'P30D44', 'Bioseed 9899', 'Ghen 703', 'EG501', 'USM Var 35', 'Filipina 753',
                'Ghen 802', 'Farco 88',
                'DK8899S (GM Hybrid)', 'NK6130 BGT (GM Hybrid)', 'H101G (GM Hybrid)',
                'J505 (GM Hybrid)', 'DK9132RRYG (GM Hybrid)', 'DK9132RRYG2 (GM Hybrid)',
            ],
            'opv' => [
                'IES Cn 5 (Yellow OPV)', 'IES Cn 7 (Yellow OPV)', 'IES 89-06 (White OPV)',
                'IES 89-10 (White OPV)', 'IES 89-12 (White OPV)', 'IES 09-02 (White OPV)',
                'IES Glut #2 (Glutinous OPV)', 'IES Glut #3 (Glutinous OPV)', 'IES Glut #4 (Glutinous OPV)',
                'IES Glut #6 (Glutinous OPV)', 'IES Glut #7 (Glutinous OPV)', 'Tupi 1 WIT', 'Tupi WIT',
                'Farco 58', 'IES Cn 1 (IES Var 7)', 'IES Cn 2 (IES Var 2)', 'IES Glut # 1', 'IES Cn 6',
                'IES E-02', 'IES Cn 3', 'IES Cn 4', 'IES 09-2', 'IES Cn 9', 'IES 10-04', 'IES Glut 8',
                'IES Glut 10', 'IES Cn 11', 'CVRC Cn 13', 'CVRC 12-06', 'CVRC Glut No. 12', 'CVRC Cn 15',
                'CVRC Glut No. 18-14', 'CVRC Glut 21-16',
            ],
        ],
    ];

    // Which crop tab should be active/open on load: whichever crop
    // actually has saved data, defaulting to Rice.
    $activeCrop = 'rice';
    foreach (['rice', 'corn'] as $c) {
        foreach (['dry', 'wet'] as $s) {
            foreach (array_keys($structure[$c]['seasons'][$s]['types']) as $t) {
                if (! empty(data_get($savedPreferences, "{$c}.{$s}.{$t}"))) {
                    $activeCrop = $c;
                }
            }
        }
    }
@endphp

@section('content')
<form id="wizardStepForm" method="POST" action="{{ isset($farmer) ? route('surveys.step.store', ['farmer' => $farmer, 'step' => 6]) : url("/survey/local/{$uuid}/step/6") }}" novalidate>
    @csrf

    {{-- Datalists, one per crop + seed type combination --}}
    @foreach ($varietyOptions as $crop => $types)
        @foreach ($types as $typeKey => $options)
            <datalist id="varietyList_{{ $crop }}_{{ $typeKey }}">
                @foreach ($options as $option)
                    <option value="{{ $option }}">
                @endforeach
            </datalist>
        @endforeach
    @endforeach

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="bg-da-green-50 border-b border-da-green-100 px-6 sm:px-8 py-5">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Seed Variety Preference</h2>
                        <p class="text-sm text-gray-600">Optional — add up to 5 varieties per combination you've grown.</p>
                    </div>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7">

            {{-- Crop Tabs: Rice / Corn --}}
            <div class="flex gap-2 border-b border-gray-200 mb-6" role="tablist">
                <button type="button" role="tab" data-crop-tab="rice"
                        class="crop-tab-btn flex-1 sm:flex-none px-6 py-3 text-sm sm:text-base font-bold rounded-t-xl border-b-2 transition"
                        onclick="switchCropTab('rice')">
                    🌾 Rice
                </button>
                <button type="button" role="tab" data-crop-tab="corn"
                        class="crop-tab-btn flex-1 sm:flex-none px-6 py-3 text-sm sm:text-base font-bold rounded-t-xl border-b-2 transition"
                        onclick="switchCropTab('corn')">
                    🌽 Corn
                </button>
            </div>

            @foreach ($structure as $crop => $cropData)
                <div id="cropPanel_{{ $crop }}" class="crop-panel space-y-4" data-crop="{{ $crop }}">
                    @foreach ($cropData['seasons'] as $season => $seasonData)
                        @php
                            $seasonHasData = false;
                            foreach (array_keys($seasonData['types']) as $t) {
                                if (! empty(data_get($savedPreferences, "{$crop}.{$season}.{$t}"))) {
                                    $seasonHasData = true;
                                }
                            }
                        @endphp
                        <details class="group rounded-xl border border-gray-200 overflow-hidden" {{ $seasonHasData ? 'open' : '' }}>
                            <summary class="cursor-pointer select-none list-none flex items-center justify-between px-5 py-4 bg-gray-50">
                                <span class="text-base font-bold text-gray-800">
                                    {{ $season === 'dry' ? '☀️' : '🌧️' }} {{ $seasonData['label'] }}
                                </span>
                                <svg class="w-5 h-5 text-gray-500 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </summary>

                            <div class="px-5 py-5 bg-white space-y-4">
                                @foreach ($seasonData['types'] as $typeKey => $typeLabel)
                                    @php
                                        $entries = data_get($savedPreferences, "{$crop}.{$season}.{$typeKey}", []);
                                        $entries = array_values($entries);
                                        $hasData = collect($entries)->filter(fn ($e) => ! empty($e['variety'] ?? null))->isNotEmpty();
                                        $isAmber = $season === 'dry';
                                        $datalistId = "varietyList_{$crop}_{$typeKey}";
                                    @endphp
                                    <details class="group/type rounded-lg border {{ $isAmber ? 'border-amber-200' : 'border-da-green-200' }} overflow-hidden" {{ $hasData ? 'open' : '' }}>
                                        <summary class="cursor-pointer select-none list-none flex items-center justify-between px-4 py-3 {{ $isAmber ? 'bg-amber-50' : 'bg-da-green-50' }}">
                                            <span class="text-sm font-bold {{ $isAmber ? 'text-amber-800' : 'text-da-green-800' }}">🌱 {{ $typeLabel }}</span>
                                            <svg class="w-4 h-4 {{ $isAmber ? 'text-amber-600' : 'text-da-green-600' }} transition-transform group-open/type:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </summary>

                                        <div class="px-4 py-4 bg-white">
                                            <div id="varietyCards_{{ $crop }}_{{ $season }}_{{ $typeKey }}" class="space-y-4"
                                                 data-crop="{{ $crop }}" data-season="{{ $season }}" data-type="{{ $typeKey }}">
                                                @foreach ($entries as $index => $entry)
                                                    @if (! empty($entry['variety'] ?? null))
                                                        <div class="variety-card bg-gray-50 border-2 border-gray-200 rounded-xl p-5">
                                                            <div class="flex items-center justify-between mb-4">
                                                                <span class="variety-card-number text-sm font-bold text-gray-800">Variety #{{ $index + 1 }}</span>
                                                                <button type="button" onclick="removeVarietyCard(this)"
                                                                        class="remove-variety-btn text-red-500 hover:text-red-700 text-sm font-semibold flex items-center gap-1 focus:outline-none">
                                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                                    </svg>
                                                                    Remove
                                                                </button>
                                                            </div>

                                                            <div class="space-y-4">
                                                                <div>
                                                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Variety Name</label>
                                                                    <input type="text" name="preferences[{{ $crop }}][{{ $season }}][{{ $typeKey }}][{{ $index }}][variety]"
                                                                           value="{{ $entry['variety'] ?? '' }}" placeholder="Type or select a variety"
                                                                           list="{{ $datalistId }}"
                                                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Source of Information</label>
                                                                    <input type="text" name="preferences[{{ $crop }}][{{ $season }}][{{ $typeKey }}][{{ $index }}][source_of_information]"
                                                                           value="{{ $entry['source_of_information'] ?? '' }}" placeholder="Where did you learn about this variety?"
                                                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                                                </div>
                                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                                    <div>
                                                                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Actual Yield (tons/ha)</label>
                                                                        <input type="number" step="0.01" min="0" name="preferences[{{ $crop }}][{{ $season }}][{{ $typeKey }}][{{ $index }}][actual_yield]"
                                                                               value="{{ $entry['actual_yield'] ?? '' }}" placeholder="0.00"
                                                                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Area Planted (ha)</label>
                                                                        <input type="number" step="0.01" min="0" name="preferences[{{ $crop }}][{{ $season }}][{{ $typeKey }}][{{ $index }}][area_planted]"
                                                                               value="{{ $entry['area_planted'] ?? '' }}" placeholder="0.00"
                                                                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Maturity (Days)</label>
                                                                    <input type="number" min="0" name="preferences[{{ $crop }}][{{ $season }}][{{ $typeKey }}][{{ $index }}][maturity_days]"
                                                                           value="{{ $entry['maturity_days'] ?? '' }}" placeholder="Number of days"
                                                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                                                                </div>

                                                                <div class="bg-da-green-50 border border-da-green-200 rounded-xl p-4">
                                                                    <h4 class="text-sm font-bold text-da-green-800 mb-3">Reasons for Choosing Variety</h4>
                                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                                        @foreach ($reasonOptions as $rKey => $rLabel)
                                                                            <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50 transition">
                                                                                <input type="checkbox" name="preferences[{{ $crop }}][{{ $season }}][{{ $typeKey }}][{{ $index }}][reasons][]" value="{{ $rKey }}"
                                                                                       {{ in_array($rKey, $entry['reasons'] ?? []) ? 'checked' : '' }}
                                                                                       class="w-4 h-4 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                                                                                <span class="text-sm text-gray-700">{{ $rLabel }}</span>
                                                                            </label>
                                                                        @endforeach
                                                                    </div>
                                                                </div>

                                                                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                                                    <h4 class="text-sm font-bold text-red-800 mb-3">Problems Encountered</h4>
                                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                                        @foreach ($problemOptions as $pKey => $pLabel)
                                                                            <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50 transition">
                                                                                <input type="checkbox" name="preferences[{{ $crop }}][{{ $season }}][{{ $typeKey }}][{{ $index }}][problems][]" value="{{ $pKey }}"
                                                                                       {{ in_array($pKey, $entry['problems'] ?? []) ? 'checked' : '' }}
                                                                                       class="w-4 h-4 rounded border-gray-400 text-red-600 focus:ring-red-600 flex-shrink-0">
                                                                                <span class="text-sm text-gray-700">{{ $pLabel }}</span>
                                                                            </label>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>

                                            <button type="button" onclick="addVarietyCard('{{ $crop }}', '{{ $season }}', '{{ $typeKey }}')"
                                                    class="add-variety-btn mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-dashed {{ $isAmber ? 'border-amber-400 text-amber-700 hover:bg-amber-50' : 'border-da-green-400 text-da-green-700 hover:bg-da-green-50' }} text-sm font-semibold focus:outline-none transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                                Add Variety (Max 5)
                                            </button>
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            @endforeach

            @error('preferences')
                <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Navigation --}}
        <div class="bg-gray-50 border-t border-gray-200 px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ isset($farmer) ? route('surveys.step.show', ['farmer' => $farmer, 'step' => 5]) : url("/survey/local/{$uuid}/step/5") }}"
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

@push('styles')
<style>
    .crop-tab-btn { color: #6b7280; border-color: transparent; }
    .crop-tab-btn.active-crop-tab { color: #156015; border-color: #1a7a1a; background: #f0f9f0; }
    .crop-panel.hidden-crop-panel { display: none; }
</style>
@endpush

@push('scripts')
<script>
    const reasonOptions = @json($reasonOptions);
    const problemOptions = @json($problemOptions);
    const activeCropOnLoad = @json($activeCrop);

    const groupCounters = {};
    document.querySelectorAll('[id^="varietyCards_"]').forEach((el) => {
        const key = `${el.dataset.crop}_${el.dataset.season}_${el.dataset.type}`;
        groupCounters[key] = el.querySelectorAll('.variety-card').length;
    });

    // --- Crop tab switching. Data for both tabs stays in the DOM (just
    // hidden via CSS), so nothing is lost when switching back and forth. ---
    function switchCropTab(crop) {
        document.querySelectorAll('.crop-panel').forEach((panel) => {
            panel.classList.toggle('hidden-crop-panel', panel.dataset.crop !== crop);
        });
        document.querySelectorAll('.crop-tab-btn').forEach((btn) => {
            btn.classList.toggle('active-crop-tab', btn.dataset.cropTab === crop);
        });
    }
    switchCropTab(activeCropOnLoad);

    function buildCheckboxGrid(options, crop, season, type, index, field, colorClass) {
        return Object.entries(options).map(([key, label]) => `
            <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50 transition">
                <input type="checkbox" name="preferences[${crop}][${season}][${type}][${index}][${field}][]" value="${key}"
                       class="w-4 h-4 rounded border-gray-400 ${colorClass} flex-shrink-0">
                <span class="text-sm text-gray-700">${label}</span>
            </label>
        `).join('');
    }

    function addVarietyCard(crop, season, type) {
        const containerId = `varietyCards_${crop}_${season}_${type}`;
        const container = document.getElementById(containerId);
        const currentCount = container.querySelectorAll('.variety-card').length;

        if (currentCount >= 5) {
            alert('You can add a maximum of 5 varieties per combination.');
            return;
        }

        const key = `${crop}_${season}_${type}`;
        const index = (groupCounters[key] ?? 0);
        groupCounters[key] = index + 1;

        const datalistId = `varietyList_${crop}_${type}`;
        const isAmber = season === 'dry';

        const card = document.createElement('div');
        card.className = 'variety-card bg-gray-50 border-2 border-gray-200 rounded-xl p-5';
        card.innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <span class="variety-card-number text-sm font-bold text-gray-800">Variety #${currentCount + 1}</span>
                <button type="button" onclick="removeVarietyCard(this)"
                        class="remove-variety-btn text-red-500 hover:text-red-700 text-sm font-semibold flex items-center gap-1 focus:outline-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Remove
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Variety Name</label>
                    <input type="text" name="preferences[${crop}][${season}][${type}][${index}][variety]" placeholder="Type or select a variety"
                           list="${datalistId}"
                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Source of Information</label>
                    <input type="text" name="preferences[${crop}][${season}][${type}][${index}][source_of_information]" placeholder="Where did you learn about this variety?"
                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Actual Yield (tons/ha)</label>
                        <input type="number" step="0.01" min="0" name="preferences[${crop}][${season}][${type}][${index}][actual_yield]" placeholder="0.00"
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Area Planted (ha)</label>
                        <input type="number" step="0.01" min="0" name="preferences[${crop}][${season}][${type}][${index}][area_planted]" placeholder="0.00"
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Maturity (Days)</label>
                    <input type="number" min="0" name="preferences[${crop}][${season}][${type}][${index}][maturity_days]" placeholder="Number of days"
                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5">
                </div>
                <div class="bg-da-green-50 border border-da-green-200 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-da-green-800 mb-3">Reasons for Choosing Variety</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        ${buildCheckboxGrid(reasonOptions, crop, season, type, index, 'reasons', 'text-da-green-600 focus:ring-da-green-600')}
                    </div>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-red-800 mb-3">Problems Encountered</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        ${buildCheckboxGrid(problemOptions, crop, season, type, index, 'problems', 'text-red-600 focus:ring-red-600')}
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
        updateVarietyButtons(containerId);
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function removeVarietyCard(button) {
        const card = button.closest('.variety-card');
        const container = card.closest('[id^="varietyCards_"]');
        card.remove();
        renumberVarietyCards(container);
        updateVarietyButtons(container.id);
    }

    function renumberVarietyCards(container) {
        container.querySelectorAll('.variety-card').forEach((card, i) => {
            card.querySelector('.variety-card-number').textContent = `Variety #${i + 1}`;
        });
    }

    function updateVarietyButtons(containerId) {
        const container = document.getElementById(containerId);
        const cards = container.querySelectorAll('.variety-card');
        cards.forEach((card) => {
            const btn = card.querySelector('.remove-variety-btn');
            btn.classList.toggle('hidden', cards.length <= 1);
        });

        const addBtn = container.parentElement.querySelector('.add-variety-btn');
        if (addBtn) {
            addBtn.classList.toggle('hidden', cards.length >= 5);
        }
    }

    document.querySelectorAll('[id^="varietyCards_"]').forEach((el) => updateVarietyButtons(el.id));
</script>

@if (isset($farmer))
@include('surveys.partials.offline-sync-init', [
    'step' => 6,
    'nextUrl' => route('surveys.step.show', ['farmer' => $farmer, 'step' => 7]),
    'hasServerData' => !empty($old_data),
])
@else
@include('surveys.partials.local-first-init', [
    'uuid' => $uuid,
    'step' => 6,
])
@endif
@endpush
@endsection
