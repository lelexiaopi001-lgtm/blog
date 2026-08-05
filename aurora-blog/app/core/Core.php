<?php
/**
 * Aurora Blog 核心框架
 * 包含: DB(PDO封装) / Helper(工具函数) / Auth(鉴权) / View(模板) / App(路由)
 * 零第三方依赖，PDO 预处理防注入，输出统一转义防 XSS
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$GLOBALS['__config'] = require dirname(__DIR__, 2) . '/config/config.php';

/* ================================================================
 * DB —— PDO 单例封装
 * ================================================================ */
class DB
{
    private static ?PDO $pdo = null;

    public static function conn(): PDO
    {
        if (self::$pdo === null) {
            $c = $GLOBALS['__config']['db'];
            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4";
            try {
                self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                Helper::abort(500, '数据库连接失败，请检查 config/config.php 配置');
            }
        }
        return self::$pdo;
    }

    public static function q(string $sql, array $params = []): PDOStatement
    {
        $st = self::conn()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::q($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::q($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = [])
    {
        return self::q($sql, $params)->fetchColumn();
    }

    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES (" .
               implode(',', array_fill(0, count($cols), '?')) . ")";
        self::q($sql, array_values($data));
        return (int) self::conn()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $wp = []): int
    {
        $set = implode(',', array_map(fn($c) => "`$c`=?", array_keys($data)));
        self::q("UPDATE `$table` SET $set WHERE $where", array_merge(array_values($data), $wp));
        return (int) self::one('SELECT ROW_COUNT()');
    }

    public static function rowsAffected(): int
    {
        return (int) self::one('SELECT ROW_COUNT()');
    }

    public static function delete(string $table, string $where, array $wp = []): void
    {
        self::q("DELETE FROM `$table` WHERE $where", $wp);
    }
}

/* ================================================================
 * Helper —— 工具函数
 * ================================================================ */
class Helper
{
    /** HTML 转义（防 XSS） */
    public static function e(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }

    /** 安全 JSON 输出并终止 */
    public static function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** 页面重定向 */
    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /** 错误页 */
    public static function abort(int $code, string $msg = ''): void
    {
        http_response_code($code);
        if (Helper::isAjax()) {
            Helper::json(['ok' => false, 'msg' => $msg ?: '请求错误'], $code);
        }
        $title = ['404' => '页面不存在', '403' => '禁止访问', '500' => '服务器错误'][(string) $code] ?? '错误';
        echo '<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' .
             "<title>{$code} - {$title}</title>" .
             '<style>body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:#0b0f1a;color:#e5e9f2;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;flex-direction:column;gap:12px}.code{font-size:72px;font-weight:800;background:linear-gradient(135deg,#6366f1,#a855f7,#ec4899);-webkit-background-clip:text;background-clip:text;color:transparent}.msg{opacity:.7}a{color:#a5b4fc;text-decoration:none;border:1px solid #312e81;padding:8px 20px;border-radius:8px}</style>' .
             '</head><body><div class="code">' . $code . '</div><div class="msg">' . self::e($msg ?: $title) . '</div>' .
             '<a href="/">返回首页</a></body></html>';
        exit;
    }

    /** 是否 AJAX 请求 */
    public static function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    /** POST 参数（默认转义留给模板层处理） */
    public static function post(string $key, $default = '')
    {
        return $_POST[$key] ?? $default;
    }

    public static function get(string $key, $default = '')
    {
        return $_GET[$key] ?? $default;
    }

    /** 生成 URL slug */
    public static function slugify(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\-_ ]+/u', '', $text);
        $text = preg_replace('/[\s_]+/', '-', $text);
        return trim($text, '-') ?: 'post-' . substr(md5($text . microtime()), 0, 6);
    }

    /** 随机字符串 */
    public static function random(int $len = 32): string
    {
        return bin2hex(random_bytes(intdiv($len + 1, 2)));
    }

    /** 客户端 IP */
    public static function ip(): string
    {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = explode(',', $_SERVER[$k])[0];
                if (filter_var(trim($ip), FILTER_VALIDATE_IP)) {
                    return trim($ip);
                }
            }
        }
        return '0.0.0.0';
    }

    /** CSRF Token */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = self::random(32);
        }
        return $_SESSION['csrf'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf" value="' . self::csrfToken() . '">';
    }

    public static function csrfCheck(): void
    {
        $token = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!hash_equals($_SESSION['csrf'] ?? '', (string) $token)) {
            self::abort(403, '安全令牌校验失败，请刷新页面重试');
        }
    }

    /** 读取站点设置（带缓存） */
    public static function setting(string $key, string $default = ''): string
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            try {
                foreach (DB::all('SELECT skey,svalue FROM settings') as $row) {
                    $cache[$row['skey']] = (string) $row['svalue'];
                }
            } catch (Exception $e) {
                // 安装前/settings表不存在时返回默认值
            }
        }
        return $cache[$key] ?? $default;
    }

    /** 站点根 URL（自动识别协议） */
    public static function siteUrl(string $path = ''): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' ? 'https' : 'http';
        $base = rtrim($scheme . '://' . $host, '/');
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    }

    /** 带版本号的静态资源 URL（防缓存） */
    public static function asset(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $file = dirname(__DIR__, 2) . '/public' . $path;
        $v = defined('AURORA_BUILD') ? AURORA_BUILD : '3';
        if (is_file($file)) {
            $v = substr(md5((string) filemtime($file)), 0, 8);
        }
        return $path . '?v=' . $v;
    }

    /** 字数统计（中英文混合） */
    public static function wordCount(string $content): int
    {
        $text = preg_replace('/[#>*_`~\[\]()!\-+|]/', ' ', $content);
        $text = preg_replace('/\s+/', ' ', trim($text));
        // 中文字符 + 英文单词
        $cn = preg_match_all('/\p{Han}/u', $text);
        $en = preg_match_all('/[a-zA-Z0-9_-]+/', $text);
        return (int) ($cn + $en);
    }

    /** 文件大小格式化 */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = min((int) floor(log($bytes, 1024)), count($units) - 1);
        return round($bytes / (1024 ** $i), $precision) . ' ' . $units[$i];
    }

    /** 通过 SMTP 发送邮件（使用站点设置中的 SMTP 配置） */
    public static function mail(string $to, string $subject, string $body): bool
    {
        $host = self::setting('smtp_host');
        $port = (int) self::setting('smtp_port', '587');
        $user = self::setting('smtp_user');
        $pass = self::setting('smtp_pass');
        $from = self::setting('smtp_from') ?: $user;
        if (!$host || !$user || !$pass || !$from) return false;

        $site = self::setting('site_name', 'Aurora Blog');
        $fromName = '=?UTF-8?B?' . base64_encode($site) . '?=';
        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers = "From: {$fromName} <{$from}>\r\n";
        $headers .= "Reply-To: {$from}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n";
        $message = base64_encode($body);

        // 优先使用 mail()，若环境未配置则使用 fsockopen 简单 SMTP
        if (function_exists('mail') && ini_get('SMTP')) {
            return mail($to, $subject, $message, $headers);
        }
        return self::smtpSend($host, $port, $user, $pass, $from, $to, $subject, $message, $headers);
    }

    private static function smtpSend(string $host, int $port, string $user, string $pass, string $from, string $to, string $subject, string $message, string $headers): bool
    {
        $errno = 0; $errstr = '';
        $fp = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$fp) return false;
        $get = fn() => fgets($fp, 512);
        $put = fn($cmd) => fwrite($fp, $cmd . "\r\n");
        $get();
        $put('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        while (($line = $get()) && substr($line, 3, 1) === '-') {}
        if ($port == 587) {
            $put('STARTTLS');
            $get();
            stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $put('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            while (($line = $get()) && substr($line, 3, 1) === '-') {}
        }
        $put('AUTH LOGIN'); $get();
        $put(base64_encode($user)); $get();
        $put(base64_encode($pass)); $get();
        $put('MAIL FROM:<' . $from . '>'); $get();
        $put('RCPT TO:<' . $to . '>'); $get();
        $put('DATA');
        $get();
        $data = "Subject: {$subject}\r\nTo: {$to}\r\n{$headers}\r\n{$message}\r\n.\r\n";
        $put($data);
        $code = (int) substr($get(), 0, 3);
        $put('QUIT');
        fclose($fp);
        return $code >= 200 && $code < 300;
    }

    /** 相对时间 */
    public static function timeAgo(string $datetime): string
    {
        $t = strtotime($datetime);
        $d = time() - $t;
        if ($d < 60) return '刚刚';
        if ($d < 3600) return intdiv($d, 60) . ' 分钟前';
        if ($d < 86400) return intdiv($d, 3600) . ' 小时前';
        if ($d < 2592000) return intdiv($d, 86400) . ' 天前';
        return date('Y-m-d', $t);
    }

    /** 时间格式 */
    public static function date(string $datetime, string $fmt = 'Y-m-d'): string
    {
        return date($fmt, strtotime($datetime));
    }

    /** 阅读时长（中文字符按 350 字/分钟） */
    public static function readingTime(string $content): int
    {
        $text = preg_replace('/[#>*_`~\[\]()!\-+|]/', ' ', $content);
        $text = preg_replace('/\s+/', ' ', trim($text));
        $count = mb_strlen($text, 'UTF-8');
        return max(1, (int) ceil($count / 350));
    }

    /** 从正文 HTML 提取目录（h2/h3） */
    public static function toc(string $html): array
    {
        preg_match_all('/<h([23])\s+id="([^"]+)">(.*?)<\/h\1>/', $html, $matches, PREG_SET_ORDER);
        $toc = [];
        foreach ($matches as $m) {
            $toc[] = ['level' => (int) $m[1], 'id' => $m[2], 'title' => strip_tags($m[3])];
        }
        return $toc;
    }

    /** 分页计算 */
    public static function paginate(int $total, int $page, int $size): array
    {
        $pages = max(1, (int) ceil($total / $size));
        $page = min(max(1, $page), $pages);
        return ['page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $size, 'total' => $total];
    }

    /** 生成分页 HTML */
    public static function pagination(array $pg, string $base): string
    {
        if ($pg['pages'] <= 1) return '';
        $html = '<nav class="pagination"><div class="pagination-inner">';
        $prev = $pg['page'] > 1 ? $base . ($pg['page'] - 1) : '';
        $html .= $prev ? "<a class=\"pg-item\" href=\"{$base}" . ($pg['page'] - 1) . "\">‹</a>" : '<span class="pg-item disabled">‹</span>';
        $start = max(1, $pg['page'] - 2);
        $end = min($pg['pages'], $start + 4);
        $start = max(1, $end - 4);
        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $pg['page'] ? ' active' : '';
            $html .= $i === $pg['page']
                ? "<span class=\"pg-item{$active}\">{$i}</span>"
                : "<a class=\"pg-item\" href=\"{$base}{$i}\">{$i}</a>";
        }
        $next = $pg['page'] < $pg['pages'] ? $base . ($pg['page'] + 1) : '';
        $html .= $next ? "<a class=\"pg-item\" href=\"{$next}\">›</a>" : '<span class="pg-item disabled">›</span>';
        return $html . '</div></nav>';
    }

    /** 浏览量 +1（防刷新刷量：同会话只记一次） */
    public static function bumpView(int $postId): void
    {
        if (!isset($_SESSION['viewed_posts'])) $_SESSION['viewed_posts'] = [];
        if (!in_array($postId, $_SESSION['viewed_posts'], true)) {
            $_SESSION['viewed_posts'][] = $postId;
            DB::q('UPDATE posts SET views = views + 1 WHERE id = ?', [$postId]);
        }
    }

    /** 记录每日 PV/UV */
    public static function recordVisit(): void
    {
        $today = date('Y-m-d');
        $ip = self::ip();
        $key = 'uv_' . $today . '_' . md5($ip);
        $isNew = empty($_SESSION[$key]);
        $_SESSION[$key] = 1;
        try {
            DB::q("INSERT INTO visits (vdate, pv, uv) VALUES (?, 1, ?)
                   ON DUPLICATE KEY UPDATE pv = pv + 1, uv = uv + ?", [$today, $isNew ? 1 : 0, $isNew ? 1 : 0]);
        } catch (Exception $e) {
            // 忽略统计失败，不影响页面
        }
    }

    /** 图片上传（白名单校验） */
    public static function upload(array $file, string $dir = 'uploads'): array
    {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'msg' => '上传失败，错误码 ' . ($file['error'] ?? '未知')];
        }
        $mime = (string) (function_exists('finfo_open') && $finfo = finfo_open(FILEINFO_MIME_TYPE)
            ? finfo_file($finfo, $file['tmp_name']) : $file['type']);
        if (!isset($allowed[$mime])) {
            return ['ok' => false, 'msg' => '仅支持 JPG / PNG / GIF / WebP 图片'];
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['ok' => false, 'msg' => '图片大小不能超过 5MB'];
        }
        $root = dirname(__DIR__, 2) . '/public/';
        $sub = $dir . '/' . date('Ym');
        if (!is_dir($root . $sub)) mkdir($root . $sub, 0755, true);
        $name = self::random(16) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $root . $sub . '/' . $name)) {
            return ['ok' => false, 'msg' => '文件保存失败，请检查 uploads 目录权限'];
        }
        return ['ok' => true, 'url' => '/' . $sub . '/' . $name];
    }

    /** 记录登录失败 */
    public static function logLoginFail(string $username): void
    {
        DB::q('INSERT INTO login_attempts (username, ip) VALUES (?, ?)', [$username, self::ip()]);
    }

    /** 是否触发登录限速 */
    public static function loginLocked(): bool
    {
        $n = DB::one('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)', [self::ip()]);
        return (int) $n >= 5;
    }

    /** 清空登录失败记录 */
    public static function clearLoginFails(): void
    {
        DB::q('DELETE FROM login_attempts WHERE ip = ?', [self::ip()]);
    }
}

/* ================================================================
 * Auth —— 登录鉴权（Session + 记住我 Cookie）
 * ================================================================ */
class Auth
{
    public static function login(string $username, string $password, bool $remember = false): array
    {
        $user = DB::fetch('SELECT * FROM users WHERE username = ? LIMIT 1', [$username]);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            Helper::logLoginFail($username);
            return ['ok' => false, 'msg' => '用户名或密码错误'];
        }
        DB::q('UPDATE users SET last_login = NOW() WHERE id = ?', [$user['id']]);
        Helper::clearLoginFails();
        session_regenerate_id(true);
        $_SESSION['uid'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nickname'] = $user['nickname'] ?: $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($remember) {
            $token = Helper::random(32);
            $expire = time() + 30 * 86400;
            setcookie('ab_remember', $user['id'] . ':' . hash('sha256', $token), $expire, '/', '', false, true);
            DB::q("UPDATE users SET remember_token = ? WHERE id = ?", [hash('sha256', $token), $user['id']]);
        } else {
            setcookie('ab_remember', '', time() - 3600, '/');
        }
        return ['ok' => true, 'user' => $user];
    }

    public static function check(): bool
    {
        if (!empty($_SESSION['uid'])) return true;
        if (!empty($_COOKIE['ab_remember'])) {
            [$id, $token] = array_pad(explode(':', $_COOKIE['ab_remember'], 2), 2, '');
            $user = DB::fetch('SELECT * FROM users WHERE id = ? AND remember_token = ?', [(int) $id, hash('sha256', $token)]);
            if ($user) {
                session_regenerate_id(true);
                $_SESSION['uid'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nickname'] = $user['nickname'] ?: $user['username'];
                $_SESSION['role'] = $user['role'];
                return true;
            }
            setcookie('ab_remember', '', time() - 3600, '/');
        }
        return false;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Helper::redirect('/admin/index.php?r=login&back=' . urlencode($_SERVER['REQUEST_URI']));
        }
    }

    public static function user(): ?array
    {
        return self::check() ? DB::fetch('SELECT * FROM users WHERE id = ?', [$_SESSION['uid']]) : null;
    }

    public static function logout(): void
    {
        if (!empty($_COOKIE['ab_remember'])) {
            [$id] = explode(':', $_COOKIE['ab_remember'], 2);
            DB::q("UPDATE users SET remember_token = '' WHERE id = ?", [(int) $id]);
        }
        setcookie('ab_remember', '', time() - 3600, '/');
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        session_destroy();
    }
}

/* ================================================================
 * View —— 轻量模板渲染
 * ================================================================ */
class View
{
    /** 渲染模板: render('post', ['post' => $post]) */
    public static function render(string $tpl, array $data = [], string $layout = 'layout'): void
    {
        extract($data, EXTR_SKIP);
        $contentFile = self::resolve($tpl);
        $GLOBALS['__contentFile'] = $contentFile;
        // 先执行内容模板（可设置 $GLOBALS['__pageTitle'] 等页面变量），缓冲其输出
        ob_start();
        require $contentFile;
        $GLOBALS['__contentHtml'] = ob_get_clean();
        require self::resolve('layout');
    }

    /** 输出内容区（供 layout 使用） */
    public static function content(): void
    {
        echo $GLOBALS['__contentHtml'];
    }

    /** 渲染纯片段（AJAX/局部） */
    public static function partial(string $tpl, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require self::resolve($tpl);
    }

    private static function resolve(string $name): string
    {
        $file = dirname(__DIR__) . '/views/' . $name . '.php';
        if (!is_file($file)) Helper::abort(500, "模板不存在: {$name}");
        return $file;
    }
}

/* ================================================================
 * App —— 前台路由
 * ================================================================ */
class App
{
    public static function run(): void
    {
        Helper::recordVisit();
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $path = '/' . trim($path, '/');
        $seg = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
        // URL 解码（支持中文 slug：/category/技术 → /category/%E6%8A%80%E6%9C%AF）
        $seg = array_map('rawurldecode', $seg);

        // 静态资源直接放行（由 web 服务器处理，这里兜底 404）
        if (count($seg) > 0 && in_array($seg[0], ['assets', 'uploads'], true)) {
            return;
        }

        $c = new FrontController();
        $first = $seg[0] ?? '';

        switch (true) {
            case $path === '/' || $first === 'page':
                $c->home((int) ($seg[1] ?? 1));
                break;
            case $first === 'post' && isset($seg[1]):
                $c->post((string) $seg[1]);
                break;
            case $first === 'category' && isset($seg[1]):
                $c->category((string) $seg[1], (int) ($seg[3] ?? 1));
                break;
            case $first === 'tag' && isset($seg[1]):
                $c->tag((string) $seg[1], (int) ($seg[3] ?? 1));
                break;
            case $first === 'archive':
                $c->archive((int) ($seg[1] ?? 1));
                break;
            case $first === 'search':
                $c->search((string) Helper::get('q'));
                break;
            case $first === 'about':
                $c->about();
                break;
            case $first === 'rss.xml':
            case $first === 'feed':
                $c->rss();
                break;
            case $first === 'admin':
                Helper::redirect('/admin/index.php');
                break;
            case $first === 'api':
                $c->api($seg[1] ?? '');
                break;
            default:
                $c->notFound();
        }
    }
}

/* 自动加载控制器与 Markdown */
require_once __DIR__ . '/Markdown.php';
require_once dirname(__DIR__) . '/controllers/FrontController.php';
