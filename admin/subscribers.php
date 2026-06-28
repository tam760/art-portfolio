<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';
Site::requireAdmin();

$db = Site::db();
$action = $_GET['action'] ?? '';
$current = 'subscribers';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM subscribers WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    Site::flash('admin_msg', '删除成功');
    Site::redirect(SITE_URL . '/admin/subscribers.php');
}

$list = $db->query("SELECT * FROM subscribers ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
include __DIR__ . '/header.php';
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-display text-ink-800">订阅用户</h1>
    <span class="text-sm text-warm-500">共 <?= count($list) ?> 位订阅者</span>
</div>
<div class="bg-warm-50 rounded border border-warm-200 overflow-x-auto">
    <table class="admin w-full">
        <thead><tr><th>ID</th><th>邮箱</th><th>订阅时间</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($list as $row): ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><?= Site::h($row['email']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><a href="subscribers.php?action=delete&id=<?= (int)$row['id'] ?>" onclick="return confirm('确定删除？')" class="btn btn-danger">删除</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/footer.php'; ?>
