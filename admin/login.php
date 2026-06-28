<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $captcha = strtoupper(trim($_POST['captcha'] ?? ''));
    if (empty($captcha) || $captcha !== $_SESSION['captcha_code']) {
        $error = '验证码错误';
        unset($_SESSION['captcha_code']);
    } else {
        unset($_SESSION['captcha_code']);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $db = Site::db();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['admin_id'] = (int)$row['id'];
            $_SESSION['admin_name'] = $row['username'];
            Site::redirect(SITE_URL . '/admin/');
        } else {
            $error = '用户名或密码错误';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>登录 · 琪琪后台</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        night: { 50:'#0f1923', 100:'#1a2a3a', 200:'#1e3040', 300:'#2a4055', 400:'#3a5570', 500:'#4a6a85', 600:'#6a8aa0', 700:'#8aaac0', 800:'#c0d0dd', 900:'#d8e8f0' },
                        amber: { 50:'#fdf9f3', 100:'#f5ede0', 200:'#e8d9c4', 300:'#d4bfa0', 400:'#c4a882', 500:'#a68b6b', 600:'#8b7355', 700:'#6b5344', 800:'#4a3b2f', 900:'#2e241c' },
                        gold: { 300:'#d4b080', 400:'#c4956a', 500:'#b08050', 600:'#9a6b40' },
                        rust: { 400:'#c4705a', 500:'#b05840', 600:'#9a4838' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family:'Noto Serif SC', serif; background:linear-gradient(135deg,#0f1923,#1a2a3a); min-height:100vh; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <form method="post" class="bg-night-100 p-8 border border-night-300 rounded-lg w-[380px] shadow-xl">
        <h1 class="font-display text-2xl text-amber-50 mb-6 text-center">管理员登录</h1>
        <?php if (isset($error)): ?>
        <div class="mb-4 p-3 bg-night-300 text-rust-400 text-sm rounded"><?= Site::h($error) ?></div>
        <?php endif; ?>
        <div class="mb-4">
            <label class="block text-sm text-amber-400 mb-2">用户名</label>
            <input type="text" name="username" required class="w-full px-4 py-3 border border-night-400 rounded bg-night-200 text-amber-100 focus:outline-none focus:border-gold-400">
        </div>
        <div class="mb-4">
            <label class="block text-sm text-amber-400 mb-2">密码</label>
            <input type="password" name="password" required class="w-full px-4 py-3 border border-night-400 rounded bg-night-200 text-amber-100 focus:outline-none focus:border-gold-400">
        </div>
        <div class="mb-6">
            <label class="block text-sm text-amber-400 mb-2">验证码</label>
            <div class="flex gap-3 items-stretch">
                <input type="text" name="captcha" required maxlength="4" class="flex-1 min-w-0 px-4 py-3 border border-night-400 rounded bg-night-200 text-amber-100 focus:outline-none focus:border-gold-400 uppercase">
                <img src="captcha.php?t=<?= time() ?>" alt="验证码" class="w-28 h-auto rounded cursor-pointer border border-night-400 hover:border-gold-400 transition object-contain" onclick="this.src='captcha.php?t='+Date.now()">
            </div>
        </div>
        <button type="submit" class="w-full py-3 bg-gold-400 text-night-50 rounded hover:bg-gold-300 transition">登录</button>
  
        <p class="mt-4 text-center"><a href="<?= SITE_URL ?>/" class="text-sm text-amber-500 hover:text-gold-400">← 返回前台</a></p>
    </form>
</body>
</html>
