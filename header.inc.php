<?php
// header.inc.php - shared topbar styles and header
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PKS Ujazd</title>
  <style>
    :root {
      --yellow: #FFD700;
      --bg: #ffffff;
      --text: #000000;
      --muted: #666;
      --danger: #b00020;
      --ok: #0a7d00;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; background: var(--bg); color: var(--text); }
    .topbar {
      width: 100%;
      background-color: var(--yellow);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 20px;
      font-weight: bold;
      color: var(--text);
    }
    .brand { font-size: 16px; text-transform: uppercase; }
    .tabs { display: flex; gap: 15px; }
    .tab a { text-decoration: none; color: var(--text); }
    .tab.active a { text-decoration: underline; }
    .clock { font-variant-numeric: tabular-nums; }
    main { max-width: 900px; margin: 30px auto; padding: 0 16px; }
    form { display: grid; gap: 8px; margin: 14px 0 24px; max-width: 400px; }
    input[type="text"], input[type="password"], textarea, select {
      padding: 10px; border: 1px solid #ccc; border-radius: 8px;
    }
    button {
      padding: 10px 14px; border-radius: 10px; border: none; cursor: pointer; font-weight: bold;
      background: #111; color: #fff;
    }
    button.secondary { background: #444; }
    button.danger { background: var(--danger); }
    .row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .card { border: 1px solid #eee; border-radius: 14px; padding: 14px; margin: 10px 0; }
    .muted { color: var(--muted); }
    .status { font-size: 12px; padding: 4px 8px; border-radius: 10px; background: #f1f1f1; }
    .status.pending { background: #fff3cd; }
    .status.approved { background: #d1e7dd; }
    .status.rejected { background: #f8d7da; }
    table { width: 100%; border-collapse: collapse; margin: 12px 0; }
    th, td { text-align: left; border-bottom: 1px solid #eee; padding: 8px; }
    .info { padding: 8px 12px; background: #eef6ff; border: 1px solid #cfe2ff; border-radius: 8px; margin: 8px 0; }
    .error { padding: 8px 12px; background: #fdecea; border: 1px solid #f5c2c7; border-radius: 8px; margin: 8px 0; }
    .success { padding: 8px 12px; background: #e9f7ef; border: 1px solid #badbcc; border-radius: 8px; margin: 8px 0; }
    .right { margin-left: auto; }
  </style>
</head>
<body>
<header class="topbar">
  <div class="brand">PKS Ujazd</div>
  <div class="tabs">
    <div class="tab active"><a href="index.php">System Stafff</a></div>
    <?php if (isset($_SESSION['user'])): ?>
      <?php if (($_SESSION['user']['role'] ?? 'user') === 'admin'): ?>
        <div class="tab"><a href="admin.php">Panel Admina</a></div>
      <?php else: ?>
        <div class="tab"><a href="dashboard.php">Panel</a></div>
      <?php endif; ?>
      <div class="tab"><a href="logout.php">Wyloguj</a></div>
    <?php endif; ?>
  </div>
  <div class="clock" id="clock">--:--:--</div>
</header>
<script>
  function updateClock() {
    const now = new Date();
    const time = now.toLocaleTimeString('pl-PL', { hour12: false });
    const el = document.getElementById('clock');
    if (el) el.textContent = time;
  }
  setInterval(updateClock, 1000);
  updateClock();
</script>
