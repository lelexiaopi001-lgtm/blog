<?php
/**
 * Aurora Blog 极光博客 - 配置文件示例
 * 复制本文件为 config.php 并填写数据库信息
 * 或直接运行 /tools/install.php 安装向导自动生成
 */

return [
    // 数据库
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'aurora_blog',
        'user' => 'root',
        'pass' => 'your_password',
    ],

    // 安全
    'app' => [
        // 用于 Cookie / CSRF 的随机密钥（安装向导会自动生成）
        'key' => 'CHANGE_ME_TO_A_RANDOM_STRING',
        // 调试模式：生产环境务必设为 false
        'debug' => false,
    ],
];
