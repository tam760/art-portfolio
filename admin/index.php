<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';
Site::requireAdmin();

$db = Site::db();
$stats = [
    ['label' => '作品总数', 'value' => (int)$db->query("SELECT COUNT(*) FROM artworks")->fetch_row()[0]],
    ['label' => '博客文章', 'value' => (int)$db->query("SELECT COUNT(*) FROM posts")->fetch_row()[0]],
    ['label' => '展览记录', 'value' => (int)$db->query("SELECT COUNT(*) FROM exhibitions")->fetch_row()[0]],
    ['label' => '留言数', 'value' => (int)$db->query("SELECT COUNT(*) FROM messages")->fetch_row()[0]],
    ['label' => '订阅用户', 'value' => (int)$db->query("SELECT COUNT(*) FROM subscribers")->fetch_row()[0]],
    ['label' => '作品总浏览', 'value' => (int)$db->query("SELECT COALESCE(SUM(views),0) FROM artworks")->fetch_row()[0]],
];
$recentMessages = $db->query("SELECT * FROM messages ORDER BY id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recentPosts = $db->query("SELECT id, title, created_at FROM posts ORDER BY id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$current = 'dashboard';
include __DIR__ . '/header.php';
?>

<h1 class="text-2xl font-display text-ink-800 mb-6">数据概览</h1>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
    <?php foreach ($stats as $s): ?>
    <div class="bg-warm-50 p-5 rounded border border-warm-200">
        <p class="text-sm text-warm-500 mb-2"><?= Site::h($s['label']) ?></p>
        <p class="text-3xl font-display text-ink-800"><?= (int)$s['value'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-warm-50 p-6 rounded border border-warm-200">
        <h2 class="font-display text-lg text-ink-800 mb-4">最新留言</h2>
        <?php if (!$recentMessages): ?>
        <p class="text-sm text-warm-500">暂无留言</p>
        <?php else: ?>
        <ul class="space-y-3">
            <?php foreach ($recentMessages as $m): ?>
            <li class="border-b border-warm-200 pb-3">
                <p class="text-sm text-ink-700"><strong><?= Site::h($m['name']) ?></strong> · <?= Site::h($m['email']) ?></p>
                <p class="text-xs text-warm-500 mt-1"><?= Site::h(mb_substr($m['message'], 0, 80)) ?></p>
                <p class="text-xs text-warm-400 mt-1"><?= $m['created_at'] ?></p>
            </li>
            <?php endforeach; ?>
        </ul>
        <a href="messages.php" class="btn btn-ghost mt-4">查看全部留言</a>
        <?php endif; ?>
    </div>

    <div class="bg-warm-50 p-6 rounded border border-warm-200">
        <h2 class="font-display text-lg text-ink-800 mb-4">最新博客</h2>
        <?php if (!$recentPosts): ?>
        <p class="text-sm text-warm-500">暂无文章</p>
        <?php else: ?>
        <ul class="space-y-3">
            <?php foreach ($recentPosts as $p): ?>
            <li class="flex justify-between items-center border-b border-warm-200 pb-3">
                <a href="posts.php" class="text-sm text-ink-700 hover:text-rust-500"><?= Site::h($p['title']) ?></a>
                <span class="text-xs text-warm-400"><?= $p['created_at'] ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <a href="posts.php" class="btn btn-ghost mt-4">管理博客</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
