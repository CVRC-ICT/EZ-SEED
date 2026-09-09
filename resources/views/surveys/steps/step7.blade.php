@extends('surveys.wizard', [
    'currentStep' => 7,
    'totalSteps' => 10,
    'stepTitle' => 'Varieties Planted (CY 2024–2025)',
])

@php
    $savedPlanted = old('planted', data_get($old_data, 'planted', []));
    if (empty($savedPlanted)) {
        $savedPlanted = [[]]; // always render at least one blank row
    }
    $savedPlanted = array_values($savedPlanted);

    // Crop dropdown options. Value format: "{crop}_{type}" e.g. "rice_hybrid".
    $cropOptions = [
        'rice_inbred' => 'Rice (Inbred)',
        'rice_hybrid' => 'Rice (Hybrid)',
        'corn_opv' => 'Corn (OPV)',
        'corn_hybrid' => 'Corn (Hybrid)',
    ];

    // Same variety lists used in Step 6, keyed the same way as the crop
    // dropdown values above so the JS can look them up directly.
    $varietyOptions = [
        'rice_hybrid' => [
            'PSB Rc 26H (Magat)', 'PSB Rc 72H (Mestizo 1)', 'PSB Rc 76H (Panay)',
            'NSIC Rc 114H (Mestizo 2)', 'NSIC Rc 116H (Mestizo 3)', 'NSIC Rc 124H (Mestizo 4)',
            'NSIC Rc 126H (Mestizo 5)', 'NSIC Rc 132H (Mestizo 6)', 'NSIC Rc 162H', 'NSIC Rc 164H',
            'NSIC Rc 202H (Mestiso 19)', 'NSIC Rc 204H (Mestiso 20)', 'NSIC Rc 240H (Mestiso 22)',
            'NSIC Rc 244H (Mestiso 29)', 'NSIC Rc 262H (Mestiso 38)', 'NSIC Rc 518H', 'NSIC Rc 522H',
            'NSIC Rc 550H', 'NSIC Rc 552H', 'NSIC Rc 586H', 'NSIC Rc 588H', 'NSIC Rc 614H',
            'NSIC Rc 616H', 'NSIC Rc 618H',
        ],
        'rice_inbred' => [
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
        'corn_hybrid' => [
            'CW 851', 'TSG 398', 'TSG 361', 'TSG 81', 'P30B80', 'P30T80', 'P3482YR', 'PAC 105',
            'Healer 101', 'P30D44', 'Bioseed 9899', 'Ghen 703', 'EG501', 'USM Var 35', 'Filipina 753',
            'Ghen 802', 'Farco 88',
            'DK8899S (GM Hybrid)', 'NK6130 BGT (GM Hybrid)', 'H101G (GM Hybrid)',
            'J505 (GM Hybrid)', 'DK9132RRYG (GM Hybrid)', 'DK9132RRYG2 (GM Hybrid)',
        ],
        'corn_opv' => [
            'IES Cn 5 (Yellow OPV)', 'IES Cn 7 (Yellow OPV)', 'IES 89-06 (White OPV)',
            'IES 89-10 (White OPV)', 'IES 89-12 (White OPV)', 'IES 09-02 (White OPV)',
            'IES Glut #2 (Glutinous OPV)', 'IES Glut #3 (Glutinous OPV)', 'IES Glut #4 (Glutinous OPV)',
            'IES Glut #6 (Glutinous OPV)', 'IES Glut #7 (Glutinous OPV)', 'Tupi 1 WIT', 'Tupi WIT',
            'Farco 58', 'IES Cn 1 (IES Var 7)', 'IES Cn 2 (IES Var 2)', 'IES Glut # 1', 'IES Cn 6',
            'IES E-02', 'IES Cn 3', 'IES Cn 4', 'IES 09-2', 'IES Cn 9', 'IES 10-04', 'IES Glut 8',
            'IES Glut 10', 'IES Cn 11', 'CVRC Cn 13', 'CVRC 12-06', 'CVRC Glut No. 12', 'CVRC Cn 15',
            'CVRC Glut No. 18-14', 'CVRC Glut 21-16',
        ],
    ];
@endphp

@section('content')
<form id="wizardStepForm" method="POST" action="{{ isset($farmer) ? route('surveys.step.store', ['farmer' => $farmer, 'step' => 7]) : url("/survey/local/{$uuid}/step/7") }}" novalidate>
    @csrf

    {{-- Variety dropdown suggestions, one datalist per crop+type combination --}}
    @foreach ($varietyOptions as $key => $options)
        <datalist id="plantedVarietyList_{{ $key }}">
            @foreach ($options as $option)
                <option value="{{ $option }}">
            @endforeach
        </datalist>
    @endforeach

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="bg-da-green-50 border-b border-da-green-100 px-6 sm:px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3.75m8.5-3.75l1 3.75m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Varieties Planted</h2>
                    <p class="text-sm text-gray-600">Crop Year 2024–2025 planting record. <span class="text-red-600 font-semibold">All fields required.</span></p>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7">
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-[760px] w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-da-green-700 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Season <span class="text-red-200">*</span></th>
                            <th class="px-4 py-3 text-left font-semibold">Crop <span class="text-red-200">*</span></th>
                            <th class="px-4 py-3 text-left font-semibold">Variety <span class="text-red-200">*</span></th>
                            <th class="px-4 py-3 text-left font-semibold">Area Planted (ha) <span class="text-red-200">*</span></th>
                            <th class="px-4 py-3 text-left font-semibold">Yield (t) <span class="text-red-200">*</span></th>
                            <th class="px-4 py-3 text-center font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody id="plantedRows" class="bg-white divide-y divide-gray-100">
                        @foreach ($savedPlanted as $index => $row)
                            @php
                                $savedCropKey = $row['crop'] ?? '';
                                $datalistId = $savedCropKey ? "plantedVarietyList_{$savedCropKey}" : null;
                            @endphp
                            <tr class="planted-row">
                                <td class="px-4 py-3">
                                    <select name="planted[{{ $index }}][season]" required
                                            class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                                        <option value="" disabled {{ empty($row['season'] ?? null) ? 'selected' : '' }}>Select</option>
                                        <option value="dry" {{ ($row['season'] ?? '') == 'dry' ? 'selected' : '' }}>Dry Season</option>
                                        <option value="wet" {{ ($row['season'] ?? '') == 'wet' ? 'selected' : '' }}>Wet Season</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    {{-- CHANGED: Crop is now a dropdown with the 4 crop+type combinations,
                                         instead of free text. Selecting one updates the Variety dropdown
                                         in this same row via updatePlantedVarietyList(). --}}
                                    <select name="planted[{{ $index }}][crop]" required onchange="updatePlantedVarietyList(this)"
                                            class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                                        <option value="" disabled {{ empty($savedCropKey) ? 'selected' : '' }}>Select</option>
                                        @foreach ($cropOptions as $key => $label)
                                            <option value="{{ $key }}" {{ $savedCropKey == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="planted[{{ $index }}][variety]" placeholder="Type or select a variety" required
                                           value="{{ $row['variety'] ?? '' }}"
                                           list="{{ $datalistId }}"
                                           class="planted-variety-input w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0" name="planted[{{ $index }}][area]" required
                                           value="{{ $row['area'] ?? '' }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0" name="planted[{{ $index }}][yield]" required
                                           value="{{ $row['yield'] ?? '' }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" onclick="removePlantedRow(this)"
                                            class="remove-planted-row {{ count($savedPlanted) <= 1 ? 'hidden' : '' }} text-red-500 hover:text-red-700 focus:outline-none" aria-label="Remove row">
                                        <svg class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-gray-500 md:hidden">Swipe sideways to see all columns.</p>

            @error('planted')
                <p class="mt-3 text-sm text-red-600 font-medium">{{ $message }}</p>
            @enderror

            <button type="button" onclick="addPlantedRow()"
                    class="mt-5 w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl border-2 border-dashed border-da-green-400 text-da-green-700 font-semibold hover:bg-da-green-50 focus:outline-none focus:ring-2 focus:ring-da-green-500 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Another Entry
            </button>
        </div>

        {{-- Navigation --}}
        <div class="bg-gray-50 border-t border-gray-200 px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ isset($farmer) ? route('surveys.step.show', ['farmer' => $farmer, 'step' => 6]) : url("/survey/local/{$uuid}/step/6") }}"
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
    let plantedIndex = document.querySelectorAll('.planted-row').length;

    // NEW: called when a row's Crop dropdown changes — points that row's
    // Variety input at the matching datalist (e.g. "rice_hybrid" ->
    // #plantedVarietyList_rice_hybrid), and clears any previously typed
    // variety name since it may not belong to the new crop/type.
    function updatePlantedVarietyList(select) {
        const row = select.closest('tr');
        const varietyInput = row.querySelector('.planted-variety-input');
        const cropKey = select.value;
        if (cropKey) {
            varietyInput.setAttribute('list', `plantedVarietyList_${cropKey}`);
        } else {
            varietyInput.removeAttribute('list');
        }
        varietyInput.value = '';
    }

    function addPlantedRow() {
        const index = plantedIndex;
        const tbody = document.getElementById('plantedRows');
        const row = document.createElement('tr');
        row.className = 'planted-row';
        row.innerHTML = `
            <td class="px-4 py-3">
                <select name="planted[${index}][season]" required class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                    <option value="" disabled selected>Select</option>
                    <option value="dry">Dry Season</option>
                    <option value="wet">Wet Season</option>
                </select>
            </td>
            <td class="px-4 py-3">
                <select name="planted[${index}][crop]" required onchange="updatePlantedVarietyList(this)" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                    <option value="" disabled selected>Select</option>
                    <option value="rice_inbred">Rice (Inbred)</option>
                    <option value="rice_hybrid">Rice (Hybrid)</option>
                    <option value="corn_opv">Corn (OPV)</option>
                    <option value="corn_hybrid">Corn (Hybrid)</option>
                </select>
            </td>
            <td class="px-4 py-3"><input type="text" name="planted[${index}][variety]" placeholder="Type or select a variety" required class="planted-variety-input w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5"></td>
            <td class="px-4 py-3"><input type="number" step="0.01" min="0" name="planted[${index}][area]" required class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5"></td>
            <td class="px-4 py-3"><input type="number" step="0.01" min="0" name="planted[${index}][yield]" required class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5"></td>
            <td class="px-4 py-3 text-center">
                <button type="button" onclick="removePlantedRow(this)" class="remove-planted-row text-red-500 hover:text-red-700 focus:outline-none" aria-label="Remove row">
                    <svg class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        plantedIndex++;
        updatePlantedRemoveButtons();
    }

    function removePlantedRow(button) {
        button.closest('tr').remove();
        updatePlantedRemoveButtons();
    }

    function updatePlantedRemoveButtons() {
        const rows = document.querySelectorAll('.planted-row');
        rows.forEach((row) => {
            row.querySelector('.remove-planted-row').classList.toggle('hidden', rows.length <= 1);
        });
    }

    updatePlantedRemoveButtons();
</script>

@if (isset($farmer))
@include('surveys.partials.offline-sync-init', [
    'step' => 7,
    'nextUrl' => route('surveys.step.show', ['farmer' => $farmer, 'step' => 8]),
    'hasServerData' => !empty($old_data),
])
@else
@include('surveys.partials.local-first-init', [
    'uuid' => $uuid,
    'step' => 7,
])
@endif
@endpush
@endsection