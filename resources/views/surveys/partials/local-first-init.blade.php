{{--
    Include this at the bottom of every step{N}.blade.php's @push('scripts')
    block, replacing the old offline-sync-init include:

        @include('surveys.partials.local-first-init', [
            'uuid' => $uuid,
            'step' => 1,
        ])

    Requires: the step's <form id="wizardStepForm"> and a #syncStatus element
    in the header (already present in every step page).

    "At least one checkbox" groups (e.g. seed_criteria, information_sources)
    can't be validated with plain HTML `required` — wrap the group's
    container with data-require-one="Your message" to enforce it. See
    step5.blade.php for a working example.
--}}
<script>
    (function () {
        const uuid = @json($uuid);
        const step = {{ $step }};
        const totalSteps = 10;
        const form = document.getElementById('wizardStepForm');
        const statusEl = document.getElementById('syncStatus');

        function setStatus(text, classes) {
            if (!statusEl) return;
            statusEl.textContent = text;
            statusEl.className = 'flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ' + classes;
        }

        function showDraftBadge() {
            setStatus('🟡 Draft', 'bg-amber-50 text-amber-700');
        }

        function showSavedBadge() {
            setTimeout(() => {
                setStatus('✓ Saved', 'bg-da-green-50 text-da-green-700');
            }, 300);
        }

        function showSavingBadge() {
            setStatus('Saving…', 'bg-gray-100 text-gray-500');
        }

        async function fetchJson(url) {
            try {
                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!res.ok) return [];
                return await res.json();
            } catch (err) {
                return [];
            }
        }

        function fillSelectOptions(select, items, placeholder) {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            items.forEach((item) => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.name;
                select.appendChild(opt);
            });
            select.disabled = false;
            select.classList.remove('bg-gray-50', 'bg-gray-100');
        }

        async function hydrateLocationCascades(payload) {
            const provinceSelects = form.querySelectorAll('select[name$="_province_id"], select[name="province_id"]');

            for (const provinceSelect of provinceSelects) {
                const provinceFieldName = provinceSelect.name;
                const prefix = provinceFieldName.slice(0, -'province_id'.length);
                const municipalitySelect = form.elements.namedItem(`${prefix}municipality_id`);
                const barangaySelect = form.elements.namedItem(`${prefix}barangay_id`);
                if (!municipalitySelect || !barangaySelect) continue;

                const provinceId = payload[provinceFieldName];
                if (!provinceId) continue;

                provinceSelect.value = provinceId;

                const municipalityId = payload[`${prefix}municipality_id`];
                if (!municipalityId) continue;

                const municipalities = await fetchJson(`/municipalities/${provinceId}`);
                fillSelectOptions(municipalitySelect, municipalities, 'Select municipality');
                municipalitySelect.value = municipalityId;

                const barangayId = payload[`${prefix}barangay_id`];
                if (!barangayId) continue;

                const barangays = await fetchJson(`/barangays/${municipalityId}`);
                fillSelectOptions(barangaySelect, barangays, 'Select barangay');
                barangaySelect.value = barangayId;
            }
        }

        // 1. Hydrate the form from IndexedDB (this step's saved values, if any).
        //
        // NOTE ON KNOWN LIMITATION: this restores simple fields (text,
        // radio, select, flat checkbox groups) and the location cascades.
        // It does NOT yet re-create extra dynamic rows (e.g. a second
        // "Add Training" row, a second "Add Finance Source" row) when you
        // navigate back to a step — Blade always renders exactly one
        // template row for these locally (there's no server-side old_data
        // to know how many rows existed), and this hydrate() only fills
        // whichever DOM inputs currently exist. If someone adds 3 finance
        // rows, saves, leaves, and comes back, only the first row's values
        // are restored on screen — the other two rows' data is still
        // safely stored in IndexedDB and WILL be submitted correctly on
        // Save & Continue (collectFormData below reads the actual DOM,
        // and if the person re-adds rows before continuing they can
        // re-enter that data) — but it won't visually reappear
        // automatically. Flagging this as a known follow-up rather than
        // silently leaving it unmentioned.
        async function hydrate() {
            const survey = await EZSeedOffline.getSurvey(uuid);
            if (!survey) {
                window.location.href = '{{ route('surveys.my-surveys') }}';
                return;
            }

            const payload = survey.payload || {};

            Object.entries(payload).forEach(([name, value]) => {
                const el = form.elements.namedItem(name);
                if (!el) return;

                if (el.tagName === 'SELECT' && /(?:^|_)(?:province|municipality|barangay)_id$/.test(name)) {
                    return;
                }

                if (el instanceof RadioNodeList) {
                    Array.from(el).forEach((radio) => {
                        radio.checked = radio.value === value;
                    });
                    const checkedRadio = Array.from(el).find((radio) => radio.checked);
                    if (checkedRadio) {
                        checkedRadio.dispatchEvent(new Event('change'));
                    }
                } else if (el.type === 'checkbox') {
                    if (Array.isArray(value)) {
                        // Checkbox group sharing a name — handled by name[] below.
                    } else {
                        el.checked = !!value;
                    }
                } else if (el.type !== 'file') {
                    el.value = value;
                }
            });

            Object.entries(payload).forEach(([name, value]) => {
                if (!Array.isArray(value)) return;
                const els = form.querySelectorAll(`[name="${name}[]"]`);
                els.forEach((el) => {
                    if (el.type === 'checkbox') {
                        el.checked = value.includes(el.value);
                    }
                });
            });

            await hydrateLocationCascades(payload);

            showDraftBadge();
        }

        // --- Bracket-notation form field parsing --------------------------
        //
        // ROOT CAUSE FIX: the previous version of this function only
        // special-cased keys ending in exactly "[]" (e.g.
        // "information_sources[]"). Any indexed/nested bracket name —
        // "planted[0][season]", "finance_sources[0][source]",
        // "trainings_attended[0][title]", "subsidy_history[0][year]",
        // "preferences[dry][hybrid][0][variety]",
        // "best_variety_ds[1][variety]" — fell through to the plain `else`
        // branch and got stored as a literal flat string key instead of
        // being nested. Since Step 4's "finance_sources" and Step 7's
        // "planted" are both `required|array|min:1` server-side, and the
        // real nested key never existed in the payload (only the garbage
        // flat key did), EVERY submission failed server-side validation
        // before a Farmer/Survey row was ever created — which is why the
        // farmers/surveys tables stayed empty regardless of how many
        // surveys were actually completed and "submitted" in the UI.
        //
        // parseFieldPath("planted[0][season]") -> ["planted", "0", "season"]
        // parseFieldPath("information_sources[]") -> ["information_sources", ""]
        // parseFieldPath("first_name") -> ["first_name"]
        function parseFieldPath(key) {
            const bracketIndex = key.indexOf('[');
            if (bracketIndex === -1) return [key];

            const base = key.slice(0, bracketIndex);
            const rest = key.slice(bracketIndex);
            const path = [base];

            const bracketRe = /\[([^\]]*)\]/g;
            let m;
            while ((m = bracketRe.exec(rest))) {
                path.push(m[1]);
            }
            return path;
        }

        // Writes `value` into `root` following `path`, creating containers
        // as needed. A path segment of '' (from a trailing "[]") means
        // "push onto an array here" rather than "use this as an object key".
        function setNestedValue(root, path, value) {
            let current = root;

            for (let i = 0; i < path.length; i++) {
                const key = path[i];
                const isLast = i === path.length - 1;

                if (key === '') {
                    // Trailing "[]" — current must be an array; push and stop
                    // (there's nothing meaningful after a bare "[]" segment).
                    if (!Array.isArray(current)) return;
                    current.push(value);
                    return;
                }

                if (isLast) {
                    current[key] = value;
                    return;
                }

                const nextKey = path[i + 1];
                if (!(key in current)) {
                    current[key] = nextKey === '' ? [] : {};
                }
                current = current[key];
            }
        }

        // 2. Collect the current form into a properly nested object,
        // matching the shape the server-side validation rules expect.
        function collectFormData() {
            const data = {};
            const formData = new FormData(form);
            for (const [rawKey, value] of formData.entries()) {
                const path = parseFieldPath(rawKey);
                setNestedValue(data, path, value);
            }
            return data;
        }

        // 2b. "At least one checked" validation for checkbox groups that
        // can't be expressed with plain HTML `required`.
        function findCheckboxGroupError() {
            const groups = form.querySelectorAll('[data-require-one]');
            for (const group of groups) {
                if (group.offsetParent === null) continue;

                const checkboxes = group.querySelectorAll('input[type="checkbox"]');
                if (checkboxes.length === 0) continue;

                const anyChecked = Array.from(checkboxes).some((cb) => cb.checked);
                if (!anyChecked) {
                    return { group, message: group.dataset.requireOne || 'Please select at least one option.' };
                }
            }
            return null;
        }

        function showCheckboxGroupError(group, message) {
            let msgEl = group.querySelector(':scope > .require-one-error');
            if (!msgEl) {
                msgEl = document.createElement('p');
                msgEl.className = 'require-one-error mt-2 text-sm text-red-600 font-medium';
                group.appendChild(msgEl);
            }
            msgEl.textContent = message;
            group.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function clearCheckboxGroupErrors() {
            form.querySelectorAll('.require-one-error').forEach((el) => el.remove());
        }

        // 3. Save on submit (Next / Back / Agree / Disagree buttons all
        // trigger this — there is no server POST anymore for individual
        // steps; everything lives in IndexedDB until final sync).
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitter = e.submitter;
            const skipValidation = submitter && submitter.value === 'disagree';

            if (!skipValidation) {
                clearCheckboxGroupErrors();

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const groupError = findCheckboxGroupError();
                if (groupError) {
                    showCheckboxGroupError(groupError.group, groupError.message);
                    return;
                }
            }

            showSavingBadge();

            const data = collectFormData();

            if (submitter && submitter.name) {
                data[submitter.name] = submitter.value;
            }

            try {
                await EZSeedOffline.saveStepData(uuid, step, data);
                showSavedBadge();
            } catch (err) {
                console.error('Failed to save step locally:', err);
                setStatus('Unable to save — your answers are still on screen, try again', 'bg-red-50 text-red-700');
                return;
            }

            if (submitter && submitter.value === 'disagree') {
                window.location.reload();
                return;
            }

            const nextStep = step + 1;
            if (nextStep > totalSteps) {
                window.location.href = `/survey/local/${uuid}/review`;
            } else {
                window.location.href = `/survey/local/${uuid}/step/${nextStep}`;
            }
        });

        // 4. Lightweight debounced autosave while typing, independent of
        // Next/Back — protects against browser close mid-step.
        let saveTimer = null;
        form.addEventListener('input', () => {
            clearTimeout(saveTimer);
            showSavingBadge();
            saveTimer = setTimeout(async () => {
                const data = collectFormData();
                try {
                    await EZSeedOffline.saveStepData(uuid, step, data);
                    showSavedBadge();
                } catch (err) {
                    console.error('Autosave failed:', err);
                }
            }, 800);
        });

        hydrate();
    })();
</script>
