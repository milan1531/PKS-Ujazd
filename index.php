<?php
declare(strict_types=1);
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";

// If logged in, redirect
if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header("Location: admin.php"); exit;
    } else {
        header("Location: dashboard.php"); exit;
    }
}

$msg = "";

// Handle login/register
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $nick = trim($_POST['nick'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $db->prepare("SELECT * FROM users WHERE nick = ? LIMIT 1");
        $stmt->execute([$nick]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'approved') {
                $msg = "Twoje konto ma status: {$user['status']}. Poczekaj na akceptację admina.";
            } else {
                $_SESSION['user'] = $user;
                header("Location: " . ($user['role']==='admin' ? "admin.php" : "dashboard.php"));
                exit;
            }
        } else {
            $msg = "Błędny nick lub hasło.";
        }
    } elseif (isset($_POST['register'])) {
        $nick = trim($_POST['nick'] ?? '');
        $password = $_POST['password'] ?? '';

        if (strlen($nick) < 3 || strlen($password) < 6) {
            $msg = "Nick min. 3 znaki, hasło min. 6 znaków.";
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $db->prepare("INSERT INTO users (nick, password, status, role) VALUES (?, ?, 'pending', 'user')");
                $ins->execute([$nick, $hash]);
                $msg = "Rejestracja zakończona. Czekaj na akceptację admina.";
            } catch (Exception $e) {
                $msg = "Taki nick już istnieje.";
            }
        }
    }
}

include __DIR__ . "/header.inc.php";
?>
<main>
  <h1>Logowanie do systemu</h1>
  <form method="post" autocomplete="off">
    <label>Nick:</label>
    <input type="text" name="nick" required>
    <label>Hasło:</label>
    <input type="password" name="password" required>
    <div class="row">
      <button type="submit" name="login">Zaloguj</button>
    </div>
  </form>

  <h2>Rejestracja</h2>
  <form method="post" autocomplete="off">
    <label>Nick:</label>
    <input type="text" name="nick" required>
    <label>Hasło:</label>
    <input type="password" name="password" required>
    <div class="row">
      <button type="submit" name="register">Zarejestruj</button>
    </div>
  </form>

  <?php if (!empty($msg)): ?>
    <div class="info"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="muted">Po rejestracji konto czeka na akceptację administratora.</div>
</main>
</body>
</html>
