<?php
require_once __DIR__ . '/../config.php';
$artistName = Site::config('artist_name', '林夕');
$artistEmail = Site::config('artist_email', 'linxi.art@email.com');
$artistStudio = Site::config('artist_studio', '');
$links = [];
try {
    $db = Site::db();
    $links = $db->query("SELECT * FROM links ORDER BY sort ASC, id DESC")->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {}
?>
</main>

<footer class="bg-night-50 text-amber-300 py-16 border-t border-night-300/30">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid md:md-cols-3 gap-12 mb-12">
            <div>
                <p class="font-hand text-3xl text-amber-50 mb-4"><?= Site::h($artistName) ?></p>
                <p class="text-sm leading-relaxed text-amber-500">
                    以画笔记录时光，<br>在色彩与线条之间寻找诗意。
                </p>
            </div>
            <div>
                <p class="text-amber-100 text-sm tracking-widest uppercase mb-4">导航</p>
                <div class="space-y-2">
                    <a href="<?= SITE_URL ?>/#home" class="block text-sm text-amber-500 hover:text-amber-100 transition-colors">首页</a>
                    <a href="<?= SITE_URL ?>/#portfolio" class="block text-sm text-amber-500 hover:text-amber-100 transition-colors">作品集</a>
                    <a href="<?= SITE_URL ?>/#about" class="block text-sm text-amber-500 hover:text-amber-100 transition-colors">关于我</a>
                    <a href="<?= SITE_URL ?>/#blog" class="block text-sm text-amber-500 hover:text-amber-100 transition-colors">创作笔记</a>
                    <a href="<?= SITE_URL ?>/#exhibitions" class="block text-sm text-amber-500 hover:text-amber-100 transition-colors">展览</a>
                    <a href="<?= SITE_URL ?>/#contact" class="block text-sm text-amber-500 hover:text-amber-100 transition-colors">联系</a>
                </div>
            </div>
            <div>
                <p class="text-amber-100 text-sm tracking-widest uppercase mb-4">订阅动态</p>
                <p class="text-sm text-amber-500 mb-4">获取最新展览信息与创作分享</p>
                <form action="<?= SITE_URL ?>/subscribe.php" method="post" class="space-y-3">
                    <input type="email" name="email" required placeholder="您的邮箱" class="w-full px-4 py-2 bg-night-200 border border-night-400 text-amber-100 text-sm placeholder-amber-600 focus:outline-none focus:border-gold-400">
                    <div class="flex gap-2">
                        <input type="text" name="captcha" required maxlength="4" placeholder="验证码" class="flex-1 px-4 py-2 bg-night-200 border border-night-400 text-amber-100 text-sm placeholder-amber-600 focus:outline-none focus:border-gold-400 uppercase">
                        <img src="<?= SITE_URL ?>/admin/captcha.php?t=<?= time() ?>" alt="验证码" class="w-24 h-10 rounded cursor-pointer border border-night-400" onclick="this.src='<?= SITE_URL ?>/admin/captcha.php?t='+Date.now()">
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-gold-400 text-night-50 text-sm hover:bg-gold-300 transition-colors">订阅</button>
                </form>
                <?php $subMsg = Site::flash('subscribe_msg'); if ($subMsg): ?>
                <p class="text-xs text-gold-400 mt-2"><?= Site::h($subMsg) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="deco-line mb-8"></div>
        <?php if (!empty($links)): ?>
        <div class="flex flex-wrap gap-6 mb-8 justify-center">
            <?php foreach ($links as $link): ?>
            <a href="<?= Site::h($link['url']) ?>" target="_blank" rel="noopener" class="text-sm text-amber-500 hover:text-amber-100 transition-colors"><?= Site::h($link['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-amber-600">&copy; <?= date('Y') ?> <?= Site::h($artistName) ?>. All rights reserved.</p>
            <?php $icpCode = Site::config('icp_code'); if ($icpCode): ?>
            <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener" class="text-xs text-amber-600 hover:text-amber-400 transition-colors"><?= Site::h($icpCode) ?></a>
            <?php endif; ?>
        </div>
    </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
</body>
</html>
