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
@endphp

@section('content')
<form id="wizardStepForm" method="POST" action="{{ isset($farmer) ? route('surveys.step.store', ['farmer' => $farmer, 'step' => 7]) : url("/survey/local/{$uuid}/step/7") }}" novalidate>
    @csrf

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
                    <p class="text-sm text-gray-600">Crop Year 2024–2025 planting record.</p>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7">
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-[720px] w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-da-green-700 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Season</th>
                            <th class="px-4 py-3 text-left font-semibold">Crop</th>
                            <th class="px-4 py-3 text-left font-semibold">Variety</th>
                            <th class="px-4 py-3 text-left font-semibold">Area Planted (ha)</th>
                            <th class="px-4 py-3 text-left font-semibold">Yield (t)</th>
                            <th class="px-4 py-3 text-center font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody id="plantedRows" class="bg-white divide-y divide-gray-100">
                        @foreach ($savedPlanted as $index => $row)
                            <tr class="planted-row">
                                <td class="px-4 py-3">
                                    <select name="planted[{{ $index }}][season]"
                                            class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                                        <option value="" disabled {{ empty($row['season'] ?? null) ? 'selected' : '' }}>Select</option>
                                        <option value="dry" {{ ($row['season'] ?? '') == 'dry' ? 'selected' : '' }}>Dry Season</option>
                                        <option value="wet" {{ ($row['season'] ?? '') == 'wet' ? 'selected' : '' }}>Wet Season</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="planted[{{ $index }}][crop]" placeholder="e.g. Rice, Corn"
                                           value="{{ $row['crop'] ?? '' }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="planted[{{ $index }}][variety]" placeholder="e.g. NSIC Rc 216"
                                           value="{{ $row['variety'] ?? '' }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0" name="planted[{{ $index }}][area]"
                                           value="{{ $row['area'] ?? '' }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0" name="planted[{{ $index }}][yield]"
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

    function addPlantedRow() {
        const index = plantedIndex;
        const tbody = document.getElementById('plantedRows');
        const row = document.createElement('tr');
        row.className = 'planted-row';
        row.innerHTML = `
            <td class="px-4 py-3">
                <select name="planted[${index}][season]" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5">
                    <option value="" disabled selected>Select</option>
                    <option value="dry">Dry Season</option>
                    <option value="wet">Wet Season</option>
                </select>
            </td>
            <td class="px-4 py-3"><input type="text" name="planted[${index}][crop]" placeholder="e.g. Rice, Corn" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5"></td>
            <td class="px-4 py-3"><input type="text" name="planted[${index}][variety]" placeholder="e.g. NSIC Rc 216" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5"></td>
            <td class="px-4 py-3"><input type="number" step="0.01" min="0" name="planted[${index}][area]" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5"></td>
            <td class="px-4 py-3"><input type="number" step="0.01" min="0" name="planted[${index}][yield]" class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 py-2 px-2.5"></td>
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