@extends('surveys.wizard', [
    'currentStep' => 1,
    'totalSteps' => 10,
    'stepTitle' => 'Informed Consent',
])

@section('content')
<a href="{{ route('landing') }}"
   class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 rounded-md px-1 mb-4">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
    </svg>
    Back to Home
</a>

<form id="wizardStepForm" method="POST" action="{{ isset($farmer) ? route('surveys.step.store', ['farmer' => $farmer, 'step' => 1]) : url("/survey/local/{$uuid}/step/1") }}" novalidate>
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- Card Header --}}
        <div class="bg-da-green-50 border-b border-da-green-100 px-6 sm:px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Informed Consent</h2>
                    <p class="text-sm text-gray-600">Please read carefully before proceeding with the survey.</p>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7 space-y-8">

            {{-- Consent Summary --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-2">Consent Summary</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-sm sm:text-base leading-relaxed text-gray-700">
                    <p>
                        This survey is conducted by the <span class="font-medium">Department of Agriculture &ndash; Regional Field Office No. 02</span>
                        to better understand farmer seed preferences and improve the delivery of seed assistance programs.
                        Your participation is <span class="font-medium">voluntary</span>, and all information you provide will be
                        treated with strict confidentiality in accordance with the Data Privacy Act of 2012 (RA 10173).
                        You may choose to stop the survey at any time without penalty.
                    </p>
                </div>
                <button type="button"
                        onclick="document.getElementById('consentModal').classList.remove('hidden')"
                        class="mt-3 inline-flex items-center gap-2 text-da-green-700 font-semibold text-sm sm:text-base hover:text-da-green-800 hover:underline focus:outline-none focus:ring-2 focus:ring-da-green-500 rounded-md px-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    Read Full Consent Form
                </button>
            </section>

            <hr class="border-gray-200">

            {{-- Assisted by DA Personnel --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    Is the farmer being assisted by a DA Personnel / Enumerator in answering this survey?
                </h3>
                <div class="flex flex-col sm:flex-row gap-4" role="radiogroup">
                    <label class="flex-1 relative">
                        <input type="radio" name="assisted_by_da" value="no" class="peer sr-only"
                               onchange="if (this.checked) toggleEnumeratorSection(false)"
                               {{ old('assisted_by_da', data_get($old_data, 'assisted_by_da', 'no')) == 'no' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-4 flex items-center gap-3 transition">
                            <span class="font-semibold text-gray-800 text-base sm:text-lg">No</span>
                        </div>
                    </label>
                    <label class="flex-1 relative">
                        <input type="radio" name="assisted_by_da" value="yes" class="peer sr-only"
                               onchange="if (this.checked) toggleEnumeratorSection(true)"
                               {{ old('assisted_by_da', data_get($old_data, 'assisted_by_da')) == 'yes' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-4 flex items-center gap-3 transition">
                            <span class="font-semibold text-gray-800 text-base sm:text-lg">Yes</span>
                        </div>
                    </label>
                </div>
                @error('assisted_by_da')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </section>

            {{-- Inline Enumerator Information — shown only when "Yes" is selected --}}
            <section id="enumeratorSection" class="{{ old('assisted_by_da', data_get($old_data, 'assisted_by_da')) == 'yes' ? '' : 'hidden' }} bg-da-green-50/50 border-2 border-da-green-200 rounded-2xl p-5 sm:p-6 space-y-6">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-da-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <h3 class="text-base font-bold text-gray-900">Enumerator Information</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Enumerator Code lookup --}}
                    <div class="sm:col-span-2">
                        <label for="enumerator_code" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Enumerator Code <span class="text-xs font-normal text-gray-500">(optional &mdash; auto-fills your info if you have one)</span>
                        </label>
                        <div class="flex gap-2 items-center">
                            <input type="text" id="enumerator_code" name="enumerator_code"
                                   value="{{ old('enumerator_code', data_get($old_data, 'enumerator_code')) }}"
                                   placeholder="e.g. DA-002"
                                   class="w-full sm:w-64 rounded-lg border-2 border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 bg-white">
                            <span id="enumeratorCodeStatus" class="text-sm text-gray-500"></span>
                        </div>
                    </div>

                    <div>
                        <label for="enumerator_name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Enumerator Name <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="enumerator_name" name="enumerator_name"
                               value="{{ old('enumerator_name', data_get($old_data, 'enumerator_name')) }}"
                               class="w-full rounded-lg border-2 border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 bg-white @error('enumerator_name') border-red-500 @enderror">
                        @error('enumerator_name')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="enumerator_position" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Position <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="enumerator_position" name="enumerator_position"
                               value="{{ old('enumerator_position', data_get($old_data, 'enumerator_position')) }}"
                               placeholder="e.g. Agricultural Technologist"
                               class="w-full rounded-lg border-2 border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 bg-white @error('enumerator_position') border-red-500 @enderror">
                        @error('enumerator_position')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="enumerator_office" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Office <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="enumerator_office" name="enumerator_office"
                               value="{{ old('enumerator_office', data_get($old_data, 'enumerator_office')) }}"
                               placeholder="e.g. Municipal Agriculture Office"
                               class="w-full rounded-lg border-2 border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 bg-white @error('enumerator_office') border-red-500 @enderror">
                        @error('enumerator_office')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="survey_date" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Date of Survey <span class="text-red-600">*</span>
                        </label>
                        <input type="date" id="survey_date" name="survey_date"
                               value="{{ old('survey_date', data_get($old_data, 'survey_date', date('Y-m-d'))) }}"
                               class="w-full rounded-lg border-2 border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 bg-white @error('survey_date') border-red-500 @enderror">
                        @error('survey_date')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-3">Survey Location</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label for="enum_province_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Province <span class="text-red-600">*</span>
                            </label>
                            <select id="enum_province_id" name="enum_province_id"
                                    data-old="{{ old('enum_province_id', data_get($old_data, 'enum_province_id')) }}"
                                    class="w-full rounded-lg border-2 border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 bg-white @error('enum_province_id') border-red-500 @enderror">
                                <option value="" disabled {{ old('enum_province_id', data_get($old_data, 'enum_province_id')) ? '' : 'selected' }}>Select Province</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province->id }}" {{ old('enum_province_id', data_get($old_data, 'enum_province_id')) == $province->id ? 'selected' : '' }}>
                                        {{ \App\Support\PlaceName::clean($province->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('enum_province_id')
                                <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="enum_municipality_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Municipality <span class="text-red-600">*</span>
                            </label>
                            <select id="enum_municipality_id" name="enum_municipality_id" disabled
                                    data-old="{{ old('enum_municipality_id', data_get($old_data, 'enum_municipality_id')) }}"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-gray-100 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('enum_municipality_id') border-red-500 @enderror">
                                <option value="">Select province first</option>
                            </select>
                            @error('enum_municipality_id')
                                <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="enum_barangay_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Barangay <span class="text-red-600">*</span>
                            </label>
                            <select id="enum_barangay_id" name="enum_barangay_id" disabled
                                    data-old="{{ old('enum_barangay_id', data_get($old_data, 'enum_barangay_id')) }}"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-gray-100 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('enum_barangay_id') border-red-500 @enderror">
                                <option value="">Select municipality first</option>
                            </select>
                            @error('enum_barangay_id')
                                <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p id="enumLocationHint" class="mt-2 hidden text-xs text-amber-600">Loading location options&hellip;</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-3">
                        Cropping System <span class="text-red-600">*</span>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <label class="flex-1 relative">
                            <input type="radio" name="cropping_system" value="irrigated" class="peer sr-only"
                                   {{ old('cropping_system', data_get($old_data, 'cropping_system')) == 'irrigated' ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-100 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center gap-3 transition bg-white">
                                <span class="font-semibold text-gray-800 text-base">Irrigated</span>
                            </div>
                        </label>
                        <label class="flex-1 relative">
                            <input type="radio" name="cropping_system" value="rainfed" class="peer sr-only"
                                   {{ old('cropping_system', data_get($old_data, 'cropping_system')) == 'rainfed' ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-100 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 flex items-center gap-3 transition bg-white">
                                <span class="font-semibold text-gray-800 text-base">Rainfed</span>
                            </div>
                        </label>
                    </div>
                    @error('cropping_system')
                        <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            {{-- Proof of Interview — photo + signature, shown only when DA-assisted --}}
            <section id="proofOfInterviewSection" class="{{ old('assisted_by_da', data_get($old_data, 'assisted_by_da')) == 'yes' ? '' : 'hidden' }} bg-da-green-50/50 border-2 border-da-green-200 rounded-2xl p-5 sm:p-6 space-y-5">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-da-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                    </svg>
                    <h3 class="text-base font-bold text-gray-900">Proof of Interview</h3>
                </div>
                <p class="text-sm text-gray-600 -mt-2">
                    Since a DA Personnel is assisting, please take a photo and capture the farmer's signature as proof this interview took place.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Photo — live camera OR upload --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Photo <span class="text-red-600">*</span>
                        </label>

                        <input type="hidden" name="proof_photo" id="proof_photo" value="{{ old('proof_photo', data_get($old_data, 'proof_photo')) }}">

                        <div id="cameraPreviewWrap" class="hidden mb-3">
                            <video id="cameraVideo" autoplay playsinline class="w-full rounded-lg border border-gray-300 bg-black max-h-64 object-cover"></video>
                            <div class="flex gap-2 mt-2">
                                <button type="button" id="capturePhotoBtn"
                                        class="flex-1 px-4 py-2 rounded-lg bg-da-green-600 text-white font-semibold text-sm hover:bg-da-green-700">
                                    📸 Capture
                                </button>
                                <button type="button" id="cancelCameraBtn"
                                        class="px-4 py-2 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-100">
                                    Cancel
                                </button>
                            </div>
                        </div>

                        <div id="photoChoiceButtons" class="flex flex-col sm:flex-row gap-2">
                            <button type="button" id="openCameraBtn"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-da-green-600 text-white font-semibold text-sm hover:bg-da-green-700">
                                📷 Take Photo (Live Camera)
                            </button>
                            <label class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-100 cursor-pointer">
                                ⬆️ Upload Photo
                                <input type="file" id="proofPhotoInput" accept="image/*" class="hidden">
                            </label>
                        </div>

                        <img id="proofPhotoPreview" class="mt-3 max-h-48 rounded-lg border border-gray-300 {{ data_get($old_data, 'proof_photo') ? '' : 'hidden' }}"
                             src="{{ data_get($old_data, 'proof_photo') }}" alt="Photo preview">

                        @error('proof_photo')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Farmer's Signature <span class="text-red-600">*</span>
                        </label>
                        <canvas id="proofSignatureCanvas" width="320" height="140"
                                class="border-2 border-gray-300 rounded-lg bg-white w-full touch-none cursor-crosshair"></canvas>
                        <input type="hidden" name="proof_signature" id="proof_signature" value="{{ old('proof_signature', data_get($old_data, 'proof_signature')) }}">
                        <button type="button" id="clearSignatureBtn"
                                class="mt-2 text-sm font-semibold text-gray-600 hover:text-gray-800">
                            Clear Signature
                        </button>
                        @error('proof_signature')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Consent Checkboxes --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">Consent Declaration</h3>
                <div class="space-y-3">
                    <label class="flex items-start gap-3 bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition">
                        <input type="checkbox" name="consent_voluntary" value="1" {{ old('consent_voluntary', data_get($old_data, 'consent_voluntary')) ? 'checked' : '' }}
                               class="mt-1 w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                        <span class="text-sm sm:text-base text-gray-700">
                            I understand that my participation in this survey is <span class="font-semibold">voluntary</span>
                            and that I may withdraw at any time.
                        </span>
                    </label>
                    <label class="flex items-start gap-3 bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition">
                        <input type="checkbox" name="consent_data_privacy" value="1" {{ old('consent_data_privacy', data_get($old_data, 'consent_data_privacy')) ? 'checked' : '' }}
                               class="mt-1 w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                        <span class="text-sm sm:text-base text-gray-700">
                            I consent to the collection and processing of my personal data in accordance with the
                            <span class="font-semibold">Data Privacy Act of 2012 (RA 10173)</span>.
                        </span>
                    </label>
                    <label class="flex items-start gap-3 bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3.5 cursor-pointer hover:bg-gray-100 transition">
                        <input type="checkbox" name="consent_accurate_info" value="1" {{ old('consent_accurate_info', data_get($old_data, 'consent_accurate_info')) ? 'checked' : '' }}
                               class="mt-1 w-5 h-5 rounded border-gray-400 text-da-green-600 focus:ring-da-green-600 flex-shrink-0">
                        <span class="text-sm sm:text-base text-gray-700">
                            I agree to provide truthful and accurate information to the best of my knowledge.
                        </span>
                    </label>
                </div>
                @error('consent_voluntary')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
                @error('consent_data_privacy')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
                @error('consent_accurate_info')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </section>
        </div>

        {{-- Navigation --}}
        <div class="bg-gray-50 border-t border-gray-200 px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <button type="submit" name="action" value="disagree"
                    class="w-full sm:w-auto px-6 py-3.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-base hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 transition">
                Disagree
            </button>
            <button type="submit" name="action" value="agree"
                    class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-da-green-600 text-white font-semibold text-base shadow-sm hover:bg-da-green-700 focus:outline-none focus:ring-2 focus:ring-da-green-500 focus:ring-offset-2 transition flex items-center justify-center gap-2">
                Agree &amp; Continue
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>
    </div>
</form>

{{-- Consent Modal --}}
<div id="consentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center px-4 py-8">
        <div class="fixed inset-0 bg-gray-900/60" onclick="document.getElementById('consentModal').classList.add('hidden')"></div>

        <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">Full Informed Consent Form</h3>
                <button type="button" onclick="document.getElementById('consentModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Close">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-5 overflow-y-auto space-y-4 text-sm sm:text-base text-gray-700 leading-relaxed">
                <p><span class="font-semibold">Title of Study:</span> Farmer-Centered Seed Preference Assessment (EZ-Seed)</p>
                <p><span class="font-semibold">Conducted by:</span> Department of Agriculture, Regional Field Office No. 02</p>
                <p><span class="font-semibold">Purpose:</span> This survey aims to gather information on farmer demographics, farming practices, and seed variety preferences to guide the design of future seed distribution and agricultural support programs in the region.</p>
                <p><span class="font-semibold">Confidentiality:</span> All responses will be kept strictly confidential and used only for research and reporting purposes.</p>
                <p><span class="font-semibold">Voluntary Participation:</span> Participation is entirely voluntary. You may decline to answer any question or withdraw at any point without consequence.</p>
                <p><span class="font-semibold">Duration:</span> The survey takes approximately 20&ndash;30 minutes to complete.</p>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="document.getElementById('consentModal').classList.add('hidden')"
                        class="px-6 py-2.5 rounded-xl bg-da-green-600 text-white font-semibold hover:bg-da-green-700 focus:outline-none focus:ring-2 focus:ring-da-green-500">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleEnumeratorSection(show) {
        document.getElementById('enumeratorSection').classList.toggle('hidden', !show);
        document.getElementById('proofOfInterviewSection').classList.toggle('hidden', !show);
    }

    (function () {
        const provinceSelect = document.getElementById('enum_province_id');
        const municipalitySelect = document.getElementById('enum_municipality_id');
        const barangaySelect = document.getElementById('enum_barangay_id');
        const hint = document.getElementById('enumLocationHint');
        const form = provinceSelect.closest('form');

        function resetSelect(select, placeholder, enable) {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.disabled = !enable;
            select.classList.toggle('bg-gray-100', !enable);
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

    // Enumerator code lookup — auto-fills name/position/office.
    (function () {
        const codeInput = document.getElementById('enumerator_code');
        const status = document.getElementById('enumeratorCodeStatus');
        if (!codeInput) return;

        let debounce;
        codeInput.addEventListener('input', function () {
            clearTimeout(debounce);
            const code = this.value.trim();
            if (!code) { status.textContent = ''; return; }

            debounce = setTimeout(async () => {
                status.textContent = 'Checking…';
                status.className = 'text-sm text-gray-500';
                try {
                    const res = await fetch(`/enumerator/lookup/${encodeURIComponent(code)}`, {
                        headers: { Accept: 'application/json' },
                    });
                    const data = await res.json();

                    if (data.found) {
                        status.textContent = '✓ Found — info filled in below';
                        status.className = 'text-sm text-da-green-700 font-semibold';
                        document.getElementById('enumerator_name').value = data.name || '';
                        document.getElementById('enumerator_position').value = data.position || '';
                        document.getElementById('enumerator_office').value = data.office || '';
                    } else {
                        status.textContent = 'New code — fill in your info below';
                        status.className = 'text-sm text-gray-500';
                    }
                } catch (err) {
                    status.textContent = '';
                }
            }, 500);
        });
    })();

    // Photo capture — LIVE CAMERA + UPLOAD, both writing to #proof_photo.
    (function () {
        const openCameraBtn = document.getElementById('openCameraBtn');
        const cancelCameraBtn = document.getElementById('cancelCameraBtn');
        const captureBtn = document.getElementById('capturePhotoBtn');
        const previewWrap = document.getElementById('cameraPreviewWrap');
        const choiceButtons = document.getElementById('photoChoiceButtons');
        const video = document.getElementById('cameraVideo');
        const fileInput = document.getElementById('proofPhotoInput');
        const hidden = document.getElementById('proof_photo');
        const preview = document.getElementById('proofPhotoPreview');

        if (!openCameraBtn) return;

        let stream = null;

        async function openCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' },
                    audio: false,
                });
                video.srcObject = stream;
                previewWrap.classList.remove('hidden');
                choiceButtons.classList.add('hidden');
            } catch (err) {
                alert('Could not access the camera. You can use "Upload Photo" instead.');
                console.error('Camera error:', err);
            }
        }

        function closeCamera() {
            if (stream) {
                stream.getTracks().forEach((track) => track.stop());
                stream = null;
            }
            previewWrap.classList.add('hidden');
            choiceButtons.classList.remove('hidden');
        }

        function capturePhoto() {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

            hidden.value = dataUrl;
            preview.src = dataUrl;
            preview.classList.remove('hidden');

            closeCamera();
        }

        openCameraBtn.addEventListener('click', openCamera);
        cancelCameraBtn.addEventListener('click', closeCamera);
        captureBtn.addEventListener('click', capturePhoto);

        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                hidden.value = e.target.result;
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });
    })();

    // Signature capture — simple canvas drawing, saved as base64 PNG.
    (function () {
        const canvas = document.getElementById('proofSignatureCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const hidden = document.getElementById('proof_signature');
        const clearBtn = document.getElementById('clearSignatureBtn');
        let drawing = false;

        if (hidden.value) {
            const img = new Image();
            img.onload = () => ctx.drawImage(img, 0, 0);
            img.src = hidden.value;
        }

        function pos(e) {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const point = e.touches ? e.touches[0] : e;
            return {
                x: (point.clientX - rect.left) * scaleX,
                y: (point.clientY - rect.top) * scaleY,
            };
        }

        function start(e) {
            drawing = true;
            const p = pos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            e.preventDefault();
        }

        function draw(e) {
            if (!drawing) return;
            const p = pos(e);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#111827';
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            e.preventDefault();
        }

        function end() {
            if (!drawing) return;
            drawing = false;
            hidden.value = canvas.toDataURL('image/png');
        }

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', end);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', end);

        clearBtn.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hidden.value = '';
        });
    })();
</script>

@if (isset($farmer))
@include('surveys.partials.offline-sync-init', [
    'step' => 1,
    'nextUrl' => route('surveys.step.show', ['farmer' => $farmer, 'step' => 2]),
    'hasServerData' => !empty($old_data),
])
@else
@include('surveys.partials.local-first-init', [
    'uuid' => $uuid,
    'step' => 1,
])
@endif
@endpush
@endsection