<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';
Site::requireAdmin();

$db = Site::db();
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$current = 'posts';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    Site::flash('admin_msg', '删除成功');
    Site::redirect(SITE_URL . '/admin/posts.php');
}

if ($action === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $tag = trim($_POST['tag'] ?? '');
    $cover = trim($_POST['cover'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = $_POST['content'] ?? '';

    if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
        $uploaded = Site::uploadImage($_FILES['cover_file'], 'posts');
        if ($uploaded) $cover = $uploaded;
    }

    if ($title === '') {
        $error = '标题必填';
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE posts SET title=?, tag=?, cover=?, summary=?, content=? WHERE id=?");
            $stmt->bind_param('sssssi', $title, $tag, $cover, $summary, $content, $id);
        } else {
            $stmt = $db->prepare("INSERT INTO posts (title, tag, cover, summary, content) VALUES (?,?,?,?,?)");
            $stmt->bind_param('sssss', $title, $tag, $cover, $summary, $content);
        }
        $stmt->execute();
        $stmt->close();
        Site::flash('admin_msg', '保存成功');
        Site::redirect(SITE_URL . '/admin/posts.php');
    }
}

if ($action === 'edit' || $action === 'add') {
    $editId = (int)($_GET['id'] ?? 0);
    $post = null;
    if ($editId > 0) {
        $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $post = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($error)) {
        $post = array_merge($post ?? [], $_POST);
    }
    include __DIR__ . '/header.php';
    ?>
    <h1 class="text-2xl font-display text-ink-800 mb-6"><?= $post ? '编辑文章' : '新增文章' ?></h1>
    <?php if (!empty($error)): ?><div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm"><?= Site::h($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="bg-warm-50 p-6 rounded border border-warm-200 max-w-3xl">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)($post['id'] ?? 0) ?>">
        <div class="grid md:grid-cols-2 gap-4">
            <div class="form-row md:col-span-2"><label>文章标题 *</label><input type="text" name="title" required value="<?= Site::h($post['title'] ?? '') ?>"></div>
            <div class="form-row"><label>标签</label><input type="text" name="tag" value="<?= Site::h($post['tag'] ?? '') ?>" placeholder="如 技法心得"></div>
            <div class="form-row"><label>封面图 URL</label><input type="text" name="cover" value="<?= Site::h($post['cover'] ?? '') ?>"></div>
            <div class="form-row md:col-span-2"><label>或上传封面</label><input type="file" name="cover_file" accept="image/*"></div>
        </div>
        <div class="form-row"><label>摘要</label><textarea name="summary"><?= Site::h($post['summary'] ?? '') ?></textarea></div>
        <div class="form-row"><label>正文</label><textarea name="content" style="min-height:320px;"><?= Site::h($post['content'] ?? '') ?></textarea></div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="posts.php" class="btn btn-ghost">取消</a>
        </div>
    </form>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}

$list = $db->query("SELECT * FROM posts ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
include __DIR__ . '/header.php';
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-display text-ink-800">博客管理</h1>
    <a href="posts.php?action=add" class="btn btn-primary">+ 新增文章</a>
</div>
<div class="bg-warm-50 rounded border border-warm-200 overflow-x-auto">
    <table class="admin w-full">
        <thead><tr><th>ID</th><th>封面</th><th>标题</th><th>标签</th><th>浏览</th><th>发布时间</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($list as $row): ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><?php if ($row['cover']): ?><img src="<?= Site::imageUrl($row['cover']) ?>" style="width:100px;height:60px;object-fit:cover;"><?php endif; ?></td>
                <td><?= Site::h($row['title']) ?></td>
                <td><?= Site::h($row['tag']) ?></td>
                <td><?= (int)$row['views'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="posts.php?action=edit&id=<?= (int)$row['id'] ?>" class="btn btn-secondary">编辑</a>
                    <a href="posts.php?action=delete&id=<?= (int)$row['id'] ?>" onclick="return confirm('确定删除？')" class="btn btn-danger">删除</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/footer.php'; ?>
