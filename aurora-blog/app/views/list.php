<?php
$GLOBALS['__pageTitle'] = $meta['title'] ?? '';
$GLOBALS['__nav'] = $type === 'archive' ? 'archive' : '';
?>
<section class="list-header reveal">
    <h1 class="list-title"><?= Helper::e($meta['title'] ?? '') ?></h1>
    <?php if (!empty($meta['desc'])): ?><p class="list-desc"><?= Helper::e($meta['desc']) ?></p><?php endif; ?>
    <?php if (!empty($meta['q'])): ?>
    <form class="list-search" action="/search" method="get">
        <input type="search" name="q" value="<?= Helper::e($meta['q']) ?>" placeholder="继续搜索…">
        <button class="btn btn-primary" type="submit">搜索</button>
    </form>
    <?php endif; ?>
</section>

<?php if (empty($posts)): ?>
<div class="empty-state reveal">
    <div class="empty-icon">🕳️</div>
    <p><?= $type === 'search' ? '没有找到相关文章，换个关键词试试？' : '这里还没有文章' ?></p>
</div>
<?php else: ?>
<div class="post-list">
<?php foreach ($posts as $p): ?>
<article class="post-list-item reveal">
    <div>
        <h3><a href="/post/<?= Helper::e($p['slug']) ?>"><?= Helper::e($p['title']) ?></a></h3>
        <div class="post-list-meta">
            <?php if ($p['cat_name']): ?><a href="/category/<?= Helper::e($p['cat_slug']) ?>"><?= Helper::e($p['cat_name']) ?></a><?php endif; ?>
            <time datetime="<?= Helper::e($p['published_at']) ?>"><?= Helper::date($p['published_at']) ?></time>
            <span><?= Helper::readingTime((string) $p['content']) ?> 分钟</span>
            <?php if ($p['tags']): ?><span><?php foreach (explode(',', $p['tags']) as $t): ?>#<?= Helper::e($t) ?> <?php endforeach; ?></span><?php endif; ?>
        </div>
    </div>
    <time class="post-list-date" datetime="<?= Helper::e($p['published_at']) ?>"><?= Helper::date($p['published_at'], 'Y-m-d') ?></time>
</article>
<?php endforeach; ?>
</div>
<?= Helper::pagination($pg, $base) ?>
<?php endif; ?>
