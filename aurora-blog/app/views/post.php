<?php
$GLOBALS['__pageTitle'] = $post['title'];
$GLOBALS['__pageDesc'] = $post['summary'] ?: Markdown::plain((string) $post['content'], 80);
$contentHtml = Markdown::render((string) $post['content']);
$readMin = Helper::readingTime((string) $post['content']);
?>

<article class="article-layout">
    <header class="article-header">
        <h1 class="article-title font-title"><?= Helper::e($post['title']) ?></h1>
        <div class="article-meta font-mono">
            <?php if ($post['cat_name']): ?><span><i data-lucide="folder" style="width:14px;height:14px;"></i> <?= Helper::e($post['cat_name']) ?></span><?php endif; ?>
            <span><i data-lucide="calendar" style="width:14px;height:14px;"></i> <?= Helper::date($post['published_at'], 'Y.m.d') ?></span>
            <span><i data-lucide="clock" style="width:14px;height:14px;"></i> <?= $readMin ?> 分钟</span>
            <span><i data-lucide="eye" style="width:14px;height:14px;"></i> <?= (int) $post['views'] ?></span>
        </div>
        <?php if ($post['tags']): ?>
        <div class="article-tags">
            <?php foreach ($post['tags'] as $t): ?><a href="/tag/<?= Helper::e($t['slug']) ?>" class="tag font-body"><?= Helper::e($t['name']) ?></a><?php endforeach; ?>
        </div>
        <?php endif; ?>
    </header>

    <?php if ($post['cover']): ?>
    <div style="text-align:center;margin-bottom:40px;">
        <img src="<?= Helper::e($post['cover']) ?>" alt="<?= Helper::e($post['title']) ?>" style="max-width:100%;border-radius:var(--aurora-radius-lg);">
    </div>
    <?php endif; ?>

    <div class="article-body font-body">
        <?= $contentHtml ?>
    </div>

    <div class="article-actions" style="text-align:center;margin-bottom:32px;">
        <button class="like-btn btn btn-ghost font-body" data-id="<?= (int) $post['id'] ?>" data-likes="<?= (int) $post['likes'] ?>">
            <i data-lucide="heart" style="width:16px;height:16px;"></i>
            点赞 <em><?= (int) $post['likes'] ?></em>
        </button>
    </div>

    <footer class="article-footer">
        <?php if ($prev): ?>
        <a href="/post/<?= Helper::e($prev['slug']) ?>" class="btn btn-ghost font-body">
            <i data-lucide="arrow-left"></i>
            <span>上一篇</span>
        </a>
        <?php else: ?>
        <span class="btn btn-ghost font-body" style="opacity:0.5;cursor:default;">
            <i data-lucide="arrow-left"></i>
            <span>上一篇</span>
        </span>
        <?php endif; ?>

        <?php if ($next): ?>
        <a href="/post/<?= Helper::e($next['slug']) ?>" class="btn btn-primary font-body">
            <span>下一篇</span>
            <i data-lucide="arrow-right"></i>
        </a>
        <?php else: ?>
        <span class="btn btn-primary font-body" style="opacity:0.5;cursor:default;">
            <span>下一篇</span>
            <i data-lucide="arrow-right"></i>
        </span>
        <?php endif; ?>
    </footer>
</article>

<?php if (!empty($related)): ?>
<section class="section">
    <div class="section-inner">
        <h2 class="section-title font-title">相关推荐</h2>
        <div class="post-cards">
            <?php foreach ($related as $r): ?>
            <a href="/post/<?= Helper::e($r['slug']) ?>" class="post-card">
                <div class="post-card-image" role="img" aria-label="<?= Helper::e($r['title']) ?>"
                    <?php if ($r['cover']): ?>style="background-image:url('<?= Helper::e($r['cover']) ?>')"<?php endif; ?>></div>
                <div class="post-card-body">
                    <h3 class="post-card-title font-title"><?= Helper::e($r['title']) ?></h3>
                    <div class="post-card-meta font-mono">
                        <span><?= Helper::date($r['published_at'], 'Y.m.d') ?></span>
                        <span><?= (int) $r['views'] ?> 次阅读</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 评论区 -->
<section class="section" id="comments">
    <div class="section-inner">
        <h2 class="section-title font-title">
            评论
            <?php if ($post['comment_count'] > 0): ?><em style="font-size:0.8rem;color:var(--aurora-ink-muted);">(<?= (int) $post['comment_count'] ?>)</em><?php endif; ?>
        </h2>

        <?php if (Helper::setting('comment_switch', '1') === '1'): ?>
        <form class="comment-form" id="commentForm" novalidate style="margin-bottom:48px;">
            <?= Helper::csrfField() ?>
            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
            <input type="hidden" name="parent_id" id="parentId" value="0">
            <input type="hidden" name="website_hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="hp-field">
            <div style="display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                <input type="text" name="nickname" placeholder="昵称 *" maxlength="30" required
                    style="flex:1;min-width:120px;padding:10px 16px;border:1px solid var(--aurora-border);border-radius:var(--aurora-radius-md);background:var(--aurora-card);color:var(--aurora-foreground);font-family:var(--aurora-font-body);">
                <input type="email" name="email" placeholder="邮箱（不会被公开）" maxlength="100"
                    style="flex:1;min-width:120px;padding:10px 16px;border:1px solid var(--aurora-border);border-radius:var(--aurora-radius-md);background:var(--aurora-card);color:var(--aurora-foreground);font-family:var(--aurora-font-body);">
                <input type="text" name="website" placeholder="网站（可选）" maxlength="200"
                    style="flex:1;min-width:120px;padding:10px 16px;border:1px solid var(--aurora-border);border-radius:var(--aurora-radius-md);background:var(--aurora-card);color:var(--aurora-foreground);font-family:var(--aurora-font-body);">
            </div>
            <textarea name="content" placeholder="写下你的想法…（支持 Markdown）" rows="4" maxlength="2000" required
                style="width:100%;padding:12px 16px;border:1px solid var(--aurora-border);border-radius:var(--aurora-radius-md);background:var(--aurora-card);color:var(--aurora-foreground);font-family:var(--aurora-font-body);resize:vertical;margin-bottom:12px;"></textarea>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span id="replyTip" hidden style="font-size:0.9rem;color:var(--aurora-ink-muted);">正在回复 <b></b> <a href="#" onclick="return cancelReply()" style="color:var(--aurora-season-deep);">取消</a></span>
                <button class="btn btn-primary font-body" type="submit" style="margin-left:auto;">发表评论</button>
            </div>
        </form>
        <?php endif; ?>

        <div id="commentList">
            <?php if (empty($tree)): ?>
            <p style="text-align:center;color:var(--aurora-ink-muted);padding:40px 0;">还没有评论，来抢沙发吧～</p>
            <?php else: foreach ($tree as $c): ?>
            <div id="comment-<?= (int) $c['id'] ?>" style="padding:20px 0;border-bottom:1px solid var(--aurora-border);">
                <div style="display:flex;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:50%;background:var(--aurora-season-bg);display:flex;align-items:center;justify-content:center;color:var(--aurora-season-deep);font-weight:600;flex-shrink:0;font-family:var(--aurora-font-title);"><?= Helper::e(mb_substr($c['nickname'], 0, 1)) ?></div>
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                            <b style="font-family:var(--aurora-font-body);"><?= Helper::e($c['nickname']) ?></b>
                            <?php if ($c['website']): ?><a href="<?= Helper::e($c['website']) ?>" target="_blank" rel="noopener" style="color:var(--aurora-ink-muted);"><i data-lucide="link" style="width:12px;height:12px;"></i></a><?php endif; ?>
                            <time style="font-size:0.8rem;color:var(--aurora-ink-muted);font-family:var(--aurora-font-mono);"><?= Helper::timeAgo($c['created_at']) ?></time>
                        </div>
                        <div style="color:var(--aurora-foreground);line-height:1.7;font-family:var(--aurora-font-body);"><?= nl2br(Helper::e($c['content'])) ?></div>
                        <?php if (Helper::setting('comment_switch', '1') === '1'): ?>
                        <a href="#" onclick="return replyTo(<?= (int) $c['id'] ?>, '<?= Helper::e($c['nickname']) ?>')" style="font-size:0.85rem;color:var(--aurora-ink-muted);margin-top:4px;display:inline-block;">回复</a>
                        <?php endif; ?>
                        <?php if (!empty($c['children'])): foreach ($c['children'] as $cc): ?>
                        <div style="margin-top:16px;padding-left:16px;border-left:2px solid var(--aurora-border);display:flex;gap:12px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:var(--aurora-muted);display:flex;align-items:center;justify-content:center;color:var(--aurora-ink-muted);font-weight:600;flex-shrink:0;font-family:var(--aurora-font-title);font-size:0.85rem;"><?= Helper::e(mb_substr($cc['nickname'], 0, 1)) ?></div>
                            <div>
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                    <b style="font-family:var(--aurora-font-body);"><?= Helper::e($cc['nickname']) ?></b>
                                    <time style="font-size:0.8rem;color:var(--aurora-ink-muted);font-family:var(--aurora-font-mono);"><?= Helper::timeAgo($cc['created_at']) ?></time>
                                </div>
                                <div style="color:var(--aurora-foreground);line-height:1.7;font-family:var(--aurora-font-body);"><?= nl2br(Helper::e($cc['content'])) ?></div>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>