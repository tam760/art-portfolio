<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';
Site::requireAdmin();

$db = Site::db();
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$current = 'artworks';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM artworks WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    Site::flash('admin_msg', '删除成功');
    Site::redirect(SITE_URL . '/admin/artworks.php');
}

if ($action === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $year = (int)($_POST['year'] ?? 0);
    $medium = trim($_POST['medium'] ?? '');
    $size_desc = trim($_POST['size_desc'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $sort = (int)($_POST['sort'] ?? 0);
    $is_featured = (int)($_POST['is_featured'] ?? 1);
    $cover = trim($_POST['cover'] ?? '');

    // 如果上传文件
    if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
        $uploaded = Site::uploadImage($_FILES['cover_file'], 'artworks', 800, 1000);
        if ($uploaded) $cover = $uploaded;
    }

    if ($title === '' || $category_id <= 0) {
        $error = '标题和分类为必填项';
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE artworks SET title=?, category_id=?, year=?, medium=?, size_desc=?, cover=?, description=?, content=?, sort=?, is_featured=? WHERE id=?");
            $stmt->bind_param('siisssssiii', $title, $category_id, $year, $medium, $size_desc, $cover, $description, $content, $sort, $is_featured, $id);
        } else {
            $stmt = $db->prepare("INSERT INTO artworks (title, category_id, year, medium, size_desc, cover, description, content, sort, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('siisssssii', $title, $category_id, $year, $medium, $size_desc, $cover, $description, $content, $sort, $is_featured);
        }
        $stmt->execute();
        $stmt->close();
        Site::flash('admin_msg', '保存成功');
        Site::redirect(SITE_URL . '/admin/artworks.php');
    }
}

if ($action === 'edit' || $action === 'add') {
    $editId = (int)($_GET['id'] ?? 0);
    $work = null;
    $categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
    if ($editId > 0) {
        $stmt = $db->prepare("SELECT * FROM artworks WHERE id = ?");
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $work = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$work) { Site::redirect(SITE_URL . '/admin/artworks.php'); }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($error)) {
        $work = array_merge($work ?? [], $_POST);
    }
    include __DIR__ . '/header.php';
    ?>
    <h1 class="text-2xl font-display text-ink-800 mb-6"><?= $work ? '编辑作品' : '新增作品' ?></h1>
    <?php if (!empty($error)): ?><div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm"><?= Site::h($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="bg-warm-50 p-6 rounded border border-warm-200 max-w-3xl">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)($work['id'] ?? 0) ?>">
        <div class="grid md:grid-cols-2 gap-4">
            <div class="form-row md:col-span-2"><label>作品标题 *</label><input type="text" name="title" required value="<?= Site::h($work['title'] ?? '') ?>"></div>
            <div class="form-row"><label>分类 *</label>
                <select name="category_id" required>
                    <option value="">请选择</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= ((int)($work['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>><?= Site::h($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row"><label>创作年份</label><input type="number" name="year" value="<?= Site::h($work['year'] ?? date('Y')) ?>"></div>
            <div class="form-row"><label>媒介</label><input type="text" name="medium" value="<?= Site::h($work['medium'] ?? '') ?>" placeholder="如 布面油画"></div>
            <div class="form-row"><label>尺寸</label><input type="text" name="size_desc" value="<?= Site::h($work['size_desc'] ?? '') ?>" placeholder="如 80×100cm"></div>
            <div class="form-row"><label>排序</label><input type="number" name="sort" value="<?= Site::h($work['sort'] ?? 0) ?>"></div>
            <div class="form-row"><label>封面图 URL</label><input type="text" name="cover" id="coverUrl" value="<?= Site::h($work['cover'] ?? '') ?>"></div>
            <div class="form-row"><label>或上传图片</label><input type="file" name="cover_file" accept="image/*"></div>
            <div class="form-row"><label>首页展示</label>
                <select name="is_featured">
                    <option value="1" <?= ((int)($work['is_featured'] ?? 1) === 1) ? 'selected' : '' ?>>是</option>
                    <option value="0" <?= ((int)($work['is_featured'] ?? 1) === 0) ? 'selected' : '' ?>>否</option>
                </select>
            </div>
        </div>
        <div class="form-row"><label>作品简介</label><textarea name="description"><?= Site::h($work['description'] ?? '') ?></textarea></div>
        <div class="form-row"><label>创作随笔/详细内容</label><textarea name="content"><?= Site::h($work['content'] ?? '') ?></textarea></div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="artworks.php" class="btn btn-ghost">取消</a>
        </div>
    </form>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}

// list
$list = $db->query("SELECT a.*, c.name AS cat_name FROM artworks a LEFT JOIN categories c ON a.category_id = c.id ORDER BY a.sort ASC, a.id DESC")->fetch_all(MYSQLI_ASSOC);
include __DIR__ . '/header.php';
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-display text-ink-800">作品集管理</h1>
    <a href="artworks.php?action=add" class="btn btn-primary">+ 新增作品</a>
</div>
<div class="bg-warm-50 rounded border border-warm-200 overflow-x-auto">
    <table class="admin w-full">
        <thead>
            <tr><th>ID</th><th>封面</th><th>标题</th><th>分类</th><th>年份</th><th>浏览</th><th>首页</th><th>操作</th></tr>
        </thead>
        <tbody>
        <?php foreach ($list as $row): ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><img src="<?= Site::imageUrl($row['cover']) ?>" alt="" style="width:60px;height:75px;object-fit:cover;"></td>
                <td><?= Site::h($row['title']) ?></td>
                <td><?= Site::h($row['cat_name']) ?></td>
                <td><?= (int)$row['year'] ?></td>
                <td><?= (int)$row['views'] ?></td>
                <td><?= ((int)$row['is_featured'] === 1) ? '<span class="badge-ok">是</span>' : '<span class="badge-warn">否</span>' ?></td>
                <td>
                    <a href="artworks.php?action=edit&id=<?= (int)$row['id'] ?>" class="btn btn-secondary">编辑</a>
                    <a href="artworks.php?action=delete&id=<?= (int)$row['id'] ?>" onclick="return confirm('确定删除？')" class="btn btn-danger">删除</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/footer.php'; ?>
