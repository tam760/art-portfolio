<?php
require_once __DIR__ . '/../config.php';
$current = $current ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $siteTitle = Site::config('site_title', SITE_NAME);
    $pageTitle = $page_title ?? '';
    $title = $pageTitle ? $pageTitle . ' · ' . $siteTitle : $siteTitle;
    ?>
    <title><?= Site::h($title) ?></title>
    <meta name="keywords" content="<?= Site::h(Site::config('site_keywords', '')) ?>">
    <meta name="description" content="<?= Site::h(Site::config('site_description', '')) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ma+Shan+Zheng&family=Noto+Serif+SC:wght@300;400;500;600;700;900&family=ZCOOL+XiaoWei&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        /* 深靛蓝背景系（替代原warm暖色） */
                        night: { 50:'#0f1923', 100:'#1a2a3a', 200:'#1e3040', 300:'#2a4055', 400:'#3a5570', 500:'#4a6a85', 600:'#6a8aa0', 700:'#8aaac0', 800:'#c0d0dd', 900:'#d8e8f0' },
                        /* 暖琥珀文字系（替代原ink墨色，作为亮文字） */
                        amber: { 50:'#fdf9f3', 100:'#f5ede0', 200:'#e8d9c4', 300:'#d4bfa0', 400:'#c4a882', 500:'#a68b6b', 600:'#8b7355', 700:'#6b5344', 800:'#4a3b2f', 900:'#2e241c' },
                        /* 赭金强调色（替代原ochre） */
                        gold: { 300:'#d4b080', 400:'#c4956a', 500:'#b08050', 600:'#9a6b40' },
                        /* 锈红保留 */
                        rust: { 400:'#c4705a', 500:'#b05840', 600:'#9a4838' }
                    },
                    fontFamily: {
                        'hand': ['"Ma Shan Zheng"', 'cursive'],
                        'serif': ['"Noto Serif SC"', 'serif'],
                        'display': ['"ZCOOL XiaoWei"', 'serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="antialiased bg-night-50 text-amber-200">

<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 bg-night-50/95 backdrop-blur-sm border-b border-night-300/50">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <a href="<?= SITE_URL ?>/" class="flex items-center gap-3">
                <span class="font-hand text-2xl lg:text-3xl text-amber-50"><?= Site::h(Site::config('artist_name','琪琪')) ?></span>
                <span class="hidden sm:block text-xs text-amber-500 tracking-widest uppercase font-serif">Artist Portfolio</span>
            </a>
            <div class="hidden lg:flex items-center gap-10">
                <a href="<?= SITE_URL ?>/#home" class="nav-link <?= $current==='home'?'active':'' ?> text-sm tracking-wide text-amber-300">首页</a>
                <a href="<?= SITE_URL ?>/#portfolio" class="nav-link text-sm tracking-wide text-amber-400">作品集</a>
                <a href="<?= SITE_URL ?>/#about" class="nav-link text-sm tracking-wide text-amber-400">关于我</a>
                <a href="<?= SITE_URL ?>/#blog" class="nav-link text-sm tracking-wide text-amber-400">创作笔记</a>
                <a href="<?= SITE_URL ?>/#exhibitions" class="nav-link text-sm tracking-wide text-amber-400">展览</a>
                <a href="<?= SITE_URL ?>/#contact" class="nav-link text-sm tracking-wide text-amber-400">联系</a>
            </div>
            <button id="menuBtn" class="lg:hidden p-2 text-amber-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>
</nav>

<div id="mobileMenu" class="mobile-menu fixed inset-y-0 right-0 w-72 bg-night-100 z-50 shadow-2xl lg:hidden">
    <div class="p-6">
        <button id="closeMenuBtn" class="absolute top-6 right-6 p-2 text-amber-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="mt-16 flex flex-col gap-6">
            <a href="<?= SITE_URL ?>/#home" class="text-lg text-amber-200 font-display">首页</a>
            <a href="<?= SITE_URL ?>/#portfolio" class="text-lg text-amber-400 font-display">作品集</a>
            <a href="<?= SITE_URL ?>/#about" class="text-lg text-amber-400 font-display">关于我</a>
            <a href="<?= SITE_URL ?>/#blog" class="text-lg text-amber-400 font-display">创作笔记</a>
            <a href="<?= SITE_URL ?>/#exhibitions" class="text-lg text-amber-400 font-display">展览</a>
            <a href="<?= SITE_URL ?>/#contact" class="text-lg text-amber-400 font-display">联系</a>
        </div>
        <div class="mt-12 pt-8 border-t border-night-300">
            <p class="font-hand text-2xl text-amber-500"><?= Site::h(Site::config('artist_name','林夕')) ?></p>
            <p class="text-sm text-amber-600 mt-2">绘画 · 插画 · 艺术创作</p>
        </div>
    </div>
</div>
<div id="menuOverlay" class="fixed inset-0 bg-night-900/30 z-40 hidden lg:hidden"></div>

<main>
