<?php
// db.php - SQLite connection and initial schema + admin seeding
declare(strict_types=1);

$db = new PDO("sqlite:" . __DIR__ . "/database.sqlite");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create tables if they don't exist
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nick TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending', -- pending | approved | rejected
    role TEXT NOT NULL DEFAULT 'user',      -- user | admin
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,                        -- NULL means visible to all approved users (staff)
    content TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// Seed admin account if not exists
$adminNick = 'milan_1531';
$adminPassPlain = 'kielce78';
$adminPassHash = password_hash($adminPassPlain, PASSWORD_DEFAULT);

$stmt = $db->prepare("SELECT id FROM users WHERE nick = ? LIMIT 1");
$stmt->execute([$adminNick]);
$exists = $stmt->fetchColumn();

if (!$exists) {
    $ins = $db->prepare("INSERT INTO users (nick, password, status, role) VALUES (?, ?, 'approved', 'admin')");
    $ins->execute([$adminNick, $adminPassHash]);
}
?>
