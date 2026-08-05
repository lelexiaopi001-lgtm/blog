/* ============================================================
   Aurora Blog 后台脚本
   ============================================================ */
(function () {
    'use strict';

    const root = document.documentElement;

    /* ---------- 主题：同步 localStorage，默认跟随系统 ---------- */
    const applyTheme = () => {
        const stored = localStorage.getItem('ab_theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.setAttribute('data-theme', stored || (systemDark ? 'dark' : 'light'));
    };
    applyTheme();

    const themeBtn = document.getElementById('adminThemeToggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            root.setAttribute('data-theme', next);
            localStorage.setItem('ab_theme', next);
        });
    }
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener?.('change', (e) => {
        if (!localStorage.getItem('ab_theme')) {
            root.setAttribute('data-theme', e.matches ? 'dark' : 'light');
        }
    });

    /* ---------- 侧边栏（移动端） ---------- */
    const side = document.getElementById('adminSide');
    const overlay = document.getElementById('sideOverlay');
    const sideToggle = document.getElementById('sideToggle');
    const setSide = (open) => {
        side?.classList.toggle('open', open);
        overlay?.classList.toggle('show', open);
        document.body.classList.toggle('side-open', open);
    };
    if (sideToggle && side) {
        sideToggle.addEventListener('click', () => setSide(!side.classList.contains('open')));
    }
    if (overlay) {
        overlay.addEventListener('click', () => setSide(false));
    }
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 900 && side?.classList.contains('open') && !side.contains(e.target) && !sideToggle?.contains(e.target)) {
            setSide(false);
        }
    });
    window.addEventListener('resize', () => { if (window.innerWidth > 900) setSide(false); });

    /* ---------- Flash 自动消失 ---------- */
    const flash = document.getElementById('flashBox');
    if (flash) setTimeout(() => { flash.style.transition = 'opacity .5s'; flash.style.opacity = '0'; setTimeout(() => flash.remove(), 600); }, 3200);

    /* ---------- 迷你 Markdown 渲染（预览用） ---------- */
    function miniMd(src) {
        let s = src.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const blocks = [];
        s = s.replace(/```([\w+-]*)\n([\s\S]*?)```/g, (m, lang, code) => {
            blocks.push('<pre><code>' + code + '</code></pre>');
            return '\x00B' + (blocks.length - 1) + '\x00';
        });
        const esc = (t) => t
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
            .replace(/\*([^*\s][^*]*)\*/g, '<em>$1</em>')
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            .replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, '<a href="$2" target="_blank">$1</a>')
            .replace(/!\[([^\]]*)\]\(([^)\s]+)\)/g, '<img src="$2" alt="$1">');
        let html = '';
        s.split('\n').forEach(line => {
            if (line.indexOf('\x00B') === 0) { html += blocks[+line.slice(2, -1)]; return; }
            const t = line.trim();
            if (!t) return;
            let m;
            if ((m = t.match(/^(#{1,6})\s+(.+)$/))) { html += '<h' + m[1].length + '>' + esc(m[2]) + '</h' + m[1].length + '>'; return; }
            if (t === '---' || t === '***') { html += '<hr>'; return; }
            if ((m = t.match(/^>\s?(.*)$/))) { html += '<blockquote>' + esc(m[1]) + '</blockquote>'; return; }
            if ((m = t.match(/^[-*+]\s+(.+)$/))) { html += '<p>• ' + esc(m[1]) + '</p>'; return; }
            if ((m = t.match(/^(\d+)[.)]\s+(.+)$/))) { html += '<p><b>' + m[1] + '.</b> ' + esc(m[2]) + '</p>'; return; }
            html += '<p>' + esc(t) + '</p>';
        });
        return html;
    }

    /* ---------- 图表（纯 SVG 折线图，零依赖） ---------- */
    function renderChart(el) {
        if (!el) return;
        let labels, pv, uv;
        try {
            labels = JSON.parse(el.dataset.labels || '[]');
            pv = JSON.parse(el.dataset.pv || '[]');
            uv = JSON.parse(el.dataset.uv || '[]');
        } catch (e) { return; }
        if (!labels.length) { el.innerHTML = '<p style="text-align:center;color:var(--text-faint);padding-top:90px">暂无数据，访问网站后自动开始统计</p>'; return; }
        const W = el.clientWidth || 800, H = el.clientHeight || 260;
        const pad = { l: 42, r: 14, t: 16, b: 30 };
        const iw = W - pad.l - pad.r, ih = H - pad.t - pad.b;
        const max = Math.max(10, ...pv, ...uv);
        const px = (i) => pad.l + i * (iw / Math.max(1, labels.length - 1));
        const py = (v) => pad.t + ih - (v / max) * ih;
        const line = (data, color, fill) => {
            const pts = data.map((v, i) => px(i) + ',' + py(v));
            let d = '<polyline points="' + pts.join(' ') + '" fill="none" stroke="' + color + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            if (fill) d += '<polygon points="' + pad.l + ',' + (pad.t + ih) + ' ' + pts.join(' ') + ' ' + px(data.length - 1) + ',' + (pad.t + ih) + '" fill="' + color + '" opacity="0.08"/>';
            d += data.map((v, i) => '<circle cx="' + px(i) + '" cy="' + py(v) + '" r="3" fill="' + color + '"><title>' + labels[i] + ' PV=' + v + '</title></circle>').join('');
            return d;
        };
        let grid = '';
        for (let g = 0; g <= 4; g++) {
            const y = pad.t + (ih / 4) * g;
            const val = Math.round(max - (max / 4) * g);
            grid += '<line x1="' + pad.l + '" y1="' + y + '" x2="' + (W - pad.r) + '" y2="' + y + '" stroke="rgba(127,127,127,.12)"/>';
            grid += '<text x="' + (pad.l - 8) + '" y="' + (y + 4) + '" fill="var(--text-faint)" font-size="10" text-anchor="end">' + val + '</text>';
        }
        const xLabels = labels.map((l, i) => {
            if (labels.length > 40 && i % Math.ceil(labels.length / 12) !== 0) return '';
            return '<text x="' + px(i) + '" y="' + (H - 8) + '" fill="var(--text-faint)" font-size="10" text-anchor="middle">' + l + '</text>';
        }).join('');
        const legend = '<text x="' + pad.l + '" y="14" fill="var(--text)" font-size="11">— PV</text><text x="' + (pad.l + 52) + '" y="14" fill="var(--text-dim)" font-size="11">— UV</text>';
        el.innerHTML = '<svg width="100%" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '" preserveAspectRatio="none" style="overflow:visible">' +
            grid + line(pv, 'var(--text)', true) + line(uv, 'var(--text-dim)', false) + xLabels + legend + '</svg>';
    }
    renderChart(document.getElementById('trendChart'));
    renderChart(document.getElementById('statsChart'));
    window.addEventListener('resize', () => { renderChart(document.getElementById('trendChart')); renderChart(document.getElementById('statsChart')); });

    /* ---------- 文章编辑器 ---------- */
    const editor = document.getElementById('editorArea');
    if (editor) {
        document.querySelectorAll('.tb-btn[data-ins]').forEach(btn => {
            btn.addEventListener('click', () => {
                const ins = btn.dataset.ins.replace(/\\n/g, '\n');
                const start = editor.selectionStart, end = editor.selectionEnd;
                editor.value = editor.value.slice(0, start) + ins + editor.value.slice(end);
                editor.focus();
                editor.selectionStart = editor.selectionEnd = start + ins.length;
            });
        });
        const previewBox = document.getElementById('editorPreview');
        const previewModal = document.getElementById('previewModal');
        const previewBody = document.getElementById('previewBody');
        const showPreview = () => {
            const html = miniMd(editor.value);
            if (previewBox) previewBox.innerHTML = html || '<p class="preview-placeholder">点击「预览」或按 Ctrl+P 查看效果</p>';
            if (previewBody) previewBody.innerHTML = html;
            if (previewModal) previewModal.hidden = false;
        };
        document.getElementById('previewBtn')?.addEventListener('click', showPreview);
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p') { e.preventDefault(); showPreview(); }
        });
        previewModal?.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', () => { previewModal.hidden = true; }));
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && previewModal && !previewModal.hidden) previewModal.hidden = true; });
        document.getElementById('postForm')?.addEventListener('submit', (e) => {
            if (!editor.value.trim()) { e.preventDefault(); alert('文章内容不能为空'); }
        });
    }

    /* ---------- 上传（AJAX） ---------- */
    function uploadFile(file, done) {
        if (!file) return;
        const fd = new FormData();
        fd.append('file', file);
        fd.append('csrf', window.__CSRF || '');
        fetch('/admin/index.php?r=upload', { method: 'POST', body: fd, headers: { 'X-CSRF-Token': window.__CSRF || '' } })
            .then(r => r.json())
            .then(res => done(res))
            .catch(() => alert('上传失败，请重试'));
    }
    const coverUpload = document.getElementById('coverUpload');
    if (coverUpload) {
        coverUpload.addEventListener('change', () => {
            const file = coverUpload.files[0];
            uploadFile(file, (res) => {
                if (res.ok) {
                    document.getElementById('coverInput').value = res.url;
                    document.getElementById('coverUrl').value = res.url;
                    document.getElementById('coverPreview').innerHTML = '<img src="' + res.url + '" alt="">';
                    alert('图片上传成功，已作为封面');
                } else alert(res.msg || '上传失败');
                coverUpload.value = '';
            });
        });
    }
    document.getElementById('syncCover')?.addEventListener('click', () => {
        const url = document.getElementById('coverUrl').value.trim();
        document.getElementById('coverInput').value = url;
        document.getElementById('coverPreview').innerHTML = url ? '<img src="' + url + '" alt="">' : '';
    });
    document.querySelectorAll('.upload-inline input[type=file]').forEach(inp => {
        inp.addEventListener('change', () => {
            const file = inp.files[0];
            const target = document.getElementById(inp.dataset.target);
            if (!target) return;
            uploadFile(file, (res) => {
                if (res.ok) { target.value = res.url; alert('上传成功，保存设置后生效'); }
                else alert(res.msg || '上传失败');
                inp.value = '';
            });
        });
    });

    /* ---------- 密码确认 ---------- */
    window.checkPwd = function (btn) {
        const f = btn.closest('form');
        const p1 = f.querySelector('input[name=new_password]').value;
        const p2 = f.querySelector('input[name=new_password2]').value;
        if (p1.length < 6) { alert('新密码至少 6 位'); return false; }
        if (p1 !== p2) { alert('两次输入的新密码不一致'); return false; }
        return true;
    };

    /* ---------- 媒体库：上传 ---------- */
    const mediaUpload = document.getElementById('mediaUpload');
    if (mediaUpload) {
        mediaUpload.addEventListener('change', () => {
            const files = Array.from(mediaUpload.files);
            if (!files.length) return;
            let done = 0, ok = 0;
            files.forEach(file => {
                uploadFile(file, (res) => {
                    done++;
                    if (res.ok) ok++;
                    if (done === files.length) {
                        alert(ok === files.length ? '全部上传成功' : (ok + '/' + files.length + ' 张上传成功，其余失败'));
                        if (ok > 0) location.reload();
                    }
                });
            });
            mediaUpload.value = '';
        });
    }

    /* ---------- 媒体库：复制链接 ---------- */
    document.querySelectorAll('[data-copy]').forEach(btn => {
        btn.addEventListener('click', () => {
            const url = btn.dataset.copy;
            const full = location.origin + url;
            const done = () => {
                const old = btn.textContent;
                btn.textContent = '已复制';
                setTimeout(() => btn.textContent = old, 1600);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(full).then(done).catch(() => { window.prompt('复制链接：', full); });
            } else {
                window.prompt('复制链接：', full);
            }
        });
    });
})();
