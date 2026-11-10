/**
 * Service worker do UniFocus.
 *
 * - Offline: navegações sem rede caem numa página de aviso.
 *
 * Fica fora do Vite de propósito: precisa de URL fixa na raiz (escopo "/").
 */
const CACHE_NAME = 'unifocus-v1';
const OFFLINE_URL = '/offline.html';
const PRECACHE = [OFFLINE_URL, '/images/pwa/icon-192.png'];

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
