<?php $siteName = Helper::setting('site_name', 'Aurora Blog'); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>登录 · <?= Helper::e($siteName) ?> 管理后台</title>
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="login-body">
<div class="bg-glow" aria-hidden="true">
    <span class="glow glow-1"></span><span class="glow glow-2"></span><span class="glow glow-3"></span>
</div>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-head">
            <span class="logo-mark big">✦</span>
            <h1><?= Helper::e($siteName) ?></h1>
            <p>管理后台登录</p>
        </div>
        <?php if ($err): ?>
        <div class="login-err">
            <svg class="icon" aria-hidden="true"><use href="/assets/img/icons.svg#i-alert-circle"></use></svg>
            <?= Helper::e($err) ?>
        </div>
        <?php endif; ?>
        <form method="post" action="/admin/index.php?r=login" autocomplete="on">
            <?= Helper::csrfField() ?>
            <label class="field">
                <span>用户名</span>
                <input type="text" name="username" required autofocus autocomplete="username" placeholder="请输入用户名">
            </label>
            <label class="field">
                <span>密码</span>
                <input type="password" name="password" required autocomplete="current-password" placeholder="请输入密码">
            </label>
            <label class="check-row">
                <input type="checkbox" name="remember" value="1"> 30 天内记住我
            </label>
            <button class="btn btn-primary btn-block" type="submit">
                <svg class="icon icon-sm" aria-hidden="true"><use href="/assets/img/icons.svg#i-check"></use></svg>
                登 录
            </button>
        </form>
        <a class="login-back" href="/">
            <svg class="icon icon-sm" aria-hidden="true"><use href="/assets/img/icons.svg#i-arrow-left"></use></svg>
            返回前台
        </a>
    </div>
</div>
</body>
</html>
