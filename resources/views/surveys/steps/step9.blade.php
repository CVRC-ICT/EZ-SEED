@extends('surveys.wizard', [
    'currentStep' => 9,
    'totalSteps' => 10,
    'stepTitle' => 'Issues and Recommendations',
])

@section('content')
<form id="wizardStepForm" method="POST" action="{{ isset($farmer) ? route('surveys.step.store', ['farmer' => $farmer, 'step' => 9]) : url("/survey/local/{$uuid}/step/9") }}" novalidate>
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="bg-da-green-50 border-b border-da-green-100 px-6 sm:px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Issues and Recommendations</h2>
                    <p class="text-sm text-gray-600">Share the farmer's concerns and suggestions.</p>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7 space-y-8">

            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-2">
                    Problems Encountered <span class="text-red-600">*</span>
                </h3>
                <p class="text-sm text-gray-500 mb-3">
                    Select all difficulties the farmer experienced related to seed access, farming practices, or government assistance.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ([
                        'Pest & Disease Infestation',
                        'Lack of Irrigation Water',
                        'High Input Costs',
                        'Insufficient Capital',
                        'Limited Market Access',
                        'Typhoon / Flood Damage',
                        'Soil Fertility Issues',
                    ] as $problem)
                        <label class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition">
                            <input type="checkbox" name="problems_encountered[]" value="{{ $problem }}"
                                   {{ in_array($problem, old('problems_encountered', data_get($old_data, 'problems_encountered', []))) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                            <span class="text-sm sm:text-base text-gray-700">{{ $problem }}</span>
                        </label>
                    @endforeach

                    <label class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition sm:col-span-2">
                        <input type="checkbox" name="problems_encountered[]" value="Others"
                               onchange="document.getElementById('problemsOthersField').classList.toggle('hidden', !this.checked)"
                               {{ in_array('Others', old('problems_encountered', data_get($old_data, 'problems_encountered', []))) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                        <span class="text-sm sm:text-base text-gray-700">Others (please specify)</span>
                    </label>
                </div>
                @error('problems_encountered')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror

                <div id="problemsOthersField" class="mt-4 {{ in_array('Others', old('problems_encountered', data_get($old_data, 'problems_encountered', []))) ? '' : 'hidden' }}">
                    <label for="problems_encountered_others" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Please specify other problem
                    </label>
                    <input type="text" id="problems_encountered_others" name="problems_encountered_others"
                           value="{{ old('problems_encountered_others', data_get($old_data, 'problems_encountered_others')) }}"
                           class="w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('problems_encountered_others') border-red-500 @enderror">
                    @error('problems_encountered_others')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <section>
                <label for="recommendations" class="block text-base font-semibold text-gray-900 mb-2">
                    Recommendations <span class="text-red-600">*</span>
                </label>
                <p class="text-sm text-gray-500 mb-3">
                    What improvements would the farmer suggest for future DA seed programs and services?
                </p>
                <textarea id="recommendations" name="recommendations" rows="5" required
                          class="w-full rounded-xl border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-3.5 px-4 @error('recommendations') border-red-500 @enderror"
                          placeholder="Type the farmer's response here...">{{ old('recommendations', data_get($old_data, 'recommendations')) }}</textarea>
                @error('recommendations')
                    <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </section>

            <section>
                <label for="additional_comments" class="block text-base font-semibold text-gray-900 mb-2">
                    Additional Comments
                </label>
                <p class="text-sm text-gray-500 mb-3">
                    Any other remarks the farmer would like to share (optional).
                </p>
                <textarea id="additional_comments" name="additional_comments" rows="4"
                          class="w-full rounded-xl border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-3.5 px-4 @error('additional_comments') border-red-500 @enderror"
                          placeholder="Type the farmer's response here...">{{ old('additional_comments', data_get($old_data, 'additional_comments')) }}</textarea>
                @error('additional_comments')
                    <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </section>
        </div>

        {{-- Navigation --}}
        <div class="bg-gray-50 border-t border-gray-200 px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ isset($farmer) ? route('surveys.step.show', ['farmer' => $farmer, 'step' => 8]) : url("/survey/local/{$uuid}/step/8") }}"
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
    'step' => 9,
    'nextUrl' => route('surveys.step.show', ['farmer' => $farmer, 'step' => 10]),
    'hasServerData' => !empty($old_data),
])
@else
@include('surveys.partials.local-first-init', [
    'uuid' => $uuid,
    'step' => 9,
])
@endif
@endpush
@endsection