const EZSeedOffline = (() => {
    const DB_NAME = 'ezseed_offline';
    const DB_VERSION = 1;
    const STORE_SURVEYS = 'surveys';
    const STORE_REFERENCE = 'reference_data';

    let dbPromise = null;

    function openDb() {
        if (dbPromise) return dbPromise;
        dbPromise = new Promise((resolve, reject) => {
            const req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(STORE_SURVEYS)) {
                    const store = db.createObjectStore(STORE_SURVEYS, { keyPath: 'offline_uuid' });
                    store.createIndex('sync_status', 'sync_status', { unique: false });
                    store.createIndex('progress_status', 'progress_status', { unique: false });
                }
                if (!db.objectStoreNames.contains(STORE_REFERENCE)) {
                    db.createObjectStore(STORE_REFERENCE, { keyPath: 'type' });
                }
            };
            req.onsuccess = (e) => resolve(e.target.result);
            req.onerror = (e) => reject(e.target.error);
        });
        return dbPromise;
    }

    function tx(storeName, mode) {
        return openDb().then((db) => db.transaction(storeName, mode).objectStore(storeName));
    }

    function uuid() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        // Fallback for older browsers/webviews without crypto.randomUUID.
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    /* =====================================================================
     | Survey record lifecycle
     |====================================================================*/

    async function createSurvey() {
        const record = {
            offline_uuid: uuid(),
            progress_status: 'draft',        // draft | completed | submitted
            sync_status: 'local',            // local | pending_sync | syncing | synced | sync_failed
            current_step: 1,
            payload: {},
            farmer_display_name: '',
            created_at: Date.now(),
            updated_at: Date.now(),
            last_synced_at: null,
            server_survey_id: null,
            server_reference_number: null,
            last_error: null,
        };
        const store = await tx(STORE_SURVEYS, 'readwrite');
        return new Promise((resolve, reject) => {
            const req = store.add(record);
            req.onsuccess = () => resolve(record);
            req.onerror = (e) => reject(e.target.error);
        });
    }

    async function getSurvey(offlineUuid) {
        const store = await tx(STORE_SURVEYS, 'readonly');
        return new Promise((resolve, reject) => {
            const req = store.get(offlineUuid);
            req.onsuccess = () => resolve(req.result || null);
            req.onerror = (e) => reject(e.target.error);
        });
    }

    async function getAllSurveys() {
        const store = await tx(STORE_SURVEYS, 'readonly');
        return new Promise((resolve, reject) => {
            const req = store.getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror = (e) => reject(e.target.error);
        });
    }

    /**
     * Merge new step data into a survey's payload, bump current_step, and
     * refresh the display name (first/last name only, never full payload,
     * for the "My Surveys" list).
     */
    async function saveStepData(offlineUuid, step, stepData) {
        const survey = await getSurvey(offlineUuid);
        if (!survey) throw new Error('Survey not found locally: ' + offlineUuid);

        survey.payload = Object.assign({}, survey.payload, stepData);
        survey.current_step = Math.max(survey.current_step, step);
        survey.updated_at = Date.now();

        const first = survey.payload.first_name || '';
        const last = survey.payload.last_name || '';
        survey.farmer_display_name = (first + ' ' + last).trim() || 'Unnamed Farmer';

        const store = await tx(STORE_SURVEYS, 'readwrite');
        return new Promise((resolve, reject) => {
            const req = store.put(survey);
            req.onsuccess = () => resolve(survey);
            req.onerror = (e) => reject(e.target.error);
        });
    }

    async function markCompleted(offlineUuid) {
        const survey = await getSurvey(offlineUuid);
        if (!survey) throw new Error('Survey not found locally: ' + offlineUuid);
        survey.progress_status = 'completed';
        survey.sync_status = 'pending_sync';
        survey.updated_at = Date.now();
        const store = await tx(STORE_SURVEYS, 'readwrite');
        return new Promise((resolve, reject) => {
            const req = store.put(survey);
            req.onsuccess = () => resolve(survey);
            req.onerror = (e) => reject(e.target.error);
        });
    }

    async function deleteSurvey(offlineUuid) {
        const store = await tx(STORE_SURVEYS, 'readwrite');
        return new Promise((resolve, reject) => {
            const req = store.delete(offlineUuid);
            req.onsuccess = () => resolve();
            req.onerror = (e) => reject(e.target.error);
        });
    }

    async function updateSurvey(offlineUuid, changes) {
        const survey = await getSurvey(offlineUuid);
        if (!survey) throw new Error('Survey not found locally: ' + offlineUuid);
        Object.assign(survey, changes, { updated_at: Date.now() });
        const store = await tx(STORE_SURVEYS, 'readwrite');
        return new Promise((resolve, reject) => {
            const req = store.put(survey);
            req.onsuccess = () => resolve(survey);
            req.onerror = (e) => reject(e.target.error);
        });
    }

    /* =====================================================================
     | Reference data cache (provinces, municipalities, barangays, etc.)
     |====================================================================*/

    async function cacheReferenceData(type, data) {
        const store = await tx(STORE_REFERENCE, 'readwrite');
        return new Promise((resolve, reject) => {
            const req = store.put({ type, data, cached_at: Date.now() });
            req.onsuccess = () => resolve();
            req.onerror = (e) => reject(e.target.error);
        });
    }

    async function getReferenceData(type) {
        const store = await tx(STORE_REFERENCE, 'readonly');
        return new Promise((resolve, reject) => {
            const req = store.get(type);
            req.onsuccess = () => resolve(req.result || null);
            req.onerror = (e) => reject(e.target.error);
        });
    }

    /**
     * Fetch + cache a reference dataset when online; fall back to whatever
     * was last cached when offline. Returns null (never throws) if nothing
     * is available either way, so callers can show a clear message instead
     * of a broken dropdown.
     */
    async function getOrRefreshReferenceData(type, url) {
        if (navigator.onLine) {
            try {
                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    await cacheReferenceData(type, data);
                    return data;
                }
            } catch (err) {
                // fall through to cache
            }
        }
        const cached = await getReferenceData(type);
        return cached ? cached.data : null;
    }

    /* =====================================================================
     | Connectivity — don't trust navigator.onLine alone
     |====================================================================*/

    async function isServerReachable() {
        if (!navigator.onLine) return false;
        try {
            const res = await fetch('/api/ping', {
                method: 'GET',
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            return res.ok;
        } catch (err) {
            return false;
        }
    }

    /* =====================================================================
     | Sync manager
     |====================================================================*/

    function buildFinalPayload(survey) {
        // Server-side validation is authoritative; this just forwards the
        // locally-built payload plus the certification flag, matching the
        // shape SurveyController::allStepRules() expects.
        return survey.payload;
    }

    async function syncOne(survey, { onStatus } = {}) {
        await updateSurvey(survey.offline_uuid, { sync_status: 'syncing' });
        if (onStatus) onStatus(survey.offline_uuid, 'syncing');

        try {
            const res = await fetch('/api/surveys/sync', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({
                    offline_uuid: survey.offline_uuid,
                    payload: buildFinalPayload(survey),
                }),
            });

            const data = await res.json().catch(() => null);

            if (res.ok && data && (data.status === 'synced' || data.status === 'already_synced')) {
                await updateSurvey(survey.offline_uuid, {
                    progress_status: 'submitted',
                    sync_status: 'synced',
                    last_synced_at: Date.now(),
                    server_survey_id: data.survey_id,
                    server_reference_number: data.reference_number,
                    last_error: null,
                });
                if (onStatus) onStatus(survey.offline_uuid, 'synced');
                return { success: true, referenceNumber: data.reference_number };
            }

            // Reached the server, but it rejected the payload (validation,
            // server error, etc.) — NOT a connectivity problem. Keep the
            // survey queued and flag it so the person can review it, but
            // never delete local data.
            const message = (data && data.message) || 'The server could not accept this survey right now.';
            await updateSurvey(survey.offline_uuid, { sync_status: 'sync_failed', last_error: message });
            if (onStatus) onStatus(survey.offline_uuid, 'sync_failed');
            return { success: false, error: message };
        } catch (err) {
            // Network-level failure — still offline, or connection dropped
            // mid-request. Leave it queued for automatic retry.
            await updateSurvey(survey.offline_uuid, {
                sync_status: 'pending_sync',
                last_error: 'Connection lost during sync — will retry automatically.',
            });
            if (onStatus) onStatus(survey.offline_uuid, 'pending_sync');
            return { success: false, error: 'network' };
        }
    }

    async function flushSyncQueue({ onStatus, onProgress } = {}) {
        const reachable = await isServerReachable();
        if (!reachable) {
            return { attempted: 0, synced: 0, failed: 0 };
        }

        const all = await getAllSurveys();
        const toSync = all.filter((s) => s.sync_status === 'pending_sync' || s.sync_status === 'sync_failed');

        let synced = 0;
        let failed = 0;

        for (const survey of toSync) {
            const result = await syncOne(survey, { onStatus });
            if (result.success) {
                synced++;
            } else if (result.error !== 'network') {
                failed++;
            } else {
                // Network dropped mid-batch — stop here, the rest stay
                // queued for the next connectivity event rather than
                // hammering a connection that's already gone.
                break;
            }
            if (onProgress) onProgress({ synced, failed, total: toSync.length });
        }

        return { attempted: toSync.length, synced, failed };
    }

    /* =====================================================================
     | Auto-sync wiring
     |====================================================================*/

    let syncInFlight = false;

    async function triggerSync(opts = {}) {
        if (syncInFlight) return;
        syncInFlight = true;
        document.dispatchEvent(new CustomEvent('ezseed-sync:start'));
        try {
            const result = await flushSyncQueue(opts);
            document.dispatchEvent(new CustomEvent('ezseed-sync:done', { detail: result }));
        } finally {
            syncInFlight = false;
        }
    }

    window.addEventListener('online', () => triggerSync());

    return {
        uuid,
        createSurvey,
        getSurvey,
        getAllSurveys,
        saveStepData,
        markCompleted,
        deleteSurvey,
        updateSurvey,
        cacheReferenceData,
        getReferenceData,
        getOrRefreshReferenceData,
        isServerReachable,
        triggerSync,
        flushSyncQueue,
    };
})();