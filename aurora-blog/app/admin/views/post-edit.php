<?php /* 文章编辑器 */
$isEdit = !empty($post);
$defaultPub = $isEdit && $post['published_at'] ? Helper::date($post['published_at'], 'Y-m-d H:i') : date('Y-m-d H:i');
?>
<div class="panel editor-panel">
    <form method="post" action="/admin/index.php?r=post_save" id="postForm">
        <?= Helper::csrfField() ?>
        <input type="hidden" name="id" value="<?= (int) ($post['id'] ?? 0) ?>">
        <input type="hidden" name="cover" id="coverInput" value="<?= Helper::e($post['cover'] ?? '') ?>">

        <div class="editor-top">
            <div class="editor-title-wrap">
                <input class="input input-xl" type="text" name="title" id="titleInput" required placeholder="请输入文章标题…" value="<?= Helper::e($post['title'] ?? '') ?>">
                <div class="editor-slug">
                    <span>链接:</span>
                    <input class="input input-sm" type="text" name="slug" id="slugInput" placeholder="自动生成" value="<?= Helper::e($post['slug'] ?? '') ?>">
                </div>
            </div>
            <div class="editor-actions">
                <button class="btn" type="button" id="previewBtn"><?= adminIcon('i-eye') ?> 预览</button>
                <button class="btn btn-primary" type="submit" name="status" value="published"><?= adminIcon('i-save') ?> <?= $isEdit ? '保存并更新' : '发布文章' ?></button>
                <button class="btn btn-ghost" type="submit" name="status" value="draft">存为草稿</button>
            </div>
        </div>

        <div class="editor-tabs">
            <div class="editor-main">
                <div class="editor-toolbar">
                    <button type="button" class="tb-btn" data-ins="**加粗**" title="加粗"><b>B</b></button>
                    <button type="button" class="tb-btn" data-ins="*斜体*" title="斜体"><i>I</i></button>
                    <button type="button" class="tb-btn" data-ins="`code`" title="行内代码">&lt;/&gt;</button>
                    <button type="button" class="tb-btn" data-ins="```php\n\n```" title="代码块">{ }</button>
                    <button type="button" class="tb-btn" data-ins="[链接文字](https://)" title="链接"><?= adminIcon('i-link') ?></button>
                    <button type="button" class="tb-btn" data-ins="![图片描述](https://)" title="图片"><?= adminIcon('i-image') ?></button>
                    <button type="button" class="tb-btn" data-ins="## 二级标题" title="标题">H2</button>
                    <button type="button" class="tb-btn" data-ins="> 引用" title="引用">❝</button>
                    <button type="button" class="tb-btn" data-ins="- 列表项" title="无序列表">•</button>
                    <span class="tb-spacer"></span>
                    <label class="tb-btn upload-btn" title="上传图片"><?= adminIcon('i-upload') ?> 图片<input type="file" accept="image/*" id="coverUpload" hidden></label>
                </div>
                <textarea class="input editor-area" name="content" id="editorArea" rows="24" placeholder="使用 Markdown 写作…"><?= Helper::e($post['content'] ?? '') ?></textarea>
            </div>
            <div class="editor-preview" id="editorPreview"><p class="preview-placeholder"><?= adminIcon('i-eye') ?> 点击「预览」或按 Ctrl+P 查看效果</p></div>
        </div>

        <div class="editor-meta-grid">
            <div class="editor-field">
                <label>分类</label>
                <select class="input" name="category_id">
                    <option value="0">— 未分类 —</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= $isEdit && (int) $post['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= Helper::e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="editor-field">
                <label>标签（逗号分隔）</label>
                <input class="input" type="text" name="tags" id="tagsInput" list="tagList" value="<?= Helper::e($postTagsStr) ?>" placeholder="PHP, 前端, 随笔">
                <datalist id="tagList">
                    <?php foreach ($allTags as $t): ?><option value="<?= Helper::e($t['name']) ?>"><?php endforeach; ?>
                </datalist>
            </div>
            <div class="editor-field">
                <label>发布时间</label>
                <input class="input" type="datetime-local" name="published_at" value="<?= Helper::e($defaultPub) ?>">
            </div>
            <div class="editor-field">
                <label>封面图 URL</label>
                <div class="cover-row">
                    <input class="input" type="text" id="coverUrl" value="<?= Helper::e($post['cover'] ?? '') ?>" placeholder="https://… 或上传">
                    <button class="btn btn-sm" type="button" id="syncCover">同步</button>
                </div>
                <div class="cover-preview" id="coverPreview">
                    <?php if (!empty($post['cover'])): ?><img src="<?= Helper::e($post['cover']) ?>" alt=""><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="editor-options">
            <label class="check-row"><input type="checkbox" name="is_top" value="1" <?= $isEdit && $post['is_top'] ? 'checked' : '' ?>> 置顶文章</label>
            <label class="check-row"><input type="checkbox" name="comment_status" value="1" <?= !$isEdit || $post['comment_status'] ? 'checked' : '' ?>> 允许评论</label>
        </div>

        <div class="editor-field summary-field">
            <label>摘要（留空则自动截取）</label>
            <textarea class="input" name="summary" rows="3" placeholder="文章摘要…"><?= Helper::e($post['summary'] ?? '') ?></textarea>
        </div>
    </form>
</div>

<div class="modal" id="previewModal" hidden>
    <div class="modal-backdrop" data-close></div>
    <div class="modal-box">
        <div class="modal-head"><h3>文章预览</h3><button class="modal-close" data-close><?= adminIcon('i-close') ?></button></div>
        <div class="modal-body prose" id="previewBody"></div>
    </div>
</div>
