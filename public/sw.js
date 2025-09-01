/* Ticketing PWA SW - Laravel 8
   Strategi:
   - Navigation requests (HTML): Network-first, fallback ke /offline
   - Static assets (CSS/JS/IMG): Stale-while-revalidate
   - Abaikan metode non-GET dan domain asing
*/
const VERSION = 'v1.0.0-2025-09-01';
const OFFLINE_URL = '/offline';

// Nama cache
const RUNTIME_CACHE = `runtime-${VERSION}`;
const ASSET_CACHE   = `assets-${VERSION}`;

// Pre-cache ringan saat install (offline page & ikon)
const PRECACHE_URLS = [
  OFFLINE_URL,
  '/manifest.webmanifest',
  '/icons/icon-192.png',
  '/icons/icon-512.png',
  '/icons/maskable-512.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(ASSET_CACHE).then(cache => cache.addAll(PRECACHE_URLS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(
      keys.map(k => {
        if (!k.includes(VERSION)) return caches.delete(k);
      })
    );
    await self.clients.claim();
  })());
});

// Helper: cek jenis request
const isNav = (req) => req.mode === 'navigate';
const isAsset = (req) => ['style','script','image','font'].includes(req.destination);

// Fetch handler
self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Hanya kelola same-origin GET
  if (req.method !== 'GET' || url.origin !== location.origin) return;

  // Navigation: network-first → fallback offline
  if (isNav(req)) {
    event.respondWith((async () => {
      try {
        const fresh = await fetch(req);
        // clone dan cache tipis HTML agar bisa dibuka saat offline
        const cache = await caches.open(RUNTIME_CACHE);
        cache.put(req, fresh.clone());
        return fresh;
      } catch (e) {
        // coba cache dulu, baru offline page
        const cache = await caches.open(RUNTIME_CACHE);
        const cached = await cache.match(req);
        return cached || caches.match(OFFLINE_URL);
      }
    })());
    return;
  }

  // Asset statis: stale-while-revalidate
  if (isAsset(req) || url.pathname.startsWith('/css/') || url.pathname.startsWith('/js/')) {
    event.respondWith((async () => {
      const cache = await caches.open(ASSET_CACHE);
      const cached = await cache.match(req);
      const fetchPromise = fetch(req).then((res) => {
        if (res && res.status === 200) cache.put(req, res.clone());
        return res;
      }).catch(() => null);
      return cached || fetchPromise;
    })());
    return;
  }

  // Default GET lain: network-first (mis. JSON GET internal), tanpa fallback
  event.respondWith((async () => {
    try {
      return await fetch(req);
    } catch (e) {
      const cache = await caches.open(RUNTIME_CACHE);
      const cached = await cache.match(req);
      return cached || new Response('', { status: 504, statusText: 'Offline' });
    }
  })());
});

// (Opsional) menerima pesan update dari halaman
self.addEventListener('message', (event) => {
  if (event.data === 'SKIP_WAITING') self.skipWaiting();
});
