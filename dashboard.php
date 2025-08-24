<?php
declare(strict_types=1);
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require_login();
refresh_session_user($db);

// Only approved users may view
if ($_SESSION['user']['status'] !== 'approved') {
    header("Location: index.php"); exit;
}

include __DIR__ . "/header.inc.php";

// Fetch messages for this user (private) and for all staff (user_id IS NULL)
$stmt = $db->prepare("SELECT m.*, u.nick AS to_nick
    FROM messages m
    LEFT JOIN users u ON m.user_id = u.id
    WHERE (m.user_id IS NULL OR m.user_id = ?)
    ORDER BY m.created_at DESC, m.id DESC");
$stmt->execute([$_SESSION['user']['id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<main>
  <h1>Panel pracownika</h1>
  <p>Witaj, <strong><?= htmlspecialchars($_SESSION['user']['nick']) ?></strong>!</p>

  <?php if (empty($messages)): ?>
    <div class="info">Brak wiadomości od administratora.</div>
  <?php else: ?>
    <h2>Informacje od admina</h2>
    <?php foreach ($messages as $m): ?>
      <div class="card">
        <div class="muted"><?= htmlspecialchars($m['created_at']) ?> <?= $m['user_id'] ? "(do Ciebie)" : "(dla całego staffu)" ?></div>
        <div><?= nl2br(htmlspecialchars($m['content'])) ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</main>
</body>
</html>
