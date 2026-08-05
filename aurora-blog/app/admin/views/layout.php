<?php
/**
 * 后台全局布局：侧边栏 + 顶栏 + 内容区
 */
$adminUser = $_SESSION['nickname'] ?? '管理员';
$active = (string) Helper::get('r', 'dashboard');
$nav = [
    'dashboard' => ['i-dashboard', '仪表盘', '/admin/index.php?r=dashboard'],
    'posts'     => ['i-posts', '文章管理', '/admin/index.php?r=posts'],
    'categories'=> ['i-categories', '分类管理', '/admin/index.php?r=categories'],
    'tags'      => ['i-tag', '标签管理', '/admin/index.php?r=tags'],
    'comments'  => ['i-comments', '评论管理', '/admin/index.php?r=comments'],
    'media'     => ['i-media', '媒体库', '/admin/index.php?r=media'],
    'links'     => ['i-link', '友情链接', '/admin/index.php?r=links'],
    'stats'     => ['i-stats', '访问统计', '/admin/index.php?r=stats'],
    'settings'  => ['i-settings', '站点设置', '/admin/index.php?r=settings'],
    'profile'   => ['i-profile', '个人资料', '/admin/index.php?r=profile'],
];
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$tpl = $GLOBALS['__adminTpl'];

function adminIcon($id, $class = '') {
    $c = 'icon' . ($class ? ' ' . $class : '');
    return '<svg class="' . $c . '" aria-hidden="true"><use href="/assets/img/icons.svg#' . $id . '"></use></svg>';
}
?>
<?php $adminTheme = Helper::setting('theme_mode', 'dark'); $adminAccent = Helper::setting('theme_accent', '#f59e0b'); ?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="<?= Helper::e($adminTheme) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="<?= Helper::e($adminAccent) ?>">
<title>管理后台 · <?= Helper::e(Helper::setting('site_name', 'Aurora Blog')) ?></title>
<link rel="stylesheet" href="<?= Helper::asset('/assets/css/admin.css') ?>">
<style>:root{--accent:<?= Helper::e($adminAccent) ?>;--accent-rgb:<?= implode(',', sscanf($adminAccent, '#%02x%02x%02x')) ?>}</style>
<script>(function(){var s=localStorage.getItem('ab_theme'),r=document.documentElement;r.setAttribute('data-theme',s||'<?= Helper::e($adminTheme) ?>');})();</script>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-side" id="adminSide">
        <a href="/admin/index.php?r=dashboard" class="side-logo">
            <span class="logo-mark">✦</span>
            <span class="logo-text">Aurora <small>管理后台</small></span>
        </a>
        <nav class="side-nav">
            <?php foreach ($nav as $key => $n): ?>
            <?php $isActive = ($key === 'posts' && in_array($active, ['post_edit'], true)) || $key === $active; ?>
            <a class="side-link <?= $isActive ? 'active' : '' ?>" href="<?= $n[2] ?>">
                <?= adminIcon($n[0]) ?>
                <span><?= $n[1] ?></span>
                <?php if ($key === 'comments'): $pending = (int) DB::one("SELECT COUNT(*) FROM comments WHERE status='pending'"); if ($pending): ?>
                <em class="side-badge"><?= $pending ?></em>
                <?php endif; endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="side-foot">
            <a class="side-home" href="/" target="_blank">
                <?= adminIcon('i-external-link', 'icon-sm') ?> 查看前台
            </a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-top">
            <div class="top-title">
                <button class="top-toggle" id="sideToggle" aria-label="展开菜单">
                    <?= adminIcon('i-menu') ?>
                </button>
                <h1><?= $nav[$active][1] ?? '管理后台' ?></h1>
            </div>
            <div class="top-actions">
                <button class="icon-btn" id="adminThemeToggle" aria-label="切换主题">
                    <?= adminIcon('i-moon', 'icon-moon') ?>
                    <?= adminIcon('i-sun', 'icon-sun') ?>
                </button>
                <a href="/admin/index.php?r=profile" class="top-avatar" title="个人资料"><?= Helper::e(mb_substr($adminUser, 0, 1)) ?></a>
                <div class="top-user">
                    <div class="top-userinfo">
                        <b><?= Helper::e($adminUser) ?></b>
                        <a href="/admin/index.php?r=logout" class="top-logout" onclick="return confirm('确定退出登录？')">退出登录</a>
                    </div>
                </div>
            </div>
        </header>

        <?php if ($flash): ?><div class="flash flash-ok" id="flashBox"><?= adminIcon('i-check-circle') ?> <?= Helper::e($flash) ?></div><?php endif; ?>

        <div class="admin-content">
            <?php require dirname(__DIR__) . '/views/' . $tpl . '.php'; ?>
        </div>

        <footer class="admin-foot">
            <span>Aurora Blog 极光博客 v1.0 · 原生 PHP · 用心构建</span>
            <span><?= adminIcon('i-moon', 'icon-sm') ?> 自动主题</span>
        </footer>
    </div>
</div>
<div class="side-overlay" id="sideOverlay" aria-hidden="true"></div>
<script>
window.__CSRF = '<?= Helper::csrfToken() ?>';
</script>
<script src="<?= Helper::asset('/assets/js/admin.js') ?>"></script>
</body>
</html>
