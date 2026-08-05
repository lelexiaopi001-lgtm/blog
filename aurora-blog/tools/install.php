<?php
/**
 * Aurora Blog 极光博客 —— 安装向导
 * 功能: 环境检测 / 写入配置 / 导入数据库 / 创建管理员
 * ⚠️ 安装完成后请务必删除本文件 (tools/install.php)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__);
$configFile = $root . '/config/config.php';
$alreadyInstalled = is_file($configFile) && (($GLOBALS['__config'] ?? null) || true);

// 已安装则禁止重复安装
if (is_file($configFile)) {
    $cfg = require $configFile;
    try {
        $pdo = new PDO(
            "mysql:host={$cfg['db']['host']};port={$cfg['db']['port']};dbname={$cfg['db']['name']};charset=utf8mb4",
            $cfg['db']['user'], $cfg['db']['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $tables = (int) $pdo->query('SHOW TABLES')->rowCount();
        if ($tables > 0) {
            http_response_code(403);
            die('<h2 style="font-family:system-ui;color:#ef4444">⚠ 系统似乎已安装完成。</h2><p style="font-family:system-ui">如需重新安装，请先删除 config/config.php 并清空数据库。</p>');
        }
    } catch (Exception $e) {
        // 配置存在但连不上库，允许重装
    }
}

$errors = [];
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbPort = (int) ($_POST['db_port'] ?? 3306);
    $dbName = trim($_POST['db_name'] ?? 'aurora_blog');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = (string) ($_POST['db_pass'] ?? '');
    $siteName = trim($_POST['site_name'] ?? 'Aurora 极光博客');
    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminPass = (string) ($_POST['admin_pass'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');

    if ($adminUser === '' || strlen($adminPass) < 6) {
        $errors[] = '管理员用户名不能为空，密码至少 6 位';
    } elseif ($adminEmail !== '' && !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '管理员邮箱格式不正确';
    } else {
        try {
            $pdo = new PDO(
                "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4",
                $dbUser, $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            // 建库
            $dbNameSafe = str_replace('`', '', $dbName);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbNameSafe}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbNameSafe}`");
            // 导入 SQL
            $sql = file_get_contents($root . '/database/blog.sql');
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                if ($stmt !== '') $pdo->exec($stmt);
            }
            // 创建管理员
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $st = $pdo->prepare('INSERT INTO users (username, password_hash, email, nickname) VALUES (?, ?, ?, ?)');
            $st->execute([$adminUser, $hash, $adminEmail, $adminUser]);
            // 更新站点名
            $st = $pdo->prepare("UPDATE settings SET svalue = ? WHERE skey = 'site_name'");
            $st->execute([$siteName]);
            // 写入配置
            $key = bin2hex(random_bytes(24));
            $config = "<?php\n/**\n * Aurora Blog 配置（由安装向导生成）\n */\n\nreturn [\n    'db' => [\n        'host' => " . var_export($dbHost, true) . ",\n        'port' => {$dbPort},\n        'name' => " . var_export($dbNameSafe, true) . ",\n        'user' => " . var_export($dbUser, true) . ",\n        'pass' => " . var_export($dbPass, true) . ",\n    ],\n    'app' => [\n        'key' => " . var_export($key, true) . ",\n        'debug' => false,\n    ],\n];\n";
            if (!@file_put_contents($configFile, $config)) {
                $errors[] = '无法写入 config/config.php，请检查目录权限（建议 755 或 775）';
            } else {
                $done = true;
            }
        } catch (PDOException $e) {
            $errors[] = '数据库操作失败：' . $e->getMessage();
        }
    }
}

/* ---------- 环境检测 ---------- */
$checks = [
    ['PHP ≥ 7.4', PHP_VERSION_ID >= 70400, '当前 ' . PHP_VERSION],
    ['PDO MySQL 扩展', extension_loaded('pdo_mysql'), '需启用 pdo_mysql'],
    ['mbstring 扩展', extension_loaded('mbstring'), '需启用 mbstring'],
    ['config 目录可写', is_writable($root . '/config') || is_writable($root), '需可写以生成配置'],
    ['public/uploads 可写', is_writable($root . '/public/uploads') || is_writable($root), '图片上传目录'],
];
// 建议项：缺失不阻止安装（图片上传有基础校验兜底）
$optional = [
    ['fileinfo 扩展（图片类型识别）', extension_loaded('fileinfo'), '未启用也能安装，仅影响图片校验精度'],
];
$allPass = !in_array(false, array_column($checks, 1), true);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aurora Blog 极光博客 · 安装向导</title>
<style>
:root{--bg:#0b0f1a;--card:#151b2e;--border:rgba(255,255,255,.09);--text:#e6eaf3;--dim:#9aa4bd;--accent:#818cf8;--grad:linear-gradient(120deg,#818cf8,#c084fc);--green:#34d399;--red:#f87171}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,"PingFang SC","Microsoft YaHei",sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:30px 16px}
.wrap{width:min(680px,100%)}
.card{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:34px;box-shadow:0 24px 80px rgba(0,0,0,.4)}
h1{font-size:22px;margin-bottom:6px;display:flex;align-items:center;gap:10px}
h1 .mark{width:38px;height:38px;border-radius:10px;background:var(--grad);display:grid;place-items:center;font-size:18px}
.sub{color:var(--dim);font-size:13.5px;margin-bottom:24px}
h2{font-size:15px;margin:24px 0 12px;color:var(--accent)}
ul{list-style:none}
li{display:flex;justify-content:space-between;padding:9px 12px;border:1px solid var(--border);border-radius:9px;margin-bottom:8px;font-size:13.5px}
li .st{font-size:12px}
.ok{color:var(--green)}.fail{color:var(--red)}
.field{margin-bottom:14px}
.field label{display:block;font-size:12.5px;color:var(--dim);margin-bottom:6px;font-weight:600}
.field input{width:100%;padding:11px 14px;border-radius:10px;border:1px solid var(--border);background:#0f1524;color:var(--text);outline:none;font-size:14px;font-family:inherit}
.field input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(129,140,248,.15)}
.row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn{width:100%;padding:13px;border:none;border-radius:11px;background:var(--grad);color:#fff;font-size:15px;font-weight:700;cursor:pointer;margin-top:10px;transition:.2s}
.btn:hover{filter:brightness(1.1)}
.err{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.35);color:var(--red);padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:14px}
.done{text-align:center;padding:20px 0}
.done .big{font-size:46px;margin-bottom:12px}
.done h2{color:var(--green);margin:0 0 10px}
.done p{color:var(--dim);font-size:14px;margin-bottom:8px;line-height:1.8}
.done code{background:#0f1524;padding:3px 8px;border-radius:6px;font-size:13px;color:var(--accent)}
.warn{background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.3);color:#fbbf24;padding:10px 14px;border-radius:10px;font-size:12.5px;margin-top:16px}
@media(max-width:560px){.row{grid-template-columns:1fr}.card{padding:24px}}
</style>
</head>
<body>
<div class="wrap">
<div class="card">
<?php if ($done): ?>
    <div class="done">
        <div class="big">🎉</div>
        <h2>安装成功！</h2>
        <p>管理员账号：<code><?= htmlspecialchars($adminUser) ?></code></p>
        <p>后台地址：<code>/admin/index.php</code> · 前台地址：<code>/</code></p>
        <div class="warn">⚠ 请立即删除 <code>tools/install.php</code> 文件，保障安全。</div>
        <a class="btn" href="/" style="text-decoration:none;display:inline-block;width:auto;padding:12px 36px;margin-top:18px">进入前台</a>
    </div>
<?php else: ?>
    <h1><span class="mark">✦</span> Aurora Blog 安装向导</h1>
    <p class="sub">极光博客 · 一键安装（PHP + MySQL）</p>

    <?php foreach ($errors as $e): ?><div class="err">⚠ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>

    <h2>① 环境检测</h2>
    <ul>
        <?php foreach ($checks as $c): ?>
        <li><span><?= htmlspecialchars($c[0]) ?></span>
            <span class="st <?= $c[1] ? 'ok' : 'fail' ?>"><?= $c[1] ? '✓ 通过' : '✗ ' . htmlspecialchars($c[2]) ?></span></li>
        <?php endforeach; ?>
        <?php foreach ($optional as $c): ?>
        <li style="opacity:.75"><span><?= htmlspecialchars($c[0]) ?><small style="color:var(--dim)">（建议）</small></span>
            <span class="st <?= $c[1] ? 'ok' : '' ?>"><?= $c[1] ? '✓ 已启用' : '○ ' . htmlspecialchars($c[2]) ?></span></li>
        <?php endforeach; ?>
    </ul>

    <?php if ($allPass): ?>
    <h2>② 数据库配置</h2>
    <form method="post">
        <div class="field"><label>数据库地址</label>
            <input type="text" name="db_host" value="127.0.0.1" required></div>
        <div class="row">
            <div class="field"><label>端口</label>
                <input type="number" name="db_port" value="3306" required></div>
            <div class="field"><label>数据库名（不存在则自动创建）</label>
                <input type="text" name="db_name" value="aurora_blog" required></div>
        </div>
        <div class="row">
            <div class="field"><label>数据库用户名</label>
                <input type="text" name="db_user" value="root" required></div>
            <div class="field"><label>数据库密码</label>
                <input type="password" name="db_pass" placeholder="留空表示无密码"></div>
        </div>

        <h2>③ 站点与管理</h2>
        <div class="field"><label>站点名称</label>
            <input type="text" name="site_name" value="Aurora 极光博客"></div>
        <div class="row">
            <div class="field"><label>管理员用户名</label>
                <input type="text" name="admin_user" value="admin" required></div>
            <div class="field"><label>管理员邮箱</label>
                <input type="email" name="admin_email" placeholder="选填"></div>
        </div>
        <div class="field"><label>管理员密码（至少 6 位）</label>
            <input type="password" name="admin_pass" required minlength="6"></div>
        <button class="btn" type="submit">🚀 开始安装</button>
    </form>
    <div class="warn">提示：安装前请先在宝塔面板创建好数据库（也可留空由本向导自动创建，需数据库账号有建库权限）。</div>
    <?php endif; ?>
<?php endif; ?>
</div>
</div>
</body>
</html>
