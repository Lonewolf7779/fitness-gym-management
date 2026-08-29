<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';

AdminMiddleware::handle();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal | IRONCORE Fitness</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body style="background-color: var(--color-bg);">
  <div class="dashboard-shell">
    <aside class="sidebar">
      <a href="/index.php" class="brand-logo" style="margin-bottom: 2rem;">
        <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
        <span>IRONCORE</span>
      </a>

      <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-accent); font-weight: 800; margin-bottom: 1rem;">ADMINISTRATOR PORTAL</div>

      <nav style="display: flex; flex-direction: column; gap: 0.75rem;">
        <a href="/admin/index.php" class="btn btn-primary" style="justify-content: flex-start;">Overview</a>
        <a href="/index.php" class="btn btn-secondary" style="justify-content: flex-start;">View Website</a>
      </nav>

      <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--color-border);">
        <div style="font-size: 0.9rem; font-weight: 700;"><?= e($_SESSION['full_name'] ?? 'Admin') ?></div>
        <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 1rem;"><?= e($_SESSION['email'] ?? '') ?></div>
        <a href="/logout.php" class="btn btn-secondary btn-block" style="padding: 0.5rem 1rem; font-size: 0.8rem;">LOGOUT</a>
      </div>
    </aside>

    <main class="main-content">
      <h1 style="font-size: 2.2rem; margin-bottom: 0.5rem;">ADMIN CONTROL CENTER</h1>
      <p style="color: var(--color-text-muted); margin-bottom: 2.5rem;">System overview and administrative operations module.</p>

      <div class="metrics-row" style="margin-bottom: 2.5rem;">
        <div class="metric-card">
          <div class="metric-label">Total System Users</div>
          <div class="metric-value">642</div>
        </div>
        <div class="metric-card">
          <div class="metric-label">Active Trainers</div>
          <div class="metric-value">38</div>
        </div>
        <div class="metric-card">
          <div class="metric-label">Monthly Revenue</div>
          <div class="metric-value">₹2.84L</div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
