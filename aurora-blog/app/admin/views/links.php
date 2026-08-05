<?php /* 友链管理 */ $editLink = null; if ($_GET['edit'] ?? null) { $editLink = DB::fetch('SELECT * FROM links WHERE id=?', [(int) $_GET['edit']]); } ?>
<div class="two-col">
    <div class="panel">
        <div class="panel-head"><h3><?= $editLink ? '编辑友链' : '添加友链' ?></h3></div>
        <form method="post" action="/admin/index.php?r=link_save" class="stack-form">
            <?= Helper::csrfField() ?>
            <input type="hidden" name="id" value="<?= (int) ($editLink['id'] ?? 0) ?>">
            <div class="form-row">
                <label>网站名称 *</label>
                <input class="input" type="text" name="name" required value="<?= Helper::e($editLink['name'] ?? '') ?>" placeholder="如：某某博客">
            </div>
            <div class="form-row">
                <label>网址 *</label>
                <input class="input" type="url" name="url" required value="<?= Helper::e($editLink['url'] ?? '') ?>" placeholder="https://example.com">
            </div>
            <div class="form-row">
                <label>Logo 地址</label>
                <input class="input" type="text" name="logo" value="<?= Helper::e($editLink['logo'] ?? '') ?>" placeholder="https://example.com/logo.png">
            </div>
            <div class="form-row">
                <label>描述</label>
                <input class="input" type="text" name="description" value="<?= Helper::e($editLink['description'] ?? '') ?>" placeholder="一句话介绍">
            </div>
            <div class="form-row two-inline">
                <div><label>排序</label><input class="input" type="number" name="sort" value="<?= (int) ($editLink['sort'] ?? 0) ?>"></div>
                <div><label>状态</label>
                    <select class="input" name="status">
                        <option value="1" <?= !$editLink || $editLink['status'] ? 'selected' : '' ?>>显示</option>
                        <option value="0" <?= $editLink && !$editLink['status'] ? 'selected' : '' ?>>隐藏</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= adminIcon('i-save', 'icon-sm') ?> <?= $editLink ? '保存修改' : '添加友链' ?></button>
                <?php if ($editLink): ?><a class="btn" href="/admin/index.php?r=links">取消</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>全部友链（<?= count($links) ?>）</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>名称</th><th>网址</th><th>排序</th><th>状态</th><th style="width:140px">操作</th></tr></thead>
                <tbody>
                <?php if (empty($links)): ?><tr><td colspan="5" class="table-empty">暂无友链</td></tr><?php endif; ?>
                <?php foreach ($links as $l): ?>
                <tr>
                    <td><b><?= Helper::e($l['name']) ?></b><br><small class="muted"><?= Helper::e($l['description']) ?></small></td>
                    <td><a class="muted" href="<?= Helper::e($l['url']) ?>" target="_blank" rel="noopener"><?= Helper::e(mb_substr($l['url'], 0, 32)) ?></a></td>
                    <td><?= (int) $l['sort'] ?></td>
                    <td><span class="tag <?= $l['status'] ? 'tag-ok' : 'tag-gray' ?>"><?= $l['status'] ? '显示' : '隐藏' ?></span></td>
                    <td>
                        <a class="btn btn-xs" href="/admin/index.php?r=links&edit=<?= (int) $l['id'] ?>"><?= adminIcon('i-edit', 'icon-sm') ?> 编辑</a>
                        <form method="post" class="inline-form"><?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                            <button class="btn btn-xs btn-danger" formaction="/admin/index.php?r=link_delete" onclick="return confirm('删除该友链？')"><?= adminIcon('i-trash', 'icon-sm') ?> 删除</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
