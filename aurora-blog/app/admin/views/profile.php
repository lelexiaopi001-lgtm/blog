<?php /* 个人资料 */ ?>
<div class="two-col">
    <div class="panel">
        <div class="panel-head"><h3><?= adminIcon('i-profile') ?> 账号资料</h3></div>
        <form method="post" action="/admin/index.php?r=profile_save" class="stack-form">
            <?= Helper::csrfField() ?>
            <div class="form-row">
                <label>用户名（不可修改）</label>
                <input class="input" type="text" value="<?= Helper::e($user['username']) ?>" disabled>
            </div>
            <div class="form-row">
                <label>昵称</label>
                <input class="input" type="text" name="nickname" value="<?= Helper::e($user['nickname']) ?>" placeholder="后台显示名称">
            </div>
            <div class="form-row">
                <label>邮箱</label>
                <input class="input" type="email" name="email" value="<?= Helper::e($user['email']) ?>">
            </div>
            <div class="form-row">
                <label>角色</label>
                <input class="input" type="text" value="<?= $user['role'] === 'admin' ? '超级管理员' : '编辑' ?>" disabled>
            </div>
            <div class="form-row">
                <label>注册时间</label>
                <input class="input" type="text" value="<?= Helper::date($user['created_at'], 'Y-m-d H:i') ?>" disabled>
            </div>
            <div class="form-actions"><button class="btn btn-primary" type="submit"><?= adminIcon('i-save', 'icon-sm') ?> 保存资料</button></div>
        </form>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>修改密码</h3></div>
        <form method="post" action="/admin/index.php?r=profile_save" class="stack-form">
            <?= Helper::csrfField() ?>
            <div class="form-row">
                <label>原密码</label>
                <input class="input" type="password" name="old_password" autocomplete="current-password">
            </div>
            <div class="form-row">
                <label>新密码（至少 6 位）</label>
                <input class="input" type="password" name="new_password" autocomplete="new-password">
            </div>
            <div class="form-row">
                <label>确认新密码</label>
                <input class="input" type="password" name="new_password2" autocomplete="new-password">
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit" onclick="return checkPwd(this)"><?= adminIcon('i-check', 'icon-sm') ?> 修改密码</button>
            </div>
        </form>
        <p class="form-hint">提示：如需修改，请同时填写原密码与新密码；不改密码则留空。</p>
    </div>
</div>
