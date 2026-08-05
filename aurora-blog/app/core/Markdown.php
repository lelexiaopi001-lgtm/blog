<?php
/**
 * Aurora Markdown —— 轻量安全的 Markdown 解析器（自研，零依赖）
 * 支持: 标题/粗体/斜体/删除线/行内代码/代码块/链接/图片/无序列表/有序列表/
 *       任务列表/引用/表格/分割线/自动链接
 * 安全: 所有文本先转义，仅输出白名单 HTML
 */

declare(strict_types=1);

class Markdown
{
    public static function render(string $md): string
    {
        $md = str_replace(["\r\n", "\r"], "\n", $md);
        $blocks = [];
        $footnotes = [];

        // ① 提取代码块
        $md = preg_replace_callback('/```([\w+-]*)\n(.*?)```/s', function ($m) use (&$blocks) {
            $lang = Helper::e($m[1] ?: 'text');
            $label = $m[1] ? '<div class="code-lang">' . $lang . '</div>' : '';
            $blocks[] = '<pre class="code-block">' . $label . '<code class="lang-' . $lang . '">'
                . Helper::e($m[2]) . '</code></pre>';
            return "\x00CODE" . (count($blocks) - 1) . "\x00";
        }, $md);

        // ①.⑤ 提取脚注定义 [^id]: note
        $md = preg_replace_callback('/^\[\^([^\]]+)\]:\s*(.+)$/m', function ($m) use (&$footnotes) {
            $footnotes[$m[1]] = self::inline($m[2]);
            return '';
        }, $md);

        // ② 提取表格块（表头行 + 分隔行 + 数据行）
        $md = preg_replace_callback('/^\|(.+)\|\n\|[\s:|-]+\|\n((?:\|.+\|\n?)*)/m', function ($m) use (&$blocks) {
            $h = '<table><thead><tr>';
            foreach (array_map('trim', explode('|', trim($m[1], '|'))) as $c) {
                $h .= '<th>' . self::inline($c) . '</th>';
            }
            $h .= '</tr></thead><tbody>';
            foreach (array_filter(explode("\n", trim($m[2]))) as $row) {
                if (!trim($row)) continue;
                $h .= '<tr>';
                foreach (array_map('trim', explode('|', trim($row, '|'))) as $c) {
                    $h .= '<td>' . self::inline($c) . '</td>';
                }
                $h .= '</tr>';
            }
            $h .= '</tbody></table>';
            $blocks[] = $h;
            return "\x00TABLE" . (count($blocks) - 1) . "\x00";
        }, $md);

        // ③ 逐行解析
        $lines = explode("\n", $md);
        $html = '';
        $inList = null;   // 'ul' | 'ol' | null
        $inQuote = false;

        $closeList = function () use (&$inList, &$html) {
            if ($inList) { $html .= "</{$inList}>"; $inList = null; }
        };
        $closeQuote = function () use (&$inQuote, &$html) {
            if ($inQuote) { $html .= '</blockquote>'; $inQuote = false; }
        };
        $openQuote = function () use (&$inQuote, &$html) {
            if (!$inQuote) { $html .= '<blockquote>'; $inQuote = true; }
        };

        foreach ($lines as $line) {
            // 代码块 / 表格 占位符
            if (str_starts_with($line, "\x00CODE")) {
                $closeList(); $closeQuote();
                $html .= $blocks[(int) substr($line, 5, -1)];
                continue;
            }
            if (str_starts_with($line, "\x00TABLE")) {
                $closeList(); $closeQuote();
                $html .= $blocks[(int) substr($line, 6, -1)];
                continue;
            }

            $trim = trim($line);
            if ($trim === '') { $closeList(); $closeQuote(); continue; }

            // 分割线
            if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trim)) {
                $closeList(); $closeQuote();
                $html .= '<hr>';
                continue;
            }

            // 标题
            if (preg_match('/^(#{1,6})\s+(.+)$/', $trim, $m)) {
                $closeList(); $closeQuote();
                $level = strlen($m[1]);
                $html .= "<h{$level} id=\"" . self::anchor($m[2]) . "\">" . self::inline($m[2]) . "</h{$level}>";
                continue;
            }

            // 引用
            if (str_starts_with($trim, '>')) {
                $closeList();
                $openQuote();
                $html .= self::inline(preg_replace('/^>\s?/', '', $trim)) . "<br>\n";
                continue;
            }

            // 任务列表
            if (preg_match('/^[-*]\s+\[([ xX])\]\s+(.+)$/', $trim, $m)) {
                $closeQuote();
                if ($inList !== 'ul') { $closeList(); $html .= '<ul class="task-list">'; $inList = 'ul'; }
                $checked = strtolower($m[1]) === 'x' ? ' checked' : '';
                $html .= "<li class=\"task-item\"><input type=\"checkbox\" disabled{$checked}> " . self::inline($m[2]) . '</li>';
                continue;
            }

            // 无序列表
            if (preg_match('/^[-*+]\s+(.+)$/', $trim, $m)) {
                $closeQuote();
                if ($inList !== 'ul') { $closeList(); $html .= '<ul>'; $inList = 'ul'; }
                $html .= '<li>' . self::inline($m[1]) . '</li>';
                continue;
            }

            // 有序列表
            if (preg_match('/^\d+[.)]\s+(.+)$/', $trim, $m)) {
                $closeQuote();
                if ($inList !== 'ol') { $closeList(); $html .= '<ol>'; $inList = 'ol'; }
                $html .= '<li>' . self::inline($m[1]) . '</li>';
                continue;
            }

            // 普通段落
            $closeList(); $closeQuote();
            $html .= '<p>' . self::inline($trim) . "</p>\n";
        }

        $closeList(); $closeQuote();

        // 脚注列表
        if (!empty($footnotes)) {
            $html .= '<div class="footnotes"><hr><ol>';
            foreach ($footnotes as $id => $note) {
                $html .= '<li id="fn-' . Helper::e($id) . '">' . $note . ' <a href="#fnref-' . Helper::e($id) . '" class="footnote-backref">↩</a></li>';
            }
            $html .= '</ol></div>';
        }

        return $html;
    }

    /** 标题锚点 */
    public static function anchor(string $title): string
    {
        $t = preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($title, 'UTF-8'));
        return trim($t, '-') ?: 'sec';
    }

    /** 行内元素解析（先转义，后套白名单标签） */
    public static function inline(string $text): string
    {
        $s = Helper::e($text);

        // 图片 ![alt](url "title")
        $s = preg_replace_callback('/!\[([^\]]*)\]\(([^)\s]+)(?:\s+["\']([^"\']*)["\'])?\)/', function ($m) {
            $title = isset($m[3]) && $m[3] !== '' ? ' title="' . $m[3] . '"' : '';
            return '<img src="' . $m[2] . '" alt="' . $m[1] . '" loading="lazy"' . $title . '>';
        }, $s);

        // 链接 [text](url "title")
        $s = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)(?:\s+["\']([^"\']*)["\'])?\)/', function ($m) {
            $title = isset($m[3]) && $m[3] !== '' ? ' title="' . $m[3] . '"' : '';
            return '<a href="' . $m[2] . '"' . $title . ' rel="noopener" target="_blank">' . $m[1] . '</a>';
        }, $s);

        // 加粗 **text**
        $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s);
        // 斜体 *text*
        $s = preg_replace('/(?<!\*)\*([^*\s][^*]*)\*(?!\*)/', '<em>$1</em>', $s);
        // 删除线 ~~text~~
        $s = preg_replace('/~~([^~]+)~~/', '<del>$1</del>', $s);
        // 行内代码
        $s = preg_replace('/`([^`]+)`/', '<code class="inline-code">$1</code>', $s);

        // 自动链接裸 URL
        $s = preg_replace('/(?<![\w])https?:\/\/[^\s<]+/i', '<a href="$0" rel="noopener" target="_blank">$0</a>', $s);

        // 脚注引用 [^id]
        $s = preg_replace_callback('/\[\^([^\]]+)\]/', function ($m) {
            $id = Helper::e($m[1]);
            return '<sup class="footnote-ref"><a id="fnref-' . $id . '" href="#fn-' . $id . '">' . $id . '</a></sup>';
        }, $s);

        return $s;
    }

    /** 摘要生成（去 Markdown 符号） */
    public static function plain(string $md, int $len = 120): string
    {
        $s = preg_replace('/```.*?```/s', ' ', $md);
        $s = preg_replace('/[#>*_`~\[\]()!\-+|]/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        $s = trim($s);
        return mb_strlen($s, 'UTF-8') > $len ? mb_substr($s, 0, $len, 'UTF-8') . '…' : $s;
    }
}
