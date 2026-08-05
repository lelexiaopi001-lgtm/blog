<?php /* 媒体库 */ ?>
<div class="panel">
    <div class="panel-head">
        <div>
            <h3><?= adminIcon('i-media') ?> 媒体库 <small class="muted">（<?= (int) $total ?> 张 · 共 <?= $sizeSum ? round($sizeSum / 1024 / 1024, 1) . 'MB' : '0KB' ?>）</small></h3>
        </div>
        <label class="btn btn-primary btn-sm upload-inline"><?= adminIcon('i-upload', 'icon-sm') ?> 上传图片<input type="file" accept="image/*" multiple id="mediaUpload" hidden></label>
    </div>

    <?php if (empty($images)): ?>
    <p class="table-empty">还没有图片，点右上角「上传图片」添加第一张吧</p>
    <?php else: ?>
    <div class="media-grid" id="mediaGrid">
        <?php foreach ($images as $img): ?>
        <div class="media-card">
            <img src="<?= Helper::e($img['url']) ?>" alt="" loading="lazy">
            <div class="media-info">
                <small class="media-name" title="<?= Helper::e($img['url']) ?>"><?= Helper::e($img['name']) ?></small>
                <small class="media-meta"><?= Helper::e($img['mtime']) ?> · <?= max(1, round($img['size'] / 1024)) ?>KB</small>
            </div>
            <div class="media-ops">
                <button class="btn btn-xs" type="button" data-copy="<?= Helper::e($img['url']) ?>"><?= adminIcon('i-copy', 'icon-sm') ?> 复制链接</button>
                <form method="post" class="inline-form"><?= Helper::csrfField() ?>
                    <input type="hidden" name="url" value="<?= Helper::e($img['url']) ?>">
                    <button class="btn btn-xs btn-danger" formaction="/admin/index.php?r=media_delete" onclick="return confirm('删除这张图片？删除后文章中引用将失效！')"><?= adminIcon('i-trash', 'icon-sm') ?> 删除</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
