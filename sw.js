/**
 * UniFocus Student Platform - Service Worker (Estratégia Network-First para Desenvolvimento)
 * Garante que qualquer alteração nos arquivos locais seja carregada imediatamente no navegador.
 */

const CACHE_NAME = 'unifocus-cache-v2';

self.addEventListener('install', (event) => {
  // Força o Service Worker a ativar imediatamente sem esperar
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  // Limpa caches antigos automaticamente
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Estratégia Network-First: Sempre tenta buscar a versão mais nova do disco/servidor primeiro
self.addEventListener('fetch', (event) => {
  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => {
        // Se a requisição foi bem sucedida, atualiza o cache
        if (networkResponse && networkResponse.status === 200 && event.request.method === 'GET') {
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        return networkResponse;
      })
      .catch(() => {
        // Se estiver offline ou falhar a rede, recorre ao cache
        return caches.match(event.request);
      })
  );
});
