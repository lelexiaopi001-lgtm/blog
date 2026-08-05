<?php
/**
 * Aurora Blog 四季神社主题 · 全局布局
 */
$cfg = $GLOBALS['__config']['app'];
$siteName = Helper::setting('site_name', 'Aurora Blog');
$siteDesc = Helper::setting('site_desc', '');
$pageTitle = $GLOBALS['__pageTitle'] ?? '';
$isHome = ($GLOBALS['__nav'] ?? '') === 'home';
$isPost = !$isHome && !empty($GLOBALS['__pageType']) && $GLOBALS['__pageType'] === 'article';
$isAbout = ($GLOBALS['__nav'] ?? '') === 'about';
$nav = $GLOBALS['__nav'] ?? '';
$pwaEnabled = Helper::setting('pwa_enabled', '1') === '1';
?>
<!DOCTYPE html>
<html lang="zh" class="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= Helper::e($GLOBALS['__pageDesc'] ?? $siteDesc) ?>">
<meta name="keywords" content="<?= Helper::e(Helper::setting('site_keywords')) ?>">
<meta name="author" content="<?= Helper::e($siteName) ?>">
<title><?= $pageTitle ? Helper::e($pageTitle) . ' - ' : '' ?><?= Helper::e($siteName) ?></title>
<link rel="canonical" href="<?= Helper::e($GLOBALS['__pageUrl'] ?? Helper::siteUrl($_SERVER['REQUEST_URI'] ?? '/')) ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⛩</text></svg>">
<link rel="alternate" type="application/rss+xml" title="<?= Helper::e($siteName) ?>" href="/rss.xml">
<?php if ($pwaEnabled): ?><link rel="manifest" href="/manifest.json"><?php endif; ?>
<meta property="og:site_name" content="<?= Helper::e($siteName) ?>">
<meta property="og:title" content="<?= Helper::e($pageTitle ?: $siteName) ?>">
<meta property="og:description" content="<?= Helper::e($GLOBALS['__pageDesc'] ?? $siteDesc) ?>">
<meta property="og:type" content="<?= Helper::e($GLOBALS['__pageType'] ?? 'website') ?>">
<meta property="og:url" content="<?= Helper::e($GLOBALS['__pageUrl'] ?? Helper::siteUrl($_SERVER['REQUEST_URI'] ?? '/')) ?>">
<?php if (!empty($GLOBALS['__pageImage'])): ?><meta property="og:image" content="<?= Helper::e(Helper::siteUrl($GLOBALS['__pageImage'])) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= Helper::e($pageTitle ?: $siteName) ?>">
<meta name="twitter:description" content="<?= Helper::e($GLOBALS['__pageDesc'] ?? $siteDesc) ?>">
<?php if (!empty($GLOBALS['__pageImage'])): ?><meta name="twitter:image" content="<?= Helper::e(Helper::siteUrl($GLOBALS['__pageImage'])) ?>"><?php endif; ?>
<?php if (!empty($GLOBALS['__structured'])): ?><script type="application/ld+json"><?= $GLOBALS['__structured'] ?></script><?php endif; ?>

<style id="theme-vars">
/* Aurora Blog 四季神社主题 · Design Tokens */
:root {
  --aurora-washi: #F7F5F0;
  --aurora-washi-deep: #EDE9E0;
  --aurora-ink: #2B2825;
  --aurora-ink-soft: #5C5650;
  --aurora-ink-muted: #8A837A;

  --aurora-spring-primary: #E8A5B4;
  --aurora-spring-deep: #C76B82;
  --aurora-spring-light: #FADCE3;
  --aurora-spring-bg: #FCF6F7;

  --aurora-summer-primary: #9BC3A8;
  --aurora-summer-deep: #6A9A7A;
  --aurora-summer-light: #D4E9DA;
  --aurora-summer-bg: #F5FAF6;

  --aurora-autumn-primary: #D99B6C;
  --aurora-autumn-deep: #A66B42;
  --aurora-autumn-light: #F2D6C0;
  --aurora-autumn-bg: #FCF7F2;

  --aurora-winter-primary: #A8B8C8;
  --aurora-winter-deep: #6E7F90;
  --aurora-winter-light: #D8E0E8;
  --aurora-winter-bg: #F5F7FA;

  --aurora-season-primary: var(--aurora-spring-primary);
  --aurora-season-deep: var(--aurora-spring-deep);
  --aurora-season-light: var(--aurora-spring-light);
  --aurora-season-bg: var(--aurora-spring-bg);

  --aurora-time-tint: rgba(255, 245, 230, 0);
  --aurora-time-brightness: 1;
  --aurora-time-saturate: 1;

  --aurora-background: var(--aurora-washi);
  --aurora-foreground: var(--aurora-ink);
  --aurora-card: var(--aurora-season-bg);
  --aurora-card-foreground: var(--aurora-ink);
  --aurora-popover: var(--aurora-washi);
  --aurora-popover-foreground: var(--aurora-ink);
  --aurora-primary: var(--aurora-season-primary);
  --aurora-primary-foreground: #FFFFFF;
  --aurora-muted: var(--aurora-washi-deep);
  --aurora-muted-foreground: var(--aurora-ink-muted);
  --aurora-border: color-mix(in srgb, var(--aurora-season-primary) 20%, var(--aurora-washi-deep));
  --aurora-input: var(--aurora-washi-deep);
  --aurora-ring: var(--aurora-season-primary);

  --aurora-radius-sm: 4px;
  --aurora-radius-md: 8px;
  --aurora-radius-lg: 12px;
  --aurora-radius-full: 9999px;

  --aurora-font-title: "Noto Serif SC", "Source Han Serif SC", "Songti SC", Georgia, serif;
  --aurora-font-body: "Noto Sans SC", "Source Han Sans SC", "PingFang SC", "Microsoft YaHei", sans-serif;
  --aurora-font-mono: "SF Mono", "Fira Code", "Noto Sans Mono CJK SC", monospace;
}

[data-theme="dark"] {
  --aurora-washi: #1E1C1A;
  --aurora-washi-deep: #2D2A26;
  --aurora-ink: #E8E3DB;
  --aurora-ink-soft: #B8B0A6;
  --aurora-ink-muted: #857D74;

  --aurora-spring-bg: #2A2124;
  --aurora-summer-bg: #212826;
  --aurora-autumn-bg: #2B241E;
  --aurora-winter-bg: #22272C;

  --aurora-background: var(--aurora-washi);
  --aurora-foreground: var(--aurora-ink);
  --aurora-card: var(--aurora-season-bg);
  --aurora-card-foreground: var(--aurora-ink);
  --aurora-popover: var(--aurora-washi-deep);
  --aurora-popover-foreground: var(--aurora-ink);
  --aurora-primary: var(--aurora-season-deep);
  --aurora-primary-foreground: #FFFFFF;
  --aurora-muted: var(--aurora-washi-deep);
  --aurora-muted-foreground: var(--aurora-ink-muted);
  --aurora-border: color-mix(in srgb, var(--aurora-season-deep) 25%, var(--aurora-washi-deep));
  --aurora-input: var(--aurora-washi-deep);
  --aurora-ring: var(--aurora-season-deep);
}

.font-title { font-family: var(--aurora-font-title); }
.font-body  { font-family: var(--aurora-font-body); }
.font-mono  { font-family: var(--aurora-font-mono); }

.vertical-text {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  letter-spacing: 0.2em;
}

*, *::before, *::after {
  transition: background-color 0.6s ease, border-color 0.6s ease, color 0.4s ease, fill 0.6s ease, stroke 0.6s ease;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.3.1/dist/index.global.js"></script>
<script src="https://unpkg.com/lucide@1.8.0/dist/umd/lucide.min.js"></script>
<style type="text/tailwindcss">
@theme inline {
  --color-background: var(--aurora-background);
  --color-foreground: var(--aurora-foreground);
  --color-card: var(--aurora-card);
  --color-card-foreground: var(--aurora-card-foreground);
  --color-popover: var(--aurora-popover);
  --color-popover-foreground: var(--aurora-popover-foreground);
  --color-primary: var(--aurora-primary);
  --color-primary-foreground: var(--aurora-primary-foreground);
  --color-muted: var(--aurora-muted);
  --color-muted-foreground: var(--aurora-muted-foreground);
  --color-border: var(--aurora-border);
  --color-input: var(--aurora-input);
  --color-ring: var(--aurora-ring);
  --radius-sm: var(--aurora-radius-sm);
  --radius-md: var(--aurora-radius-md);
  --radius-lg: var(--aurora-radius-lg);
}
@layer base {
  body { background: var(--aurora-background); color: var(--aurora-foreground); }
  td, th { word-break: break-all; word-break: auto-phrase; }
  th { white-space: nowrap; }
}
</style>
<style id="semantic-token-fallback">
.bg-background { background-color: var(--aurora-background); }
.bg-foreground { background-color: var(--aurora-foreground); }
.bg-card { background-color: var(--aurora-card); }
.bg-card-foreground { background-color: var(--aurora-card-foreground); }
.bg-popover { background-color: var(--aurora-popover); }
.bg-popover-foreground { background-color: var(--aurora-popover-foreground); }
.bg-primary { background-color: var(--aurora-primary); }
.bg-primary-foreground { background-color: var(--aurora-primary-foreground); }
.bg-muted { background-color: var(--aurora-muted); }
.bg-muted-foreground { background-color: var(--aurora-muted-foreground); }
.bg-border { background-color: var(--aurora-border); }
.bg-input { background-color: var(--aurora-input); }
.bg-ring { background-color: var(--aurora-ring); }
.text-background { color: var(--aurora-background); }
.text-foreground { color: var(--aurora-foreground); }
.text-card { color: var(--aurora-card); }
.text-card-foreground { color: var(--aurora-card-foreground); }
.text-popover { color: var(--aurora-popover); }
.text-popover-foreground { color: var(--aurora-popover-foreground); }
.text-primary { color: var(--aurora-primary); }
.text-primary-foreground { color: var(--aurora-primary-foreground); }
.text-muted { color: var(--aurora-muted); }
.text-muted-foreground { color: var(--aurora-muted-foreground); }
.text-border { color: var(--aurora-border); }
.text-input { color: var(--aurora-input); }
.text-ring { color: var(--aurora-ring); }
.border-background { border-color: var(--aurora-background); }
.border-foreground { border-color: var(--aurora-foreground); }
.border-card { border-color: var(--aurora-card); }
.border-card-foreground { border-color: var(--aurora-card-foreground); }
.border-popover { border-color: var(--aurora-popover); }
.border-popover-foreground { border-color: var(--aurora-popover-foreground); }
.border-primary { border-color: var(--aurora-primary); }
.border-primary-foreground { border-color: var(--aurora-primary-foreground); }
.border-muted { border-color: var(--aurora-muted); }
.border-muted-foreground { border-color: var(--aurora-muted-foreground); }
.border-border { border-color: var(--aurora-border); }
.border-input { border-color: var(--aurora-input); }
.border-ring { border-color: var(--aurora-ring); }
</style>
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
<link rel="stylesheet" href="<?= Helper::asset('/assets/css/site.css') ?>">
<script src="<?= Helper::asset('/assets/js/aurora.js') ?>" defer></script>
<script>
(function() {
  var s = localStorage.getItem('aurora-theme');
  var prefersDark = matchMedia('(prefers-color-scheme: dark)').matches;
  document.documentElement.setAttribute('data-theme', s || (prefersDark ? 'dark' : 'light'));
})();
</script>
</head>
<body class="min-h-screen font-sans antialiased">
<div class="time-overlay" aria-hidden="true"></div>

<?php if ($isPost): ?>
<div class="reading-progress" aria-hidden="true" id="reading-progress">
  <div class="reading-progress-bar"></div>
</div>
<?php endif; ?>

<header class="site-header" id="site-header">
  <div class="header-inner">
    <a href="/" class="site-logo" data-dom-id="nav-home">
      <svg class="torii-logo" viewBox="0 0 64 48" aria-hidden="true">
        <path d="M4 12h56M8 8v-4h48v4M12 12v28h4V12M48 12v28h4V12M20 40h24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span class="site-name font-title"><?= Helper::e($siteName) ?></span>
    </a>

    <nav class="site-nav" aria-label="主导航">
      <a href="/" class="nav-link<?= $nav === 'home' ? ' is-active' : '' ?>" data-nav-key="home">首页</a>
      <a href="/archive" class="nav-link<?= $nav === 'archive' ? ' is-active' : '' ?>" data-nav-key="posts">文章</a>
      <a href="/about" class="nav-link<?= $nav === 'about' ? ' is-active' : '' ?>" data-nav-key="about">关于</a>
    </nav>

    <div class="season-switcher" aria-label="季节切换">
      <button class="season-dot" data-season="spring" aria-label="春">春</button>
      <button class="season-dot" data-season="summer" aria-label="夏">夏</button>
      <button class="season-dot" data-season="autumn" aria-label="秋">秋</button>
      <button class="season-dot" data-season="winter" aria-label="冬">冬</button>
    </div>

    <button class="theme-toggle" id="themeToggle" aria-label="切换主题" title="切换明暗主题"
        style="background:none;border:1px solid var(--aurora-border);border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--aurora-ink-soft);flex-shrink:0;">
        <i data-lucide="sun" style="width:16px;height:16px;" class="icon-sun"></i>
        <i data-lucide="moon" style="width:16px;height:16px;display:none;" class="icon-moon"></i>
    </button>
  </div>
</header>

<main class="site-main">
<?php View::content(); ?>
</main>

<footer class="site-footer">
  <div class="footer-inner">
    <div class="torii-divider" aria-hidden="true">
      <svg viewBox="0 0 120 24" class="torii-line">
        <path d="M4 12h112M12 8V4h96v4M20 12v8h4v-8M96 12v8h4v-8M36 20h48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <p class="footer-copy font-body">© <?= date('Y') ?> <?= Helper::e($siteName) ?> · <?= Helper::e(Helper::setting('footer_text', '四季流转，笔随参道')) ?></p>
    <?php if (Helper::setting('site_icp')): ?><p class="font-mono" style="font-size:0.8rem;color:var(--aurora-ink-muted);margin-top:8px;"><a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener" style="color:inherit;text-decoration:none;"><?= Helper::e(Helper::setting('site_icp')) ?></a></p><?php endif; ?>
  </div>
</footer>

<script>
window.__CSRF = '<?= Helper::csrfToken() ?>';
window.__IS_HOME = <?= $isHome ? 'true' : 'false' ?>;
</script>
<script>
lucide.createIcons();
</script>
</body>
</html>