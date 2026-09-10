<?php
/**
 * IRONCORE Unified Admin Navigation & Header Layout Partial
 * Standardized across all Admin modules (Dashboard, Members, Trainers, Plans, Attendance, Payments, Workouts, Reports, Settings).
 */

$currentSection    = $currentSection ?? 'dashboard';
$adminName         = $_SESSION['full_name'] ?? 'System Admin';
$adminEmail        = $_SESSION['email'] ?? 'admin@ironcore.com';
$adminInitial      = strtoupper(substr($adminName, 0, 1)) ?: 'A';
$pageTitle         = $pageHeading ?? 'OPERATIONAL CONTROL CENTER';
$pageSubtitle      = $pageSubtitle ?? 'Gym Operations, Member Metrics & Performance Tracking';
$extraHeaderAction = $extraHeaderAction ?? '';
$expiries          = $expiries ?? [];
?>

<!-- Mobile Sidebar Backdrop Overlay -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- Unified Admin Sidebar Navigation -->
<aside class="sidebar" id="admin-sidebar">
  <div class="sidebar-brand">
    <a href="/index.php" class="brand-logo" aria-label="IRONCORE Home">
      <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/>
      </svg>
      <span>IRONCORE</span>
    </a>
    <span class="sidebar-badge">ADMINISTRATOR CONTROL</span>
  </div>

  <ul class="sidebar-nav">
    <li>
      <a href="/admin/index.php" class="nav-item-link <?= $currentSection === 'dashboard' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span>Dashboard</span>
      </a>
    </li>
    <li>
      <a href="/admin/members.php" class="nav-item-link <?= $currentSection === 'members' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Members</span>
      </a>
    </li>
    <li>
      <a href="/admin/trainers.php" class="nav-item-link <?= $currentSection === 'trainers' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
        <span>Trainers</span>
      </a>
    </li>
    <li>
      <a href="/admin/memberships.php" class="nav-item-link <?= $currentSection === 'memberships' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <span>Memberships</span>
      </a>
    </li>
    <li>
      <a href="/admin/attendance.php" class="nav-item-link <?= $currentSection === 'attendance' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><polyline points="9 16 11 18 15 14"/></svg>
        <span>Attendance</span>
      </a>
    </li>
    <li>
      <a href="/admin/payments.php" class="nav-item-link <?= $currentSection === 'payments' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        <span>Payments</span>
      </a>
    </li>
    <li>
      <a href="/admin/workouts.php" class="nav-item-link <?= $currentSection === 'workouts' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
        <span>Workouts</span>
      </a>
    </li>
    <li>
      <a href="/admin/reports.php" class="nav-item-link <?= $currentSection === 'reports' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        <span>Reports</span>
      </a>
    </li>
  </ul>

  <div class="sidebar-footer">
    <div class="user-profile-badge">
      <div class="avatar-circle"><?= e($adminInitial) ?></div>
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

<!-- MAIN WRAPPER START -->
<div class="main-wrapper">

<!-- Unified Admin Header Component -->
<header class="dashboard-header">
  <div class="header-left" style="display: flex; align-items: center; gap: 1rem; min-width: 0;">
    <button class="admin-mobile-toggle" aria-label="Toggle navigation drawer">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <div class="header-title-group" style="min-width: 0;">
      <h1><?= e($pageTitle) ?></h1>
      <p><?= e($pageSubtitle) ?></p>
    </div>
  </div>

  <div class="header-actions">
    <!-- Global Quick Search Bar -->
    <div class="header-search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="admin-search-input" placeholder="Search members, trainers..." aria-label="Global Admin Search" autocomplete="off">
      <span class="search-kbd">⌘K</span>
      <div class="header-search-results" id="admin-search-results"></div>
    </div>

    <?= $extraHeaderAction ?>

    <!-- Notification Icon Dropdown -->
    <div class="dropdown-menu-wrapper">
      <button class="icon-btn" id="notif-btn" aria-label="Notifications">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <?php if (!empty($expiries)): ?><span class="notification-badge"></span><?php endif; ?>
      </button>
      <div class="dropdown-panel" id="notif-dropdown">
        <div class="dropdown-header">
          <span>SYSTEM ALERTS</span>
          <span style="font-size: 0.75rem; color: var(--color-accent);"><?= count($expiries) ?> Expiring</span>
        </div>
        <?php if (!empty($expiries)): ?>
          <?php foreach (array_slice($expiries, 0, 3) as $notifExp): ?>
          <div class="dropdown-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-warning)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
              <div style="font-weight: 700; color: #FFF;"><?= e($notifExp['full_name']) ?></div>
              <div style="font-size: 0.75rem; color: var(--color-text-muted);"><?= e($notifExp['plan_title']) ?> (Expires in <?= (int)$notifExp['days_left'] ?> days)</div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="dropdown-item">
            <div style="font-size: 0.8rem; color: var(--color-text-muted);">All memberships in good standing.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- User Profile Dropdown -->
    <div class="dropdown-menu-wrapper">
      <button class="icon-btn" id="profile-btn" aria-label="User profile menu" style="padding: 0; overflow: hidden;">
        <div class="avatar-circle" style="width: 100%; height: 100%; border-radius: 0; font-size: 0.85rem;"><?= e($adminInitial) ?></div>
      </button>
      <div class="dropdown-panel" id="profile-dropdown" style="width: 220px;">
        <div style="padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-border); margin-bottom: 0.5rem;">
          <div style="font-weight: 700; color: #FFF; font-size: 0.9rem;"><?= e($adminName) ?></div>
          <div style="font-size: 0.75rem; color: var(--color-text-muted);"><?= e($adminEmail) ?></div>
        </div>
        <a href="/admin/settings.php" class="dropdown-item">Gym Settings</a>
        <a href="/logout.php" class="dropdown-item" style="color: var(--color-danger);">Sign Out</a>
      </div>
    </div>
  </div>
</header>
