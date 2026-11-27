// Live updates via Supabase Realtime Broadcast.
//
// Laravel sends small "something changed" hints (see app/Services/RealtimeBroadcaster.php).
// A hint is never trusted as data: on a hint, this page re-fetches itself from our own
// Laravel route and swaps in the fresh <main>. Supabase is never asked for table data.

const CATALOG_TOPIC = 'qualora-scholarships';
const OWN_SAVE_WINDOW_MS = 5000;
const OWN_SAVE_KEY = 'qualora:lastOwnSave';

function meta(name) {
    return document.querySelector(`meta[name="${name}"]`)?.content || null;
}

// --- Notice ------------------------------------------------------------------

const notice = document.getElementById('live-notice');
let hideTimer = null;

function showNotice(messageKey, { withRefresh = false, sticky = false } = {}) {
    if (!notice) return;
    notice.querySelector('[data-live-text]').textContent = notice.dataset[messageKey] || '';
    notice.querySelector('[data-live-refresh]').hidden = !withRefresh;
    notice.hidden = false;
    clearTimeout(hideTimer);
    if (!sticky) hideTimer = setTimeout(() => (notice.hidden = true), 6000);
}

notice?.querySelector('[data-live-close]')?.addEventListener('click', () => (notice.hidden = true));
notice?.querySelector('[data-live-refresh]')?.addEventListener('click', () => window.location.reload());

// --- Refreshing the page in place --------------------------------------------

// Do not swap the page under the student while they are in the middle of something:
// typing, an open dialog, or controls they changed (e.g. ticked document checklist).
function userIsBusy(main) {
    const active = document.activeElement;
    if (active && main.contains(active) && active.matches('input, textarea, select, [contenteditable="true"]')) {
        return true;
    }

    const openDialog = [...main.querySelectorAll('[role="dialog"], [aria-modal="true"]')]
        .some((el) => el.getClientRects().length > 0);
    if (openDialog) return true;

    return [...main.querySelectorAll('input, textarea, select')].some((el) => {
        if (el.type === 'checkbox' || el.type === 'radio') return el.checked !== el.defaultChecked;
        if (el.tagName === 'SELECT') return [...el.options].some((o) => o.selected !== o.defaultSelected);
        if (el.type === 'hidden' || el.type === 'file') return false;
        return el.value !== el.defaultValue;
    });
}

async function refreshMain() {
    const current = document.querySelector('main');
    if (!current) return false;

    const response = await fetch(window.location.href, {
        credentials: 'same-origin',
        headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
    });

    // Logged out, removed, or redirected elsewhere: leave the page alone.
    if (!response.ok || new URL(response.url).pathname !== window.location.pathname) return false;

    const fresh = new DOMParser().parseFromString(await response.text(), 'text/html').querySelector('main');
    if (!fresh) return false;

    // Alpine's MutationObserver tears down the old tree and initialises the new one.
    current.replaceWith(document.adoptNode(fresh));
    return true;
}

let pending = null;
let refreshing = false;

// Several hints often arrive together (e.g. an import of many scholarships): handle once.
function scheduleRefresh(messageKey) {
    pending = messageKey;
    setTimeout(async () => {
        if (refreshing || pending === null) return;
        const key = pending;
        pending = null;

        const main = document.querySelector('main');
        if (!main) return;

        if (document.hidden || userIsBusy(main)) {
            showNotice(key === 'msgSaved' ? 'msgSaved' : 'msgStale', { withRefresh: true, sticky: true });
            return;
        }

        refreshing = true;
        try {
            const ok = await refreshMain();
            showNotice(ok ? key : 'msgStale', { withRefresh: !ok, sticky: !ok });
        } catch {
            showNotice('msgStale', { withRefresh: true, sticky: true });
        } finally {
            refreshing = false;
        }
    }, 400);
}

// --- Subscriptions -----------------------------------------------------------

function currentPage() {
    const marker = document.querySelector('main [data-live-page]');
    if (!marker) return null;
    return { type: marker.dataset.livePage, scholarshipId: marker.dataset.scholarshipId || null };
}

function onScholarshipChanged(payload) {
    const page = currentPage();
    if (!page) return;

    const id = String(payload?.id ?? '');

    if (page.type === 'catalog') {
        scheduleRefresh('msgRefreshed');
        return;
    }

    if (page.type === 'scholarship' && id === page.scholarshipId) {
        if (payload?.action === 'deleted') {
            showNotice('msgRemoved', { sticky: true });
            return;
        }
        scheduleRefresh('msgRefreshed');
    }
}

function onSavedChanged() {
    if (!currentPage()) return;

    // The tab that just saved already shows the result after its own redirect.
    const lastOwnSave = Number(sessionStorage.getItem(OWN_SAVE_KEY) || 0);
    if (Date.now() - lastOwnSave < OWN_SAVE_WINDOW_MS) return;

    scheduleRefresh('msgSaved');
}

// Remember saves made in this tab, so it does not refresh itself a second time.
document.addEventListener('submit', (event) => {
    if (event.target instanceof HTMLFormElement && event.target.action.includes('/bookmark')) {
        sessionStorage.setItem(OWN_SAVE_KEY, String(Date.now()));
    }
}, true);

export async function startRealtime() {
    const url = meta('supabase-url');
    const key = meta('supabase-publishable-key');
    const userTopic = meta('realtime-user-topic');

    // Not configured, or this page shows nothing that can change live.
    if (!url || !key || !currentPage()) return;

    // Loaded only on pages that need it, so other pages stay light.
    const { createClient } = await import('@supabase/supabase-js');
    const supabase = createClient(url, key, {
        auth: { persistSession: false, autoRefreshToken: false, detectSessionInUrl: false },
    });

    supabase
        .channel(CATALOG_TOPIC)
        .on('broadcast', { event: 'scholarship_changed' }, ({ payload }) => onScholarshipChanged(payload))
        .subscribe();

    if (userTopic) {
        supabase
            .channel(userTopic)
            .on('broadcast', { event: 'saved_changed' }, () => onSavedChanged())
            .subscribe();
    }
}
