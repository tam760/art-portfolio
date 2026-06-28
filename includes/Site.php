<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Database.php';

class Site
{
    private static ?mysqli $db = null;

    public static function db(): mysqli
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function config($key, $default = ''): string
    {
        $stmt = self::db()->prepare("SELECT v FROM site_configs WHERE k = ?");
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ? $row['v'] : $default;
    }

    public static function updateConfig(array $data): void
    {
        $stmt = self::db()->prepare("INSERT INTO site_configs (k, v) VALUES (?,?) ON DUPLICATE KEY UPDATE v = VALUES(v)");
        foreach ($data as $k => $v) {
            $stmt->bind_param('ss', $k, $v);
            $stmt->execute();
        }
        $stmt->close();
    }

    public static function h($str): string
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }

    /** Normalize image path to absolute URL
     *  - Local relative paths (uploads/...) => SITE_URL + '/' + path
     *  - External URLs (http/https) => keep as-is
     */
    public static function imageUrl($path): string
    {
        if (empty($path)) return '';
        $path = (string)$path;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }
        return SITE_URL . '/' . $path;
    }

    /** Get CSS aspect-ratio value from local image dimensions
     *  Returns e.g. "4/5", "16/9", "1/1" or empty string if unknown
     */
    public static function imageAspect($path): string
    {
        if (empty($path)) return '';
        $path = (string)$path;
        // External URLs: can't reliably check dimensions
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return '';
        // Resolve local path - path is like "uploads/xxx"
        $local = UPLOAD_DIR . '/../' . $path;
        if (!file_exists($local)) return '';
        $info = getimagesize($local);
        if (!$info) return '';
        $w = $info[0];
        $h = $info[1];
        if ($w <= 0 || $h <= 0) return '';
        // Simplify ratio using GCD
        $gcd = self::gcd($w, $h);
        $rw = (int)($w / $gcd);
        $rh = (int)($h / $gcd);
        // Clamp large ratios for readability
        if ($rw > 20 || $rh > 20) {
            $rw = round($w / min($w, $h) * 4);
            $rh = round($h / min($w, $h) * 4);
            // Simplify again
            $gcd2 = self::gcd((int)$rw, (int)$rh);
            $rw = (int)($rw / $gcd2);
            $rh = (int)($rh / $gcd2);
        }
        return "$rw/$rh";
    }

    private static function gcd(int $a, int $b): int
    {
        while ($b !== 0) {
            $t = $b;
            $b = $a % $b;
            $a = $t;
        }
        return $a;
    }

    public static function redirect($url): void
    {
        header("Location: $url");
        exit;
    }

    public static function flash($key, $value = null): ?string
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $v = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $v;
    }

    public static function isAdmin(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            self::redirect(SITE_URL . '/admin/login.php');
        }
    }

    public static function uploadImage($file, $dir = 'artworks', $maxWidth = 1200, $maxHeight = 1600): ?string
    {
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allow = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allow)) {
            return null;
        }
        $sub = trim($dir, '/') . '/' . date('Ymd');
        $targetDir = UPLOAD_DIR . '/' . $sub;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $name = time() . '_' . bin2hex(random_bytes(4)) . '.webp';
        $target = $targetDir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return null;
        }
        
        $srcInfo = getimagesize($target);
        if ($srcInfo === false) {
            unlink($target);
            return null;
        }
        $srcWidth = $srcInfo[0];
        $srcHeight = $srcInfo[1];
        
        $ratio = min($maxWidth / $srcWidth, $maxHeight / $srcHeight);
        $newWidth = (int)($srcWidth * $ratio);
        $newHeight = (int)($srcHeight * $ratio);
        
        $srcImage = match ($srcInfo[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($target),
            IMAGETYPE_PNG => imagecreatefrompng($target),
            IMAGETYPE_GIF => imagecreatefromgif($target),
            IMAGETYPE_WEBP => imagecreatefromwebp($target),
            default => null,
        };
        
        if ($srcImage === null) {
            unlink($target);
            return null;
        }
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        
        imagecopyresampled($newImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
        imagedestroy($srcImage);
        
        imagewebp($newImage, $target, 80);
        imagedestroy($newImage);
        
        return UPLOAD_URL . '/' . $sub . '/' . $name;
    }
}
