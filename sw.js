// sw.js — Service Worker de NutriPredict Escolar (PWA básica)
const CACHE = 'nutripredict-v1';
const ASSETS = [
  'css/main.css',
  'js/main.js',
  'css/icons/icon-192.png',
  'css/icons/icon-512.png',
  'manifest.webmanifest'
];

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE).then((c) => c.addAll(ASSETS)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return; // nunca cachear POST (login, formularios, NutriBot)

  const url = new URL(req.url);

  // Páginas dinámicas (.php) y navegaciones: red primero, caché como respaldo offline
  if (req.mode === 'navigate' || url.pathname.endsWith('.php')) {
    e.respondWith(fetch(req).catch(() => caches.match(req)));
    return;
  }

  // Recursos estáticos: caché primero, con actualización en segundo plano
  e.respondWith(
    caches.match(req).then((cached) =>
      cached || fetch(req).then((resp) => {
        const copy = resp.clone();
        caches.open(CACHE).then((c) => c.put(req, copy));
        return resp;
      })
    )
  );
});
