<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Site.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Site::requireAdmin();
    $action = $_POST['action'] ?? '';
    if ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        Site::redirect(SITE_URL . '/admin/login.php');
    }
}
Site::redirect(SITE_URL . '/admin/login.php');
