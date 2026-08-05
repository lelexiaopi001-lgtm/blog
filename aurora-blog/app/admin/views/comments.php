<?php /* 评论管理 */ $tabs = ['pending' => '待审核', 'approved' => '已通过', 'rejected' => '已拒绝', 'all' => '全部']; ?>
<div class="panel">
    <div class="panel-head">
        <div class="tabs">
            <?php foreach ($tabs as $k => $label): ?>
            <a class="tab <?= $status === $k ? 'active' : '' ?>" href="/admin/index.php?r=comments&status=<?= $k ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th style="width:15%">评论者</th><th>内容</th><th>文章</th><th>时间</th><th style="width:190px">操作</th></tr></thead>
            <tbody>
            <?php if (empty($comments)): ?><tr><td colspan="5" class="table-empty">暂无评论</td></tr><?php endif; ?>
            <?php foreach ($comments as $c): ?>
            <tr>
                <td>
                    <b><?= Helper::e($c['nickname']) ?></b>
                    <?php if ($c['website']): ?><a class="muted" href="<?= Helper::e($c['website']) ?>" target="_blank" rel="noopener"><?= adminIcon('i-external-link', 'icon-sm') ?></a><?php endif; ?>
                    <?php if ($c['email']): ?><br><small class="muted"><?= Helper::e($c['email']) ?></small><?php endif; ?>
                    <br><small class="muted"><?= Helper::e($c['ip']) ?></small>
                </td>
                <td class="comment-cell"><?= nl2br(Helper::e($c['content'])) ?></td>
                <td><a class="table-title" href="/post/<?= Helper::e($c['post_title'] ?: '') ?>" target="_blank"><?= Helper::e(mb_substr($c['post_title'] ?: '未知', 0, 20)) ?></a></td>
                <td class="table-time"><?= Helper::date($c['created_at'], 'm-d H:i') ?></td>
                <td>
                    <?php if ($c['status'] === 'pending'): ?>
                    <form method="post" class="inline-form"><?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                        <button class="btn btn-xs btn-ok" formaction="/admin/index.php?r=comment_approve"><?= adminIcon('i-check', 'icon-sm') ?> 通过</button>
                        <button class="btn btn-xs" formaction="/admin/index.php?r=comment_reject">拒绝</button>
                    </form>
                    <?php endif; ?>
                    <form method="post" class="inline-form"><?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                        <button class="btn btn-xs btn-danger" formaction="/admin/index.php?r=comment_delete" onclick="return confirm('删除该评论？')"><?= adminIcon('i-trash', 'icon-sm') ?> 删除</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= Helper::pagination($pg, '/admin/index.php?r=comments&status=' . $status . '&page=') ?>
</div>
