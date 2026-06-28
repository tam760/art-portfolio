<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Site.php';
$db = Site::db();

$catFilter = $_GET['cat'] ?? '';
$yearFilter = $_GET['year'] ?? '';
$keyword = trim($_GET['q'] ?? '');

$where = ['1=1'];
$params = [];
$types = '';

if ($catFilter) {
    $where[] = 'c.slug = ?';
    $params[] = $catFilter;
    $types .= 's';
}
if ($yearFilter) {
    $where[] = 'a.year = ?';
    $params[] = (int)$yearFilter;
    $types .= 'i';
}
if ($keyword !== '') {
    $where[] = '(a.title LIKE ? OR a.description LIKE ?)';
    $like = '%' . $keyword . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$sql = "SELECT a.*, c.slug AS cat_slug, c.name AS cat_name FROM artworks a LEFT JOIN categories c ON a.category_id = c.id WHERE " . implode(' AND ', $where) . " ORDER BY a.sort ASC, a.id DESC";
$stmt = $db->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$artworks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$yearRows = $db->query("SELECT DISTINCT year FROM artworks WHERE year IS NOT NULL ORDER BY year DESC")->fetch_all(MYSQLI_ASSOC);
$years = array_column($yearRows, 'year');

include __DIR__ . '/includes/header.php';
?>

<section class="pt-32 pb-16 bg-night-100/30 min-h-[60vh]">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="font-display text-4xl lg:text-5xl text-amber-50 mb-3">全部作品</h1>
            <p class="text-amber-500">共 <?= count($artworks) ?> 件作品</p>
        </div>

        <form method="get" action="works.php" class="bg-night-100 p-6 hand-border mb-10 flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-amber-400 mb-2">关键词</label>
                <input type="text" name="q" value="<?= Site::h($keyword) ?>" placeholder="搜索作品标题或描述" class="form-input w-full px-4 py-3">
            </div>
            <div>
                <label class="block text-sm text-amber-400 mb-2">分类</label>
                <select name="cat" class="form-input w-full px-4 py-3">
                    <option value="">全部分类</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= Site::h($c['slug']) ?>" <?= $catFilter === $c['slug'] ? 'selected' : '' ?>><?= Site::h($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm text-amber-400 mb-2">年份</label>
                <select name="year" class="form-input w-full px-4 py-3">
                    <option value="">全部年份</option>
                    <?php foreach ($years as $y): ?>
                    <option value="<?= (int)$y ?>" <?= $yearFilter === (string)$y ? 'selected' : '' ?>><?= (int)$y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-3 bg-gold-400 text-night-50 text-sm tracking-widest hover:bg-gold-300 transition-colors">筛选</button>
                <a href="works.php" class="px-6 py-3 border border-night-400 text-amber-300 text-sm tracking-widest hover:border-night-500 transition-colors">重置</a>
            </div>
        </form>

        <?php if (!$artworks): ?>
        <p class="text-center text-amber-500 py-16">暂无符合条件的作品</p>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <?php foreach ($artworks as $art): ?>
            <a href="work.php?id=<?= (int)$art['id'] ?>" class="portfolio-card group relative overflow-hidden cursor-pointer block" data-category="<?= Site::h($art['cat_slug']) ?>">
                <div class="aspect-[4/5] overflow-hidden bg-night-200">
                    <img src="<?= Site::imageUrl($art['cover']) ?>" alt="<?= Site::h($art['title']) ?>" loading="lazy" class="card-image object-cover object-center w-full h-full">
                </div>
                <div class="overlay absolute inset-0 bg-night-50/80 flex flex-col justify-end p-6">
                    <span class="text-gold-400 text-xs tracking-widest uppercase mb-2"><?= Site::h($art['cat_name']) ?> · <?= (int)$art['year'] ?></span>
                    <h3 class="font-display text-xl text-amber-50 mb-1"><?= Site::h($art['title']) ?></h3>
                    <p class="text-amber-400 text-sm"><?= Site::h($art['medium']) ?> · <?= Site::h($art['size_desc']) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
