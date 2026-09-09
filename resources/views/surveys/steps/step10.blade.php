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

            {{-- Survey Summary Snapshot --}}
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

            {{-- Proof of Interview — only shown when the farmer answered
                 WITHOUT DA assistance. $needsProofHere is passed from
                 SurveyController::showStep() for the {farmer} flow. The
                 local/offline flow ($uuid) doesn't have server-side access
                 to assisted_by_da at render time, so a small inline script
                 below checks IndexedDB and reveals this section client-side
                 in that case instead. --}}
            <section id="proofOfInterviewSection"
                     class="{{ ($needsProofHere ?? false) ? '' : 'hidden' }} bg-da-green-50/50 border-2 border-da-green-200 rounded-2xl p-5 sm:p-6 space-y-5">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-da-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                    </svg>
                    <h3 class="text-base font-bold text-gray-900">Proof of Interview</h3>
                </div>
                <p class="text-sm text-gray-600 -mt-2">
                    Since you answered this survey on your own, please take a photo and sign below as proof of your participation.
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
                            Your Signature <span class="text-red-600">*</span>
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
@if (!isset($farmer))
<script>
    (function () {
        const uuid = @json($uuid);

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

                if (locationEl) {
                    locationEl.textContent = p.farm_province_id ? 'Saved — full address shown after sync' : '—';
                }

                if (sectionsEl) {
                    const completed = Math.min(survey.current_step, 9);
                    sectionsEl.textContent = `${completed} of 9`;
                }

                if (p.assisted_by_da !== 'yes') {
                    document.getElementById('proofOfInterviewSection').classList.remove('hidden');
                }
            } catch (err) {
                console.error('Failed to load summary from local storage:', err);
            }
        }

        populateSummary();
    })();
</script>
@endif

<script>
    // Photo capture — LIVE CAMERA + UPLOAD, both writing to #proof_photo.
    // No-ops safely if the Proof of Interview section isn't rendered.
    (function () {
        const openCameraBtn = document.getElementById('openCameraBtn');
        if (!openCameraBtn) return;

        const cancelCameraBtn = document.getElementById('cancelCameraBtn');
        const captureBtn = document.getElementById('capturePhotoBtn');
        const previewWrap = document.getElementById('cameraPreviewWrap');
        const choiceButtons = document.getElementById('photoChoiceButtons');
        const video = document.getElementById('cameraVideo');
        const fileInput = document.getElementById('proofPhotoInput');
        const hidden = document.getElementById('proof_photo');
        const preview = document.getElementById('proofPhotoPreview');

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