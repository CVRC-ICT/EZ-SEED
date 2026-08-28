{{--
    Reusable offline-sync wiring for a single wizard step form.

    Usage (place inside the step's @push('scripts') block, at the end):

        @include('surveys.partials.offline-sync-init', [
            'step' => 2,
            'nextUrl' => isset($farmer)
                ? route('surveys.step.show', ['farmer' => $farmer, 'step' => 3])
                : route('surveys.local.step.show', ['uuid' => $uuid, 'step' => 3]),
            'hasServerData' => !empty($old_data),
        ])

    Requires:
      - The form has id="wizardStepForm"
      - The card header has a <div id="syncStatus"> badge (see any updated step file for the markup)
      - offline-sync.js is loaded once, globally, via the wizard layout

    Note: this partial is shared by both the old {farmer}-bound wizard and the
    new local/uuid (IndexedDB) flow. Exactly one of $farmer or $uuid will be
    set depending on which flow rendered the parent step view — never both.
--}}
<script>
    (function () {
        const badge = document.getElementById('syncStatus');
        const form = document.getElementById('wizardStepForm');
        if (!form || !badge) {
            console.warn('offline-sync-init: #wizardStepForm or #syncStatus not found on this page — offline sync not attached.');
            return;
        }

        function setBadge(state) {
            const map = {
                saving:           ['Saving…',                 'bg-gray-100 text-gray-500'],
                saved:            ['Draft saved locally',      'bg-gray-100 text-gray-500'],
                'offline-queued': ['Saved offline — will sync', 'bg-amber-100 text-amber-700'],
                'online-syncing': ['Syncing…',                  'bg-blue-100 text-blue-700'],
                synced:           ['Synced',                    'bg-da-green-50 text-da-green-700'],
                stale:            ['Sync expired — please review', 'bg-red-100 text-red-700'],
            };
            const [text, cls] = map[state] || ['', 'bg-gray-100 text-gray-500'];
            badge.textContent = text;
            badge.className = 'flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ' + cls;
        }

        function setConnectivityBadge() {
            if (!navigator.onLine) {
                badge.textContent = 'Offline';
                badge.className = 'flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-amber-100 text-amber-700';
            }
        }
        setConnectivityBadge();
        window.addEventListener('offline', setConnectivityBadge);

        // The 'online' listener in offline-sync.js kicks off the actual flush and
        // dispatches these custom events — we just reflect them in the badge here.
        document.addEventListener('offline-sync:syncing', () => setBadge('online-syncing'));
        document.addEventListener('offline-sync:done', () => setBadge('synced'));
        document.addEventListener('offline-sync:stale', () => setBadge('stale'));

        OfflineSync.attach({
            form,
            @if (isset($farmer))
            farmerId: {{ Js::from((int) $farmer->id) }},
            offlineUuid: null,
            @else
            farmerId: null,
            offlineUuid: {{ Js::from((string) $uuid) }},
            @endif
            step: {{ Js::from((int) $step) }},
            nextUrl: {{ Js::from($nextUrl) }},
            hasServerData: {{ Js::from((bool) $hasServerData) }},
            onStatus: setBadge,
        });

        // Catch up on anything queued from a previous offline session, in case the
        // 'online' event already fired before this listener was attached (e.g. the
        // page loaded already-online right after connectivity was restored).
        if (navigator.onLine) {
            OfflineSync.flushQueue().then(({ synced, stale }) => {
                if (synced > 0) setBadge('synced');
                if (stale > 0) setBadge('stale');
            });
        }
    })();
</script>