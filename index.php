<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Site.php';
$db = Site::db();

// 读取数据
$categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

$catMap = [];
foreach ($categories as $c) $catMap[$c['id']] = $c;

$artworks = $db->query("SELECT a.*, c.slug AS cat_slug, c.name AS cat_name
    FROM artworks a LEFT JOIN categories c ON a.category_id = c.id
    WHERE 1=1 ORDER BY a.sort ASC, a.id DESC LIMIT 30")->fetch_all(MYSQLI_ASSOC);

$posts = $db->query("SELECT * FROM posts ORDER BY id DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);

$exhibitions = $db->query("SELECT * FROM exhibitions ORDER BY is_upcoming DESC, sort ASC, id DESC")->fetch_all(MYSQLI_ASSOC);

$bio = Site::config('bio');
$philosophy = Site::config('philosophy');
$artistName = Site::config('artist_name', '琪琪');
$artistEn = Site::config('artist_en', 'Lin Xi');
$artistEmail = Site::config('artist_email', 'linxi.art@email.com');
$artistStudio = Site::config('artist_studio', '');
$artistHours = Site::config('artist_hours', '');
$artistPhoto = Site::config('artist_photo', '');
$artistStudioPhoto = Site::config('artist_studio_photo', '');

// 统计
$stats = [
    ['label' => '年创作经验', 'value' => '6+'],
    ['label' => '原创作品', 'value' => (string)count($artworks)],
    ['label' => '展览经历', 'value' => (string)count($exhibitions)]
];

$skills = ['油画','水彩','插画','素描','版画','综合材料'];

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section id="home" class="min-h-screen relative flex items-center justify-center overflow-hidden pt-20">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-32 left-8 w-64 h-64 rounded-full bg-gold-400/5 blur-3xl"></div>
        <div class="absolute bottom-32 right-8 w-96 h-96 rounded-full bg-rust-400/5 blur-3xl"></div>
    </div>
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-20 lg:py-32 relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            <div class="order-2 lg:order-1 text-center lg:text-left">
                <div class="deco-line w-24 mx-auto lg:mx-0 mb-8"></div>
                <p class="font-hand text-3xl lg:text-4xl text-gold-400 mb-4 tracking-wide">以画笔记录时光</p>
                <h1 class="font-display text-5xl lg:text-7xl text-amber-50 mb-6 leading-tight">
                    <?= Site::h($artistName) ?>
                    <span class="block text-2xl lg:text-3xl text-amber-500 mt-2 font-serif font-light"><?= Site::h($artistEn) ?> · Artist</span>
                </h1>
                <p class="text-lg text-amber-400 leading-relaxed max-w-md mx-auto lg:mx-0 mb-10">
                    在色彩与线条之间寻找诗意，<br>用画笔描绘内心深处的风景。<br>每一幅作品都是一段与自我对话的旅程。
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="#portfolio" class="inline-flex items-center justify-center px-8 py-3 bg-gold-400 text-night-50 text-sm tracking-widest hover:bg-gold-300 transition-colors duration-300">浏览作品</a>
                    <a href="#about" class="inline-flex items-center justify-center px-8 py-3 border border-night-400 text-amber-300 text-sm tracking-widest hover:border-night-500 hover:text-amber-200 transition-colors duration-300">了解更多</a>
                </div>
            </div>
            <div class="order-1 lg:order-2 relative">
                <div class="relative max-w-md mx-auto lg:max-w-none">
                    <div class="absolute -inset-4 border border-night-400/50 rounded-sm"></div>
                    <div class="absolute -inset-8 border border-night-300/30 rounded-sm"></div>
                    <img src="<?= Site::imageUrl($artistStudioPhoto) ?: 'https://picsum.photos/seed/artist-studio/600/800' ?>" alt="艺术家工作室" class="object-contain w-full relative z-10 shadow-[0_20px_60px_rgba(15,25,35,0.4)] bg-night-300/30" style="max-height:75vh">
                    <div class="absolute -bottom-6 -left-6 bg-night-100 px-6 py-4 shadow-lg z-20 hand-border">
                        <p class="font-hand text-xl text-gold-400">Since 2018</p>
                        <p class="text-xs text-amber-600 mt-1 tracking-wide">持续创作中</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2">
        <span class="text-xs text-amber-600 tracking-widest uppercase">Scroll</span>
        <div class="w-px h-8 bg-gradient-to-b from-amber-600 to-transparent"></div>
    </div>
</section>

<!-- Portfolio Section -->
<section id="portfolio" class="py-24 lg:py-32 bg-night-100/50">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16 reveal">
            <div class="deco-ornament flex items-center justify-center text-sm text-amber-500 mb-4">
                <span class="font-hand text-2xl text-gold-400">作品集</span>
            </div>
            <h2 class="font-display text-4xl lg:text-5xl text-amber-50 mb-4">精选作品</h2>
            <p class="text-amber-500 max-w-lg mx-auto">每一幅画作都承载着独特的故事与情感，邀请您走进我的艺术世界</p>
        </div>

        <div class="flex flex-wrap justify-center gap-3 mb-12 reveal">
            <button class="filter-btn active px-6 py-2 text-sm tracking-wide rounded-full border border-night-400" data-filter="all">全部</button>
            <?php foreach ($categories as $cat): ?>
            <button class="filter-btn px-6 py-2 text-sm tracking-wide rounded-full border border-night-400 text-amber-400" data-filter="<?= Site::h($cat['slug']) ?>"><?= Site::h($cat['name']) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 reveal">
            <?php foreach ($artworks as $art): ?>
            <div class="portfolio-card group relative overflow-hidden cursor-pointer" data-category="<?= Site::h($art['cat_slug']) ?>" onclick="location.href='work.php?id=<?= (int)$art['id'] ?>'">
                <div class="aspect-[4/5] overflow-hidden bg-night-200">
                    <img src="<?= Site::imageUrl($art['cover']) ?>" alt="<?= Site::h($art['title']) ?>" loading="lazy" class="card-image object-cover object-center w-full h-full">
                </div>
                <div class="overlay absolute inset-0 bg-night-50/80 flex flex-col justify-end p-6">
                    <span class="text-gold-400 text-xs tracking-widest uppercase mb-2"><?= Site::h($art['cat_name']) ?> · <?= (int)$art['year'] ?></span>
                    <h3 class="font-display text-xl text-amber-50 mb-1"><?= Site::h($art['title']) ?></h3>
                    <p class="text-amber-400 text-sm"><?= Site::h($art['medium']) ?> · <?= Site::h($art['size_desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-12">
            <a href="<?= SITE_URL ?>/works.php" class="inline-flex items-center gap-2 text-amber-400 text-sm tracking-widest hover:text-gold-400 transition-colors">
                <span>查看更多作品</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-24 lg:py-32">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid lg:grid-cols-5 gap-12 lg:gap-20 items-start">
            <div class="lg:col-span-2 reveal">
                <div class="relative">
                    <img src="<?= Site::imageUrl($artistPhoto) ?: 'https://picsum.photos/seed/artist-portrait/500/667' ?>" alt="<?= Site::h($artistName) ?>肖像" class="object-contain w-full shadow-[0_20px_60px_rgba(15,25,35,0.3)] bg-night-300/30" style="max-height:500px">
                    <div class="absolute -bottom-6 -right-6 w-full h-full border-2 border-gold-400/30 -z-10"></div>
                    <div class="absolute -top-6 -left-6 w-24 h-24 border-t-2 border-l-2 border-gold-400/30"></div>
                </div>
                <div class="mt-12 grid grid-cols-3 gap-4 text-center">
                    <?php foreach ($stats as $s): ?>
                    <div>
                        <p class="font-display text-3xl text-amber-50"><?= Site::h($s['value']) ?></p>
                        <p class="text-xs text-amber-500 mt-1"><?= Site::h($s['label']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="lg:col-span-3 reveal">
                <div class="deco-ornament flex items-center mb-4">
                    <span class="font-hand text-2xl text-gold-400">关于我</span>
                </div>
                <h2 class="font-display text-4xl lg:text-5xl text-amber-50 mb-8">用画笔诉说<br>内心的故事</h2>
                <div class="space-y-6 text-amber-400 leading-relaxed">
                    <p><?= nl2br(Site::h($bio)) ?></p>
                </div>
                <?php if ($philosophy): ?>
                <div class="mt-10 p-8 bg-night-100/50 border-l-4 border-gold-400">
                    <p class="font-hand text-2xl text-amber-100 mb-4">创作理念</p>
                    <p class="text-amber-400 italic leading-relaxed"><?= nl2br(Site::h($philosophy)) ?></p>
                </div>
                <?php endif; ?>
                <div class="mt-10">
                    <h3 class="font-display text-xl text-amber-50 mb-6">创作领域</h3>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($skills as $s): ?>
                        <span class="px-4 py-2 bg-night-200 text-amber-400 text-sm rounded-full"><?= Site::h($s) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section -->
<section id="blog" class="py-24 lg:py-32 bg-night-100/30">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16 reveal">
            <div class="deco-ornament flex items-center justify-center mb-4">
                <span class="font-hand text-2xl text-gold-400">创作笔记</span>
            </div>
            <h2 class="font-display text-4xl lg:text-5xl text-amber-50 mb-4">博客</h2>
            <p class="text-amber-500 max-w-lg mx-auto">记录创作过程中的思考、技法探索与生活感悟</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 reveal">
            <?php foreach ($posts as $post): ?>
            <article class="blog-card bg-night-100 overflow-hidden shadow-[0_4px_20px_rgba(15,25,35,0.2)] cursor-pointer" onclick="location.href='post.php?id=<?= (int)$post['id'] ?>'">
                <div class="aspect-[16/10] overflow-hidden">
                    <img src="<?= Site::imageUrl($post['cover']) ?>" alt="<?= Site::h($post['title']) ?>" loading="lazy" class="object-cover object-top w-full h-full">
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="px-3 py-1 bg-night-300 text-gold-300 text-xs rounded-full"><?= Site::h($post['tag'] ?? '随笔') ?></span>
                        <span class="text-xs text-amber-600"><?= date('Y.m.d', strtotime($post['created_at'])) ?></span>
                    </div>
                    <h3 class="font-display text-xl text-amber-50 mb-3 hover:text-gold-400 transition-colors"><?= Site::h($post['title']) ?></h3>
                    <p class="text-amber-500 text-sm leading-relaxed mb-4"><?= Site::h(mb_substr($post['summary'] ?? '', 0, 100)) ?></p>
                    <span class="inline-flex items-center gap-2 text-sm text-amber-400 hover:text-gold-400 transition-colors">
                        <span>阅读全文</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </span>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Exhibitions Section -->
<section id="exhibitions" class="py-24 lg:py-32">
    <div class="max-w-4xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16 reveal">
            <div class="deco-ornament flex items-center justify-center mb-4">
                <span class="font-hand text-2xl text-gold-400">展览与活动</span>
            </div>
            <h2 class="font-display text-4xl lg:text-5xl text-amber-50 mb-4">展览历程</h2>
            <p class="text-amber-500 max-w-lg mx-auto">记录每一次与观众相遇的珍贵时刻</p>
        </div>
        <div class="relative reveal">
            <div class="timeline-line"></div>
            <?php foreach ($exhibitions as $ex): ?>
            <div class="relative pl-16 pb-12">
                <div class="timeline-dot <?= $ex['is_upcoming'] ? 'upcoming' : '' ?> absolute left-[18px] top-1"></div>
                <div class="<?= $ex['is_upcoming'] ? 'bg-night-100 p-6 lg:p-8 hand-border' : 'bg-night-100/50 p-6 lg:p-8 border border-night-300/50' ?>">
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        <?php if ($ex['is_upcoming']): ?>
                        <span class="px-3 py-1 bg-night-300 text-gold-300 text-xs rounded-full font-medium">即将开幕</span>
                        <?php else: ?>
                        <span class="px-3 py-1 bg-night-200 text-amber-500 text-xs rounded-full"><?= Site::h($ex['type']) ?></span>
                        <?php endif; ?>
                        <span class="text-sm text-amber-600">
                            <?= Site::h($ex['start_date']) ?><?php if ($ex['end_date']): ?> — <?= Site::h($ex['end_date']) ?><?php endif; ?>
                        </span>
                    </div>
                    <h3 class="font-display text-xl text-amber-50 mb-2"><?= Site::h($ex['title']) ?></h3>
                    <?php if ($ex['venue']): ?><p class="text-amber-500 mb-3"><?= Site::h($ex['venue']) ?></p><?php endif; ?>
                    <?php if ($ex['description']): ?><p class="text-amber-400 text-sm leading-relaxed"><?= Site::h($ex['description']) ?></p><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-24 lg:py-32 bg-night-100/30">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20">
            <div class="reveal">
                <div class="deco-ornament flex items-center mb-4">
                    <span class="font-hand text-2xl text-gold-400">联系方式</span>
                </div>
                <h2 class="font-display text-4xl lg:text-5xl text-amber-50 mb-6">期待与您<br>交流创作</h2>
                <p class="text-amber-500 leading-relaxed mb-10">无论是作品收藏、展览合作、艺术委托还是单纯的交流探讨，都欢迎与我取得联系。</p>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-night-200 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-amber-600 mb-1">电子邮箱</p>
                            <a href="mailto:<?= Site::h($artistEmail) ?>" class="text-amber-100 hover:text-gold-400 transition-colors"><?= Site::h($artistEmail) ?></a>
                        </div>
                    </div>
                    <?php if ($artistStudio): ?>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-night-200 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-amber-600 mb-1">工作室地址</p>
                            <p class="text-amber-100"><?= nl2br(Site::h($artistStudio)) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($artistHours): ?>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-night-200 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-amber-600 mb-1">工作时间</p>
                            <p class="text-amber-100"><?= nl2br(Site::h($artistHours)) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="reveal">
                <form id="contactForm" action="<?= SITE_URL ?>/contact.php" method="post" class="bg-night-100 p-8 lg:p-10 hand-border space-y-6">
                    <div>
                        <label class="block text-sm text-amber-400 mb-2">您的姓名</label>
                        <input type="text" name="name" required class="form-input w-full px-4 py-3 placeholder-amber-700" placeholder="请输入姓名">
                    </div>
                    <div>
                        <label class="block text-sm text-amber-400 mb-2">电子邮箱</label>
                        <input type="email" name="email" required class="form-input w-full px-4 py-3 placeholder-amber-700" placeholder="请输入邮箱地址">
                    </div>
                    <div>
                        <label class="block text-sm text-amber-400 mb-2">联系主题</label>
                        <select name="subject" class="form-input w-full px-4 py-3 bg-transparent">
                            <option value="">请选择联系主题</option>
                            <option value="作品收藏咨询">作品收藏咨询</option>
                            <option value="展览合作">展览合作</option>
                            <option value="艺术委托">艺术委托</option>
                            <option value="其他">其他</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-amber-400 mb-2">留言内容</label>
                        <textarea name="message" rows="5" required class="form-input w-full px-4 py-3 placeholder-amber-700 resize-none" placeholder="请输入您想说的话..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-amber-400 mb-2">验证码</label>
                        <div class="flex gap-3">
                            <input type="text" name="captcha" required maxlength="4" class="form-input w-full px-4 py-3 placeholder-amber-700 uppercase" placeholder="请输入验证码">
                            <img src="<?= SITE_URL ?>/admin/captcha.php?t=<?= time() ?>" alt="验证码" class="w-28 h-12 rounded cursor-pointer border border-night-400" onclick="this.src='<?= SITE_URL ?>/admin/captcha.php?t='+Date.now()">
                        </div>
                    </div>
                    <div id="contactResult"></div>
                    <button type="submit" class="w-full py-4 bg-gold-400 text-night-50 text-sm tracking-widest hover:bg-gold-300 transition-colors duration-300">发送消息</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
