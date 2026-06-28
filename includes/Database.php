<?php
require_once __DIR__ . '/../config.php';

class Database
{
    private static ?mysqli $instance = null;

    public static function getInstance(): mysqli
    {
        if (self::$instance === null) {
            try {
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                self::$instance = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
                self::$instance->set_charset(DB_CHARSET);
                self::ensureDatabase(self::$instance);
                self::$instance->select_db(DB_NAME);
                self::ensureTables(self::$instance);
            } catch (\mysqli_sql_exception $e) {
                http_response_code(500);
                echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>系统错误</title><style>body{font-family:sans-serif;background:#fdf9f3;color:#2e241c;padding:40px;}.box{max-width:600px;margin:60px auto;background:#fff;padding:40px;border:1px solid #d4bfa0;border-radius:4px;}h1{color:#b05840;}</style></head><body><div class="box"><h1>⚠️ 数据库连接失败</h1><p>请检查 MySQL 是否已启动，以及 <code>config.php</code> 中的数据库配置是否正确。</p><p><strong>错误详情：</strong></p><pre style="background:#f5ede0;padding:10px;border-radius:4px;overflow:auto;">' . htmlspecialchars($e->getMessage()) . '</pre><p style="color:#8b7355;font-size:13px;margin-top:20px;">默认配置：host=' . htmlspecialchars(DB_HOST) . ', user=' . htmlspecialchars(DB_USER) . ', db=' . htmlspecialchars(DB_NAME) . '</p></div></body></html>';
                exit;
            }
        }
        return self::$instance;
    }

    private static function ensureDatabase(mysqli $mysqli): void
    {
        $safe = DB_NAME;
        $mysqli->query("CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");
    }

    private static function ensureTables(mysqli $mysqli): void
    {
        $sqlList = [
            "CREATE TABLE IF NOT EXISTS categories (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug VARCHAR(32) NOT NULL UNIQUE,
                name VARCHAR(50) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS artworks (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                category_id INT UNSIGNED NOT NULL,
                year SMALLINT UNSIGNED,
                medium VARCHAR(100),
                size_desc VARCHAR(200),
                cover VARCHAR(500),
                description TEXT,
                content LONGTEXT,
                views INT UNSIGNED DEFAULT 0,
                sort INT DEFAULT 0,
                is_featured TINYINT(1) DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_category (category_id),
                CONSTRAINT fk_art_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS posts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                cover VARCHAR(500),
                tag VARCHAR(50),
                summary VARCHAR(500),
                content LONGTEXT,
                views INT UNSIGNED DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS exhibitions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                type VARCHAR(50),
                start_date DATE,
                end_date DATE,
                venue VARCHAR(200),
                description TEXT,
                is_upcoming TINYINT(1) DEFAULT 0,
                sort INT DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS messages (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL,
                subject VARCHAR(200),
                message TEXT,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS subscribers (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(150) NOT NULL UNIQUE,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS admins (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS site_configs (
                k VARCHAR(100) PRIMARY KEY,
                v TEXT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS links (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                url VARCHAR(500) NOT NULL,
                sort INT DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];

        foreach ($sqlList as $sql) {
            $mysqli->query($sql);
        }

        // 初始化默认管理员 (admin / admin123)
        $pwHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
        $stmt->bind_param('s', $u);
        $u = 'admin';
        $stmt->execute();
        $count = (int)($stmt->get_result()->fetch_row()[0] ?? 0);
        $stmt->close();
        if ($count === 0) {
            $stmt = $mysqli->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
            $stmt->bind_param('ss', $u, $pwHash);
            $stmt->execute();
            $stmt->close();
        }

        self::seedDemoData($mysqli);
    }

    private static function seedDemoData(mysqli $mysqli): void
    {
        // 分类
        $cats = [
            ['oil', '油画'],
            ['watercolor', '水彩'],
            ['illustration', '插画'],
            ['sketch', '素描'],
            ['print', '版画'],
            ['mixed', '综合材料']
        ];
        $stmt = $mysqli->prepare("INSERT IGNORE INTO categories (slug, name) VALUES (?, ?)");
        foreach ($cats as $c) {
            $stmt->bind_param('ss', $c[0], $c[1]);
            $stmt->execute();
        }
        $stmt->close();

        // 示例作品（若为空时插入）
        $count = (int)($mysqli->query("SELECT COUNT(*) FROM artworks")->fetch_row()[0]);
        if ($count === 0) {
            $oil = $mysqli->query("SELECT id FROM categories WHERE slug='oil'")->fetch_assoc()['id'];
            $water = $mysqli->query("SELECT id FROM categories WHERE slug='watercolor'")->fetch_assoc()['id'];
            $illu = $mysqli->query("SELECT id FROM categories WHERE slug='illustration'")->fetch_assoc()['id'];
            $sketch = $mysqli->query("SELECT id FROM categories WHERE slug='sketch'")->fetch_assoc()['id'];

            $works = [
                ['湖畔黄昏', $oil, 2024, '布面油画', '80×100cm',
                 'https://picsum.photos/seed/lakeside-golden-hour/500/625',
                 '湖畔的黄昏，光与影的交响', '在柔和的金色光线下，湖畔景色静谧悠远。', 0],
                ['樱花纷飞', $water, 2024, '纸本水彩', '40×50cm',
                 'https://picsum.photos/seed/cherry-blossoms-spring/500/625',
                 '春日樱花', '樱花纷飞的季节，水彩的透明感呈现花瓣的轻盈。', 1],
                ['蘑菇下的阅读时光', $illu, 2023, '数码绘画', '系列作品',
                 'https://picsum.photos/seed/mushroom-reading-girl/500/625',
                 '插画作品', '奇幻森林中的阅读时光。', 2],
                ['岁月', $sketch, 2023, '炭笔素描', '50×65cm',
                 'https://picsum.photos/seed/charcoal-portrait-wise/500/625',
                 '人物素描', '岁月的痕迹在素描线条中流淌。', 3],
                ['灯下', $oil, 2023, '布面油画', '60×80cm',
                 'https://picsum.photos/seed/lamp-cozy-interior/500/625',
                 '室内油画', '温暖灯光下的静谧书房。', 4],
                ['地中海夏日', $water, 2022, '纸本水彩', '30×40cm',
                 'https://picsum.photos/seed/mediterranean-coast-village/500/625',
                 '地中海海岸', '夏日海岸的明亮色彩。', 5]
            ];
            $stmt = $mysqli->prepare("INSERT INTO artworks (title, category_id, year, medium, size_desc, cover, description, content, sort) VALUES (?,?,?,?,?,?,?,?,?)");
            foreach ($works as $w) {
                $stmt->bind_param('siisssssi', $w[0], $w[1], $w[2], $w[3], $w[4], $w[5], $w[6], $w[7], $w[8]);
                $stmt->execute();
            }
            $stmt->close();
        }

        // 博客示例
        $count = (int)($mysqli->query("SELECT COUNT(*) FROM posts")->fetch_row()[0]);
        if ($count === 0) {
            $posts = [
                ['调色盘上的色彩哲学', '技法心得', '2024.03.15',
                 'https://picsum.photos/seed/artist-palette-oil/600/375',
                 '色彩不仅是视觉的呈现，更是情感的载体……', '调色是一门需要用心感受的学问……'],
                ['写生之旅：寻找光的痕迹', '写生随记', '2024.02.28',
                 'https://picsum.photos/seed/misty-morning-lone-tree/600/375',
                 '清晨五点半起床，背着画具走在青石板路上……', '写生的乐趣在于捕捉稍纵即逝的光线。'],
                ['素描入门：从线条开始', '教学分享', '2024.01.20',
                 'https://picsum.photos/seed/sketchbook-pencil-cafe/600/375',
                 '很多初学者问我，素描最重要的是什么……', '观察，是所有绘画的起点。']
            ];
            $stmt = $mysqli->prepare("INSERT INTO posts (title, tag, created_at, cover, summary, content) VALUES (?,?,?,?,?,?)");
            foreach ($posts as $p) {
                $dt = date('Y-m-d H:i:s', strtotime(str_replace('.', '-', $p[2])));
                $stmt->bind_param('ssssss', $p[0], $p[1], $dt, $p[3], $p[4], $p[5]);
                $stmt->execute();
            }
            $stmt->close();
        }

        // 展览示例
        $count = (int)($mysqli->query("SELECT COUNT(*) FROM exhibitions")->fetch_row()[0]);
        if ($count === 0) {
            $exhibs = [
                ['「光影之间」个人画展', '个展', '2025-04-15', '2025-05-30', '北京798艺术区 · 桥艺术空间', '以「光」为主题的三十余幅作品', 1, 1],
                ['「新生代」青年艺术家联展', '群展', '2024-09-01', '2024-10-15', '上海当代艺术博物馆', '与十二位青年艺术家共同参展', 0, 2],
                ['「春日絮语」水彩个展', '个展', '2024-03-10', '2024-04-20', '杭州西湖美术馆', '以春天为主题的水彩作品个展', 0, 3],
                ['全国青年美术作品展 · 优秀奖', '获奖', '2023-11-05', '2023-11-05', '中国美术馆 · 北京', '作品《岁月》获油画类优秀奖', 0, 4],
                ['「初见」毕业作品展', '个展', '2023-05-20', '2023-06-30', '中央美术学院美术馆', '硕士毕业个人展览', 0, 5]
            ];
            $stmt = $mysqli->prepare("INSERT INTO exhibitions (title, type, start_date, end_date, venue, description, is_upcoming, sort) VALUES (?,?,?,?,?,?,?,?)");
            foreach ($exhibs as $e) {
                $stmt->bind_param('ssssssii', $e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $e[6], $e[7]);
                $stmt->execute();
            }
            $stmt->close();
        }

        // 站点默认配置
        $defaults = [
            'site_title' => '林夕 — 艺术家个人网站',
            'site_keywords' => '林夕,艺术家,油画,水彩,插画,素描',
            'site_description' => '林夕个人艺术作品集',
            'artist_name' => '林夕',
            'artist_en' => 'Lin Xi',
            'artist_email' => 'linxi.art@email.com',
            'artist_studio' => '北京市朝阳区酒仙桥路4号 798艺术区 创意广场B座',
            'artist_hours' => '周二至周日 10:00 - 18:00',
            'bio' => '我是林夕，一名专注于油画与水彩创作的独立艺术家。毕业于中央美术学院油画系，现居北京。',
            'philosophy' => '绘画于我而言，不仅是技艺的展现，更是心灵的对话。我追求在每一笔色彩中注入情感的温度，让静止的画面拥有流动的生命力。'
        ];
        $stmt = $mysqli->prepare("INSERT IGNORE INTO site_configs (k, v) VALUES (?,?)");
        foreach ($defaults as $k => $v) {
            $stmt->bind_param('ss', $k, $v);
            $stmt->execute();
        }
        $stmt->close();
    }
}
