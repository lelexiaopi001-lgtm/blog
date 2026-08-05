<?php
$GLOBALS['__pageTitle'] = '关于';
$GLOBALS['__nav'] = 'about';
$siteName = Helper::setting('site_name', 'Aurora Blog');
$avatar = Helper::setting('site_avatar');
$email = Helper::setting('site_social_email');
$github = Helper::setting('site_social_github');
$twitter = Helper::setting('site_social_twitter');
$about = Helper::setting('site_about', '');
?>

<section class="about-hero" id="about-hero">
    <div class="about-hero-content">
        <h1 class="page-banner-title font-title">关于 <?= Helper::e($siteName) ?></h1>
        <p class="page-banner-subtitle font-body">一座记录四季与心绪的私人神社</p>
    </div>
</section>

<section class="section" id="about-body" style="padding-top:0;">
    <div class="about-card">
        <div class="avatar" role="img" aria-label="站主头像"
            <?php if ($avatar): ?>style="background-image:url('<?= Helper::e($avatar) ?>')"<?php endif; ?>></div>
        <h2 class="about-name font-title"><?= Helper::e($siteName) ?></h2>
        <?php if ($about): ?>
        <div class="about-bio font-body">
            <?= Markdown::render($about) ?>
        </div>
        <?php endif; ?>

        <div class="social-links">
            <?php if ($email): ?>
            <a href="mailto:<?= Helper::e($email) ?>" class="social-link" aria-label="邮箱">
                <i data-lucide="mail" style="width:18px;height:18px;"></i>
            </a>
            <?php endif; ?>
            <?php if ($github): ?>
            <a href="<?= Helper::e($github) ?>" class="social-link" target="_blank" rel="noopener" aria-label="GitHub">
                <i data-lucide="github" style="width:18px;height:18px;"></i>
            </a>
            <?php endif; ?>
            <?php if ($twitter): ?>
            <a href="<?= Helper::e($twitter) ?>" class="social-link" target="_blank" rel="noopener" aria-label="Twitter / X">
                <i data-lucide="twitter" style="width:18px;height:18px;"></i>
            </a>
            <?php endif; ?>
            <a href="/rss.xml" class="social-link" aria-label="RSS">
                <i data-lucide="rss" style="width:18px;height:18px;"></i>
            </a>
        </div>
    </div>
</section>

<?php if (!empty($links)): ?>
<section class="section">
    <div class="section-inner">
        <h2 class="section-title font-title">友情链接</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;">
            <?php foreach ($links as $l): ?>
            <a href="<?= Helper::e($l['url']) ?>" target="_blank" rel="noopener"
                style="display:flex;align-items:center;gap:14px;padding:16px;background:var(--aurora-card);border:1px solid var(--aurora-border);border-radius:var(--aurora-radius-lg);text-decoration:none;color:inherit;transition:transform 0.2s ease,box-shadow 0.2s ease;"
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px color-mix(in srgb, var(--aurora-ink) 8%, transparent)';"
                onmouseout="this.style.transform='';this.style.boxShadow='';">
                <?php if ($l['logo']): ?>
                <img src="<?= Helper::e($l['logo']) ?>" alt="" loading="lazy" style="width:40px;height:40px;border-radius:50%;flex-shrink:0;">
                <?php else: ?>
                <span style="width:40px;height:40px;border-radius:50%;background:var(--aurora-season-bg);display:flex;align-items:center;justify-content:center;color:var(--aurora-season-deep);font-weight:600;flex-shrink:0;font-family:var(--aurora-font-title);"><?= Helper::e(mb_substr($l['name'], 0, 1)) ?></span>
                <?php endif; ?>
                <div>
                    <b style="font-family:var(--aurora-font-body);"><?= Helper::e($l['name']) ?></b>
                    <?php if ($l['description']): ?><small style="display:block;color:var(--aurora-ink-muted);font-size:0.85rem;font-family:var(--aurora-font-body);"><?= Helper::e($l['description']) ?></small><?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>