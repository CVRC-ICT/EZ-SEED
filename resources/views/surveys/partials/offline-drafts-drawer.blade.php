{{--
    Global "Offline Drafts" drawer.

    Include ONCE in the wizard layout, after offline-sync.js has loaded, e.g.:

        <script src="{{ asset('js/offline-sync.js') }}"></script>
        @include('surveys.partials.offline-drafts-drawer')
        @stack('scripts')

    Shows two things, purely from IndexedDB (no server round-trip needed):
      - "pending": submissions that were queued while offline and are waiting to sync
      - "drafts": autosaved-but-not-yet-submitted form data for the current step in progress

    Lets the user manually trigger a retry sync instead of waiting for the
    automatic 'online' event (useful on flaky connections).
--}}
<div id="offlineDraftsRoot" class="no-print">
    <button type="button" id="offlineDraftsToggle"
            class="fixed bottom-5 right-5 z-40 hidden items-center gap-2 bg-da-green-800 text-white text-sm font-semibold pl-4 pr-3 py-3 rounded-full shadow-lg hover:bg-da-green-900 focus:outline-none focus:ring-2 focus:ring-da-green-500 focus:ring-offset-2 transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 3.75c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
        </svg>
        <span id="offlineDraftsCount">0</span> Offline
    </button>

    <div id="offlineDraftsOverlay" class="fixed inset-0 z-50 hidden bg-gray-900/60"></div>

    <div id="offlineDraftsPanel"
         class="fixed inset-y-0 right-0 z-50 hidden w-full sm:w-[420px] bg-white shadow-xl flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">Offline Data on This Device</h3>
            <button type="button" id="offlineDraftsClose" class="text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Close">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="px-5 py-3 border-b border-gray-100 bg-amber-50 text-xs text-amber-700">
            This list is local to this browser/device. Data here has not left this device yet.
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-6" id="offlineDraftsBody">
            <p class="text-sm text-gray-500">Loading…</p>
        </div>

        <div class="px-5 py-4 border-t border-gray-200">
            <button type="button" id="offlineDraftsRetry"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-da-green-600 text-white font-semibold text-sm hover:bg-da-green-700 focus:outline-none focus:ring-2 focus:ring-da-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Retry Sync Now
            </button>
            <p id="offlineDraftsRetryStatus" class="mt-2 text-xs text-center text-gray-500"></p>
        </div>
    </div>
</div>

<script>
(function () {
    const toggleBtn = document.getElementById('offlineDraftsToggle');
    const countEl = document.getElementById('offlineDraftsCount');
    const overlay = document.getElementById('offlineDraftsOverlay');
    const panel = document.getElementById('offlineDraftsPanel');
    const closeBtn = document.getElementById('offlineDraftsClose');
    const body = document.getElementById('offlineDraftsBody');
    const retryBtn = document.getElementById('offlineDraftsRetry');
    const retryStatus = document.getElementById('offlineDraftsRetryStatus');

    if (!toggleBtn || typeof OfflineSync === 'undefined') return;

    function fmtTime(ts) {
        if (!ts) return '';
        try {
            return new Date(ts).toLocaleString(undefined, {
                month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit',
            });
        } catch (e) {
            return '';
        }
    }

    function farmerLabel(item) {
        return item.farmerId != null ? `Farmer #${item.farmerId}` : 'Unknown farmer';
    }

    async function refreshCount() {
        try {
            const pending = await OfflineSync.getAllPending();
            countEl.textContent = pending.length;
            toggleBtn.classList.toggle('hidden', pending.length === 0);
            toggleBtn.classList.toggle('flex', pending.length > 0);
        } catch (e) {
            // IndexedDB not available (e.g. private mode in some browsers) — hide silently.
            toggleBtn.classList.add('hidden');
        }
    }

    async function renderPanel() {
        body.innerHTML = '<p class="text-sm text-gray-500">Loading…</p>';
        let pending = [];
        let drafts = [];
        try {
            [pending, drafts] = await Promise.all([
                OfflineSync.getAllPending(),
                OfflineSync.getAllDrafts(),
            ]);
        } catch (e) {
            body.innerHTML = '<p class="text-sm text-red-600">Could not read offline storage on this device.</p>';
            return;
        }

        const sections = [];

        sections.push(`
            <div>
                <h4 class="text-sm font-bold text-gray-900 mb-2">Waiting to Sync (${pending.length})</h4>
                ${pending.length === 0
                    ? '<p class="text-sm text-gray-500">Nothing queued — everything has been sent to the server.</p>'
                    : pending.map((item) => `
                        <div class="flex items-center justify-between bg-amber-50 border border-amber-200 rounded-lg px-3.5 py-2.5 mb-2">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">${farmerLabel(item)} &middot; Step ${item.step ?? '—'}</p>
                                <p class="text-xs text-gray-500">Queued ${fmtTime(item.queuedAt)}</p>
                            </div>
                            <span class="text-xs font-semibold text-amber-700 bg-amber-100 px-2 py-1 rounded-full flex-shrink-0">Pending</span>
                        </div>
                    `).join('')
                }
            </div>
        `);

        sections.push(`
            <div>
                <h4 class="text-sm font-bold text-gray-900 mb-2">Autosaved Drafts In Progress (${drafts.length})</h4>
                ${drafts.length === 0
                    ? '<p class="text-sm text-gray-500">No unsaved step drafts on this device.</p>'
                    : drafts.map((item) => `
                        <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3.5 py-2.5 mb-2">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">${farmerLabel(item)} &middot; Step ${item.step ?? '—'}</p>
                                <p class="text-xs text-gray-500">Last saved ${fmtTime(item.savedAt)}</p>
                            </div>
                        </div>
                    `).join('')
                }
            </div>
        `);

        body.innerHTML = sections.join('');
    }

    function openPanel() {
        overlay.classList.remove('hidden');
        panel.classList.remove('hidden');
        panel.classList.add('flex');
        renderPanel();
    }

    function closePanel() {
        overlay.classList.add('hidden');
        panel.classList.add('hidden');
        panel.classList.remove('flex');
    }

    toggleBtn.addEventListener('click', openPanel);
    closeBtn.addEventListener('click', closePanel);
    overlay.addEventListener('click', closePanel);

    retryBtn.addEventListener('click', async () => {
        if (!navigator.onLine) {
            retryStatus.textContent = 'Still offline — connect to the internet first.';
            return;
        }
        retryBtn.disabled = true;
        retryStatus.textContent = 'Syncing…';
        try {
            const { synced, stale } = await OfflineSync.flushQueue();
            retryStatus.textContent = synced > 0
                ? `Synced ${synced} submission${synced === 1 ? '' : 's'}.` + (stale > 0 ? ` ${stale} expired and need re-review.` : '')
                : (stale > 0 ? `${stale} expired and need re-review.` : 'Nothing to sync.');
            await renderPanel();
            await refreshCount();
        } catch (e) {
            retryStatus.textContent = 'Sync failed — please try again.';
        } finally {
            retryBtn.disabled = false;
        }
    });

    // Keep the badge count fresh as things change elsewhere on the page.
    document.addEventListener('offline-sync:done', () => { refreshCount(); });
    document.addEventListener('offline-sync:stale', () => { refreshCount(); });
    window.addEventListener('online', () => setTimeout(refreshCount, 1500));

    refreshCount();
})();
</script>