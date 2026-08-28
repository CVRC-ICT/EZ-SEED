<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Surveys | EZ-Seed</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { da: { green: { 50:'#f0f9f0',100:'#dcf0dc',600:'#16803C',700:'#0F6B32',800:'#0F6B32',900:'#0a350a' }, yellow: { 100:'#fef3c7',600:'#D99A00' } } } } }
        };
    </script>
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">

        <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to Home
        </a>

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Surveys</h1>
                <p class="text-sm text-gray-500 mt-1">Surveys saved on this device</p>
            </div>
            <div id="connectionBadge" class="text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                &nbsp;
            </div>
        </div>

        <button id="startNewBtn" type="button"
                class="w-full mb-6 px-6 py-4 rounded-2xl bg-da-green-600 text-white font-bold text-base shadow-sm hover:bg-da-green-700 focus:outline-none focus:ring-2 focus:ring-da-green-500 focus:ring-offset-2 transition flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Start New Survey
        </button>

        <div id="surveyList" class="space-y-3">
            <p class="text-sm text-gray-400 text-center py-8">Loading your surveys&hellip;</p>
        </div>

        <div id="emptyState" class="hidden text-center py-12">
            <p class="text-gray-400 text-sm">No surveys yet on this device.</p>
            <p class="text-gray-400 text-xs mt-1">Tap "Start New Survey" above to begin.</p>
        </div>

    </div>

    {{-- Delete confirmation modal --}}
    <div id="deleteConfirmOverlay" class="hidden fixed inset-0 z-50 bg-gray-900/60 flex items-center justify-center px-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete this survey?</h3>
            <p class="text-sm text-gray-600 mb-5">
                <span id="deleteConfirmName" class="font-semibold text-gray-800"></span> will be permanently removed
                from this device. This cannot be undone, and if it hasn't synced yet, that data is lost for good.
            </p>
            <div class="flex gap-3">
                <button type="button" id="deleteConfirmCancel"
                        class="flex-1 px-4 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-100 focus:outline-none">
                    Cancel
                </button>
                <button type="button" id="deleteConfirmProceed"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 text-white font-semibold text-sm hover:bg-red-700 focus:outline-none">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/offline-sync.js') }}"></script>
    <script>
        const statusMeta = {
            draft:     { label: 'Draft',            color: 'bg-amber-50 text-amber-700 border-amber-200',  dot: 'bg-amber-500' },
            completed: { label: 'Ready to Submit',  color: 'bg-da-green-50 text-da-green-700 border-da-green-100', dot: 'bg-da-green-600' },
            submitted: { label: 'Submitted',        color: 'bg-gray-100 text-gray-600 border-gray-200',    dot: 'bg-gray-400' },
        };

        const syncMeta = {
            local:        '',
            pending_sync: '· Waiting to sync',
            syncing:      '· Syncing…',
            synced:       '· Synced',
            sync_failed:  '· Sync will retry automatically',
        };

        function timeAgo(ts) {
            if (!ts) return '';
            const diff = Date.now() - ts;
            const mins = Math.floor(diff / 60000);
            if (mins < 1) return 'just now';
            if (mins < 60) return `${mins} min ago`;
            const hrs = Math.floor(mins / 60);
            if (hrs < 24) return `${hrs} hr ago`;
            return new Date(ts).toLocaleDateString();
        }

        // NEW: formats a submitted/filled-up timestamp for display.
        // Accepts either a numeric epoch ms timestamp or an ISO date string,
        // since offline records and server-synced records may store this
        // differently. Returns null when there's nothing to show yet.
        function formatFilledDate(ts) {
            if (!ts) return null;
            const date = new Date(ts);
            if (isNaN(date.getTime())) return null;
            return date.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function esc(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function renderConnectionBadge() {
            const badge = document.getElementById('connectionBadge');
            if (navigator.onLine) {
                badge.textContent = '🟢 Online';
                badge.className = 'text-xs font-semibold px-3 py-1.5 rounded-full bg-da-green-50 text-da-green-700';
            } else {
                badge.textContent = '🟠 Offline — saved on this device';
                badge.className = 'text-xs font-semibold px-3 py-1.5 rounded-full bg-amber-50 text-amber-700';
            }
        }

        async function renderSurveyList() {
            const container = document.getElementById('surveyList');
            const empty = document.getElementById('emptyState');

            const surveys = await EZSeedOffline.getAllSurveys();
            surveys.sort((a, b) => b.updated_at - a.updated_at);

            if (surveys.length === 0) {
                container.innerHTML = '';
                empty.classList.remove('hidden');
                return;
            }
            empty.classList.add('hidden');

            container.innerHTML = surveys.map((s) => {
                const meta = statusMeta[s.progress_status] || statusMeta.draft;
                const progressPct = Math.round((s.current_step / 10) * 100);
                const name = s.farmer_display_name || 'Unnamed Farmer';
                const syncNote = syncMeta[s.sync_status] || '';

                // NEW: date the survey was filled up / submitted.
                // offline-sync.js has no `submitted_at` field — the closest
                // real timestamp is `last_synced_at`, set in syncOne() at
                // the moment progress_status flips to 'submitted'. Note this
                // is the sync time, not necessarily the exact moment the
                // farmer finished answering, if sync happened later.
                const filledDate = formatFilledDate(s.last_synced_at);

                let actionButton = '';
                if (s.progress_status === 'submitted') {
                    actionButton = `<span class="text-xs font-semibold text-gray-400">${esc(s.server_reference_number || '')}</span>`;
                } else if (s.progress_status === 'completed') {
                    actionButton = `<button data-action="review" data-uuid="${esc(s.offline_uuid)}" class="px-4 py-2 rounded-xl bg-da-green-600 text-white text-sm font-semibold hover:bg-da-green-700">Review</button>`;
                } else {
                    actionButton = `<button data-action="continue" data-uuid="${esc(s.offline_uuid)}" data-step="${s.current_step}" class="px-4 py-2 rounded-xl border-2 border-da-green-600 text-da-green-700 text-sm font-semibold hover:bg-da-green-50">Continue</button>`;
                }

                const isSynced = s.progress_status === 'submitted' && s.sync_status === 'synced';
                const deleteLabel = isSynced ? 'Remove' : 'Delete';

                // FIX: previously this was built as an inline onclick string
                // like onclick="confirmDeleteSurvey('${uuid}', ${JSON.stringify(name)})".
                // JSON.stringify() wraps the name in DOUBLE quotes, but the
                // onclick attribute itself is also delimited with double
                // quotes in the template literal — so the attribute got cut
                // off at the first quote in the name, corrupting the HTML
                // and silently breaking the click handler on every row.
                // Using data-* attributes (HTML-escaped via esc()) plus a
                // single delegated listener below avoids ever having to
                // hand-build a JS call inside an HTML attribute.
                const deleteBtn = `
                    <button data-action="delete" data-uuid="${esc(s.offline_uuid)}" data-name="${esc(name)}"
                            class="text-xs font-semibold text-red-500 hover:text-red-700 hover:underline focus:outline-none px-1">
                        ${deleteLabel}
                    </button>
                `;

                return `
                    <div class="bg-white rounded-2xl border ${meta.color.split(' ')[2] || 'border-gray-200'} shadow-sm p-4 sm:p-5">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2 h-2 rounded-full ${meta.dot} flex-shrink-0"></span>
                                <p class="font-semibold text-gray-900 truncate">${esc(name)}</p>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full ${meta.color} flex-shrink-0">${meta.label}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex-1">
                                <div class="w-full bg-gray-100 rounded-full h-1.5 mb-1.5">
                                    <div class="bg-da-green-600 h-1.5 rounded-full" style="width: ${progressPct}%"></div>
                                </div>
                                <p class="text-xs text-gray-400">
                                    Step ${s.current_step} of 10 &middot; Last saved ${timeAgo(s.updated_at)} ${syncNote}
                                </p>
                                ${filledDate ? `<p class="text-xs text-gray-400 mt-0.5">Filled up on ${filledDate}</p>` : ''}
                            </div>
                            <div class="flex-shrink-0 flex items-center gap-3">
                                ${deleteBtn}
                                ${actionButton}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Single delegated listener for every action button in the list —
        // works no matter how many times renderSurveyList() re-renders the
        // container's innerHTML (no need to re-bind listeners per button).
        document.getElementById('surveyList').addEventListener('click', (e) => {
            const btn = e.target.closest('[data-action]');
            if (!btn) return;

            const action = btn.dataset.action;
            const uuid = btn.dataset.uuid;

            if (action === 'continue') {
                window.location.href = `/survey/local/${uuid}/step/${btn.dataset.step}`;
            } else if (action === 'review') {
                window.location.href = `/survey/local/${uuid}/review`;
            } else if (action === 'delete') {
                confirmDeleteSurvey(uuid, btn.dataset.name);
            }
        });

        // --- Delete flow -----------------------------------------------
        let pendingDeleteUuid = null;
        const deleteOverlay = document.getElementById('deleteConfirmOverlay');
        const deleteNameEl = document.getElementById('deleteConfirmName');

        function confirmDeleteSurvey(offlineUuid, name) {
            pendingDeleteUuid = offlineUuid;
            deleteNameEl.textContent = name || 'This survey';
            deleteOverlay.classList.remove('hidden');
        }

        function closeDeleteConfirm() {
            pendingDeleteUuid = null;
            deleteOverlay.classList.add('hidden');
        }

        document.getElementById('deleteConfirmCancel').addEventListener('click', closeDeleteConfirm);
        deleteOverlay.addEventListener('click', (e) => {
            if (e.target === deleteOverlay) closeDeleteConfirm();
        });

        document.getElementById('deleteConfirmProceed').addEventListener('click', async () => {
            if (!pendingDeleteUuid) return;
            try {
                await EZSeedOffline.deleteSurvey(pendingDeleteUuid);
            } catch (err) {
                console.error('Failed to delete survey:', err);
                alert('Could not delete this survey. Please try again.');
            } finally {
                closeDeleteConfirm();
                renderSurveyList();
            }
        });

        document.getElementById('startNewBtn').addEventListener('click', async () => {
            const survey = await EZSeedOffline.createSurvey();
            window.location.href = `/survey/local/${survey.offline_uuid}/step/1`;
        });

        window.addEventListener('online', renderConnectionBadge);
        window.addEventListener('offline', renderConnectionBadge);
        document.addEventListener('ezseed-sync:done', renderSurveyList);

        renderConnectionBadge();
        renderSurveyList();

        // Opportunistically try syncing anything pending as soon as this
        // screen loads, in case connectivity returned while the app was closed.
        EZSeedOffline.triggerSync();
    </script>
</body>
</html>