/* ============================================================
   Aurora Blog Service Worker
   离线资源缓存策略：静态资源缓存优先，HTML 页面始终走网络
   避免缓存含 CSRF Token 的动态页面导致表单提交失败
   ============================================================ */
const CACHE_NAME = 'aurora-blog-v2';
const OFFLINE_URL = '/offline.html';
const CORE_ASSETS = [
  '/offline.html',
  '/assets/css/main.css',
  '/assets/js/main.js',
  '/assets/css/admin.css',
  '/assets/js/admin.js',
  '/assets/img/icons.svg'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(CORE_ASSETS)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);

  // 不处理跨域、API、后台与上传文件
  if (url.origin !== self.location.origin) return;
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin/') || url.pathname.startsWith('/uploads/')) {
    return;
  }

  const isDocument = request.mode === 'navigate' || request.destination === 'document';

  if (isDocument) {
    // HTML 页面：始终网络优先，失败时返回离线页
    event.respondWith(
      fetch(request).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }

  // 静态资源：缓存优先，命中失败再回源并更新缓存
  event.respondWith(
    caches.match(request).then((cached) => {
      if (cached) {
        // 后台刷新缓存，下次使用最新资源
        fetch(request).then((response) => {
          if (response && response.status === 200 && response.type === 'basic') {
            caches.open(CACHE_NAME).then((cache) => cache.put(request, response.clone()));
          }
        }).catch(() => {});
        return cached;
      }
      return fetch(request).then((response) => {
        if (!response || response.status !== 200 || response.type !== 'basic') return response;
        caches.open(CACHE_NAME).then((cache) => cache.put(request, response.clone()));
        return response;
      });
    })
  );
});
