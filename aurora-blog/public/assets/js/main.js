/* ============================================================
   Aurora Blog 前台脚本 —— Aurora Luminous
   ============================================================ */
(function () {
    'use strict';

    const root = document.documentElement;
    const body = document.body;

    /* ---------- 主题切换：优先跟随系统，用户选择后记忆 ---------- */
    const stored = localStorage.getItem('ab_theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const defaultTheme = systemDark ? 'dark' : 'light';
    if (stored) {
        root.setAttribute('data-theme', stored);
    } else {
        root.setAttribute('data-theme', defaultTheme);
    }

    const themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const current = root.getAttribute('data-theme') || defaultTheme;
            const next = current === 'light' ? 'dark' : 'light';
            root.setAttribute('data-theme', next);
            localStorage.setItem('ab_theme', next);
        });
    }

    const media = window.matchMedia('(prefers-color-scheme: dark)');
    media.addEventListener?.('change', (e) => {
        if (!localStorage.getItem('ab_theme')) {
            root.setAttribute('data-theme', e.matches ? 'dark' : 'light');
        }
    });

    /* ---------- 头部滚动效果 ---------- */
    const header = document.getElementById('siteHeader');
    if (header) {
        const onScroll = () => {
            header.classList.toggle('scrolled', window.scrollY > 10);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ---------- 移动端菜单 ---------- */
    const menuBtn = document.getElementById('menuToggle');
    const nav = document.getElementById('siteNav');
    if (menuBtn && nav) {
        menuBtn.addEventListener('click', () => {
            const open = nav.classList.toggle('open');
            menuBtn.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('click', (e) => {
            if (!nav.contains(e.target) && !menuBtn.contains(e.target)) {
                nav.classList.remove('open');
                menuBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ---------- 公告关闭 ---------- */
    const notice = document.getElementById('siteNotice');
    const noticeClose = document.getElementById('noticeClose');
    if (notice && noticeClose) {
        if (localStorage.getItem('ab_notice_closed')) {
            notice.hidden = true;
        }
        noticeClose.addEventListener('click', () => {
            notice.hidden = true;
            localStorage.setItem('ab_notice_closed', '1');
        });
    }

    /* ---------- 回到顶部 ---------- */
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        const toggle = () => {
            backToTop.classList.toggle('visible', window.scrollY > 400);
        };
        window.addEventListener('scroll', toggle, { passive: true });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ---------- 阅读进度条 ---------- */
    const progress = document.getElementById('readProgress');
    const articleBody = document.getElementById('articleBody');
    if (progress && articleBody) {
        const updateProgress = () => {
            const rect = articleBody.getBoundingClientRect();
            const headerH = header ? header.offsetHeight : 0;
            const start = rect.top + window.scrollY - headerH - 24;
            const end = start + rect.height;
            let pct = (window.scrollY - start) / (end - start) * 100;
            pct = Math.max(0, Math.min(100, pct));
            progress.style.width = pct + '%';
        };
        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    }

    /* ---------- 滚动渐入 ---------- */
    const revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((en) => {
                if (en.isIntersecting) {
                    en.target.classList.add('visible');
                    io.unobserve(en.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach((el, i) => {
            el.style.transitionDelay = Math.min(i % 5, 4) * 60 + 'ms';
            io.observe(el);
        });
    } else {
        revealEls.forEach((el) => el.classList.add('visible'));
    }

    /* ---------- PWA Service Worker ---------- */
    if (window.__PWA_ENABLED && 'serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js').catch(() => {});
        });
    }

    /* ---------- 通用请求 ---------- */
    function post(url, data, onOk, onErr) {
        const body = data instanceof FormData ? data : new URLSearchParams(data);
        if (!(body instanceof FormData)) body.append('csrf', window.__CSRF || '');
        fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body,
            credentials: 'same-origin'
        }).then(r => r.json()).then(onOk).catch(() => onErr && onErr());
    }

    /* ---------- 点赞 ---------- */
    const likeBtn = document.querySelector('.like-btn');
    if (likeBtn) {
        const id = likeBtn.dataset.id;
        let liked = localStorage.getItem('ab_liked_' + id);
        if (liked) likeBtn.classList.add('liked');
        likeBtn.addEventListener('click', () => {
            if (liked) return;
            post('/api/like', { id }, (res) => {
                if (res.ok) {
                    liked = '1';
                    localStorage.setItem('ab_liked_' + id, '1');
                    likeBtn.classList.add('liked');
                    likeBtn.querySelector('em').textContent = res.likes;
                }
            });
        });
    }

    /* ---------- 评论 ---------- */
    const form = document.getElementById('commentForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type=submit]');
            const oldText = btn.textContent;
            btn.disabled = true; btn.textContent = '提交中…';
            post('/api/comment', new FormData(form), (res) => {
                btn.disabled = false; btn.textContent = oldText;
                if (res.ok) {
                    form.reset();
                    cancelReply();
                    const tip = document.createElement('div');
                    tip.className = 'comment-msg';
                    tip.style.cssText = 'color:var(--text-dim);font-size:13px;margin-top:10px;';
                    tip.textContent = res.msg;
                    form.appendChild(tip);
                    setTimeout(() => tip.remove(), 4000);
                } else {
                    alert(res.msg || '提交失败，请重试');
                }
            }, () => { btn.disabled = false; btn.textContent = oldText; alert('网络异常，请重试'); });
        });
    }

    window.replyTo = function (id, name) {
        const tip = document.getElementById('replyTip');
        if (tip) { tip.hidden = false; tip.querySelector('b').textContent = '@' + name; }
        const pid = document.getElementById('parentId');
        if (pid) pid.value = id;
        const ta = form ? form.querySelector('textarea') : null;
        if (ta) { ta.focus(); ta.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        return false;
    };
    window.cancelReply = function () {
        const tip = document.getElementById('replyTip');
        if (tip) tip.hidden = true;
        const pid = document.getElementById('parentId');
        if (pid) pid.value = 0;
        return false;
    };

    /* ---------- 代码块一键复制 ---------- */
    document.querySelectorAll('pre.code-block').forEach(pre => {
        pre.style.position = 'relative';
        const btn = document.createElement('button');
        btn.className = 'copy-btn';
        btn.innerHTML = '<svg class="icon icon-sm" style="width:12px;height:12px;"><use href="/assets/img/icons.svg#i-copy"></use></svg> 复制';
        btn.addEventListener('click', () => {
            const code = pre.querySelector('code').textContent;
            const done = () => {
                btn.innerHTML = '<svg class="icon icon-sm" style="width:12px;height:12px;"><use href="/assets/img/icons.svg#i-check"></use></svg> 已复制';
                setTimeout(() => btn.innerHTML = '<svg class="icon icon-sm" style="width:12px;height:12px;"><use href="/assets/img/icons.svg#i-copy"></use></svg> 复制', 1400);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(done).catch(() => {});
            } else {
                const ta = document.createElement('textarea'); ta.value = code; ta.style.opacity = '0'; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); done();
            }
        });
        pre.appendChild(btn);
    });

    /* ---------- 目录高亮 ---------- */
    const toc = document.querySelector('.article-toc');
    if (toc && articleBody) {
        const heads = articleBody.querySelectorAll('h2[id], h3[id]');
        const tocLinks = toc.querySelectorAll('a');
        if (heads.length && 'IntersectionObserver' in window) {
            const spy = new IntersectionObserver((entries) => {
                entries.forEach((en) => {
                    if (en.isIntersecting) {
                        tocLinks.forEach(a => a.classList.remove('active'));
                        const link = toc.querySelector('a[href="#' + en.target.id + '"]');
                        if (link) link.classList.add('active');
                    }
                });
            }, { rootMargin: '-80px 0px -70%' });
            heads.forEach((h) => spy.observe(h));
        }
    }
})();
