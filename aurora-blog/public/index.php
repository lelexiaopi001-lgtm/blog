<?php
/**
 * Aurora Blog 前台入口
 * 宝塔 nginx 伪静态: try_files $uri $uri/ /index.php?$query_string;
 */
declare(strict_types=1);

require dirname(__DIR__) . '/app/core/Core.php';

try {
    App::run();
} catch (Throwable $e) {
    $debug = $GLOBALS['__config']['app']['debug'] ?? false;
    if ($debug) {
        Helper::abort(500, $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    }
    Helper::abort(500, '服务器开小差了，请稍后再试');
}
