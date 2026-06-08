// Service Worker — NutriPredict (PWA de microservicios)
const CACHE = 'nutripredict-ms-v1';
const SHELL = ['./', 'icons/icon-192.png', 'icons/icon-512.png', 'manifest.webmanifest'];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE).then(c => c.addAll(SHELL)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', e => {
  e.waitUntil(caches.keys().then(ks => Promise.all(ks.filter(k => k !== CACHE).map(k => caches.delete(k)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;                  // nunca cachear POST (login, formularios, NutriBot)
  const url = new URL(req.url);
  if (url.pathname.startsWith('/api/')) return;        // la API siempre va a la red
  // App shell y estáticos: caché primero con respaldo de red
  e.respondWith(
    caches.match(req).then(c => c || fetch(req).then(r => {
      const cp = r.clone(); caches.open(CACHE).then(c => c.put(req, cp)); return r;
    }).catch(() => caches.match('./')))
  );
});
