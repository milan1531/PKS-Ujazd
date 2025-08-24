<?php
declare(strict_types=1);
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require_admin();
refresh_session_user($db);

$flash = "";

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Approve / Reject
    if (isset($_POST['action'], $_POST['user_id'])) {
        $uid = (int)$_POST['user_id'];
        if ($_POST['action'] === 'approve') {
            $upd = $db->prepare("UPDATE users SET status='approved' WHERE id=?");
            $upd->execute([$uid]);
            $flash = "Użytkownik zatwierdzony.";
        } elseif ($_POST['action'] === 'reject') {
            $upd = $db->prepare("UPDATE users SET status='rejected' WHERE id=?");
            $upd->execute([$uid]);
            $flash = "Użytkownik odrzucony.";
        } elseif ($_POST['action'] === 'delete') {
            // Prevent admin from deleting self
            if ($uid === (int)$_SESSION['user']['id']) {
                $flash = "Nie możesz usunąć własnego konta.";
            } else {
                $del = $db->prepare("DELETE FROM users WHERE id=? AND role!='admin'");
                $del->execute([$uid]);
                $flash = "Konto usunięte (jeśli to nie był admin).";
            }
        }
    }

    // Add message
    if (isset($_POST['message_content'])) {
        $content = trim($_POST['message_content']);
        $to = $_POST['to'] ?? "all";
        if ($content !== "") {
            if ($to === "all") {
                $ins = $db->prepare("INSERT INTO messages (user_id, content) VALUES (NULL, ?)");
                $ins->execute([$content]);
            } else {
                $uid = (int)$to;
                $ins = $db->prepare("INSERT INTO messages (user_id, content) VALUES (?, ?)");
                $ins->execute([$uid, $content]);
            }
            $flash = "Wiadomość dodana.";
        } else {
            $flash = "Treść wiadomości nie może być pusta.";
        }
    }
}

// Load lists
$pending = $db->query("SELECT id, nick, created_at FROM users WHERE status='pending' ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
$users = $db->query("SELECT id, nick, role, status, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// For message select
$approvedUsers = $db->query("SELECT id, nick FROM users WHERE status='approved' ORDER BY nick ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/header.inc.php";
?>
<main>
  <h1>Panel administratora</h1>
  <?php if (!empty($flash)): ?>
    <div class="success"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <section class="card">
    <h2>Oczekujące konta</h2>
    <?php if (empty($pending)): ?>
      <div class="muted">Brak oczekujących rejestracji.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>Nick</th><th>Data</th><th>Akcje</th></tr></thead>
        <tbody>
        <?php foreach ($pending as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['nick']) ?></td>
            <td class="muted"><?= htmlspecialchars($p['created_at']) ?></td>
            <td class="row">
              <form method="post">
                <input type="hidden" name="user_id" value="<?= (int)$p['id'] ?>">
                <button type="submit" name="action" value="approve">Akceptuj</button>
              </form>
              <form method="post">
                <input type="hidden" name="user_id" value="<?= (int)$p['id'] ?>">
                <button type="submit" class="secondary" name="action" value="reject">Odrzuć</button>
              </form>
              <form method="post" onsubmit="return confirm('Usunąć to konto?')">
                <input type="hidden" name="user_id" value="<?= (int)$p['id'] ?>">
                <button type="submit" class="danger" name="action" value="delete">Usuń</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <section class="card">
    <h2>Wszyscy użytkownicy</h2>
    <table>
      <thead><tr><th>ID</th><th>Nick</th><th>Rola</th><th>Status</th><th>Założono</th><th>Akcje</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= htmlspecialchars($u['nick']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td><span class="status <?= htmlspecialchars($u['status']) ?>"><?= htmlspecialchars($u['status']) ?></span></td>
            <td class="muted"><?= htmlspecialchars($u['created_at']) ?></td>
            <td class="row">
              <?php if ($u['role'] !== 'admin'): ?>
                <form method="post">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" name="action" value="approve">Akceptuj</button>
                </form>
                <form method="post">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" class="secondary" name="action" value="reject">Odrzuć</button>
                </form>
                <form method="post" onsubmit="return confirm('Usunąć to konto?')">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" class="danger" name="action" value="delete">Usuń</button>
                </form>
              <?php else: ?>
                <span class="muted">Admin</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section class="card">
    <h2>Wiadomość od admina</h2>
    <form method="post">
      <label>Do:</label>
      <select name="to">
        <option value="all">Wszyscy (staff)</option>
        <?php foreach ($approvedUsers as $au): ?>
          <option value="<?= (int)$au['id'] ?>"><?= htmlspecialchars($au['nick']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Treść wiadomości:</label>
      <textarea name="message_content" rows="4" required></textarea>
      <div class="row">
        <button type="submit">Wyślij</button>
      </div>
    </form>
    <div class="muted">Wiadomości są widoczne w panelu użytkownika (dashboard).</div>
  </section>
</main>
</body>
</html>
