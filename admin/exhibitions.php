<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';
Site::requireAdmin();

$db = Site::db();
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$current = 'exhibitions';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM exhibitions WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    Site::flash('admin_msg', '删除成功');
    Site::redirect(SITE_URL . '/admin/exhibitions.php');
}

if ($action === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_upcoming = (int)($_POST['is_upcoming'] ?? 0);
    $sort = (int)($_POST['sort'] ?? 0);

    if ($title === '') {
        $error = '标题必填';
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE exhibitions SET title=?, type=?, start_date=?, end_date=?, venue=?, description=?, is_upcoming=?, sort=? WHERE id=?");
            $stmt->bind_param('ssssssii', $title, $type, $start_date, $end_date, $venue, $description, $is_upcoming, $sort, $id);
        } else {
            $stmt = $db->prepare("INSERT INTO exhibitions (title, type, start_date, end_date, venue, description, is_upcoming, sort) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssssssii', $title, $type, $start_date, $end_date, $venue, $description, $is_upcoming, $sort);
        }
        $stmt->execute();
        $stmt->close();
        Site::flash('admin_msg', '保存成功');
        Site::redirect(SITE_URL . '/admin/exhibitions.php');
    }
}

if ($action === 'edit' || $action === 'add') {
    $editId = (int)($_GET['id'] ?? 0);
    $ex = null;
    if ($editId > 0) {
        $stmt = $db->prepare("SELECT * FROM exhibitions WHERE id = ?");
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $ex = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($error)) {
        $ex = array_merge($ex ?? [], $_POST);
    }
    include __DIR__ . '/header.php';
    ?>
    <h1 class="text-2xl font-display text-ink-800 mb-6"><?= $ex ? '编辑展览' : '新增展览' ?></h1>
    <?php if (!empty($error)): ?><div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm"><?= Site::h($error) ?></div><?php endif; ?>
    <form method="post" class="bg-warm-50 p-6 rounded border border-warm-200 max-w-3xl">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)($ex['id'] ?? 0) ?>">
        <div class="grid md:grid-cols-2 gap-4">
            <div class="form-row md:col-span-2"><label>展览名称 *</label><input type="text" name="title" required value="<?= Site::h($ex['title'] ?? '') ?>"></div>
            <div class="form-row"><label>类型</label>
                <select name="type">
                    <?php foreach (['个展','群展','联展','获奖','其他'] as $t): ?>
                    <option value="<?= $t ?>" <?= ($ex['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row"><label>即将开幕</label>
                <select name="is_upcoming">
                    <option value="0" <?= ((int)($ex['is_upcoming'] ?? 0) === 0) ? 'selected' : '' ?>>否</option>
                    <option value="1" <?= ((int)($ex['is_upcoming'] ?? 0) === 1) ? 'selected' : '' ?>>是</option>
                </select>
            </div>
            <div class="form-row"><label>开始日期</label><input type="date" name="start_date" value="<?= Site::h($ex['start_date'] ?? '') ?>"></div>
            <div class="form-row"><label>结束日期</label><input type="date" name="end_date" value="<?= Site::h($ex['end_date'] ?? '') ?>"></div>
            <div class="form-row md:col-span-2"><label>展览地点</label><input type="text" name="venue" value="<?= Site::h($ex['venue'] ?? '') ?>"></div>
            <div class="form-row md:col-span-2"><label>展览介绍</label><textarea name="description"><?= Site::h($ex['description'] ?? '') ?></textarea></div>
            <div class="form-row"><label>排序</label><input type="number" name="sort" value="<?= Site::h($ex['sort'] ?? 0) ?>"></div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="exhibitions.php" class="btn btn-ghost">取消</a>
        </div>
    </form>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}

$list = $db->query("SELECT * FROM exhibitions ORDER BY is_upcoming DESC, sort ASC, id DESC")->fetch_all(MYSQLI_ASSOC);
include __DIR__ . '/header.php';
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-display text-ink-800">展览管理</h1>
    <a href="exhibitions.php?action=add" class="btn btn-primary">+ 新增展览</a>
</div>
<div class="bg-warm-50 rounded border border-warm-200 overflow-x-auto">
    <table class="admin w-full">
        <thead><tr><th>ID</th><th>标题</th><th>类型</th><th>即将</th><th>开始</th><th>结束</th><th>地点</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($list as $row): ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><?= Site::h($row['title']) ?></td>
                <td><?= Site::h($row['type']) ?></td>
                <td><?= ((int)$row['is_upcoming'] === 1) ? '<span class="badge-ok">是</span>' : '<span class="badge-warn">否</span>' ?></td>
                <td><?= Site::h($row['start_date']) ?></td>
                <td><?= Site::h($row['end_date']) ?></td>
                <td><?= Site::h($row['venue']) ?></td>
                <td>
                    <a href="exhibitions.php?action=edit&id=<?= (int)$row['id'] ?>" class="btn btn-secondary">编辑</a>
                    <a href="exhibitions.php?action=delete&id=<?= (int)$row['id'] ?>" onclick="return confirm('确定删除？')" class="btn btn-danger">删除</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/footer.php'; ?>
