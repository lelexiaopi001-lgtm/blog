<?php /* 站点设置 */ ?>
<div class="panel">
    <div class="panel-head"><h3><?= adminIcon('i-settings') ?> 站点设置</h3></div>
    <form method="post" action="/admin/index.php?r=settings_save" class="stack-form settings-form">
        <?= Helper::csrfField() ?>

        <div class="form-section">基本信息</div>
        <div class="form-row">
            <label>站点名称</label>
            <input class="input" type="text" name="site_name" value="<?= Helper::e($all['site_name'] ?? '') ?>">
        </div>
        <div class="form-row">
            <label>站点描述</label>
            <input class="input" type="text" name="site_desc" value="<?= Helper::e($all['site_desc'] ?? '') ?>">
        </div>
        <div class="form-row">
            <label>签名 / Slogan（首页大标题下方展示）</label>
            <input class="input" type="text" name="site_slogan" value="<?= Helper::e($all['site_slogan'] ?? '') ?>" placeholder="如：Stay curious, keep writing.">
        </div>
        <div class="form-row">
            <label>名言 / Motto（首页签名下方展示）</label>
            <input class="input" type="text" name="site_motto" value="<?= Helper::e($all['site_motto'] ?? '') ?>" placeholder="如：「用文字点亮极光」">
        </div>
        <div class="form-row">
            <label>首页顶部欢迎语（小字标签）</label>
            <input class="input" type="text" name="site_hero_eyebrow" value="<?= Helper::e($all['site_hero_eyebrow'] ?? '') ?>" placeholder="如：✦ Welcome to my space">
        </div>
        <div class="form-row">
            <label>SEO 关键词</label>
            <input class="input" type="text" name="site_keywords" value="<?= Helper::e($all['site_keywords'] ?? '') ?>">
        </div>
        <div class="form-row">
            <label>ICP 备案号（留空不显示）</label>
            <input class="input" type="text" name="site_icp" value="<?= Helper::e($all['site_icp'] ?? '') ?>" placeholder="如：京ICP备00000000号">
        </div>
        <div class="form-row">
            <label>页脚文字</label>
            <input class="input" type="text" name="footer_text" value="<?= Helper::e($all['footer_text'] ?? '') ?>">
        </div>

        <div class="form-section">头像与 Logo</div>
        <div class="form-row">
            <label>头像 URL（关于页展示）</label>
            <input class="input" type="text" name="site_avatar" id="sAvatar" value="<?= Helper::e($all['site_avatar'] ?? '') ?>" placeholder="https://…">
            <label class="btn btn-xs upload-inline"><?= adminIcon('i-upload', 'icon-sm') ?> 上传<input type="file" accept="image/*" data-target="sAvatar" hidden></label>
        </div>
        <div class="form-row">
            <label>Logo URL</label>
            <input class="input" type="text" name="site_logo" value="<?= Helper::e($all['site_logo'] ?? '') ?>" placeholder="https://…">
        </div>

        <div class="form-section">社交链接</div>
        <div class="form-row">
            <label>GitHub</label>
            <input class="input" type="url" name="site_social_github" value="<?= Helper::e($all['site_social_github'] ?? '') ?>">
        </div>
        <div class="form-row">
            <label>Twitter / X</label>
            <input class="input" type="url" name="site_social_twitter" value="<?= Helper::e($all['site_social_twitter'] ?? '') ?>">
        </div>
        <div class="form-row">
            <label>联系邮箱</label>
            <input class="input" type="email" name="site_social_email" value="<?= Helper::e($all['site_social_email'] ?? '') ?>">
        </div>

        <div class="form-section">关于页内容（Markdown）</div>
        <div class="form-row">
            <textarea class="input" name="site_about" rows="8" placeholder="关于我…"><?= Helper::e($all['site_about'] ?? '') ?></textarea>
        </div>

        <div class="form-section">评论与显示</div>
        <div class="form-row two-inline">
            <div>
                <label>评论功能</label>
                <select class="input" name="comment_switch">
                    <option value="1" <?= ($all['comment_switch'] ?? '1') === '1' ? 'selected' : '' ?>>开启</option>
                    <option value="0" <?= ($all['comment_switch'] ?? '1') !== '1' ? 'selected' : '' ?>>关闭</option>
                </select>
            </div>
            <div>
                <label>评论审核</label>
                <select class="input" name="comment_audit">
                    <option value="1" <?= ($all['comment_audit'] ?? '1') === '1' ? 'selected' : '' ?>>需要审核后显示</option>
                    <option value="0" <?= ($all['comment_audit'] ?? '1') !== '1' ? 'selected' : '' ?>>直接显示</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <label>每页文章数</label>
            <input class="input" type="number" name="page_size" value="<?= Helper::e($all['page_size'] ?? '10') ?>" min="1" max="50">
        </div>

        <div class="form-section">首页 Hero</div>
        <div class="form-row">
            <label>Hero 背景图 URL（留空则使用默认渐变）</label>
            <input class="input" type="text" name="site_hero_bg" id="sHeroBg" value="<?= Helper::e($all['site_hero_bg'] ?? '') ?>" placeholder="https://…">
            <label class="btn btn-xs upload-inline" style="margin-top:8px"><?= adminIcon('i-upload', 'icon-sm') ?> 上传背景<input type="file" accept="image/*" data-target="sHeroBg" hidden></label>
        </div>

        <div class="form-section">主题与外观</div>
        <div class="form-row two-inline">
            <div>
                <label>默认主题模式</label>
                <select class="input" name="theme_mode">
                    <option value="dark" <?= ($all['theme_mode'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>深邃暗色</option>
                    <option value="light" <?= ($all['theme_mode'] ?? 'dark') === 'light' ? 'selected' : '' ?>>明亮浅色</option>
                </select>
            </div>
            <div>
                <label>主题强调色</label>
                <input class="input" type="color" name="theme_accent" value="<?= Helper::e($all['theme_accent'] ?? '#f59e0b') ?>" style="height:40px;padding:4px">
            </div>
        </div>

        <div class="form-section">渐进式 Web 应用 (PWA)</div>
        <div class="form-row two-inline">
            <div>
                <label>启用 PWA</label>
                <select class="input" name="pwa_enabled">
                    <option value="1" <?= ($all['pwa_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>开启</option>
                    <option value="0" <?= ($all['pwa_enabled'] ?? '1') !== '1' ? 'selected' : '' ?>>关闭</option>
                </select>
            </div>
            <div>
                <label>主题色</label>
                <input class="input" type="color" name="pwa_theme_color" value="<?= Helper::e($all['pwa_theme_color'] ?? '#0a0a0c') ?>" style="height:40px;padding:4px">
            </div>
        </div>
        <div class="form-row">
            <label>背景色</label>
            <input class="input" type="color" name="pwa_bg_color" value="<?= Helper::e($all['pwa_bg_color'] ?? '#0a0a0c') ?>" style="height:40px;padding:4px">
        </div>

        <div class="form-section">站点公告</div>
        <div class="form-row two-inline">
            <div>
                <label>显示公告</label>
                <select class="input" name="site_notice_enabled">
                    <option value="1" <?= ($all['site_notice_enabled'] ?? '0') === '1' ? 'selected' : '' ?>>开启</option>
                    <option value="0" <?= ($all['site_notice_enabled'] ?? '0') !== '1' ? 'selected' : '' ?>>关闭</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <label>公告内容（支持 Markdown）</label>
            <textarea class="input" name="site_notice" rows="3" placeholder="如：新版博客上线，欢迎反馈"><?= Helper::e($all['site_notice'] ?? '') ?></textarea>
        </div>

        <div class="form-section">SMTP 邮件通知</div>
        <div class="form-row two-inline">
            <div>
                <label>SMTP 服务器</label>
                <input class="input" type="text" name="smtp_host" value="<?= Helper::e($all['smtp_host'] ?? '') ?>" placeholder="如：smtp.qq.com">
            </div>
            <div>
                <label>端口</label>
                <input class="input" type="number" name="smtp_port" value="<?= Helper::e($all['smtp_port'] ?? '587') ?>">
            </div>
        </div>
        <div class="form-row two-inline">
            <div>
                <label>用户名</label>
                <input class="input" type="text" name="smtp_user" value="<?= Helper::e($all['smtp_user'] ?? '') ?>" placeholder="如：hello@example.com">
            </div>
            <div>
                <label>密码 / 授权码</label>
                <input class="input" type="password" name="smtp_pass" value="<?= Helper::e($all['smtp_pass'] ?? '') ?>" placeholder="留空则不修改">
            </div>
        </div>
        <div class="form-row two-inline">
            <div>
                <label>发件人邮箱</label>
                <input class="input" type="email" name="smtp_from" value="<?= Helper::e($all['smtp_from'] ?? '') ?>" placeholder="默认与用户名相同">
            </div>
            <div>
                <label>管理员通知邮箱</label>
                <input class="input" type="email" name="smtp_notify_email" value="<?= Helper::e($all['smtp_notify_email'] ?? '') ?>" placeholder="新评论将通知此邮箱">
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= adminIcon('i-save', 'icon-sm') ?> 保存设置</button>
        </div>
    </form>
</div>
