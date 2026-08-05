<?php
/**
 * 前台全局布局
 * 可用数据: $GLOBALS['side'] 侧边栏 / View::content() 内容区
 */
$cfg = $GLOBALS['__config']['app'];
$siteName = Helper::setting('site_name', 'Aurora Blog');
$siteDesc = Helper::setting('site_desc', '');
$pageTitle = $GLOBALS['__pageTitle'] ?? '';
$theme = Helper::setting('theme_mode', 'dark');
$accent = Helper::setting('theme_accent', '#f59e0b');
$isHome = ($GLOBALS['__nav'] ?? '') === 'home';
$pwaEnabled = Helper::setting('pwa_enabled', '1') === '1';
$noticeEnabled = Helper::setting('site_notice_enabled', '0') === '1';
$notice = Helper::setting('site_notice', '');
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="<?= Helper::e($theme) ?>">
<head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= Helper::e($GLOBALS['__pageDesc'] ?? $siteDesc) ?>">
<meta name="keywords" content="<?= Helper::e(Helper::setting('site_keywords')) ?>">
<meta name="theme-color" content="<?= Helper::e($accent) ?>">
<meta name="author" content="<?= Helper::e($siteName) ?>">
<title><?= $pageTitle ? Helper::e($pageTitle) . ' - ' : '' ?><?= Helper::e($siteName) ?></title>
<link rel="canonical" href="<?= Helper::e($GLOBALS['__pageUrl'] ?? Helper::siteUrl($_SERVER['REQUEST_URI'] ?? '/')) ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>✦</text></svg>">
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
<link rel="stylesheet" href="<?= Helper::asset('/assets/css/main.css') ?>">
<style>:root{--accent:<?= Helper::e($accent) ?>;--accent-rgb:<?= implode(',', sscanf($accent, '#%02x%02x%02x')) ?>}</style>
<script>(function(){var s=localStorage.getItem('ab_theme'),r=document.documentElement;r.setAttribute('data-theme',s||(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'));})();</script>
</head>
<body class="<?= $isHome ? 'is-home' : '' ?>">
<div class="read-progress" id="readProgress" aria-hidden="true"></div>

<?php if ($noticeEnabled && $notice): ?>
<div class="site-notice" id="siteNotice">
    <div class="container notice-inner">
        <span class="notice-text"><?= Markdown::render($notice) ?></span>
        <button class="notice-close" id="noticeClose" aria-label="关闭公告"><svg class="icon" aria-hidden="true"><use href="/assets/img/icons.svg#i-close"></use></svg></button>
    </div>
</div>
<?php endif; ?>

<header class="site-header" id="siteHeader">
    <div class="container header-inner">
        <a href="/" class="logo">
            <span class="logo-mark">✦</span>
            <span class="logo-text"><?= Helper::e($siteName) ?></span>
        </a>
        <nav class="site-nav" id="siteNav">
            <a href="/" class="nav-link <?= ($GLOBALS['__nav'] ?? '') === 'home' ? 'active' : '' ?>">首页</a>
            <a href="/archive" class="nav-link <?= ($GLOBALS['__nav'] ?? '') === 'archive' ? 'active' : '' ?>">归档</a>
            <a href="/about" class="nav-link <?= ($GLOBALS['__nav'] ?? '') === 'about' ? 'active' : '' ?>">关于</a>
            <a href="/rss.xml" class="nav-link">RSS</a>
            <form class="nav-search" action="/search" method="get" role="search">
                <input type="search" name="q" placeholder="搜索文章…" value="<?= Helper::e(Helper::get('q')) ?>" aria-label="搜索">
            </form>
        </nav>
        <div class="header-actions">
            <button class="icon-btn" id="themeToggle" title="切换主题" aria-label="切换主题">
                <svg class="icon icon-moon" aria-hidden="true"><use href="/assets/img/icons.svg#i-moon"></use></svg>
                <svg class="icon icon-sun" aria-hidden="true"><use href="/assets/img/icons.svg#i-sun"></use></svg>
            </button>
            <button class="icon-btn menu-btn" id="menuToggle" aria-label="菜单" aria-expanded="false">
                <svg class="icon" aria-hidden="true"><use href="/assets/img/icons.svg#i-menu"></use></svg>
            </button>
        </div>
    </div>
</header>

<main class="container main-grid">
    <div class="content-area"><?php View::content(); ?></div>
    <?php $side = $GLOBALS['side'] ?? []; if (!$isHome && !empty($side)): ?>
    <aside class="sidebar">
        <?php if (!empty($side['categories'])): ?>
        <section class="widget reveal">
            <h3 class="widget-title">分类</h3>
            <div class="cat-list">
                <?php foreach ($side['categories'] as $c): ?>
                <a class="cat-item" href="/category/<?= Helper::e($c['slug']) ?>">
                    <span><?= Helper::e($c['name']) ?></span><em><?= (int) $c['post_count'] ?></em>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($side['tags'])): ?>
        <section class="widget reveal">
            <h3 class="widget-title">标签</h3>
            <div class="tag-cloud">
                <?php foreach ($side['tags'] as $t): ?>
                <a class="tag-chip" href="/tag/<?= Helper::e($t['slug']) ?>"># <?= Helper::e($t['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($side['recent'])): ?>
        <section class="widget reveal">
            <h3 class="widget-title">最新文章</h3>
            <ol class="hot-list">
                <?php foreach ($side['recent'] as $h): ?>
                <li><a href="/post/<?= Helper::e($h['slug']) ?>"><?= Helper::e($h['title']) ?></a></li>
                <?php endforeach; ?>
            </ol>
        </section>
        <?php endif; ?>

        <?php if (!empty($side['archive'])): ?>
        <section class="widget reveal">
            <h3 class="widget-title">时间线</h3>
            <ul class="archive-list">
                <?php foreach ($side['archive'] as $a): ?>
                <li>
                    <a href="/search?q=<?= Helper::e($a['ym']) ?>"><?= Helper::e($a['ym']) ?></a><em><?= (int) $a['cnt'] ?></em>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
    </aside>
    <?php endif; ?>
</main>

<button class="back-to-top" id="backToTop" aria-label="回到顶部" title="回到顶部">
    <svg class="icon" aria-hidden="true"><use href="/assets/img/icons.svg#i-arrow-left"></use></svg>
</button>

<footer class="site-footer">
    <div class="container footer-inner">
        <p>© <?= date('Y') ?> <?= Helper::e($siteName) ?> · <?= Helper::e(Helper::setting('footer_text', '用 ❤ 与代码构建')) ?></p>
        <?php if (Helper::setting('site_icp')): ?><p class="icp"><a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener"><?= Helper::e(Helper::setting('site_icp')) ?></a></p><?php endif; ?>
    </div>
</footer>

<script>
window.__CSRF = '<?= Helper::csrfToken() ?>';
window.__IS_HOME = <?= $isHome ? 'true' : 'false' ?>;
window.__PWA_ENABLED = <?= $pwaEnabled ? 'true' : 'false' ?>;
</script>
<script src="<?= Helper::asset('/assets/js/main.js') ?>"></script>
</body>
</html>
