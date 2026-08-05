<?php /* 分类管理 */ $editCat = null; if ($_GET['edit'] ?? null) { $editCat = DB::fetch('SELECT * FROM categories WHERE id=?', [(int) $_GET['edit']]); } ?>
<div class="two-col">
    <div class="panel">
        <div class="panel-head"><h3><?= $editCat ? '编辑分类' : '新增分类' ?></h3></div>
        <form method="post" action="/admin/index.php?r=category_save" class="stack-form">
            <?= Helper::csrfField() ?>
            <input type="hidden" name="id" value="<?= (int) ($editCat['id'] ?? 0) ?>">
            <div class="form-row">
                <label>名称 *</label>
                <input class="input" type="text" name="name" required value="<?= Helper::e($editCat['name'] ?? '') ?>" placeholder="如：技术">
            </div>
            <div class="form-row">
                <label>Slug（留空自动生成）</label>
                <input class="input" type="text" name="slug" value="<?= Helper::e($editCat['slug'] ?? '') ?>" placeholder="tech">
            </div>
            <div class="form-row">
                <label>描述</label>
                <input class="input" type="text" name="description" value="<?= Helper::e($editCat['description'] ?? '') ?>" placeholder="一句话描述该分类">
            </div>
            <div class="form-row">
                <label>排序（数字越小越靠前）</label>
                <input class="input" type="number" name="sort" value="<?= (int) ($editCat['sort'] ?? 0) ?>">
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= adminIcon('i-save', 'icon-sm') ?> <?= $editCat ? '保存修改' : '创建分类' ?></button>
                <?php if ($editCat): ?><a class="btn" href="/admin/index.php?r=categories">取消</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>全部分类（<?= count($cats) ?>）</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>名称</th><th>Slug</th><th>文章</th><th>排序</th><th style="width:140px">操作</th></tr></thead>
                <tbody>
                <?php if (empty($cats)): ?><tr><td colspan="5" class="table-empty">暂无分类</td></tr><?php endif; ?>
                <?php foreach ($cats as $c): ?>
                <tr>
                    <td><b><?= Helper::e($c['name']) ?></b><br><small class="muted"><?= Helper::e($c['description']) ?></small></td>
                    <td>/category/<?= Helper::e($c['slug']) ?></td>
                    <td><?= (int) $c['cnt'] ?></td>
                    <td><?= (int) $c['sort'] ?></td>
                    <td>
                        <a class="btn btn-xs" href="/admin/index.php?r=categories&edit=<?= (int) $c['id'] ?>"><?= adminIcon('i-edit', 'icon-sm') ?> 编辑</a>
                        <form method="post" class="inline-form"><?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button class="btn btn-xs btn-danger" formaction="/admin/index.php?r=category_delete" onclick="return confirm('删除分类？该分类下文章将变为未分类')"><?= adminIcon('i-trash', 'icon-sm') ?> 删除</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
