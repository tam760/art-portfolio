<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Site.php';
$db = Site::db();

$id = (int)($_GET['id'] ?? 0);
$art = null;
if ($id > 0) {
    $stmt = $db->prepare("SELECT a.*, c.slug AS cat_slug, c.name AS cat_name FROM artworks a LEFT JOIN categories c ON a.category_id = c.id WHERE a.id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $art = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($art) {
        $stmt = $db->prepare("UPDATE artworks SET views = views + 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
}
if (!$art) {
    http_response_code(404);
}

$page_title = $art ? $art['title'] : '';

// 相关作品
$related = [];
if ($art) {
    $stmt = $db->prepare("SELECT id, title, cover FROM artworks WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 3");
    $stmt->bind_param('ii', $art['category_id'], $id);
    $stmt->execute();
    $related = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

include __DIR__ . '/includes/header.php';
?>

<section class="pt-32 pb-16 bg-night-100/30">
    <div class="max-w-5xl mx-auto px-6 lg:px-8">
        <?php if (!$art): ?>
        <p class="text-center text-amber-500 py-20">作品不存在</p>
        <?php else: ?>
        <div class="mb-6">
            <a href="<?= SITE_URL ?>/#portfolio" class="text-sm text-amber-600 hover:text-gold-400">&larr; 返回作品集</a>
        </div>
        <div class="grid lg:grid-cols-5 gap-10">
            <div class="lg:col-span-3">
                <div class="hand-border bg-night-100 overflow-hidden">
                    <img src="<?= Site::imageUrl($art['cover']) ?>" alt="<?= Site::h($art['title']) ?>" class="w-full h-auto">
                </div>
            </div>
            <div class="lg:col-span-2">
                <span class="inline-block px-3 py-1 bg-night-300 text-gold-300 text-xs rounded-full mb-3"><?= Site::h($art['cat_name']) ?></span>
                <h1 class="font-display text-4xl text-amber-50 mb-4"><?= Site::h($art['title']) ?></h1>
                <div class="grid grid-cols-2 gap-4 text-sm text-amber-400 mb-6">
                    <div><span class="text-amber-600">创作年份：</span><?= (int)$art['year'] ?></div>
                    <div><span class="text-amber-600">媒介：</span><?= Site::h($art['medium']) ?></div>
                    <div class="col-span-2"><span class="text-amber-600">尺寸：</span><?= Site::h($art['size_desc']) ?></div>
                    <div class="col-span-2"><span class="text-amber-600">浏览：</span><?= (int)$art['views'] ?></div>
                </div>
                <?php if ($art['description']): ?>
                <div class="mb-6">
                    <h3 class="font-display text-xl text-amber-50 mb-3">作品简介</h3>
                    <p class="text-amber-400 leading-relaxed"><?= nl2br(Site::h($art['description'])) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($art['content']): ?>
                <div class="mb-6">
                    <h3 class="font-display text-xl text-amber-50 mb-3">创作随笔</h3>
                    <p class="text-amber-400 leading-relaxed"><?= nl2br(Site::h($art['content'])) ?></p>
                </div>
                <?php endif; ?>
                <a href="#contact" class="inline-flex items-center justify-center px-6 py-3 bg-gold-400 text-night-50 text-sm tracking-widest hover:bg-gold-300 transition-colors">咨询收藏</a>
            </div>
        </div>

        <?php if ($related): ?>
        <div class="mt-20">
            <h3 class="font-display text-2xl text-amber-50 mb-8">相关作品</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($related as $r): ?>
                <a href="work.php?id=<?= (int)$r['id'] ?>" class="portfolio-card group relative overflow-hidden cursor-pointer">
                    <div class="aspect-[4/5] overflow-hidden bg-night-200">
                        <img src="<?= Site::imageUrl($r['cover']) ?>" alt="<?= Site::h($r['title']) ?>" loading="lazy" class="card-image object-cover object-top w-full h-full">
                    </div>
                    <div class="overlay absolute inset-0 bg-night-50/80 flex flex-col justify-end p-6">
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
