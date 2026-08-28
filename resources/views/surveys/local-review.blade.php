@extends('surveys.wizard')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-3 mb-1">
        <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
            </svg>
        </div>
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Review Your Answers</h2>
            <p class="text-sm text-gray-600">Please verify all information before final submission. This data is stored on your device.</p>
        </div>
    </div>
</div>

<div id="reviewLoading" class="text-center py-16 text-gray-400 text-sm">Loading your saved answers&hellip;</div>

<div id="reviewContent" class="hidden space-y-6"></div>

{{-- Submit Bar --}}
<div id="reviewSubmitBar" class="hidden sticky bottom-0 mt-8 bg-white border border-gray-200 rounded-2xl shadow-lg px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
    <a href="{{ route('surveys.local.step.show', ['uuid' => $uuid, 'step' => 10]) }}"
       class="w-full sm:w-auto text-center px-6 py-3.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-base hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 transition flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Back
    </a>
    <button type="button" id="reviewSubmitBtn"
            class="w-full sm:w-auto px-10 py-3.5 rounded-xl bg-da-green-600 text-white font-bold text-base shadow-sm hover:bg-da-green-700 focus:outline-none focus:ring-2 focus:ring-da-green-500 focus:ring-offset-2 transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        <span id="reviewSubmitLabel">Submit Survey</span>
    </button>
</div>

@push('scripts')
{{-- offline-sync.js is already loaded globally by surveys.wizard — do not
     re-include it here, doing so throws "Identifier 'EZSeedOffline' has
     already been declared" since the file declares a top-level const. --}}
<script>
(function () {
    const uuid = @json($uuid);
    const loading = document.getElementById('reviewLoading');
    const content = document.getElementById('reviewContent');
    const submitBar = document.getElementById('reviewSubmitBar');
    const submitBtn = document.getElementById('reviewSubmitBtn');
    const submitLabel = document.getElementById('reviewSubmitLabel');

    function esc(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function row(label, value) {
        return `
            <div class="flex justify-between sm:block">
                <span class="text-gray-500">${esc(label)}</span>
                <span class="font-semibold text-gray-900 sm:block">${esc(value) || '—'}</span>
            </div>
        `;
    }

    function sectionHeader(num, title, step) {
        return `
            <div class="flex items-center justify-between bg-da-green-50 border-b border-da-green-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-da-green-600 text-white text-sm flex items-center justify-center font-bold">${num}</span>
                    ${esc(title)}
                </h3>
                <a href="/survey/local/${uuid}/step/${step}" class="text-sm font-semibold text-da-green-700 hover:text-da-green-800 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>
            </div>
        `;
    }

    function card(inner) {
        return `<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">${inner}</div>`;
    }

    // --- Location name resolution --------------------------------------
    // Offline records only ever store province_id / municipality_id /
    // barangay_id, never the human-readable names (the device never had
    // the full reference dataset, just whatever IDs were picked). Once
    // we're viewing the review page — which, in practice, generally
    // happens with a live connection since it's the last step before
    // submitting — we can resolve those IDs back into names by hitting
    // the same lookup endpoints the dropdowns themselves use, plus the
    // new /provinces endpoint for the top level. Every result is cached
    // in-memory per page load so repeated lookups (e.g. same province
    // for both the farmer's address and the farm location) don't refetch.
    const provinceNameCache = {};
    let provinceListPromise = null;

    async function getProvinceName(provinceId) {
        if (!provinceId) return null;
        if (provinceNameCache[provinceId]) return provinceNameCache[provinceId];
        if (!provinceListPromise) {
            provinceListPromise = fetch('/provinces', { headers: { Accept: 'application/json' } })
                .then((res) => (res.ok ? res.json() : []))
                .catch(() => []);
        }
        const list = await provinceListPromise;
        list.forEach((p) => { provinceNameCache[p.id] = p.name; });
        return provinceNameCache[provinceId] || null;
    }

    const municipalityListCache = {};
    async function getMunicipalityName(provinceId, municipalityId) {
        if (!provinceId || !municipalityId) return null;
        if (!municipalityListCache[provinceId]) {
            municipalityListCache[provinceId] = fetch(`/municipalities/${provinceId}`, { headers: { Accept: 'application/json' } })
                .then((res) => (res.ok ? res.json() : []))
                .catch(() => []);
        }
        const list = await municipalityListCache[provinceId];
        const match = list.find((m) => String(m.id) === String(municipalityId));
        return match ? match.name : null;
    }

    const barangayListCache = {};
    async function getBarangayName(municipalityId, barangayId) {
        if (!municipalityId || !barangayId) return null;
        if (!barangayListCache[municipalityId]) {
            barangayListCache[municipalityId] = fetch(`/barangays/${municipalityId}`, { headers: { Accept: 'application/json' } })
                .then((res) => (res.ok ? res.json() : []))
                .catch(() => []);
        }
        const list = await barangayListCache[municipalityId];
        const match = list.find((b) => String(b.id) === String(barangayId));
        return match ? match.name : null;
    }

    // Resolves a "{prefix}province_id" / "{prefix}municipality_id" /
    // "{prefix}barangay_id" trio (e.g. prefix "farmer_" or "farm_") into a
    // single display string. Returns null if we're offline / lookups
    // failed / nothing was ever selected, so the caller can fall back to
    // a clear placeholder instead of showing a misleading blank.
    async function resolveLocation(payload, prefix) {
        const provinceId = payload[`${prefix}province_id`];
        if (!provinceId) return null;

        const municipalityId = payload[`${prefix}municipality_id`];
        const barangayId = payload[`${prefix}barangay_id`];

        const [provinceName, municipalityName, barangayName] = await Promise.all([
            getProvinceName(provinceId),
            getMunicipalityName(provinceId, municipalityId),
            getBarangayName(municipalityId, barangayId),
        ]);

        // If we're offline (or the lookups otherwise failed) every one of
        // these comes back null even though IDs ARE saved — that's a
        // different situation from "nothing was ever selected", so we
        // distinguish it in render() rather than showing a bare "—".
        if (!provinceName && !municipalityName && !barangayName) {
            return { unresolved: true };
        }

        const parts = [barangayName, municipalityName, provinceName].filter(Boolean);
        return { text: parts.join(', ') };
    }

    async function render(p) {
        const fullName = [p.first_name, p.middle_name, p.last_name, p.suffix].filter(Boolean).join(' ');

        // Resolve both address trios up front so the two cards below can
        // stay simple template strings.
        const [farmerAddress, farmLocation] = await Promise.all([
            resolveLocation(p, 'farmer_'),
            resolveLocation(p, 'farm_'),
        ]);

        function addressRow(label, resolved, addressLine) {
            let display;
            if (!resolved) {
                display = '—';
            } else if (resolved.unresolved) {
                display = 'Saved — will show once back online';
            } else {
                display = addressLine ? `${addressLine}, ${resolved.text}` : resolved.text;
            }
            return row(label, display);
        }

        const consentCard = card(`
            ${sectionHeader(1, 'Informed Consent', 1)}
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm sm:text-base">
                ${row('Assisted by DA Personnel', p.assisted_by_da)}
                ${row('Consent Given', (p.consent_voluntary && p.consent_data_privacy && p.consent_accurate_info) ? 'Yes, all conditions accepted' : 'Incomplete')}
                ${p.assisted_by_da === 'yes' ? row('Enumerator Name', p.enumerator_name) : ''}
                ${p.assisted_by_da === 'yes' ? row('Position', p.enumerator_position) : ''}
                ${p.assisted_by_da === 'yes' ? row('Office', p.enumerator_office) : ''}
                ${p.assisted_by_da === 'yes' ? row('Date of Survey', p.survey_date) : ''}
                ${p.assisted_by_da === 'yes' ? row('Cropping System', p.cropping_system) : ''}
            </div>
        `);

        const farmerCard = card(`
            ${sectionHeader(2, 'Farmer Socio-Demographic Profile', 2)}
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-3 text-sm sm:text-base">
                ${row('Full Name', fullName)}
                ${row('RSBSA Number', p.rsbsa_number)}
                ${row('Age / Sex', `${p.age || '—'} / ${p.sex || '—'}`)}
                ${row('Contact Number', p.contact_number)}
                ${row('Civil Status', p.civil_status)}
                ${row('Educational Attainment', p.educational_attainment)}
                ${row('Ethnicity', p.ethnicity === 'others' ? p.ethnicity_others : p.ethnicity)}
                ${row('Crop Type', p.crop_type)}
                <div class="sm:col-span-3">
                    ${addressRow("Farmer's Address", farmerAddress, p.farmer_address_line)}
                </div>
            </div>
        `);

        const farmCard = card(`
            ${sectionHeader(3, 'Farm Information', 3)}
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-3 text-sm sm:text-base">
                <div class="sm:col-span-3">
                    ${addressRow('Farm Location', farmLocation, null)}
                </div>
                ${row('Farm Area (ha)', p.farm_area)}
                ${row('Tenurial Status', p.tenurial_status)}
                ${row('Years in Farming', p.years_farming)}
                ${row('Household Size', p.household_size)}
                ${row('Occupation', p.occupation)}
                ${row('Annual Income', p.annual_income ? `₱${Number(p.annual_income).toLocaleString()}` : '')}
                ${row('Organization Membership', p.organization_membership === 'yes' ? (p.organization_name || 'Yes') : 'No')}
            </div>
            ${(farmerAddress && farmerAddress.unresolved) || (farmLocation && farmLocation.unresolved) ? `
                <p class="px-6 pb-5 text-xs text-gray-400">Address details are saved on this device but couldn't be looked up right now (you're offline, or the server is unreachable) — they'll display correctly here once you're back online.</p>
            ` : ''}
        `);

        const issuesCard = card(`
            ${sectionHeader(4, 'Issues and Recommendations', 9)}
            <div class="px-6 py-5 space-y-4 text-sm sm:text-base">
                <div>
                    <span class="text-gray-500 block mb-1">Problems Encountered</span>
                    <p class="text-gray-900">${esc((p.problems_encountered || []).join(', ')) || '—'}</p>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1">Recommendations</span>
                    <p class="text-gray-900">${esc(p.recommendations) || '—'}</p>
                </div>
            </div>
        `);

        content.innerHTML = consentCard + farmerCard + farmCard + issuesCard;
        content.classList.remove('hidden');
        submitBar.classList.remove('hidden');
        loading.classList.add('hidden');
    }

    async function load() {
        try {
            const survey = await EZSeedOffline.getSurvey(uuid);
            if (!survey) {
                window.location.href = '{{ route('surveys.my-surveys') }}';
                return;
            }
            await render(survey.payload || {});
        } catch (err) {
            console.error('Failed to load saved survey:', err);
            loading.innerHTML = `
                <p class="text-red-600 font-semibold mb-2">Could not load your saved answers.</p>
                <p class="text-sm text-gray-500">Open the browser console for details, or go back to
                    <a href="{{ route('surveys.my-surveys') }}" class="text-da-green-700 underline">My Surveys</a>.
                </p>
            `;
        }
    }

    function showSuccess(synced, referenceNumber) {
        content.classList.add('hidden');
        submitBar.classList.add('hidden');
        loading.classList.add('hidden');

        const wrapper = document.createElement('div');
        wrapper.className = 'max-w-2xl mx-auto text-center py-8 sm:py-12';
        wrapper.innerHTML = `
            <div class="flex justify-center mb-6">
                <div class="w-24 h-24 rounded-full bg-da-green-100 flex items-center justify-center">
                    <div class="w-16 h-16 rounded-full bg-da-green-600 flex items-center justify-center shadow-lg">
                        <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                </div>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-3">
                ${synced ? 'Survey Submitted Successfully!' : 'Survey Saved — Will Sync Automatically'}
            </h1>
            <p class="text-base text-gray-600 leading-relaxed mb-2">
                ${synced
                    ? 'Thank you for participating in the EZ-Seed Farmer-Centered Seed Preference Survey.'
                    : "Your answers are safely stored on this device. They will be sent to the server automatically once you're back online — you can close the app now, no further action is needed."}
            </p>
            ${synced && referenceNumber ? `
                <div class="inline-flex flex-col items-center bg-da-green-50 border border-da-green-200 rounded-xl px-6 py-4 my-6">
                    <span class="text-xs uppercase tracking-wide text-da-green-700 font-semibold mb-1">Reference Number</span>
                    <span class="text-lg font-bold text-da-green-800 font-mono">${esc(referenceNumber)}</span>
                </div>
            ` : ''}
            <div class="mt-6">
                <a href="{{ route('surveys.my-surveys') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-da-green-600 text-white font-semibold text-base shadow-sm hover:bg-da-green-700 transition">
                    Back to My Surveys
                </a>
            </div>
        `;
        content.parentNode.insertBefore(wrapper, submitBar);
    }

    submitBtn.addEventListener('click', async () => {
        submitBtn.disabled = true;
        submitLabel.textContent = 'Submitting…';
        try {
            await EZSeedOffline.markCompleted(uuid);
            await EZSeedOffline.triggerSync();

            // Re-read the record — triggerSync updates sync_status/reference
            // number synchronously before it resolves, so this reflects
            // whatever actually happened (synced now vs. still queued).
            const updated = await EZSeedOffline.getSurvey(uuid);
            const synced = updated && updated.sync_status === 'synced';
            showSuccess(synced, updated ? updated.server_reference_number : null);
        } catch (err) {
            console.error('Submit failed:', err);
            submitBtn.disabled = false;
            submitLabel.textContent = 'Submit Survey';
            alert('Could not submit right now. Your answers are safely saved on this device — please try again.');
        }
    });

    load();
})();
</script>
@endpush
@endsection
