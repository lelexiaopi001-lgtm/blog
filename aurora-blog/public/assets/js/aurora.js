/* Aurora Blog 四季神社主题 · 季节、时间、主题与交互 */
(function () {
  'use strict';

  const STORAGE_KEY = 'aurora-season';
  const THEME_KEY = 'aurora-theme';
  const SEASONS = ['spring', 'summer', 'autumn', 'winter'];

  /* ===== 主题切换 ===== */
  function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    try { localStorage.setItem(THEME_KEY, theme); } catch (e) {}
  }

  function updateThemeIcons(theme) {
    var sunIcon = document.querySelector('.icon-sun');
    var moonIcon = document.querySelector('.icon-moon');
    if (sunIcon && moonIcon) {
      sunIcon.style.display = theme === 'dark' ? 'none' : '';
      moonIcon.style.display = theme === 'dark' ? '' : 'none';
    }
  }

  function initTheme() {
    var saved;
    try { saved = localStorage.getItem(THEME_KEY); } catch (e) {}
    var theme = saved || getSystemTheme();
    applyTheme(theme);
    updateThemeIcons(theme);

    var toggle = document.getElementById('themeToggle');
    if (toggle) {
      toggle.addEventListener('click', function () {
        var current = document.documentElement.getAttribute('data-theme');
        var next = current === 'dark' ? 'light' : 'dark';
        applyTheme(next);
        updateThemeIcons(next);
      });
    }

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
      if (!localStorage.getItem(THEME_KEY)) {
        var theme = e.matches ? 'dark' : 'light';
        applyTheme(theme);
        updateThemeIcons(theme);
      }
    });
  }

  /* ===== 季节计算与切换 ===== */
  function seasonFromDate() {
    var month = new Date().getMonth() + 1;
    if (month >= 3 && month <= 5) return 'spring';
    if (month >= 6 && month <= 8) return 'summer';
    if (month >= 9 && month <= 11) return 'autumn';
    return 'winter';
  }

  function applySeason(season) {
    if (SEASONS.indexOf(season) === -1) return;
    document.documentElement.dataset.season = season;
    try { localStorage.setItem(STORAGE_KEY, season); } catch (e) {}

    document.querySelectorAll('.season-dot').forEach(function (btn) {
      btn.classList.toggle('is-active', btn.dataset.season === season);
    });
  }

  function initSeason() {
    var saved;
    try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) {}
    applySeason(saved && SEASONS.indexOf(saved) !== -1 ? saved : seasonFromDate());

    document.querySelectorAll('.season-dot').forEach(function (btn) {
      btn.addEventListener('click', function () {
        applySeason(btn.dataset.season);
      });
    });
  }

  /* ===== 时间氛围滤镜 ===== */
  function applyTimeAtmosphere() {
    var hour = new Date().getHours();
    var tint, brightness, saturate;

    if (hour >= 5 && hour < 9) {
      tint = 'rgba(255, 235, 200, 0.10)';
      brightness = '1.02';
      saturate = '1.05';
    } else if (hour >= 9 && hour < 17) {
      tint = 'rgba(255, 255, 255, 0)';
      brightness = '1';
      saturate = '1';
    } else if (hour >= 17 && hour < 21) {
      tint = 'rgba(255, 180, 120, 0.14)';
      brightness = '0.96';
      saturate = '1.08';
    } else {
      tint = 'rgba(30, 35, 55, 0.18)';
      brightness = '0.92';
      saturate = '0.92';
    }

    var root = document.documentElement;
    root.style.setProperty('--aurora-time-tint', tint);
    root.style.setProperty('--aurora-time-brightness', brightness);
    root.style.setProperty('--aurora-time-saturate', saturate);
  }

  /* ===== 阅读进度 ===== */
  function initReadingProgress() {
    var bar = document.querySelector('.reading-progress-bar');
    if (!bar) return;

    var article = document.querySelector('.article-layout');
    if (!article) return;

    function update() {
      var rect = article.getBoundingClientRect();
      var viewportHeight = window.innerHeight;
      var total = rect.height - viewportHeight;
      var percent = total <= 0 ? 100 : (-rect.top / total) * 100;
      percent = Math.max(0, Math.min(100, percent));
      bar.style.width = percent + '%';
    }

    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
  }

  /* ===== 章节标记联动 ===== */
  function initChapterMarkers() {
    var markers = document.querySelectorAll('.chapter-marker span');
    var headings = document.querySelectorAll('.article-body h2');
    if (!markers.length || !headings.length) return;

    markers.forEach(function (marker) {
      marker.addEventListener('click', function () {
        var target = document.getElementById(marker.dataset.target);
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var id = entry.target.id;
            markers.forEach(function (m) {
              m.classList.toggle('is-active', m.dataset.target === id);
            });
          }
        });
      },
      { rootMargin: '-40% 0px -50% 0px', threshold: 0 }
    );

    headings.forEach(function (h) { observer.observe(h); });
  }

  /* ===== 当前导航高亮 ===== */
  function initActiveNav() {
    var path = window.location.pathname;
    document.querySelectorAll('.site-nav .nav-link').forEach(function (link) {
      var href = link.getAttribute('href');
      if (href === path || (path === '/' && href === '/')) {
        link.classList.add('is-active');
      }
    });
  }

  /* ===== 通用 AJAX POST ===== */
  function post(url, data, onOk, onErr) {
    var body = data instanceof FormData ? data : new URLSearchParams(data);
    if (!(body instanceof FormData)) body.append('csrf', window.__CSRF || '');
    fetch(url, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: body,
      credentials: 'same-origin'
    }).then(function (r) { return r.json(); }).then(onOk).catch(function () { if (onErr) onErr(); });
  }

  /* ===== 点赞 ===== */
  function initLikeButton() {
    var likeBtn = document.querySelector('.like-btn');
    if (!likeBtn) return;
    var id = likeBtn.dataset.id;
    var liked = localStorage.getItem('ab_liked_' + id);
    if (liked) likeBtn.classList.add('liked');
    likeBtn.addEventListener('click', function () {
      if (liked) return;
      post('/api/like', { id: id }, function (res) {
        if (res.ok) {
          liked = '1';
          localStorage.setItem('ab_liked_' + id, '1');
          likeBtn.classList.add('liked');
          var em = likeBtn.querySelector('em');
          if (em) em.textContent = res.likes;
        }
      });
    });
  }

  /* ===== 评论表单 ===== */
  function initCommentForm() {
    var form = document.getElementById('commentForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('button[type=submit]');
      var oldText = btn.textContent;
      btn.disabled = true;
      btn.textContent = '提交中…';
      post('/api/comment', new FormData(form), function (res) {
        btn.disabled = false;
        btn.textContent = oldText;
        if (res.ok) {
          form.reset();
          cancelReply();
          var tip = document.createElement('div');
          tip.style.cssText = 'color:var(--aurora-ink-muted);font-size:0.85rem;margin-top:10px;text-align:center;';
          tip.textContent = res.msg;
          form.appendChild(tip);
          setTimeout(function () { tip.remove(); }, 4000);
          if (!res.audit) {
            setTimeout(function () { location.reload(); }, 1500);
          }
        } else {
          alert(res.msg || '提交失败，请重试');
        }
      }, function () {
        btn.disabled = false;
        btn.textContent = oldText;
        alert('网络异常，请重试');
      });
    });
  }

  window.replyTo = function (id, name) {
    var tip = document.getElementById('replyTip');
    if (tip) { tip.hidden = false; tip.querySelector('b').textContent = '@' + name; }
    var pid = document.getElementById('parentId');
    if (pid) pid.value = id;
    var form = document.getElementById('commentForm');
    var ta = form ? form.querySelector('textarea') : null;
    if (ta) { ta.focus(); ta.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    return false;
  };
  window.cancelReply = function () {
    var tip = document.getElementById('replyTip');
    if (tip) tip.hidden = true;
    var pid = document.getElementById('parentId');
    if (pid) pid.value = 0;
    return false;
  };

  /* ===== 代码块复制 ===== */
  function initCodeCopy() {
    document.querySelectorAll('pre code').forEach(function (code) {
      var pre = code.parentElement;
      if (pre.querySelector('.copy-btn')) return;
      pre.style.position = 'relative';
      var btn = document.createElement('button');
      btn.className = 'copy-btn';
      btn.textContent = '复制';
      btn.style.cssText = 'position:absolute;top:8px;right:8px;padding:4px 10px;font-size:0.8rem;border-radius:var(--aurora-radius-sm);border:1px solid var(--aurora-border);background:var(--aurora-card);color:var(--aurora-ink-muted);cursor:pointer;font-family:var(--aurora-font-body);transition:all 0.2s ease;';
      btn.addEventListener('mouseenter', function () { btn.style.color = 'var(--aurora-season-deep)'; });
      btn.addEventListener('mouseleave', function () { btn.style.color = 'var(--aurora-ink-muted)'; });
      btn.addEventListener('click', function () {
        var text = code.textContent;
        var done = function () {
          btn.textContent = '已复制';
          setTimeout(function () { btn.textContent = '复制'; }, 1400);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(done).catch(function () {});
        } else {
          var ta = document.createElement('textarea');
          ta.value = text;
          ta.style.opacity = '0';
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta);
          done();
        }
      });
      pre.appendChild(btn);
    });
  }

  /* ===== 启动 ===== */
  document.addEventListener('DOMContentLoaded', function () {
    initTheme();
    initSeason();
    applyTimeAtmosphere();
    initActiveNav();
    initReadingProgress();
    initChapterMarkers();
    initLikeButton();
    initCommentForm();
    initCodeCopy();

    setInterval(applyTimeAtmosphere, 60 * 60 * 1000);
  });
})();