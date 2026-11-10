/**
 * Service worker do UniFocus.
 *
 * - Push: o servidor envia o conteúdo cifrado (Web Push); o navegador
 *   decifra e entrega aqui em event.data, e mostramos a notificação.
 * - Clique: foca uma aba já aberta do app (ou abre uma) no destino.
 * - Offline: navegações sem rede caem numa página de aviso.
 *
 * Fica fora do Vite de propósito: precisa de URL fixa na raiz (escopo "/").
 */
const CACHE_NAME = 'unifocus-v1';
const OFFLINE_URL = '/offline.html';
const PRECACHE = [OFFLINE_URL, '/images/pwa/icon-192.png', '/images/pwa/badge-96.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

/** Só as navegações passam por aqui; assets e requisições do app seguem direto para a rede. */
self.addEventListener('fetch', (event) => {
    if (event.request.mode !== 'navigate') return;

    event.respondWith(fetch(event.request).catch(() => caches.match(OFFLINE_URL)));
});

self.addEventListener('push', (event) => {
    let data = {};

    try {
        data = event.data ? event.data.json() : {};
    } catch {
        data = { body: event.data?.text() };
    }

    event.waitUntil(showNotification(data));
});

/**
 * @param {{title?: string, body?: string, url?: string, tag?: string|null, icon?: string, badge?: string}} data
 */
async function showNotification(data) {
    const url = new URL(data.url || '/dashboard', self.location.origin);

    /** A pessoa já está olhando exatamente essa tela (ex.: a conversa do chat): o polling dela já mostra a novidade. */
    const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    const isAlreadyVisible = windows.some((client) => client.visibilityState === 'visible' && new URL(client.url).pathname === url.pathname);

    if (isAlreadyVisible) return;

    return self.registration.showNotification(data.title || 'UniFocus', {
        body: data.body || '',
        icon: data.icon || '/images/pwa/icon-192.png',
        badge: data.badge || '/images/pwa/badge-96.png',
        tag: data.tag || undefined,
        renotify: Boolean(data.tag),
        lang: 'pt-BR',
        data: { url: url.href },
    });
}

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    event.waitUntil(openApp(event.notification.data?.url || '/dashboard'));
});

async function openApp(url) {
    const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    const client = windows.find((candidate) => new URL(candidate.url).origin === self.location.origin);

    if (client) {
        await client.focus();

        if ('navigate' in client) {
            try {
                return await client.navigate(url);
            } catch {
                // Aba não controlada por este service worker: abre uma nova.
            }
        }
    }

    return self.clients.openWindow(url);
}
