<?php
/**
 * Aurora Blog 后台控制器 —— 可视化管理系统
 * 路由: /admin/index.php?r=action
 */

declare(strict_types=1);

class AdminController
{
    public function __construct()
    {
        $r = (string) Helper::get('r', 'dashboard');
        if (!in_array($r, ['login'], true) && !Auth::check()) {
            Helper::redirect('/admin/index.php?r=login');
        }
        // POST 请求统一 CSRF 校验
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($r, ['login', 'upload'], true)) {
            Helper::csrfCheck();
        }
    }

    /* ================= 路由分发 ================= */
    public function dispatch(string $r): void
    {
        $actions = [
            'login' => 'login', 'logout' => 'logout',
            'dashboard' => 'dashboard',
            'posts' => 'posts', 'post_edit' => 'postEdit', 'post_save' => 'postSave',
            'post_delete' => 'postDelete', 'post_restore' => 'postRestore', 'post_purge' => 'postPurge',
            'categories' => 'categories', 'category_save' => 'categorySave', 'category_delete' => 'categoryDelete',
            'tags' => 'tags', 'tag_save' => 'tagSave', 'tag_delete' => 'tagDelete',
            'comments' => 'comments', 'comment_approve' => 'commentApprove', 'comment_reject' => 'commentReject', 'comment_delete' => 'commentDelete',
            'links' => 'links', 'link_save' => 'linkSave', 'link_delete' => 'linkDelete',
            'settings' => 'settings', 'settings_save' => 'settingsSave',
            'stats' => 'stats', 'profile' => 'profile', 'profile_save' => 'profileSave',
            'media' => 'media', 'media_delete' => 'mediaDelete',
            'upload' => 'upload',
        ];
        if (!isset($actions[$r])) Helper::abort(404, '后台页面不存在');
        $this->{$actions[$r]}();
    }

    /* ================= 登录 ================= */
    private function login(): void
    {
        if (Auth::check()) Helper::redirect('/admin/index.php?r=dashboard');
        $err = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Helper::loginLocked()) {
                $err = '尝试次数过多，请 15 分钟后再试';
            } else {
                $res = Auth::login(
                    trim((string) Helper::post('username')),
                    (string) Helper::post('password'),
                    Helper::post('remember') === '1'
                );
                if ($res['ok']) {
                    Helper::redirect('/admin/index.php?r=dashboard');
                }
                $err = $res['msg'];
            }
        }
        $this->plainRender('login', compact('err'));
    }

    private function logout(): void
    {
        Auth::logout();
        Helper::redirect('/admin/index.php?r=login');
    }

    /* ================= 仪表盘 ================= */
    private function dashboard(): void
    {
        $stat = [
            'posts' => (int) DB::one("SELECT COUNT(*) FROM posts WHERE status='published'"),
            'drafts' => (int) DB::one("SELECT COUNT(*) FROM posts WHERE status='draft'"),
            'trash' => (int) DB::one("SELECT COUNT(*) FROM posts WHERE status='trash'"),
            'comments' => (int) DB::one("SELECT COUNT(*) FROM comments WHERE status='pending'"),
            'comments_total' => (int) DB::one('SELECT COUNT(*) FROM comments'),
            'categories' => (int) DB::one('SELECT COUNT(*) FROM categories'),
            'tags' => (int) DB::one('SELECT COUNT(*) FROM tags'),
            'links' => (int) DB::one('SELECT COUNT(*) FROM links'),
            'pv_total' => (int) DB::one('SELECT COALESCE(SUM(pv),0) FROM visits'),
            'pv_today' => (int) DB::one("SELECT COALESCE(SUM(pv),0) FROM visits WHERE vdate = CURDATE()"),
            'uv_today' => (int) DB::one("SELECT COALESCE(SUM(uv),0) FROM visits WHERE vdate = CURDATE()"),
            'views_top' => (int) DB::one("SELECT COALESCE(MAX(views),0) FROM posts WHERE status='published'"),
        ];
        // 近 14 天趋势（给前端画图）
        $trend = DB::all("SELECT vdate, pv, uv FROM visits WHERE vdate >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) ORDER BY vdate ASC");
        $map = [];
        foreach ($trend as $t) $map[$t['vdate']] = $t;
        $labels = []; $pvData = []; $uvData = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i day"));
            $labels[] = date('m-d', strtotime($d));
            $pvData[] = (int) ($map[$d]['pv'] ?? 0);
            $uvData[] = (int) ($map[$d]['uv'] ?? 0);
        }
        $recentComments = DB::all("SELECT c.*, p.title AS post_title FROM comments c JOIN posts p ON p.id = c.post_id ORDER BY c.id DESC LIMIT 6");
        $recentPosts = DB::all("SELECT id, title, status, views, published_at FROM posts ORDER BY id DESC LIMIT 6");

        $this->render('dashboard', compact('stat', 'labels', 'pvData', 'uvData', 'recentComments', 'recentPosts'));
    }

    /* ================= 文章管理 ================= */
    private function posts(): void
    {
        $status = in_array(Helper::get('status', 'all'), ['published', 'draft', 'trash'], true) ? Helper::get('status') : 'all';
        $kw = trim((string) Helper::get('kw'));
        $page = max(1, (int) Helper::get('page', 1));
        $size = 15;

        $where = $status === 'all' ? "status != 'trash'" : "status = '" . $status . "'";
        $params = [];
        if ($kw !== '') {
            $where .= ' AND (title LIKE ? OR content LIKE ?)';
            $params[] = "%$kw%"; $params[] = "%$kw%";
        }
        $total = (int) DB::one("SELECT COUNT(*) FROM posts WHERE $where", $params);
        $pg = Helper::paginate($total, $page, $size);
        $posts = DB::all("SELECT p.*, c.name AS cat_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id
            WHERE $where ORDER BY p.is_top DESC, p.id DESC LIMIT {$pg['offset']}, $size", $params);

        $this->render('posts', compact('posts', 'pg', 'status', 'kw'));
    }

    private function postEdit(): void
    {
        $id = (int) Helper::get('id', 0);
        $post = $id ? DB::fetch('SELECT * FROM posts WHERE id = ?', [$id]) : null;
        if ($id && !$post) Helper::abort(404, '文章不存在');
        $categories = DB::all('SELECT * FROM categories ORDER BY sort ASC');
        $allTags = DB::all('SELECT * FROM tags ORDER BY post_count DESC');
        $postTags = $post ? DB::all("SELECT t.name FROM tags t JOIN post_tags pt ON pt.tag_id=t.id WHERE pt.post_id=?", [$post['id']]) : [];
        $postTagsStr = implode(', ', array_column($postTags, 'name'));
        $this->render('post-edit', compact('post', 'categories', 'allTags', 'postTagsStr'));
    }

    private function postSave(): void
    {
        $id = (int) Helper::post('id', 0);
        $title = trim((string) Helper::post('title'));
        if ($title === '') $this->back('文章标题不能为空', '/admin/index.php?r=post_edit' . ($id ? "&id=$id" : ''));
        $slugRaw = trim((string) Helper::post('slug'));
        $slug = $slugRaw !== '' ? Helper::slugify($slugRaw) : Helper::slugify($title);
        // slug 唯一性
        $dup = DB::fetch('SELECT id FROM posts WHERE slug = ? AND id != ?', [$slug, $id]);
        if ($dup) $slug .= '-' . substr(md5($slug . microtime()), 0, 4);

        $status = in_array(Helper::post('status'), ['draft', 'published'], true) ? Helper::post('status') : 'draft';
        $categoryId = (int) Helper::post('category_id', 0) ?: null;
        $content = (string) Helper::post('content');
        $summary = trim((string) Helper::post('summary'));
        if ($summary === '') $summary = Markdown::plain($content, 120);
        $publishedAt = trim((string) Helper::post('published_at'));
        if ($status === 'published') {
            $publishedAt = $publishedAt !== '' ? date('Y-m-d H:i:s', strtotime($publishedAt)) : date('Y-m-d H:i:s');
        } else {
            $publishedAt = $publishedAt ?: ($id ? DB::one('SELECT published_at FROM posts WHERE id=?', [$id]) : null);
        }

        $data = [
            'title' => $title,
            'slug' => $slug,
            'summary' => mb_substr($summary, 0, 500),
            'content' => $content,
            'cover' => trim((string) Helper::post('cover')),
            'category_id' => $categoryId,
            'status' => $status,
            'is_top' => Helper::post('is_top') === '1' ? 1 : 0,
            'comment_status' => Helper::post('comment_status') === '1' ? 1 : 0,
            'published_at' => $publishedAt,
        ];

        if ($id) {
            DB::update('posts', $data, 'id = ?', [$id]);
        } else {
            $id = DB::insert('posts', $data + ['user_id' => (int) $_SESSION['uid']]);
        }
        $this->syncTags((int) $id, (string) Helper::post('tags'));
        $this->syncCategoryCount();

        $this->back($id ? '文章已更新' : '文章已发布', '/admin/index.php?r=post_edit&id=' . $id);
    }

    /** 同步文章标签并更新计数 */
    private function syncTags(int $postId, string $tagsStr): void
    {
        DB::delete('post_tags', 'post_id = ?', [$postId]);
        $names = array_filter(array_map('trim', explode(',', $tagsStr)));
        foreach ($names as $name) {
            $name = mb_substr($name, 0, 30);
            $slug = Helper::slugify($name);
            $tag = DB::fetch('SELECT id FROM tags WHERE slug = ?', [$slug]);
            if (!$tag) {
                $tagId = DB::insert('tags', ['name' => $name, 'slug' => $slug]);
            } else {
                $tagId = (int) $tag['id'];
            }
            DB::q('INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)', [$postId, $tagId]);
        }
        DB::q('UPDATE tags SET post_count = (SELECT COUNT(*) FROM post_tags WHERE tag_id = tags.id)');
        // 清理空标签
        DB::q('DELETE FROM tags WHERE post_count = 0');
    }

    private function syncCategoryCount(): void
    {
        DB::q("UPDATE categories c SET post_count = (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id AND p.status='published')");
    }

    private function postDelete(): void
    {
        $id = (int) Helper::post('id');
        DB::update('posts', ['status' => 'trash'], 'id = ?', [$id]);
        $this->syncCategoryCount();
        $this->back('已移入回收站', '/admin/index.php?r=posts');
    }

    private function postRestore(): void
    {
        $id = (int) Helper::post('id');
        DB::update('posts', ['status' => 'draft'], 'id = ?', [$id]);
        $this->syncCategoryCount();
        $this->back('已恢复为草稿', '/admin/index.php?r=posts&status=trash');
    }

    private function postPurge(): void
    {
        $id = (int) Helper::post('id');
        DB::delete('post_tags', 'post_id = ?', [$id]);
        DB::delete('comments', 'post_id = ?', [$id]);
        DB::delete('posts', 'id = ?', [$id]);
        $this->back('已彻底删除', '/admin/index.php?r=posts&status=trash');
    }

    /* ================= 分类管理 ================= */
    private function categories(): void
    {
        $cats = DB::all('SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id=c.id AND p.status="published") AS cnt
            FROM categories c ORDER BY c.sort ASC, c.id ASC');
        $this->render('categories', compact('cats'));
    }

    private function categorySave(): void
    {
        $id = (int) Helper::post('id', 0);
        $name = trim((string) Helper::post('name'));
        if ($name === '') $this->back('分类名称不能为空', '/admin/index.php?r=categories');
        $slug = Helper::slugify((string) (Helper::post('slug') ?: $name));
        if (DB::fetch('SELECT id FROM categories WHERE slug=? AND id!=?', [$slug, $id])) {
            $slug .= '-' . substr(md5($slug), 0, 4);
        }
        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string) Helper::post('description')),
            'sort' => (int) Helper::post('sort', 0),
        ];
        $id ? DB::update('categories', $data, 'id=?', [$id]) : DB::insert('categories', $data);
        $this->back($id ? '分类已更新' : '分类已创建', '/admin/index.php?r=categories');
    }

    private function categoryDelete(): void
    {
        $id = (int) Helper::post('id');
        DB::q('UPDATE posts SET category_id = NULL WHERE category_id = ?', [$id]);
        DB::delete('categories', 'id = ?', [$id]);
        $this->back('分类已删除', '/admin/index.php?r=categories');
    }

    /* ================= 标签管理 ================= */
    private function tags(): void
    {
        $tags = DB::all('SELECT * FROM tags ORDER BY post_count DESC, id DESC');
        $this->render('tags', compact('tags'));
    }

    private function tagSave(): void
    {
        $id = (int) Helper::post('id', 0);
        $name = trim((string) Helper::post('name'));
        if ($name === '') $this->back('标签名称不能为空', '/admin/index.php?r=tags');
        $slug = Helper::slugify($name);
        if (DB::fetch('SELECT id FROM tags WHERE slug=? AND id!=?', [$slug, $id])) {
            $slug .= '-' . substr(md5($slug), 0, 4);
        }
        $id ? DB::update('tags', ['name' => $name, 'slug' => $slug], 'id=?', [$id]) : DB::insert('tags', ['name' => $name, 'slug' => $slug]);
        $this->back($id ? '标签已更新' : '标签已创建', '/admin/index.php?r=tags');
    }

    private function tagDelete(): void
    {
        $id = (int) Helper::post('id');
        DB::delete('post_tags', 'tag_id = ?', [$id]);
        DB::delete('tags', 'id = ?', [$id]);
        DB::q('UPDATE tags SET post_count = (SELECT COUNT(*) FROM post_tags WHERE tag_id = tags.id)');
        $this->back('标签已删除', '/admin/index.php?r=tags');
    }

    /* ================= 评论管理 ================= */
    private function comments(): void
    {
        $status = in_array(Helper::get('status', 'pending'), ['pending', 'approved', 'rejected', 'all'], true) ? Helper::get('status') : 'pending';
        $page = max(1, (int) Helper::get('page', 1));
        $size = 15;
        $where = $status === 'all' ? '1=1' : "c.status = '" . $status . "'";
        $total = (int) DB::one("SELECT COUNT(*) FROM comments c WHERE $where");
        $pg = Helper::paginate($total, $page, $size);
        $comments = DB::all("SELECT c.*, p.title AS post_title FROM comments c
            LEFT JOIN posts p ON p.id = c.post_id WHERE $where
            ORDER BY c.id DESC LIMIT {$pg['offset']}, $size");
        $this->render('comments', compact('comments', 'pg', 'status'));
    }

    private function commentApprove(): void
    {
        $id = (int) Helper::post('id');
        DB::q("UPDATE comments SET status='approved' WHERE id=?", [$id]);
        $this->back('评论已通过', '/admin/index.php?r=comments&status=' . Helper::get('status', 'pending'));
    }

    private function commentReject(): void
    {
        $id = (int) Helper::post('id');
        DB::q("UPDATE comments SET status='rejected' WHERE id=?", [$id]);
        $this->back('评论已拒绝', '/admin/index.php?r=comments');
    }

    private function commentDelete(): void
    {
        $id = (int) Helper::post('id');
        $c = DB::fetch('SELECT post_id FROM comments WHERE id=?', [$id]);
        DB::delete('comments', 'id = ? OR parent_id = ?', [$id, $id]);
        if ($c) {
            DB::q('UPDATE posts SET comment_count = (SELECT COUNT(*) FROM comments WHERE post_id=? AND status="approved") WHERE id=?', [$c['post_id'], $c['post_id']]);
        }
        $this->back('评论已删除', '/admin/index.php?r=comments');
    }

    /* ================= 友链管理 ================= */
    private function links(): void
    {
        $links = DB::all('SELECT * FROM links ORDER BY sort ASC, id ASC');
        $this->render('links', compact('links'));
    }

    private function linkSave(): void
    {
        $id = (int) Helper::post('id', 0);
        $name = trim((string) Helper::post('name'));
        $url = trim((string) Helper::post('url'));
        if ($name === '' || $url === '') $this->back('名称和网址必填', '/admin/index.php?r=links');
        $data = [
            'name' => mb_substr($name, 0, 80),
            'url' => mb_substr($url, 0, 255),
            'logo' => trim((string) Helper::post('logo')),
            'description' => mb_substr(trim((string) Helper::post('description')), 0, 255),
            'sort' => (int) Helper::post('sort', 0),
            'status' => Helper::post('status') === '1' ? 1 : 0,
        ];
        $id ? DB::update('links', $data, 'id=?', [$id]) : DB::insert('links', $data);
        $this->back($id ? '友链已更新' : '友链已添加', '/admin/index.php?r=links');
    }

    private function linkDelete(): void
    {
        DB::delete('links', 'id = ?', [(int) Helper::post('id')]);
        $this->back('友链已删除', '/admin/index.php?r=links');
    }

    /* ================= 站点设置 ================= */
    private function settings(): void
    {
        $all = [];
        foreach (DB::all('SELECT skey, svalue FROM settings') as $row) {
            $all[$row['skey']] = $row['svalue'];
        }
        $this->render('settings', compact('all'));
    }

    private function settingsSave(): void
    {
        $fields = [
            'site_name', 'site_desc', 'site_slogan', 'site_motto', 'site_hero_eyebrow', 'site_hero_bg', 'site_keywords', 'site_icp', 'site_logo', 'site_avatar',
            'site_about', 'site_social_github', 'site_social_twitter', 'site_social_email',
            'site_notice', 'site_notice_enabled',
            'pwa_enabled', 'pwa_theme_color', 'pwa_bg_color',
            'theme_accent', 'theme_mode',
            'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from', 'smtp_notify_email',
            'comment_switch', 'comment_audit', 'page_size', 'footer_text',
        ];
        foreach ($fields as $f) {
            DB::q('INSERT INTO settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)', [$f, (string) Helper::post($f)]);
        }
        $this->back('设置已保存', '/admin/index.php?r=settings');
    }

    /* ================= 访问统计 ================= */
    private function stats(): void
    {
        $days = (int) Helper::get('days', 30);
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;
        $trend = DB::all("SELECT vdate, pv, uv FROM visits WHERE vdate >= DATE_SUB(CURDATE(), INTERVAL ? DAY) ORDER BY vdate ASC", [$days - 1]);
        $map = [];
        foreach ($trend as $t) $map[$t['vdate']] = $t;
        $labels = []; $pv = []; $uv = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i day"));
            $labels[] = date('m-d', strtotime($d));
            $pv[] = (int) ($map[$d]['pv'] ?? 0);
            $uv[] = (int) ($map[$d]['uv'] ?? 0);
        }
        $hot = DB::all("SELECT title, slug, views, likes, comment_count FROM posts WHERE status='published' ORDER BY views DESC LIMIT 10");
        $catStat = DB::all("SELECT c.name, c.post_count FROM categories c ORDER BY c.post_count DESC");
        $pvTotal = (int) DB::one('SELECT COALESCE(SUM(pv),0) FROM visits');
        $uvTotal = (int) DB::one('SELECT COALESCE(SUM(uv),0) FROM visits');
        $avgPv = $pvTotal ? (int) round($pvTotal / max(1, DB::one('SELECT COUNT(*) FROM visits'))) : 0;
        $this->render('stats', compact('labels', 'pv', 'uv', 'hot', 'catStat', 'pvTotal', 'uvTotal', 'avgPv', 'days'));
    }

    /* ================= 个人资料 ================= */
    private function profile(): void
    {
        $user = Auth::user();
        $this->render('profile', compact('user'));
    }

    private function profileSave(): void
    {
        $user = Auth::user();
        $nickname = mb_substr(trim((string) Helper::post('nickname')), 0, 50) ?: $user['username'];
        $email = trim((string) Helper::post('email'));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $this->back('邮箱格式不正确', '/admin/index.php?r=profile');
        $data = ['nickname' => $nickname, 'email' => mb_substr($email, 0, 100)];
        $oldPwd = (string) Helper::post('old_password');
        $newPwd = (string) Helper::post('new_password');
        $msg = '资料已更新';
        if ($newPwd !== '') {
            if (!password_verify($oldPwd, $user['password_hash'])) $this->back('原密码错误', '/admin/index.php?r=profile');
            if (strlen($newPwd) < 6) $this->back('新密码至少 6 位', '/admin/index.php?r=profile');
            $data['password_hash'] = password_hash($newPwd, PASSWORD_DEFAULT);
            $msg = '资料已更新，密码已修改';
        }
        DB::update('users', $data, 'id=?', [$user['id']]);
        $_SESSION['nickname'] = $nickname;
        $this->back($msg, '/admin/index.php?r=profile');
    }

    /* ================= 媒体库 ================= */
    private function media(): void
    {
        $root = dirname(__DIR__, 2) . '/public/uploads';
        $images = [];
        if (is_dir($root)) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
            foreach ($it as $file) {
                if ($file->isFile()) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                        $rel = str_replace($root, '', $file->getPathname());
                        $rel = str_replace('\\', '/', $rel);
                        $images[] = [
                            'url' => '/uploads' . $rel,
                            'name' => $file->getFilename(),
                            'size' => $file->getSize(),
                            'mtime' => date('Y-m-d H:i', $file->getMTime()),
                        ];
                    }
                }
            }
            usort($images, fn($a, $b) => strcmp($b['mtime'], $a['mtime']));
        }
        $total = count($images);
        $sizeSum = array_sum(array_column($images, 'size'));
        $this->render('media', compact('images', 'total', 'sizeSum'));
    }

    private function mediaDelete(): void
    {
        $url = (string) Helper::post('url');
        if (!str_starts_with($url, '/uploads/')) {
            $this->back('无效的图片地址', '/admin/index.php?r=media');
        }
        $realRoot = realpath(dirname(__DIR__, 2) . '/public/uploads');
        $realFile = realpath(dirname(__DIR__, 2) . '/public' . $url);
        if ($realRoot && $realFile && str_starts_with($realFile, $realRoot) && is_file($realFile)) {
            @unlink($realFile);
        }
        $this->back('图片已删除', '/admin/index.php?r=media');
    }

    /* ================= 图片上传（AJAX） ================= */
    private function upload(): void
    {
        Auth::requireLogin();
        if (empty($_FILES['file'])) Helper::json(['ok' => false, 'msg' => '未收到文件']);
        $res = Helper::upload($_FILES['file']);
        Helper::json($res, $res['ok'] ? 200 : 400);
    }

    /* ================= 内部工具 ================= */

    /** 带消息跳转 */
    private function back(string $msg, string $url): void
    {
        $_SESSION['flash'] = $msg;
        Helper::redirect($url);
    }

    /** 后台视图渲染（含布局） */
    private function render(string $tpl, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $GLOBALS['__adminData'] = $data;
        $GLOBALS['__adminTpl'] = $tpl;
        require dirname(__DIR__) . '/admin/views/layout.php';
    }

    /** 无布局渲染（登录页） */
    private function plainRender(string $tpl, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/admin/views/' . $tpl . '.php';
    }
}
