<?php
$GLOBALS['__pageTitle'] = '关于';
$GLOBALS['__nav'] = 'about';
$siteName = Helper::setting('site_name', 'Aurora Blog');
?>
<section class="about-page reveal">
    <h1 class="list-title" style="text-align:center;margin-bottom:32px;">关于我</h1>
    <div class="about-card">
        <div class="about-avatar">
            <?php if (Helper::setting('site_avatar')): ?>
            <img src="<?= Helper::e(Helper::setting('site_avatar')) ?>" alt="avatar">
            <?php else: ?><span><?= Helper::e(mb_substr(Helper::setting('site_name', 'A'), 0, 1)) ?></span><?php endif; ?>
        </div>
        <div class="about-info">
            <h2><?= Helper::e($siteName) ?></h2>
            <div class="about-links">
                <?php if (Helper::setting('site_social_email')): ?><a class="about-link" href="mailto:<?= Helper::e(Helper::setting('site_social_email')) ?>"><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-mail"></use></svg> <?= Helper::e(Helper::setting('site_social_email')) ?></a><?php endif; ?>
                <?php if (Helper::setting('site_social_github')): ?><a class="about-link" href="<?= Helper::e(Helper::setting('site_social_github')) ?>" target="_blank" rel="noopener"><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-github"></use></svg> GitHub</a><?php endif; ?>
                <?php if (Helper::setting('site_social_twitter')): ?><a class="about-link" href="<?= Helper::e(Helper::setting('site_social_twitter')) ?>" target="_blank" rel="noopener"><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-twitter"></use></svg> Twitter / X</a><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="prose about-content"><?= Markdown::render(Helper::setting('site_about', '')) ?></div>
</section>

<?php if (!empty($links)): ?>
<section class="links-page reveal">
    <h2 class="section-title">友情链接</h2>
    <div class="links-grid">
        <?php foreach ($links as $l): ?>
        <a class="link-card" href="<?= Helper::e($l['url']) ?>" target="_blank" rel="noopener">
            <?php if ($l['logo']): ?><img class="link-logo" src="<?= Helper::e($l['logo']) ?>" alt="" loading="lazy">
            <?php else: ?><span class="link-logo fallback"><?= Helper::e(mb_substr($l['name'], 0, 1)) ?></span><?php endif; ?>
            <div class="link-info">
                <b><?= Helper::e($l['name']) ?></b>
                <?php if ($l['description']): ?><small><?= Helper::e($l['description']) ?></small><?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
