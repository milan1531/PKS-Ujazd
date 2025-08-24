<?php
// auth.php - session helpers
declare(strict_types=1);
session_start();

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php");
        exit;
    }
}

function require_admin() {
    require_login();
    if (($_SESSION['user']['role'] ?? 'user') !== 'admin') {
        header("Location: dashboard.php");
        exit;
    }
}

// Reload user from DB to keep session fresh (role/status changes)
function refresh_session_user(PDO $db) {
    if (!isset($_SESSION['user']['id'])) return;
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user']['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['user'] = $user;
    }
}
?>
