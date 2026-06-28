<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Site.php';
$db = Site::db();

$id = (int)($_GET['id'] ?? 0);
$post = null;
if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($post) {
        $stmt = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
}
if (!$post) http_response_code(404);

$page_title = $post ? $post['title'] : '';

// 相关文章
$related = [];
if ($post) {
    $stmt = $db->prepare("SELECT id, title, cover, tag FROM posts WHERE id != ? ORDER BY RAND() LIMIT 3");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $related = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

include __DIR__ . '/includes/header.php';
?>

<section class="pt-32 pb-16 bg-night-100/30">
    <div class="max-w-3xl mx-auto px-6 lg:px-8">
        <?php if (!$post): ?>
        <p class="text-center text-amber-500 py-20">文章不存在</p>
        <?php else: ?>
        <div class="mb-6">
            <a href="<?= SITE_URL ?>/#blog" class="text-sm text-amber-600 hover:text-gold-400">&larr; 返回博客</a>
        </div>
        <article class="bg-night-100 hand-border overflow-hidden">
            <img src="<?= Site::imageUrl($post['cover']) ?>" alt="<?= Site::h($post['title']) ?>" class="w-full h-auto">
            <div class="p-8 lg:p-12">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-3 py-1 bg-night-300 text-gold-300 text-xs rounded-full"><?= Site::h($post['tag'] ?? '随笔') ?></span>
                    <span class="text-sm text-amber-600"><?= date('Y年m月d日', strtotime($post['created_at'])) ?> · 阅读 <?= (int)$post['views'] ?></span>
                </div>
                <h1 class="font-display text-3xl lg:text-4xl text-amber-50 mb-6"><?= Site::h($post['title']) ?></h1>
                <div class="prose max-w-none text-amber-300 leading-loose">
                    <?= nl2br(Site::h($post['content'])) ?>
                </div>
            </div>
        </article>

        <?php if ($related): ?>
        <div class="mt-16">
            <h3 class="font-display text-2xl text-amber-50 mb-8">继续阅读</h3>
            <div class="grid md:grid-cols-3 gap-6">
                <?php foreach ($related as $r): ?>
                <a href="post.php?id=<?= (int)$r['id'] ?>" class="blog-card bg-night-100 overflow-hidden shadow-sm block">
                    <div class="aspect-[16/10] overflow-hidden">
                        <img src="<?= Site::imageUrl($r['cover']) ?>" alt="<?= Site::h($r['title']) ?>" loading="lazy" class="object-cover w-full h-full">
                    </div>
                    <div class="p-5">
                        <span class="text-xs text-gold-400 mb-2 inline-block"><?= Site::h($r['tag']) ?></span>
                        <h4 class="font-display text-lg text-amber-50"><?= Site::h($r['title']) ?></h4>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
