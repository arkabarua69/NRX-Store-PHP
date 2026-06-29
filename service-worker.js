importScripts("https://js.pusher.com/beams/service-worker.js");

var staticCacheName = "pwa-v" + new Date().getTime();
var offlinePage = '/offline.html';

self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName).then(cache => {
            return cache.addAll([offlinePage]);
        })
    );
});


// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Network-first strategy for navigation/API requests; cache-first for static assets
self.addEventListener("fetch", event => {
    if (event.request.mode === 'navigate' || event.request.url.includes('/api/')) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    return response;
                })
                .catch(() => {
                    return caches.match(offlinePage);
                })
        );
    } else {
        event.respondWith(
            caches.match(event.request)
                .then(response => {
                    return response || fetch(event.request);
                })
                .catch(() => {
                    return caches.match(offlinePage);
                })
        );
    }
});