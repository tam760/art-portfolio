<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';
Site::requireAdmin();

$db = Site::db();
$current = 'settings';
$keys = ['site_title','site_keywords','site_description','artist_name','artist_en','artist_email','artist_studio','artist_hours','bio','philosophy','artist_photo','artist_studio_photo','icp_code'];

$configs = [];
$res = $db->query("SELECT * FROM site_configs");
while ($row = $res->fetch_assoc()) {
    $configs[$row['k']] = $row['v'];
}
$res->free();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $data = [];
    foreach ($keys as $k) {
        $data[$k] = $_POST[$k] ?? '';
    }
    
    $photoFields = ['artist_photo', 'artist_studio_photo'];
    foreach ($photoFields as $field) {
        if (!empty($_FILES[$field]['tmp_name'])) {
            $uploaded = Site::uploadImage($_FILES[$field], 'artist', 600, 800);
            if ($uploaded) {
                $data[$field] = $uploaded;
            }
        } else {
            $data[$field] = $configs[$field] ?? '';
        }
    }
    
    Site::updateConfig($data);
    Site::flash('admin_msg', '设置已保存');
    Site::redirect(SITE_URL . '/admin/settings.php');
}

// 处理修改密码
if (($_POST['action'] ?? '') === 'password') {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $admin = $db->query("SELECT * FROM admins WHERE id = " . (int)$_SESSION['admin_id'])->fetch_assoc();
    if (!$admin || !password_verify($old, $admin['password'])) {
        $pwd_error = '原密码错误';
    } elseif ($new !== $confirm || strlen($new) < 6) {
        $pwd_error = '两次新密码不一致或过短（至少6位）';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $hash, (int)$_SESSION['admin_id']);
        $stmt->execute();
        $stmt->close();
        $pwd_ok = true;
    }
}

include __DIR__ . '/header.php';
?>

<h1 class="text-2xl font-display text-ink-800 mb-6">站点设置</h1>

<form method="post" enctype="multipart/form-data" class="bg-warm-50 p-6 rounded border border-warm-200 max-w-3xl mb-10">
    <input type="hidden" name="action" value="save">
    <div class="grid md:grid-cols-2 gap-4">
        <div class="form-row"><label>站点标题</label><input type="text" name="site_title" value="<?= Site::h($configs['site_title'] ?? '') ?>"></div>
        <div class="form-row"><label>艺术家中文名</label><input type="text" name="artist_name" value="<?= Site::h($configs['artist_name'] ?? '') ?>"></div>
        <div class="form-row"><label>艺术家英文名</label><input type="text" name="artist_en" value="<?= Site::h($configs['artist_en'] ?? '') ?>"></div>
        <div class="form-row"><label>联系邮箱</label><input type="email" name="artist_email" value="<?= Site::h($configs['artist_email'] ?? '') ?>"></div>
        <div class="form-row md:col-span-2"><label>关键词（SEO）</label><input type="text" name="site_keywords" value="<?= Site::h($configs['site_keywords'] ?? '') ?>"></div>
        <div class="form-row md:col-span-2"><label>站点描述</label><textarea name="site_description"><?= Site::h($configs['site_description'] ?? '') ?></textarea></div>
        <div class="form-row"><label>ICP备案号</label><input type="text" name="icp_code" value="<?= Site::h($configs['icp_code'] ?? '') ?>" placeholder="如：京ICP备12345678号"></div>
        <div class="form-row md:col-span-2"><label>艺术家肖像照</label>
            <?php if (!empty($configs['artist_photo'])): ?>
            <img src="<?= SITE_URL . '/' . Site::h($configs['artist_photo']) ?>" class="max-w-xs mb-2 rounded">
            <?php endif; ?>
            <input type="file" name="artist_photo" accept="image/*"></div>
        <div class="form-row md:col-span-2"><label>艺术家工作室照片</label>
            <?php if (!empty($configs['artist_studio_photo'])): ?>
            <img src="<?= SITE_URL . '/' . Site::h($configs['artist_studio_photo']) ?>" class="max-w-xs mb-2 rounded">
            <?php endif; ?>
            <input type="file" name="artist_studio_photo" accept="image/*"></div>
        <div class="form-row md:col-span-2"><label>工作室地址</label><textarea name="artist_studio"><?= Site::h($configs['artist_studio'] ?? '') ?></textarea></div>
        <div class="form-row md:col-span-2"><label>工作时间</label><textarea name="artist_hours"><?= Site::h($configs['artist_hours'] ?? '') ?></textarea></div>
        <div class="form-row md:col-span-2"><label>个人简介</label><textarea name="bio"><?= Site::h($configs['bio'] ?? '') ?></textarea></div>
        <div class="form-row md:col-span-2"><label>创作理念</label><textarea name="philosophy"><?= Site::h($configs['philosophy'] ?? '') ?></textarea></div>
    </div>
    <button type="submit" class="btn btn-primary">保存设置</button>
</form>

<h2 class="text-xl font-display text-ink-800 mb-4">修改管理员密码</h2>
<form method="post" class="bg-warm-50 p-6 rounded border border-warm-200 max-w-md">
    <input type="hidden" name="action" value="password">
    <?php if (!empty($pwd_error)): ?><div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm"><?= Site::h($pwd_error) ?></div><?php endif; ?>
    <?php if (!empty($pwd_ok)): ?><div class="mb-4 p-3 bg-emerald-100 text-emerald-800 rounded text-sm">密码已更新</div><?php endif; ?>
    <div class="form-row"><label>原密码</label><input type="password" name="old_password" required></div>
    <div class="form-row"><label>新密码</label><input type="password" name="new_password" required></div>
    <div class="form-row"><label>确认新密码</label><input type="password" name="confirm_password" required></div>
    <button type="submit" class="btn btn-primary">修改密码</button>
</form>

<?php include __DIR__ . '/footer.php'; ?>
