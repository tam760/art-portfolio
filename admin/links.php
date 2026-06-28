<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';
Site::requireAdmin();

$db = Site::db();
$current = 'links';

// 添加/编辑友链
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $sort = (int)($_POST['sort'] ?? 0);
    
    if ($name === '' || $url === '') {
        $error = '名称和链接不能为空';
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE links SET name=?, url=?, sort=? WHERE id=?");
            $stmt->bind_param('ssii', $name, $url, $sort, $id);
        } else {
            $stmt = $db->prepare("INSERT INTO links (name, url, sort) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $name, $url, $sort);
        }
        $stmt->execute();
        $stmt->close();
        Site::flash('admin_msg', '保存成功');
        Site::redirect(SITE_URL . '/admin/links.php');
    }
}

// 删除友链
if (($action = $_GET['action'] ?? '') === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM links WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    Site::flash('admin_msg', '删除成功');
    Site::redirect(SITE_URL . '/admin/links.php');
}

// 编辑友链
$editLink = null;
if (($_GET['action'] ?? '') === 'edit') {
    $editId = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM links WHERE id = ?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editLink = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$links = $db->query("SELECT * FROM links ORDER BY sort ASC, id DESC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/header.php';
?>

<h1 class="text-2xl font-display text-ink-800 mb-6">友情链接管理</h1>

<?php if (!empty($error)): ?>
<div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm"><?= Site::h($error) ?></div>
<?php endif; ?>

<div class="bg-warm-50 p-6 rounded border border-warm-200 max-w-lg mb-10">
    <h2 class="text-lg font-display text-ink-800 mb-4"><?= $editLink ? '编辑链接' : '添加链接' ?></h2>
    <form method="post" class="space-y-4">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)($editLink['id'] ?? 0) ?>">
        <div class="form-row">
            <label>链接名称</label>
            <input type="text" name="name" required value="<?= Site::h($editLink['name'] ?? '') ?>" placeholder="例如：中央美术学院">
        </div>
        <div class="form-row">
            <label>链接地址</label>
            <input type="url" name="url" required value="<?= Site::h($editLink['url'] ?? '') ?>" placeholder="https://example.com">
        </div>
        <div class="form-row">
            <label>排序</label>
            <input type="number" name="sort" value="<?= (int)($editLink['sort'] ?? 0) ?>" placeholder="数字越小越靠前">
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">保存</button>
            <?php if ($editLink): ?>
            <a href="links.php" class="btn btn-ghost">取消</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-warm-50 rounded border border-warm-200 overflow-x-auto">
    <table class="admin w-full">
        <thead>
            <tr><th>ID</th><th>名称</th><th>链接</th><th>排序</th><th>操作</th></tr>
        </thead>
        <tbody>
        <?php foreach ($links as $link): ?>
            <tr>
                <td><?= (int)$link['id'] ?></td>
                <td><?= Site::h($link['name']) ?></td>
                <td><a href="<?= Site::h($link['url']) ?>" target="_blank" class="text-blue-600 hover:underline"><?= Site::h($link['url']) ?></a></td>
                <td><?= (int)$link['sort'] ?></td>
                <td>
                    <a href="links.php?action=edit&id=<?= (int)$link['id'] ?>" class="btn btn-secondary">编辑</a>
                    <a href="links.php?action=delete&id=<?= (int)$link['id'] ?>" onclick="return confirm('确定删除？')" class="btn btn-danger">删除</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($links)): ?>
            <tr><td colspan="5" class="text-center text-ink-500 py-8">暂无链接</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/footer.php'; ?>
