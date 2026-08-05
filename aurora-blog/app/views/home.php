<?php
$GLOBALS['__pageTitle'] = '';
$GLOBALS['__nav'] = 'home';
$siteName = Helper::setting('site_name', 'Aurora Blog');
$siteDesc = Helper::setting('site_desc', '');
$heroBg = Helper::setting('site_hero_bg');
?>

<!-- Hero -->
<section class="hero" id="hero">
    <div class="hero-content">
        <svg class="hero-logo" viewBox="0 0 64 48" aria-hidden="true">
            <path d="M4 12h56M8 8v-4h48v4M12 12v28h4V12M48 12v28h4V12M20 40h24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <h1 class="hero-title font-title"><?= Helper::e($siteName) ?></h1>
        <p class="hero-tagline font-body"><?= Helper::e(Helper::setting('site_slogan') ?: $siteDesc) ?></p>
        <?php if (Helper::setting('site_motto')): ?>
        <p class="font-body" style="opacity:0.8;margin-bottom:24px;font-size:0.95rem;"><?= Helper::e(Helper::setting('site_motto')) ?></p>
        <?php endif; ?>
        <a href="#recent-posts" class="hero-scroll font-body">
            <i data-lucide="chevron-down"></i>
            <span>最近文章</span>
        </a>
    </div>
    <div class="hero-vertical font-body">
        <span>物哀</span><span>·</span><span>時間</span><span>·</span><span>参道</span>
    </div>
</section>

<?php if (empty($posts) && empty($tops)): ?>
<section class="section" style="text-align:center;padding:120px 24px;">
    <div class="section-inner">
        <h2 class="font-title" style="font-size:1.5rem;margin-bottom:16px;">欢迎来到 <?= Helper::e($siteName) ?></h2>
        <p style="color:var(--aurora-ink-muted);margin-bottom:24px;">博客刚刚搭建好，还没有文章。</p>
        <a class="btn btn-primary font-body" href="/admin/index.php?r=post_edit">去后台写第一篇文章</a>
    </div>
</section>
<?php endif; ?>

<!-- 精选文章 -->
<?php if (!empty($tops)): ?>
<section class="section" id="featured-posts">
    <div class="section-inner">
        <h2 class="section-title font-title">精选文章</h2>
        <div class="post-cards">
            <?php foreach (array_slice($tops, 0, 4) as $top): ?>
            <a href="/post/<?= Helper::e($top['slug']) ?>" class="post-card">
                <div class="post-card-image" role="img" aria-label="<?= Helper::e($top['title']) ?>"
                    <?php if ($top['cover']): ?>style="background-image:url('<?= Helper::e($top['cover']) ?>')"<?php endif; ?>></div>
                <div class="post-card-body">
                    <h3 class="post-card-title font-title"><?= Helper::e($top['title']) ?></h3>
                    <p class="post-card-excerpt font-body"><?= Helper::e($top['summary'] ?: Markdown::plain((string) $top['content'], 100)) ?></p>
                    <div class="post-card-meta font-mono">
                        <span><?= Helper::date($top['published_at'], 'Y.m.d') ?></span>
                        <?php if ($top['cat_name']): ?><span><?= Helper::e($top['cat_name']) ?></span><?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 最新文章 -->
<?php if (!empty($posts)): ?>
<section id="recent-posts" class="section">
    <div class="section-inner">
        <h2 class="section-title font-title">最近文章</h2>
        <div class="post-cards">
            <?php foreach ($posts as $p): ?>
            <a href="/post/<?= Helper::e($p['slug']) ?>" class="post-card">
                <div class="post-card-image" role="img" aria-label="<?= Helper::e($p['title']) ?>"
                    <?php if ($p['cover']): ?>style="background-image:url('<?= Helper::e($p['cover']) ?>')"<?php endif; ?>></div>
                <div class="post-card-body">
                    <h3 class="post-card-title font-title"><?= Helper::e($p['title']) ?></h3>
                    <p class="post-card-excerpt font-body"><?= Helper::e($p['summary'] ?: Markdown::plain((string) $p['content'], 100)) ?></p>
                    <div class="post-card-meta font-mono">
                        <span><?= Helper::date($p['published_at'], 'Y.m.d') ?></span>
                        <?php if ($p['cat_name']): ?><span><?= Helper::e($p['cat_name']) ?></span><?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?= Helper::pagination($pg, '/page/') ?>
    </div>
</section>
<?php endif; ?>