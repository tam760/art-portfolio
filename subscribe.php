<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Site.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Site::redirect(SITE_URL . '/#contact');
}

$email = trim($_POST['email'] ?? '');
$captcha = strtoupper(trim($_POST['captcha'] ?? ''));

if (empty($captcha) || $captcha !== $_SESSION['captcha_code']) {
    Site::flash('subscribe_msg', '验证码错误');
    Site::redirect(SITE_URL . '/#contact');
}
unset($_SESSION['captcha_code']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Site::flash('subscribe_msg', '邮箱格式不正确');
    Site::redirect(SITE_URL . '/#contact');
}

$db = Site::db();
$stmt = $db->prepare("INSERT IGNORE INTO subscribers (email) VALUES (?)");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->close();

Site::flash('subscribe_msg', '订阅成功，感谢关注！');
Site::redirect(SITE_URL . '/#contact');
