const CACHE_NAME = 'crumbs-cream-v1';
const ASSETS_TO_CACHE = [
  '/crumbs-cream-main/',
  '/crumbs-cream-main/index.php',
  '/crumbs-cream-main/css/style.css',
  '/crumbs-cream-main/manifest.json'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        return cache.addAll(ASSETS_TO_CACHE);
      })
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Return cached version or fetch from network
        return response || fetch(event.request).catch(() => {
            // Fallback for offline if not in cache (could be a generic offline page)
            // For now, let it fail gracefully if offline and not cached.
        });
      })
  );
});

self.addEventListener('activate', (event) => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
