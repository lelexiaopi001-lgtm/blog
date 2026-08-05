<?php
/**
 * Aurora Blog 前台控制器
 */

declare(strict_types=1);

class FrontController
{
    /** 首页：置顶 + 最新文章分页 */
    public function home(int $page = 1): void
    {
        $size = (int) (Helper::setting('page_size', '10') ?: 10);
        $total = DB::one("SELECT COUNT(*) FROM posts WHERE status='published'");
        $pg = Helper::paginate((int) $total, $page, $size);

        $tops = DB::all("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
                (SELECT GROUP_CONCAT(t.name ORDER BY t.id) FROM post_tags pt JOIN tags t ON t.id=pt.tag_id WHERE pt.post_id=p.id) AS tags
            FROM posts p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.status='published' AND p.is_top=1
            ORDER BY p.published_at DESC");

        $posts = DB::all("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
                (SELECT GROUP_CONCAT(t.name ORDER BY t.id) FROM post_tags pt2 JOIN tags t ON t.id=pt2.tag_id WHERE pt2.post_id=p.id) AS tags
            FROM posts p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.status='published'
            ORDER BY p.is_top DESC, p.published_at DESC
            LIMIT {$pg['offset']}, {$size}");

        $this->side();
        View::render('home', compact('posts', 'tops', 'pg'), 'layout');
    }

    /** 文章详情 */
    public function post(string $slug): void
    {
        $post = DB::fetch("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug, u.nickname AS author, u.email AS author_email
            FROM posts p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN users u ON u.id = p.user_id
            WHERE p.slug = ? AND p.status = 'published' LIMIT 1", [$slug]);
        if (!$post) Helper::abort(404, '文章不存在或已下架');

        Helper::bumpView((int) $post['id']);
        $post['tags'] = DB::all("SELECT t.name, t.slug FROM tags t JOIN post_tags pt ON pt.tag_id=t.id WHERE pt.post_id=?", [$post['id']]);
        $post['word_count'] = Helper::wordCount((string) $post['content']);

        // SEO 元数据
        $GLOBALS['__pageImage'] = $post['cover'] ?: Helper::setting('site_avatar');
        $GLOBALS['__pageUrl'] = Helper::siteUrl('/post/' . $post['slug']);
        $GLOBALS['__pageType'] = 'article';
        $GLOBALS['__structured'] = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post['title'],
            'description' => $post['summary'] ?: Markdown::plain((string) $post['content'], 160),
            'url' => $GLOBALS['__pageUrl'],
            'image' => $post['cover'] ? Helper::siteUrl($post['cover']) : '',
            'datePublished' => $post['published_at'],
            'dateModified' => $post['updated_at'],
            'author' => ['@type' => 'Person', 'name' => $post['author'] ?: Helper::setting('site_name')],
            'publisher' => ['@type' => 'Organization', 'name' => Helper::setting('site_name')],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 上一篇 / 下一篇
        $prev = DB::fetch("SELECT id, title, slug FROM posts WHERE status='published' AND published_at < ? ORDER BY published_at DESC LIMIT 1", [$post['published_at']]);
        $next = DB::fetch("SELECT id, title, slug FROM posts WHERE status='published' AND published_at > ? ORDER BY published_at ASC LIMIT 1", [$post['published_at']]);

        // 相关文章（同分类，按标签相似度）
        $related = DB::all("SELECT DISTINCT p.id, p.title, p.slug, p.cover, p.published_at, p.views
            FROM posts p
            JOIN post_tags pt ON pt.post_id = p.id
            JOIN post_tags pt2 ON pt2.tag_id = pt.tag_id AND pt2.post_id = ?
            WHERE p.status='published' AND p.id != ?
            LIMIT 3", [$post['id'], $post['id']]);

        // 评论（含回复树，仅展示已审核）
        $comments = DB::all("SELECT * FROM comments WHERE post_id=? AND status='approved' ORDER BY created_at ASC", [$post['id']]);
        $tree = $this->buildCommentTree($comments);

        $this->side();
        View::render('post', compact('post', 'prev', 'next', 'related', 'tree'), 'layout');
    }

    /** 分类列表 */
    public function category(string $slug, int $page = 1): void
    {
        $cat = DB::fetch('SELECT * FROM categories WHERE slug = ?', [$slug]);
        if (!$cat) Helper::abort(404, '分类不存在');
        $this->listPage(
            "p.category_id = ? AND p.status='published'",
            [$cat['id']],
            "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
                (SELECT GROUP_CONCAT(t.name ORDER BY t.id) FROM post_tags pt2 JOIN tags t ON t.id=pt2.tag_id WHERE pt2.post_id=p.id) AS tags
             FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE %s ORDER BY p.published_at DESC",
            $page,
            'category',
            ['title' => $cat['name'], 'desc' => $cat['description'], 'slug' => $cat['slug']]
        );
    }

    /** 标签列表 */
    public function tag(string $slug, int $page = 1): void
    {
        $tag = DB::fetch('SELECT * FROM tags WHERE slug = ?', [$slug]);
        if (!$tag) Helper::abort(404, '标签不存在');
        $this->listPage(
            "p.status='published' AND EXISTS (SELECT 1 FROM post_tags pt WHERE pt.post_id=p.id AND pt.tag_id=?)",
            [$tag['id']],
            "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
                (SELECT GROUP_CONCAT(t.name ORDER BY t.id) FROM post_tags pt2 JOIN tags t ON t.id=pt2.tag_id WHERE pt2.post_id=p.id) AS tags
             FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE %s ORDER BY p.published_at DESC",
            $page,
            'tag',
            ['title' => '# ' . $tag['name'], 'desc' => '', 'slug' => $tag['slug']]
        );
    }

    /** 归档（按年月） */
    public function archive(int $page = 1): void
    {
        $this->listPage(
            "p.status='published'",
            [],
            "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
                (SELECT GROUP_CONCAT(t.name ORDER BY t.id) FROM post_tags pt2 JOIN tags t ON t.id=pt2.tag_id WHERE pt2.post_id=p.id) AS tags
             FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE %s ORDER BY p.published_at DESC",
            $page,
            'archive',
            ['title' => '文章归档', 'desc' => '全部文章按时间倒序排列']
        );
    }

    /** 搜索 */
    public function search(string $q): void
    {
        $q = trim($q);
        if ($q === '') Helper::redirect('/');
        $like = '%' . $q . '%';
        $this->listPage(
            "p.status='published' AND (p.title LIKE ? OR p.content LIKE ? OR p.summary LIKE ?)",
            [$like, $like, $like],
            "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
                (SELECT GROUP_CONCAT(t.name ORDER BY t.id) FROM post_tags pt2 JOIN tags t ON t.id=pt2.tag_id WHERE pt2.post_id=p.id) AS tags
             FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE %s ORDER BY p.published_at DESC",
            (int) Helper::get('page', 1),
            'search',
            ['title' => '搜索：' . $q, 'desc' => '共找到相关结果', 'q' => $q]
        );
    }

    /** 关于页（含友链） */
    public function about(): void
    {
        $about = Helper::setting('site_about', '');
        $links = DB::all("SELECT * FROM links WHERE status=1 ORDER BY sort ASC, id ASC");
        $this->side();
        View::render('about', compact('about', 'links'), 'layout');
    }

    /** RSS 订阅 */
    public function rss(): void
    {
        header('Content-Type: application/rss+xml; charset=utf-8');
        $posts = DB::all("SELECT p.*, c.name AS cat_name, u.nickname AS author
            FROM posts p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN users u ON u.id = p.user_id
            WHERE p.status='published' ORDER BY p.published_at DESC LIMIT 20");
        $site = Helper::setting('site_name', 'Blog');
        $desc = Helper::setting('site_desc', '');
        $base = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $lang = 'zh-CN';

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">';
        $xml[] = '<channel>';
        $xml[] = '<title>' . Helper::e($site) . '</title>';
        $xml[] = '<link>' . $base . '</link>';
        $xml[] = '<description>' . Helper::e($desc) . '</description>';
        $xml[] = '<language>' . $lang . '</language>';
        $xml[] = '<lastBuildDate>' . date(DATE_RSS) . '</lastBuildDate>';
        $xml[] = '<atom:link href="' . $base . '/rss.xml" rel="self" type="application/rss+xml"/>';
        foreach ($posts as $p) {
            $link = $base . '/post/' . Helper::e($p['slug']);
            $summary = $p['summary'] ?: Markdown::plain((string) $p['content'], 200);
            $content = Markdown::render((string) $p['content']);
            $xml[] = '<item>';
            $xml[] = '<title>' . Helper::e($p['title']) . '</title>';
            $xml[] = '<link>' . $link . '</link>';
            $xml[] = '<guid isPermaLink="true">' . $link . '</guid>';
            $xml[] = '<pubDate>' . date(DATE_RSS, strtotime($p['published_at'])) . '</pubDate>';
            if ($p['author']) {
                $xml[] = '<dc:creator>' . Helper::e($p['author']) . '</dc:creator>';
            }
            if ($p['cat_name']) {
                $xml[] = '<category>' . Helper::e($p['cat_name']) . '</category>';
            }
            $xml[] = '<description><![CDATA[' . $summary . ']]></description>';
            $xml[] = '<content:encoded><![CDATA[' . $content . ']]></content:encoded>';
            $xml[] = '</item>';
        }
        $xml[] = '</channel></rss>';
        echo implode("\n", $xml);
        exit;
    }

    /** 轻量 API：点赞 / 评论提交 */
    public function api(string $action): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::abort(405);
        switch ($action) {
            case 'like':
                Helper::csrfCheck();
                $id = (int) Helper::post('id');
                DB::q('UPDATE posts SET likes = likes + 1 WHERE id = ?', [$id]);
                Helper::json(['ok' => true, 'likes' => (int) DB::one('SELECT likes FROM posts WHERE id=?', [$id])]);
                break;

            case 'comment':
                $this->submitComment();
                break;

            default:
                Helper::json(['ok' => false, 'msg' => '未知接口'], 404);
        }
    }

    /** 评论提交（AJAX） */
    private function submitComment(): void
    {
        Helper::csrfCheck();
        if (Helper::setting('comment_switch', '1') !== '1') {
            Helper::json(['ok' => false, 'msg' => '评论区已关闭']);
        }
        // Honeypot 反垃圾
        if (Helper::post('website_hp') !== '') {
            Helper::json(['ok' => true, 'msg' => '提交成功']); // 假装成功
        }
        $postId = (int) Helper::post('post_id');
        $nickname = trim((string) Helper::post('nickname'));
        $email = trim((string) Helper::post('email'));
        $content = trim((string) Helper::post('content'));

        if ($nickname === '' || mb_strlen($nickname) > 30) Helper::json(['ok' => false, 'msg' => '请填写昵称（30字以内）']);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) Helper::json(['ok' => false, 'msg' => '邮箱格式不正确']);
        if ($content === '' || mb_strlen($content) < 2 || mb_strlen($content) > 2000) Helper::json(['ok' => false, 'msg' => '评论内容需在 2-2000 字之间']);
        // 频率限制：60 秒内同一 IP 仅一条
        $last = (int) DB::one("SELECT COUNT(*) FROM comments WHERE ip=? AND created_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)", [Helper::ip()]);
        if ($last > 0) Helper::json(['ok' => false, 'msg' => '评论太频繁，请稍后再试']);

        $status = Helper::setting('comment_audit', '1') === '1' ? 'pending' : 'approved';
        DB::insert('comments', [
            'post_id' => $postId,
            'parent_id' => (int) Helper::post('parent_id', 0),
            'nickname' => mb_substr($nickname, 0, 30),
            'email' => mb_substr($email, 0, 100),
            'website' => mb_substr(trim((string) Helper::post('website')), 0, 200),
            'content' => mb_substr($content, 0, 2000),
            'status' => $status,
            'ip' => Helper::ip(),
            'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
        DB::q('UPDATE posts SET comment_count = comment_count + 1 WHERE id = ?', [$postId]);

        // 评论邮件通知管理员
        $this->notifyComment($postId, $nickname, $content, $status);

        Helper::json(['ok' => true, 'audit' => $status === 'pending', 'msg' => $status === 'pending' ? '评论已提交，审核通过后展示' : '评论成功']);
    }

    /** 发送评论通知邮件 */
    private function notifyComment(int $postId, string $nickname, string $content, string $status): void
    {
        $adminEmail = Helper::setting('smtp_notify_email');
        if (!$adminEmail) {
            $adminEmail = Helper::setting('site_social_email');
        }
        if (!$adminEmail) return;

        $post = DB::fetch('SELECT title, slug FROM posts WHERE id = ?', [$postId]);
        if (!$post) return;

        $site = Helper::setting('site_name', 'Aurora Blog');
        $subject = '【' . $site . '】收到新评论' . ($status === 'pending' ? '（待审核）' : '');
        $url = Helper::siteUrl('/post/' . $post['slug']);
        $body = '<p><b>' . Helper::e($nickname) . '</b> 在《<a href="' . $url . '">' . Helper::e($post['title']) . '</a>》发表了评论：</p>'
              . '<blockquote style="border-left:3px solid #ccc;padding-left:12px;color:#555;">' . nl2br(Helper::e($content)) . '</blockquote>'
              . '<p><a href="' . Helper::siteUrl('/admin/index.php?r=comments') . '">去后台审核</a></p>';
        Helper::mail($adminEmail, $subject, $body);
    }

    /** 通用列表页 */
    private function listPage(string $where, array $params, string $sqlTpl, int $page, string $type, array $meta): void
    {
        $size = (int) (Helper::setting('page_size', '10') ?: 10);
        $countSql = 'SELECT COUNT(*) FROM posts p WHERE ' . $where;
        $total = (int) DB::one($countSql, $params);
        $pg = Helper::paginate($total, $page, $size);

        $sql = sprintf($sqlTpl, $where) . " LIMIT {$pg['offset']}, {$size}";
        $posts = DB::all($sql, $params);

        $base = '/';
        if ($type === 'category') $base = '/category/' . $meta['slug'] . '/page/';
        if ($type === 'tag') $base = '/tag/' . $meta['slug'] . '/page/';
        if ($type === 'archive') $base = '/archive/page/';
        if ($type === 'search') $base = '/search?q=' . urlencode($meta['q'] ?? '') . '&page=';

        $this->side();
        View::render('list', compact('posts', 'pg', 'type', 'meta', 'base'), 'layout');
    }

    /** 侧边栏数据 */
    private function side(): void
    {
        $GLOBALS['side'] = [
            'categories' => DB::all('SELECT *, MAX(id) AS id FROM categories GROUP BY slug ORDER BY sort ASC, id ASC'),
            'tags' => DB::all('SELECT * FROM tags ORDER BY post_count DESC, id ASC LIMIT 20'),
            'hot' => DB::all("SELECT id, title, slug, views FROM posts WHERE status='published' ORDER BY views DESC, published_at DESC LIMIT 6"),
            'recent' => DB::all("SELECT id, title, slug, published_at FROM posts WHERE status='published' ORDER BY published_at DESC LIMIT 6"),
            'links' => DB::all('SELECT * FROM links WHERE status=1 ORDER BY sort ASC LIMIT 12'),
            'archive' => DB::all("SELECT DATE_FORMAT(published_at,'%Y年%m月') AS ym, COUNT(*) AS cnt FROM posts WHERE status='published' GROUP BY ym ORDER BY ym DESC LIMIT 12"),
        ];
    }

    /** 评论树构建 */
    private function buildCommentTree(array $comments): array
    {
        $map = [];
        foreach ($comments as $c) $map[$c['id']] = $c + ['children' => []];
        $tree = [];
        foreach ($map as $id => &$c) {
            if ($c['parent_id'] && isset($map[$c['parent_id']])) {
                $map[$c['parent_id']]['children'][] = &$c;
            } else {
                $tree[] = &$c;
            }
        }
        unset($c);
        return $tree;
    }

    public function notFound(): void
    {
        Helper::abort(404, '你访问的页面不存在');
    }
}
