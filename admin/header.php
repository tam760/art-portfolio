<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';

$current = $current ?? 'dashboard';
$flash = Site::flash('admin_msg');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 · <?= Site::h(Site::config('site_title', SITE_NAME)) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        night: { 50:'#0f1923', 100:'#1a2a3a', 200:'#1e3040', 300:'#2a4055', 400:'#3a5570', 500:'#4a6a85', 600:'#6a8aa0', 700:'#8aaac0', 800:'#c0d0dd', 900:'#d8e8f0' },
                        amber: { 50:'#fdf9f3', 100:'#f5ede0', 200:'#e8d9c4', 300:'#d4bfa0', 400:'#c4a882', 500:'#a68b6b', 600:'#8b7355', 700:'#6b5344', 800:'#4a3b2f', 900:'#2e241c' },
                        gold: { 300:'#d4b080', 400:'#c4956a', 500:'#b08050', 600:'#9a6b40' },
                        rust: { 400:'#c4705a', 500:'#b05840', 600:'#9a4838' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Noto Serif SC', serif; background:#1a2a3a; color:#e8d9c4; }
        .sidebar a.active { background:#c4956a; color:#0f1923; }
        table.admin th { background:#1e3040; font-weight:600; text-align:left; padding:10px 12px; font-size:13px; color:#e8d9c4; }
        table.admin td { padding:12px; border-bottom:1px solid #2a4055; font-size:14px; vertical-align:top; color:#d4bfa0; }
        table.admin tr:hover td { background:#1e3040; }
        .btn { display:inline-block; padding:6px 14px; border-radius:4px; font-size:13px; transition:all .2s; cursor:pointer; }
        .btn-primary { background:#c4956a; color:#0f1923; }
        .btn-primary:hover { background:#d4b080; }
        .btn-secondary { background:#4a6a85; color:#e8d9c4; }
        .btn-secondary:hover { background:#6a8aa0; }
        .btn-danger { background:#9a4838; color:#e8d9c4; }
        .btn-danger:hover { background:#7a3828; }
        .btn-ghost { background:transparent; color:#a68b6b; border:1px solid #3a5570; }
        .btn-ghost:hover { background:#1e3040; }
        .form-row { margin-bottom:16px; }
        .form-row label { display:block; margin-bottom:6px; color:#e8d9c4; font-size:14px; font-weight:500; }
        .form-row input[type=text], .form-row input[type=number], .form-row input[type=date], .form-row input[type=email], .form-row select, .form-row textarea {
            width:100%; padding:10px 12px; border:1px solid #2a4055; border-radius:4px; background:#0f1923; font-size:14px; color:#e8d9c4;
        }
        .form-row input:focus, .form-row select:focus, .form-row textarea:focus { outline:none; border-color:#c4956a; box-shadow:0 0 0 3px rgba(196,149,106,.1); }
        .form-row textarea { min-height:140px; resize:vertical; }
        .badge-ok { background:#1a3a28; color:#c4e8a0; padding:3px 10px; border-radius:12px; font-size:12px; }
        .badge-warn { background:#3a1a1a; color:#f5a0a0; padding:3px 10px; border-radius:12px; font-size:12px; }
    </style>
</head>
<body>
<div class="flex min-h-screen">
    <aside class="w-60 bg-night-50 text-amber-300 min-h-screen">
        <div class="p-6 border-b border-night-300">
            <p class="text-2xl font-display text-amber-50"><?= Site::h(Site::config('artist_name', '林夕')) ?></p>
            <p class="text-xs text-amber-600 mt-1">艺术家后台管理</p>
        </div>
        <nav class="p-4 space-y-1 sidebar">
            <a href="<?= SITE_URL ?>/admin/" class="flex items-center gap-3 px-4 py-3 rounded text-sm hover:bg-night-200 <?= $current==='dashboard'?'active':'' ?>">📊 概览</a>
            <a href="<?= SITE_URL ?>/admin/artworks.php" class="flex items-center gap-3 px-4 py-3 rounded text-sm hover:bg-night-200 <?= $current==='artworks'?'active':'' ?>">🎨 作品集</a>
            <a href="<?= SITE_URL ?>/admin/categories.php" class="flex items-center gap-3 px-4 py-3 rounded text-sm hover:bg-night-200 <?= $current==='categories'?'active':'' ?>">🏷️ 分类</a>
            <a href="<?= SITE_URL ?>/admin/posts.php" class="flex items-center gap-3 px-4 py-3 rounded text-sm hover:bg-night-200 <?= $current==='posts'?'active':'' ?>">📝 博客</a>
            <a href="<?= SITE_URL ?>/admin/exhibitions.php" class="flex items-center gap-3 px-4 py-3 rounded text-sm hover:bg-night-200 <?= $current==='exhibitions'?'active':'' ?>">🖼️ 展览</a>
            <a href="<?= SITE_URL ?>/admin/messages.php" class="flex items-center gap-3 px-4 py-3 rounded text-sm hover:bg-night-200 <?= $current==='messages'?'active':'' ?>">✉️ 留言</a>
            <a href="<?= SITE_URL ?>/admin/subscribers.php" class="flex items-center gap-3 px-4 py-3 rounded text-sm hover:bg-night-200 <?= $current==='subscribers'?'active':'' ?>">📮 订阅</a>
            <a href="<?= SITE_URL ?>/admin/links.php" class="flex items-center gap-3 px-4 py-3 rounded text-sm hover:bg-night-200 <?= $current==='links'?'active':'' ?>">🔗 友情链接</a>
            <a href="<?= SITE_URL ?>/admin/settings.php" class="flex items-center gap-3 px-4 py-3 rounded text-sm hover:bg-night-200 <?= $current==='settings'?'active':'' ?>">⚙️ 设置</a>
        </nav>
        <div class="p-4 mt-8 border-t border-night-300">
            <a href="<?= SITE_URL ?>/" target="_blank" class="block w-full text-center py-2 rounded bg-night-200 hover:bg-night-300 text-amber-100 text-sm mb-2">查看前台</a>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="block w-full text-center py-2 rounded text-amber-500 hover:bg-night-200 text-sm">退出登录</a>
        </div>
    </aside>
    <main class="flex-1 p-8">
        <?php if ($flash): ?>
        <div class="mb-4 px-4 py-3 rounded bg-night-200 text-gold-400 text-sm"><?= Site::h($flash) ?></div>
        <?php endif; ?>
