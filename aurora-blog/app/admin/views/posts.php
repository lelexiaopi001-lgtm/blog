<?php /* 文章列表 */ $tabs = ['all' => '全部', 'published' => '已发布', 'draft' => '草稿', 'trash' => '回收站']; ?>
<div class="panel">
    <div class="panel-head">
        <div class="tabs">
            <?php foreach ($tabs as $k => $label): ?>
            <a class="tab <?= $status === $k ? 'active' : '' ?>" href="/admin/index.php?r=posts&status=<?= $k ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
        <div class="toolbar">
            <form method="get" action="/admin/index.php" class="toolbar-search">
                <input type="hidden" name="r" value="posts">
                <input type="hidden" name="status" value="<?= Helper::e($status) ?>">
                <input type="search" name="kw" value="<?= Helper::e($kw) ?>" placeholder="搜索标题/内容…">
                <button class="btn btn-sm"><?= adminIcon('i-search', 'icon-sm') ?> 搜索</button>
            </form>
            <a class="btn btn-primary btn-sm" href="/admin/index.php?r=post_edit"><?= adminIcon('i-plus', 'icon-sm') ?> 写文章</a>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr><th style="width:38%">标题</th><th>分类</th><th>状态</th><th>浏览</th><th>评论</th><th>发布时间</th><th style="width:170px">操作</th></tr>
            </thead>
            <tbody>
            <?php if (empty($posts)): ?>
            <tr><td colspan="7" class="table-empty">暂无文章</td></tr>
            <?php endif; ?>
            <?php foreach ($posts as $p): ?>
            <tr>
                <td>
                    <a class="table-title" href="/admin/index.php?r=post_edit&id=<?= (int) $p['id'] ?>">
                        <?php if ($p['is_top']): ?><span class="tag tag-top">置顶</span><?php endif; ?>
                        <?= Helper::e($p['title']) ?>
                    </a>
                    <?php if ($p['status'] === 'published'): ?>
                    <a class="table-view" href="/post/<?= Helper::e($p['slug']) ?>" target="_blank" rel="noopener">
                        <?= adminIcon('i-external-link', 'icon-sm') ?> 查看
                    </a>
                    <?php endif; ?>
                </td>
                <td><?= Helper::e($p['cat_name'] ?: '—') ?></td>
                <td><span class="tag <?= $p['status'] === 'published' ? 'tag-ok' : ($p['status'] === 'draft' ? 'tag-warn' : 'tag-gray') ?>"><?= ['published' => '已发布', 'draft' => '草稿', 'trash' => '回收站'][$p['status']] ?></span></td>
                <td><?= (int) $p['views'] ?></td>
                <td><?= (int) $p['comment_count'] ?></td>
                <td class="table-time"><?= Helper::date($p['published_at'] ?: $p['created_at'], 'Y-m-d H:i') ?></td>
                <td class="table-ops">
                    <?php if ($p['status'] === 'trash'): ?>
                    <form method="post" class="inline-form"><?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button class="btn btn-xs btn-ok" formaction="/admin/index.php?r=post_restore"><?= adminIcon('i-check', 'icon-sm') ?> 恢复</button>
                        <button class="btn btn-xs btn-danger" formaction="/admin/index.php?r=post_purge" onclick="return confirm('彻底删除后不可恢复，确定？')"><?= adminIcon('i-trash', 'icon-sm') ?> 删除</button>
                    </form>
                    <?php else: ?>
                    <a class="btn btn-xs" href="/admin/index.php?r=post_edit&id=<?= (int) $p['id'] ?>"><?= adminIcon('i-edit', 'icon-sm') ?> 编辑</a>
                    <form method="post" class="inline-form"><?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button class="btn btn-xs btn-danger" formaction="/admin/index.php?r=post_delete" onclick="return confirm('移入回收站？')"><?= adminIcon('i-trash', 'icon-sm') ?> 删除</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= Helper::pagination($pg, '/admin/index.php?r=posts&status=' . $status . '&page=') ?>
</div>
