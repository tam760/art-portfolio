<?php
// 站点配置
define('SITE_NAME', '林夕 — 艺术家个人网站');
define('SITE_URL', '');
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('UPLOAD_URL', 'uploads');

// 数据库配置（可根据实际环境修改）
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'art_portfolio');
define('DB_CHARSET', 'utf8mb4');

// 时区
date_default_timezone_set('Asia/Shanghai');

// 启动 session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 自动加载
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/includes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
