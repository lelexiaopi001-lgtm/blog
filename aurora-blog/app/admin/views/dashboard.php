<?php /* 仪表盘 */ ?>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-head">
            <?= adminIcon('i-posts') ?>
            <span class="stat-label">已发布文章</span>
        </div>
        <div class="stat-value"><?= (int) $stat['posts'] ?></div>
        <a class="stat-link" href="/admin/index.php?r=posts&status=published">查看全部 <?= adminIcon('i-arrow-right', 'icon-sm') ?></a>
    </div>
    <div class="stat-card">
        <div class="stat-head">
            <?= adminIcon('i-book-open') ?>
            <span class="stat-label">草稿箱</span>
        </div>
        <div class="stat-value"><?= (int) $stat['drafts'] ?></div>
        <a class="stat-link" href="/admin/index.php?r=posts&status=draft">继续写作 <?= adminIcon('i-arrow-right', 'icon-sm') ?></a>
    </div>
    <div class="stat-card">
        <div class="stat-head">
            <?= adminIcon('i-comments') ?>
            <span class="stat-label">待审核评论</span>
        </div>
        <div class="stat-value"><?= (int) $stat['comments'] ?></div>
        <a class="stat-link" href="/admin/index.php?r=comments&status=pending">去处理 <?= adminIcon('i-arrow-right', 'icon-sm') ?></a>
    </div>
    <div class="stat-card">
        <div class="stat-head">
            <?= adminIcon('i-eye') ?>
            <span class="stat-label">总访问量 PV</span>
        </div>
        <div class="stat-value"><?= number_format($stat['pv_total']) ?></div>
        <span class="stat-foot">今日 <?= (int) $stat['pv_today'] ?> PV · <?= (int) $stat['uv_today'] ?> UV</span>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h3><?= adminIcon('i-stats') ?> 近 14 天访问趋势</h3>
        <a class="btn btn-sm" href="/admin/index.php?r=stats">详细统计</a>
    </div>
    <div class="chart-box" id="trendChart" data-labels='<?= Helper::e(json_encode($labels, JSON_UNESCAPED_UNICODE)) ?>'
         data-pv='<?= Helper::e(json_encode($pvData)) ?>' data-uv='<?= Helper::e(json_encode($uvData)) ?>'></div>
</div>

<div class="two-col">
    <div class="panel">
        <div class="panel-head">
            <h3><?= adminIcon('i-comments') ?> 最新评论</h3>
            <a class="btn btn-sm" href="/admin/index.php?r=comments">全部</a>
        </div>
        <ul class="mini-list">
            <?php if (empty($recentComments)): ?><li class="mini-empty">暂无评论</li><?php endif; ?>
            <?php foreach ($recentComments as $c): ?>
            <li>
                <div class="mini-head">
                    <b><?= Helper::e($c['nickname']) ?></b>
                    <span class="tag <?= $c['status'] === 'pending' ? 'tag-warn' : ($c['status'] === 'approved' ? 'tag-ok' : 'tag-gray') ?>"><?= ['pending' => '待审', 'approved' => '已通过', 'rejected' => '已拒绝'][$c['status']] ?></span>
                    <small><?= Helper::timeAgo($c['created_at']) ?></small>
                </div>
                <p class="mini-text"><?= Helper::e(mb_substr($c['content'], 0, 60)) ?><?= mb_strlen($c['content']) > 60 ? '…' : '' ?></p>
                <small class="mini-sub">《<?= Helper::e($c['post_title'] ?: '未知文章') ?>》</small>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="panel">
        <div class="panel-head">
            <h3><?= adminIcon('i-posts') ?> 最新文章</h3>
            <a class="btn btn-sm btn-primary" href="/admin/index.php?r=post_edit"><?= adminIcon('i-plus', 'icon-sm') ?> 写文章</a>
        </div>
        <ul class="mini-list">
            <?php if (empty($recentPosts)): ?><li class="mini-empty">还没有文章，点击右上角开始创作</li><?php endif; ?>
            <?php foreach ($recentPosts as $p): ?>
            <li>
                <a class="mini-title" href="/admin/index.php?r=post_edit&id=<?= (int) $p['id'] ?>"><?= Helper::e($p['title']) ?></a>
                <div class="mini-meta">
                    <span class="tag <?= $p['status'] === 'published' ? 'tag-ok' : ($p['status'] === 'draft' ? 'tag-warn' : 'tag-gray') ?>"><?= ['published' => '已发布', 'draft' => '草稿', 'trash' => '回收站'][$p['status']] ?></span>
                    <small><?= (int) $p['views'] ?> 浏览 · <?= Helper::date($p['published_at'] ?: $p['created_at']) ?></small>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
