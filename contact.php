<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Site.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['ok'=>false, 'msg'=>'请求方式错误']);
        exit;
    }
    Site::redirect(SITE_URL . '/#contact');
}

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$captcha = strtoupper(trim($_POST['captcha'] ?? ''));

$errors = [];
if (empty($captcha) || $captcha !== $_SESSION['captcha_code']) {
    $errors[] = '验证码错误';
} else {
    unset($_SESSION['captcha_code']);
}
if ($name === '') $errors[] = '姓名不能为空';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = '请输入有效的邮箱';
if (mb_strlen($message) < 5) $errors[] = '留言内容至少 5 个字符';

if ($errors) {
    $msg = implode('；', $errors);
    if ($isAjax) { echo json_encode(['ok'=>false, 'msg'=>$msg]); exit; }
    Site::flash('contact_error', $msg);
    Site::redirect(SITE_URL . '/#contact');
}

$db = Site::db();
$stmt = $db->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $name, $email, $subject, $message);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    if ($isAjax) { echo json_encode(['ok'=>true, 'msg'=>'感谢您的留言！我会尽快回复您。']); exit; }
    Site::flash('contact_success', '留言提交成功！');
    Site::redirect(SITE_URL . '/#contact');
} else {
    if ($isAjax) { echo json_encode(['ok'=>false, 'msg'=>'提交失败，请稍后重试']); exit; }
    Site::flash('contact_error', '提交失败，请稍后重试');
    Site::redirect(SITE_URL . '/#contact');
}
