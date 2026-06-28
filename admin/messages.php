<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';
Site::requireAdmin();

$db = Site::db();
$action = $_GET['action'] ?? '';
$current = 'messages';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    Site::flash('admin_msg', '删除成功');
    Site::redirect(SITE_URL . '/admin/messages.php');
}

if ($action === 'read') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("UPDATE messages SET id = id WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

$list = $db->query("SELECT * FROM messages ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
include __DIR__ . '/header.php';
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-display text-ink-800">客户留言</h1>
    <span class="text-sm text-warm-500">共 <?= count($list) ?> 条</span>
</div>
<div class="bg-warm-50 rounded border border-warm-200 overflow-x-auto">
    <table class="admin w-full">
        <thead><tr><th>ID</th><th>姓名</th><th>邮箱</th><th>主题</th><th>留言</th><th>时间</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($list as $row): ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><?= Site::h($row['name']) ?></td>
                <td><a href="mailto:<?= Site::h($row['email']) ?>" class="text-rust-500 hover:underline"><?= Site::h($row['email']) ?></a></td>
                <td><?= Site::h($row['subject']) ?></td>
                <td style="max-width:300px;"><?= nl2br(Site::h($row['message'])) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><a href="messages.php?action=delete&id=<?= (int)$row['id'] ?>" onclick="return confirm('确定删除？')" class="btn btn-danger">删除</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/footer.php'; ?>
