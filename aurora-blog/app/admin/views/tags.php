<?php /* 标签管理 */ $editTag = null; if ($_GET['edit'] ?? null) { $editTag = DB::fetch('SELECT * FROM tags WHERE id=?', [(int) $_GET['edit']]); } ?>
<div class="two-col">
    <div class="panel">
        <div class="panel-head"><h3><?= $editTag ? '编辑标签' : '新增标签' ?></h3></div>
        <form method="post" action="/admin/index.php?r=tag_save" class="stack-form">
            <?= Helper::csrfField() ?>
            <input type="hidden" name="id" value="<?= (int) ($editTag['id'] ?? 0) ?>">
            <div class="form-row">
                <label>标签名 *</label>
                <input class="input" type="text" name="name" required value="<?= Helper::e($editTag['name'] ?? '') ?>" placeholder="如：PHP">
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= adminIcon('i-save', 'icon-sm') ?> <?= $editTag ? '保存修改' : '创建标签' ?></button>
                <?php if ($editTag): ?><a class="btn" href="/admin/index.php?r=tags">取消</a><?php endif; ?>
            </div>
        </form>
        <p class="form-hint">提示：在文章编辑器里填写标签会自动创建，无需手动添加。</p>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>全部标签（<?= count($tags) ?>）</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>名称</th><th>Slug</th><th>文章数</th><th style="width:140px">操作</th></tr></thead>
                <tbody>
                <?php if (empty($tags)): ?><tr><td colspan="4" class="table-empty">暂无标签</td></tr><?php endif; ?>
                <?php foreach ($tags as $t): ?>
                <tr>
                    <td><b><?= adminIcon('i-tag', 'icon-sm') ?> <?= Helper::e($t['name']) ?></b></td>
                    <td>/tag/<?= Helper::e($t['slug']) ?></td>
                    <td><?= (int) $t['post_count'] ?></td>
                    <td>
                        <a class="btn btn-xs" href="/admin/index.php?r=tags&edit=<?= (int) $t['id'] ?>"><?= adminIcon('i-edit', 'icon-sm') ?> 编辑</a>
                        <form method="post" class="inline-form"><?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                            <button class="btn btn-xs btn-danger" formaction="/admin/index.php?r=tag_delete" onclick="return confirm('删除标签？不影响文章本身')"><?= adminIcon('i-trash', 'icon-sm') ?> 删除</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
