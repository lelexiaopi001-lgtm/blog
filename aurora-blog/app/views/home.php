<?php
$GLOBALS['__pageTitle'] = '';
$GLOBALS['__nav'] = 'home';
$siteName = Helper::setting('site_name', 'Aurora Blog');
$siteDesc = Helper::setting('site_desc', '');
$heroBg = Helper::setting('site_hero_bg');
?>

<!-- Hero -->
<section class="hero reveal">
    <?php if ($heroBg): ?>
    <div class="hero-bg">
        <img src="<?= Helper::e($heroBg) ?>" alt="" loading="eager">
    </div>
    <?php endif; ?>
    <div class="hero-content">
        <p class="hero-eyebrow"><?= Helper::e(Helper::setting('site_hero_eyebrow', 'WELCOME')) ?></p>
        <h1 class="hero-title"><?= Helper::e($siteName) ?></h1>
        <p class="hero-desc"><?= Helper::e(Helper::setting('site_slogan') ?: $siteDesc) ?></p>
        <?php if (Helper::setting('site_motto')): ?>
        <p class="hero-motto"><?= Helper::e(Helper::setting('site_motto')) ?></p>
        <?php endif; ?>
        <div class="hero-actions">
            <a class="btn btn-primary" href="/archive">
                <svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-book-open"></use></svg>
                开始阅读
            </a>
            <a class="btn btn-ghost" href="/about">关于我</a>
        </div>
    </div>
    <?php if (Helper::setting('site_avatar')): ?>
    <div class="hero-visual reveal">
        <img src="<?= Helper::e(Helper::setting('site_avatar')) ?>" alt="<?= Helper::e($siteName) ?>">
    </div>
    <?php endif; ?>
</section>

<?php if (empty($posts) && empty($tops)): ?>
<div class="empty-state reveal">
    <div class="empty-icon">🚀</div>
    <h2>欢迎来到 <?= Helper::e($siteName) ?></h2>
    <p>博客刚刚搭建好，还没有文章。</p>
    <a class="btn btn-primary" href="/admin/index.php?r=post_edit" style="margin-top:16px">去后台写第一篇文章</a>
</div>
<?php endif; ?>

<!-- 精选文章 -->
<?php if (!empty($tops)): ?>
<section class="featured-section reveal">
    <h2 class="section-title">精选文章</h2>
    <div class="post-grid">
        <?php foreach (array_slice($tops, 0, 2) as $top): ?>
        <article class="post-card featured">
            <div class="featured-badge">精选</div>
            <?php if ($top['cover']): ?>
            <div class="card-cover">
                <img src="<?= Helper::e($top['cover']) ?>" alt="<?= Helper::e($top['title']) ?>" loading="lazy">
            </div>
            <?php endif; ?>
            <div class="card-body">
                <div class="card-meta">
                    <?php if ($top['cat_name']): ?><a class="cat-badge" href="/category/<?= Helper::e($top['cat_slug']) ?>"><?= Helper::e($top['cat_name']) ?></a><?php endif; ?>
                    <time datetime="<?= Helper::e($top['published_at']) ?>"><?= Helper::date($top['published_at']) ?></time>
                    <span><?= Helper::readingTime((string) $top['content']) ?> 分钟</span>
                </div>
                <h2 class="card-title"><a href="/post/<?= Helper::e($top['slug']) ?>"><?= Helper::e($top['title']) ?></a></h2>
                <p class="card-summary"><?= Helper::e($top['summary'] ?: Markdown::plain((string) $top['content'], 140)) ?></p>
                <div class="card-foot">
                    <span><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-eye"></use></svg> <?= (int) $top['views'] ?></span>
                    <span><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-message"></use></svg> <?= (int) $top['comment_count'] ?></span>
                    <span><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-heart"></use></svg> <?= (int) $top['likes'] ?></span>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- 最新文章 -->
<?php if (!empty($posts)): ?>
<section class="latest-section reveal">
    <h2 class="section-title">最新文章</h2>
    <div class="post-list">
        <?php foreach ($posts as $p): ?>
        <article class="post-list-item reveal">
            <div>
                <h3><a href="/post/<?= Helper::e($p['slug']) ?>"><?= Helper::e($p['title']) ?></a></h3>
                <div class="post-list-meta">
                    <?php if ($p['cat_name']): ?><a href="/category/<?= Helper::e($p['cat_slug']) ?>"><?= Helper::e($p['cat_name']) ?></a><?php endif; ?>
                    <time datetime="<?= Helper::e($p['published_at']) ?>"><?= Helper::date($p['published_at']) ?></time>
                    <span><?= Helper::readingTime((string) $p['content']) ?> 分钟</span>
                </div>
            </div>
            <time class="post-list-date" datetime="<?= Helper::e($p['published_at']) ?>"><?= Helper::date($p['published_at'], 'Y-m-d') ?></time>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?= Helper::pagination($pg, '/page/') ?>
<?php endif; ?>
