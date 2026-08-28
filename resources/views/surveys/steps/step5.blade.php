@extends('surveys.wizard', [
    'currentStep' => 5,
    'totalSteps' => 10,
    'stepTitle' => 'Seed Selection Criteria',
])

@section('content')
<form id="wizardStepForm" method="POST" action="{{ isset($farmer) ? route('surveys.step.store', ['farmer' => $farmer, 'step' => 5]) : url("/survey/local/{$uuid}/step/5") }}" novalidate>
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="bg-da-green-50 border-b border-da-green-100 px-6 sm:px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m6 2.25a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Seed Selection Criteria</h2>
                    <p class="text-sm text-gray-600">What matters most to the farmer when choosing seeds?</p>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7 space-y-6">
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    Select all criteria considered important <span class="text-red-600">*</span>
                </h3>
                {{-- FIX: wrapped with data-require-one so local-first-init.blade.php's
                     JS blocks "Save & Continue" if nothing here is checked.
                     Plain HTML `required` can't express "at least one checkbox
                     in this group", which is why this could be skipped before. --}}
                <div data-require-one="Please select at least one seed selection criterion.">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ([
                            'high_yield' => ['High Yielding Variety', 'M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h16.5A2.25 2.25 0 0022.5 19.5v-7.5a2.25 2.25 0 00-2.25-2.25H3.75A2.25 2.25 0 001.5 12v7.5a2.25 2.25 0 002.25 2.25z'],
                            'pest_resistant' => ['Resistance to Insect Pest', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'disease_resistant' => ['Resistance to Diseases', 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
                            'early_maturity' => ['Early Maturing', 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'good_milling_recovery' => ['Good Milling Recovery', 'M4.5 12.75l6 6 9-13.5'],
                            'market_demand' => ['Market Demand', 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V6'],
                            'good_eating_quality' => ['Good Eating Quality', 'M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.25 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z'],
                            'climate_adaptability' => ['Climate Adaptability', 'M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z'],
                        ] as $key => [$label, $icon])
                            <label class="flex items-center gap-3 bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-4 cursor-pointer hover:bg-da-green-50 hover:border-da-green-300 has-[:checked]:bg-da-green-50 has-[:checked]:border-da-green-600 transition">
                                <input type="checkbox" name="seed_criteria[]" value="{{ $key }}"
                                       {{ in_array($key, old('seed_criteria', data_get($old_data, 'seed_criteria', []))) ? 'checked' : '' }}
                                       class="w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                                <svg class="w-6 h-6 text-da-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                                </svg>
                                <span class="text-sm sm:text-base font-medium text-gray-800">{{ $label }}</span>
                            </label>
                        @endforeach

                        <label class="flex items-center gap-3 bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-4 cursor-pointer hover:bg-da-green-50 hover:border-da-green-300 has-[:checked]:bg-da-green-50 has-[:checked]:border-da-green-600 transition sm:col-span-2">
                            <input type="checkbox" id="criteria_others_check" name="seed_criteria[]" value="others"
                                   {{ in_array('others', old('seed_criteria', data_get($old_data, 'seed_criteria', []))) ? 'checked' : '' }}
                                   onchange="document.getElementById('criteriaOthersField').classList.toggle('hidden', !this.checked)"
                                   class="w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                            <span class="text-sm sm:text-base font-medium text-gray-800">Others (please specify)</span>
                        </label>
                    </div>
                </div>
                @error('seed_criteria')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror

                <div id="criteriaOthersField" class="mt-4 {{ in_array('others', old('seed_criteria', data_get($old_data, 'seed_criteria', []))) ? '' : 'hidden' }}">
                    <label for="seed_criteria_others" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Please specify other criteria
                    </label>
                    <input type="text" id="seed_criteria_others" name="seed_criteria_others"
                           value="{{ old('seed_criteria_others', data_get($old_data, 'seed_criteria_others')) }}"
                           class="w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('seed_criteria_others') border-red-500 @enderror">
                    @error('seed_criteria_others')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </section>
        </div>

        {{-- Navigation --}}
        <div class="bg-gray-50 border-t border-gray-200 px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ isset($farmer) ? route('surveys.step.show', ['farmer' => $farmer, 'step' => 4]) : url("/survey/local/{$uuid}/step/4") }}"
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
@if (isset($farmer))
@include('surveys.partials.offline-sync-init', [
    'step' => 5,
    'nextUrl' => route('surveys.step.show', ['farmer' => $farmer, 'step' => 6]),
    'hasServerData' => !empty($old_data),
])
@else
@include('surveys.partials.local-first-init', [
    'uuid' => $uuid,
    'step' => 5,
])
@endif
@endpush
@endsection
