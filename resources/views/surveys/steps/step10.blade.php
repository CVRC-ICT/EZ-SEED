@extends('surveys.wizard', [
    'currentStep' => 10,
    'totalSteps' => 10,
    'stepTitle' => 'Final Confirmation',
])

@section('content')
<form id="wizardStepForm" method="POST" action="{{ isset($farmer) ? route('surveys.step.store', ['farmer' => $farmer, 'step' => 10]) : url("/survey/local/{$uuid}/step/10") }}" novalidate>
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="bg-da-green-50 border-b border-da-green-100 px-6 sm:px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Final Confirmation</h2>
                    <p class="text-sm text-gray-600">Review the reminder below before proceeding to the summary.</p>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7 space-y-8">

            {{-- Survey Summary Snapshot
                 FIX: this used to read from an $summary variable that
                 LocalSurveyController::showStep() never actually passes
                 (there is no server-side hydration in the local/offline
                 flow — everything lives in IndexedDB). isset($summary)
                 was therefore always false, so every field always showed
                 "—" regardless of what was actually filled in. Now these
                 spans are populated client-side by the script below,
                 the same way local-review.blade.php builds its summary. --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">Survey Summary</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 sm:p-6 space-y-3">
                    <div class="flex items-center justify-between text-sm sm:text-base">
                        <span class="text-gray-600">Farmer Name</span>
                        <span id="summaryFarmerName" class="font-semibold text-gray-900">—</span>
                    </div>
                    <div class="flex items-center justify-between text-sm sm:text-base">
                        <span class="text-gray-600">RSBSA Number</span>
                        <span id="summaryRsbsaNumber" class="font-semibold text-gray-900">—</span>
                    </div>
                    <div class="flex items-center justify-between text-sm sm:text-base">
                        <span class="text-gray-600">Farm Location</span>
                        <span id="summaryFarmLocation" class="font-semibold text-gray-900">—</span>
                    </div>
                    <div class="flex items-center justify-between text-sm sm:text-base">
                        <span class="text-gray-600">Total Sections Completed</span>
                        <span id="summarySectionsCompleted" class="font-semibold text-da-green-700">— of 9</span>
                    </div>
                </div>
            </section>

            {{-- Reminder --}}
            <section class="bg-da-yellow-50 border border-da-yellow-400 rounded-xl p-5 sm:p-6 flex gap-4">
                <svg class="w-7 h-7 text-da-yellow-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <h4 class="font-bold text-da-yellow-700 mb-1">Before You Proceed</h4>
                    <p class="text-sm sm:text-base text-gray-700 leading-relaxed">
                        Please review all your answers on the next page carefully. Once submitted, changes to your
                        responses will require coordination with the Municipal Agriculture Office. Make sure all
                        information reflects the farmer's true and accurate responses.
                    </p>
                </div>
            </section>

            {{-- Certification --}}
            <section>
                <label class="flex items-start gap-3 bg-gray-50 border-2 border-gray-200 rounded-xl px-5 py-4 cursor-pointer hover:bg-gray-100 has-[:checked]:bg-da-green-50 has-[:checked]:border-da-green-600 transition">
                    <input type="checkbox" name="certification" value="1" required
                           {{ old('certification', data_get($old_data, 'certification')) ? 'checked' : '' }}
                           class="mt-1 w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                    <span class="text-sm sm:text-base font-medium text-gray-800">
                        I certify that all information provided is true and correct to the best of my knowledge.
                    </span>
                </label>
                @error('certification')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </section>
        </div>

        {{-- Navigation --}}
        <div class="bg-gray-50 border-t border-gray-200 px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ isset($farmer) ? route('surveys.step.show', ['farmer' => $farmer, 'step' => 9]) : url("/survey/local/{$uuid}/step/9") }}"
               class="w-full sm:w-auto text-center px-6 py-3.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-base hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back
            </a>
            <button type="submit"
                    class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-da-green-600 text-white font-semibold text-base shadow-sm hover:bg-da-green-700 focus:outline-none focus:ring-2 focus:ring-da-green-500 focus:ring-offset-2 transition flex items-center justify-center gap-2">
                Proceed to Review
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>
    </div>
</form>

@push('scripts')
{{-- Populate the Survey Summary snapshot from IndexedDB. This only runs for
     the local/offline flow (when $uuid is set) — the legacy {farmer} flow
     never had this problem since it isn't affected by this bug. --}}
@if (!isset($farmer))
<script>
    (function () {
        const uuid = @json($uuid);

        function esc(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        async function populateSummary() {
            try {
                const survey = await EZSeedOffline.getSurvey(uuid);
                if (!survey) return;

                const p = survey.payload || {};
                const fullName = [p.first_name, p.middle_name, p.last_name, p.suffix].filter(Boolean).join(' ');

                const nameEl = document.getElementById('summaryFarmerName');
                const rsbsaEl = document.getElementById('summaryRsbsaNumber');
                const locationEl = document.getElementById('summaryFarmLocation');
                const sectionsEl = document.getElementById('summarySectionsCompleted');

                if (nameEl) nameEl.textContent = fullName || '—';
                if (rsbsaEl) rsbsaEl.textContent = p.rsbsa_number || '—';

                // Province/Municipality/Barangay names aren't cached locally,
                // only their IDs — same limitation noted on the review page.
                // Showing "Saved (syncs to show full location)" is more
                // honest than a bare "—" when the IDs ARE present.
                if (locationEl) {
                    locationEl.textContent = p.farm_province_id ? 'Saved — full address shown after sync' : '—';
                }

                // current_step reflects the highest step actually saved so
                // far, which is a reasonable proxy for sections completed
                // out of the 9 data-entry steps (step 10 is confirmation,
                // not a data section).
                if (sectionsEl) {
                    const completed = Math.min(survey.current_step, 9);
                    sectionsEl.textContent = `${completed} of 9`;
                }
            } catch (err) {
                console.error('Failed to load summary from local storage:', err);
            }
        }

        populateSummary();
    })();
</script>
@endif

{{-- NOTE: nextUrl points to surveys.review — SurveyController::storeStep() redirects there
     (not to a step 11) once the final step is stored successfully. This is the one step
     whose "next" destination isn't another wizard step. The local flow's next destination
     is handled internally by local-first-init.blade.php's JS (it redirects to
     /survey/local/{uuid}/review once step > totalSteps), so no nextUrl is passed there. --}}
@if (isset($farmer))
@include('surveys.partials.offline-sync-init', [
    'step' => 10,
    'nextUrl' => route('surveys.review', ['farmer' => $farmer]),
    'hasServerData' => !empty($old_data),
])
@else
@include('surveys.partials.local-first-init', [
    'uuid' => $uuid,
    'step' => 10,
])
@endif
@endpush
@endsection
