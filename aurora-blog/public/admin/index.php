<?php
/**
 * Aurora Blog 后台入口
 * 访问: /admin/index.php?r=dashboard
 */
declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/core/Core.php';
require dirname(__DIR__, 2) . '/app/admin/AdminController.php';

try {
    $ctrl = new AdminController();
    $ctrl->dispatch((string) ($_GET['r'] ?? 'dashboard'));
} catch (Throwable $e) {
    $debug = $GLOBALS['__config']['app']['debug'] ?? false;
    if ($debug) {
        Helper::abort(500, $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    }
    Helper::abort(500, '服务器开小差了，请稍后再试');
}
