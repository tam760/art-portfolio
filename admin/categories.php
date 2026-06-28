<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';
Site::requireAdmin();

$db = Site::db();
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$current = 'categories';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    Site::flash('admin_msg', '删除成功');
    Site::redirect(SITE_URL . '/admin/categories.php');
}

if ($action === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $slug = trim($_POST['slug'] ?? '');
    $name = trim($_POST['name'] ?? '');
    if ($slug === '' || $name === '') {
        $error = 'slug 和名称必填';
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE categories SET slug=?, name=? WHERE id=?");
            $stmt->bind_param('ssi', $slug, $name, $id);
        } else {
            $stmt = $db->prepare("INSERT INTO categories (slug, name) VALUES (?,?)");
            $stmt->bind_param('ss', $slug, $name);
        }
        $stmt->execute();
        $stmt->close();
        Site::flash('admin_msg', '保存成功');
        Site::redirect(SITE_URL . '/admin/categories.php');
    }
}

if ($action === 'edit' || $action === 'add') {
    $editId = (int)($_GET['id'] ?? 0);
    $cat = null;
    if ($editId > 0) {
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $cat = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($error)) {
        $cat = array_merge($cat ?? [], $_POST);
    }
    include __DIR__ . '/header.php';
    ?>
    <h1 class="text-2xl font-display text-ink-800 mb-6"><?= $cat ? '编辑分类' : '新增分类' ?></h1>
    <?php if (!empty($error)): ?><div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm"><?= Site::h($error) ?></div><?php endif; ?>
    <form method="post" class="bg-warm-50 p-6 rounded border border-warm-200 max-w-xl">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)($cat['id'] ?? 0) ?>">
        <div class="form-row"><label>slug（英文标识，用于筛选）</label><input type="text" name="slug" required value="<?= Site::h($cat['slug'] ?? '') ?>" placeholder="如 oil"></div>
        <div class="form-row"><label>分类名称</label><input type="text" name="name" required value="<?= Site::h($cat['name'] ?? '') ?>" placeholder="如 油画"></div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="categories.php" class="btn btn-ghost">取消</a>
        </div>
    </form>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}

$list = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
include __DIR__ . '/header.php';
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-display text-ink-800">分类管理</h1>
    <a href="categories.php?action=add" class="btn btn-primary">+ 新增分类</a>
</div>
<div class="bg-warm-50 rounded border border-warm-200 overflow-x-auto">
    <table class="admin w-full">
        <thead><tr><th>ID</th><th>slug</th><th>名称</th><th>作品数</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($list as $row):
            $cnt = (int)$db->query("SELECT COUNT(*) FROM artworks WHERE category_id = " . (int)$row['id'])->fetch_row()[0];
        ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><code><?= Site::h($row['slug']) ?></code></td>
                <td><?= Site::h($row['name']) ?></td>
                <td><?= $cnt ?></td>
                <td>
                    <a href="categories.php?action=edit&id=<?= (int)$row['id'] ?>" class="btn btn-secondary">编辑</a>
                    <?php if ($cnt === 0): ?>
                    <a href="categories.php?action=delete&id=<?= (int)$row['id'] ?>" onclick="return confirm('确定删除？')" class="btn btn-danger">删除</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/footer.php'; ?>
