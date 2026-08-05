<?php
$GLOBALS['__pageTitle'] = $meta['title'] ?? '';
$GLOBALS['__nav'] = $type === 'archive' ? 'archive' : '';
?>

<section class="page-banner" id="list-banner">
    <div class="page-banner-content">
        <h1 class="page-banner-title font-title"><?= Helper::e($meta['title'] ?? '') ?></h1>
        <?php if (!empty($meta['desc'])): ?><p class="page-banner-subtitle font-body"><?= Helper::e($meta['desc']) ?></p><?php endif; ?>
        <?php if (!empty($meta['q'])): ?>
        <form action="/search" method="get" style="margin-top:20px;display:flex;gap:8px;justify-content:center;">
            <input type="search" name="q" value="<?= Helper::e($meta['q']) ?>" placeholder="继续搜索…"
                style="padding:10px 16px;border:1px solid rgba(255,255,255,0.4);border-radius:var(--aurora-radius-full);background:rgba(255,255,255,0.15);color:#fff;min-width:260px;font-family:var(--aurora-font-body);">
            <button class="btn btn-primary font-body" type="submit" style="background:rgba(255,255,255,0.25);border-color:rgba(255,255,255,0.4);">搜索</button>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php if (empty($posts)): ?>
<section class="section" style="text-align:center;padding:120px 24px;">
    <div class="section-inner">
        <p style="color:var(--aurora-ink-muted);font-size:1.1rem;"><?= $type === 'search' ? '没有找到相关文章，换个关键词试试？' : '这里还没有文章' ?></p>
    </div>
</section>
<?php else: ?>
<section class="section" id="post-grid">
    <div class="section-inner">
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
        <?= Helper::pagination($pg, $base) ?>
    </div>
</section>
<?php endif; ?>