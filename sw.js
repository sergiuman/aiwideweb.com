const CACHE_NAME = 'momentum-v2';
const ASSETS = [
    './',
    './index.html',
    './app.php',
    './app.js',
    './styles.css',
    './manifest.json',
    './assets/icon-192.png',
    './assets/icon-512.png'
];

self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            // Use catch() so failing to cache one file (like an ungenerated icon) doesn't break the whole SW
            return Promise.all(
                ASSETS.map(url => {
                    return cache.add(url).catch(err => console.log('Failed to cache:', url, err));
                })
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', e => {
    // Only intercept GET requests, leave POST (like audio uploads) alone
    if (e.request.method !== 'GET') return;
    // Don't intercept API calls
    if (e.request.url.includes('api.php')) return;

    e.respondWith(
        caches.match(e.request).then(cachedResponse => {
            return cachedResponse || fetch(e.request);
        })
    );
});
