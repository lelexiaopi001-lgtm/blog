<?php
$GLOBALS['__pageTitle'] = $post['title'];
$GLOBALS['__pageDesc'] = $post['summary'] ?: Markdown::plain((string) $post['content'], 80);
$contentHtml = Markdown::render((string) $post['content']);
$toc = Helper::toc($contentHtml);
$readMin = Helper::readingTime((string) $post['content']);
?>

<div class="article-layout">
    <?php if (count($toc) >= 3): ?>
    <nav class="article-toc reveal" aria-label="文章目录">
        <div class="toc-title">目录</div>
        <ul class="toc-list">
            <?php foreach ($toc as $h): ?>
            <li class="toc-h<?= $h['level'] ?>"><a href="#<?= Helper::e($h['id']) ?>"><?= Helper::e($h['title']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <article class="article-main">
        <header class="article-header reveal">
            <div class="article-meta">
                <?php if ($post['cat_name']): ?><a class="cat-badge" href="/category/<?= Helper::e($post['cat_slug']) ?>"><?= Helper::e($post['cat_name']) ?></a><?php endif; ?>
                <span><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-clock"></use></svg> <?= Helper::date($post['published_at'], 'Y-m-d') ?></span>
                <span><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-book-open"></use></svg> <?= $readMin ?> 分钟</span>
                <span title="约 <?= (int) $post['word_count'] ?> 字"><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-posts"></use></svg> <?= (int) $post['word_count'] ?> 字</span>
                <span><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-eye"></use></svg> <?= (int) $post['views'] ?></span>
            </div>
            <h1 class="article-title"><?= Helper::e($post['title']) ?></h1>
            <?php if ($post['tags']): ?>
            <div class="article-tags">
                <?php foreach ($post['tags'] as $t): ?><a class="tag-chip" href="/tag/<?= Helper::e($t['slug']) ?>"># <?= Helper::e($t['name']) ?></a><?php endforeach; ?>
            </div>
            <?php endif; ?>
        </header>

        <?php if ($post['cover']): ?>
        <div class="article-cover reveal">
            <img src="<?= Helper::e($post['cover']) ?>" alt="<?= Helper::e($post['title']) ?>">
        </div>
        <?php endif; ?>

        <div class="article-content prose reveal" id="articleBody">
            <?= $contentHtml ?>
        </div>

        <div class="article-actions reveal">
            <button class="like-btn" data-id="<?= (int) $post['id'] ?>" data-likes="<?= (int) $post['likes'] ?>">
                <svg class="icon icon-sm like-heart" aria-hidden="true"><use href="/assets/img/icons.svg#i-heart"></use></svg>
                点赞 <em><?= (int) $post['likes'] ?></em>
            </button>
            <span class="article-tip">本文由 <?= Helper::e($post['author'] ?: '作者') ?> 原创</span>
        </div>

        <nav class="post-nav reveal">
            <?php if ($prev): ?><a class="post-nav-item prev" href="/post/<?= Helper::e($prev['slug']) ?>"><small>← 上一篇</small><?= Helper::e($prev['title']) ?></a>
            <?php else: ?><span class="post-nav-item disabled"><small>← 上一篇</small>没有了</span><?php endif; ?>
            <?php if ($next): ?><a class="post-nav-item next" href="/post/<?= Helper::e($next['slug']) ?>"><small>下一篇 →</small><?= Helper::e($next['title']) ?></a>
            <?php else: ?><span class="post-nav-item disabled"><small>下一篇 →</small>没有了</span><?php endif; ?>
        </nav>
    </article>
</div>

<?php if (!empty($related)): ?>
<section class="related reveal">
    <h3 class="section-title">相关推荐</h3>
    <div class="related-grid">
        <?php foreach ($related as $r): ?>
        <a class="related-card" href="/post/<?= Helper::e($r['slug']) ?>">
            <?php if ($r['cover']): ?><img src="<?= Helper::e($r['cover']) ?>" alt="" loading="lazy"><?php endif; ?>
            <h4><?= Helper::e($r['title']) ?></h4>
            <small><?= Helper::date($r['published_at']) ?> · <svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-eye"></use></svg> <?= (int) $r['views'] ?></small>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="comments reveal" id="comments">
    <h3 class="section-title">
        评论
        <?php if ($post['comment_count'] > 0): ?><em>(<?= (int) $post['comment_count'] ?>)</em><?php endif; ?>
    </h3>
    <?php if (Helper::setting('comment_switch', '1') === '1'): ?>
    <form class="comment-form" id="commentForm" novalidate>
        <?= Helper::csrfField() ?>
        <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
        <input type="hidden" name="parent_id" id="parentId" value="0">
        <input type="hidden" name="website_hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="hp-field">
        <div class="cf-row">
            <input type="text" name="nickname" placeholder="昵称 *" maxlength="30" required>
            <input type="email" name="email" placeholder="邮箱（不会被公开）" maxlength="100">
            <input type="text" name="website" placeholder="网站（可选）" maxlength="200">
        </div>
        <textarea name="content" placeholder="写下你的想法…（支持 Markdown）" rows="4" maxlength="2000" required></textarea>
        <div class="cf-foot">
            <span class="cf-tip" id="replyTip" hidden>正在回复 <b></b> <a href="#" onclick="return cancelReply()">取消</a></span>
            <button class="btn btn-primary" type="submit">发表评论</button>
        </div>
    </form>
    <?php endif; ?>

    <div class="comment-list" id="commentList">
        <?php if (empty($tree)): ?>
        <p class="empty-tip">还没有评论，来抢沙发吧～</p>
        <?php else: foreach ($tree as $c): ?>
        <div class="comment-item" id="comment-<?= (int) $c['id'] ?>">
            <div class="comment-avatar"><?= Helper::e(mb_substr($c['nickname'], 0, 1)) ?></div>
            <div class="comment-main">
                <div class="comment-head">
                    <b><?= Helper::e($c['nickname']) ?></b>
                    <?php if ($c['website']): ?><a href="<?= Helper::e($c['website']) ?>" target="_blank" rel="noopener" class="comment-site"><svg class="icon icon-sm"><use href="/assets/img/icons.svg#i-link"></use></svg></a><?php endif; ?>
                    <time><?= Helper::timeAgo($c['created_at']) ?></time>
                </div>
                <div class="comment-content"><?= nl2br(Helper::e($c['content'])) ?></div>
                <?php if (Helper::setting('comment_switch', '1') === '1'): ?>
                <a class="comment-reply" href="#" onclick="return replyTo(<?= (int) $c['id'] ?>, '<?= Helper::e($c['nickname']) ?>')">回复</a>
                <?php endif; ?>
                <?php if (!empty($c['children'])): foreach ($c['children'] as $cc): ?>
                <div class="comment-item child">
                    <div class="comment-avatar"><?= Helper::e(mb_substr($cc['nickname'], 0, 1)) ?></div>
                    <div class="comment-main">
                        <div class="comment-head"><b><?= Helper::e($cc['nickname']) ?></b><time><?= Helper::timeAgo($cc['created_at']) ?></time></div>
                        <div class="comment-content"><?= nl2br(Helper::e($cc['content'])) ?></div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</section>
