<?php
/**
 * IRONCORE Admin Settings Placeholder View
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';

AdminMiddleware::handle();

$adminName  = $_SESSION['full_name'] ?? 'System Admin';
$adminEmail = $_SESSION['email'] ?? 'admin@ironcore.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Settings | IRONCORE</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body style="background-color: var(--color-bg);">
  <div class="dashboard-shell">
    <div class="sidebar-overlay"></div>
    <aside class="sidebar">
      <div class="sidebar-brand">
        <a href="/index.php" class="brand-logo">
          <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
          <span>IRONCORE</span>
        </a>
        <span class="sidebar-badge">ADMINISTRATOR CONTROL</span>
      </div>

      <ul class="sidebar-nav">
        <li><a href="/admin/index.php" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg><span>Dashboard</span></a></li>
        <li><a href="/admin/members.php" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><span>Members</span></a></li>
        <li><a href="/admin/trainers.php" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg><span>Trainers</span></a></li>
        <li><a href="/admin/memberships.php" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg><span>Memberships</span></a></li>
        <li><a href="/admin/attendance.php" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg><span>Attendance</span></a></li>
        <li><a href="/admin/payments.php" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span>Payments</span></a></li>
        <li><a href="/admin/workouts.php" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg><span>Workouts</span></a></li>
        <li><a href="/admin/reports.php" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Reports</span></a></li>
      </ul>

      <div class="sidebar-footer">
        <div class="user-profile-badge">
          <div class="avatar-circle"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
          <div class="user-info">
            <div class="user-name"><?= e($adminName) ?></div>
            <div class="user-role">Super Admin</div>
          </div>
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <a href="/admin/settings.php" class="btn btn-secondary" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; justify-content: center;">Settings</a>
          <a href="/logout.php" class="btn btn-primary" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; justify-content: center;">Logout</a>
        </div>
      </div>
    </aside>

    <div class="main-wrapper">
      <header class="dashboard-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <button class="admin-mobile-toggle" aria-label="Toggle navigation drawer">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>
          <div class="header-title-group">
            <h1>SYSTEM SETTINGS</h1>
            <p>Gym branding settings, security parameters, & backup options.</p>
          </div>
        </div>
      </header>

      <main class="dashboard-body">
        <div class="panel-card" style="padding: 3rem; text-align: center; max-width: 720px; margin: 2rem auto;">
          <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(232,255,0,0.1); border: 1px solid rgba(232,255,0,0.3); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--color-accent);">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          </div>
          <span class="status-pill warning" style="margin-bottom: 1rem; display: inline-block;">MODULE UNDER DEVELOPMENT</span>
          <h2 style="font-size: 1.8rem; margin-bottom: 0.75rem;">SYSTEM SETTINGS</h2>
          <p style="color: var(--color-text-muted); margin-bottom: 2rem; line-height: 1.6;">This section will manage facility details, operating hours, role permissions, and database backup configurations. Database integration is planned for the next development phase.</p>
          <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="/admin/index.php" class="btn btn-primary">BACK TO DASHBOARD</a>
            <a href="/admin/members.php" class="btn btn-secondary">GO TO MEMBERS</a>
          </div>
        </div>
      </main>
    </div>
  </div>
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
