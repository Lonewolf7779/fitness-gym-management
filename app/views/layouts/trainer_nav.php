<?php
/**
 * IRONCORE Unified Trainer Navigation & Header Layout Partial
 */

$currentSection = $currentSection ?? 'dashboard';
$trainerName = $_SESSION['full_name'] ?? 'Marcus Vance';
$trainerEmail = $_SESSION['email'] ?? 'marcus@ironcore.com';
$trainerInitial = strtoupper(substr($trainerName, 0, 1)) ?: 'T';
$pageTitle = $pageHeading ?? 'TRAINER ATHLETE HUB';
$pageSubtitle = $pageSubtitle ?? 'Live assigned athletes, weekly session metrics, & routine assignments';
$extraHeaderAction = $extraHeaderAction ?? '';
?>

<!-- Mobile Sidebar Backdrop Overlay -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- Unified Trainer Sidebar Navigation -->
<aside class="sidebar" id="trainer-sidebar">
  <div class="sidebar-brand">
    <a href="/index.php" class="brand-logo" aria-label="IRONCORE Home">
      <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/>
      </svg>
      <span>IRONCORE</span>
    </a>
    <span class="sidebar-badge">TRAINER COACH PORTAL</span>
  </div>

  <ul class="sidebar-nav">
    <li>
      <a href="/trainer/index.php" class="nav-item-link <?= $currentSection === 'dashboard' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span>Athlete Roster</span>
      </a>
    </li>
  </ul>

  <div class="sidebar-footer">
    <div class="user-profile-badge">
      <div class="avatar-circle"><?= e($trainerInitial) ?></div>
      <div class="user-info">
        <div class="user-name"><?= e($trainerName) ?></div>
        <div class="user-role">Certified Coach</div>
      </div>
    </div>
    <div style="display: flex; gap: 0.5rem;">
      <a href="/logout.php" class="btn btn-primary" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; justify-content: center;">Logout</a>
    </div>
  </div>
</aside>

<!-- MAIN WRAPPER START -->
<div class="main-wrapper">

<!-- Unified Trainer Header Component -->
<header class="dashboard-header">
  <div class="header-top-row">
    <div class="header-left">
      <button class="admin-mobile-toggle" aria-label="Toggle navigation drawer">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>

      <div class="header-title-group">
        <h1><?= e($pageTitle) ?></h1>
        <p><?= e($pageSubtitle) ?></p>
      </div>
    </div>

    <div class="header-actions">
      <?php if (!empty($extraHeaderAction)): ?>
      <div class="desktop-header-cta">
        <?= $extraHeaderAction ?>
      </div>
      <?php endif; ?>

      <!-- User Profile Dropdown -->
      <div class="dropdown-menu-wrapper">
        <button class="icon-btn" id="profile-btn" aria-label="User profile menu" style="padding: 0; overflow: hidden;">
          <div class="avatar-circle" style="width: 100%; height: 100%; border-radius: 0; font-size: 0.85rem;"><?= e($trainerInitial) ?></div>
        </button>
        <div class="dropdown-panel" id="profile-dropdown" style="width: 220px;">
          <div style="padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-border); margin-bottom: 0.5rem;">
            <div style="font-weight: 700; color: #FFF; font-size: 0.9rem;"><?= e($trainerName) ?></div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted);"><?= e($trainerEmail) ?></div>
          </div>
          <a href="/logout.php" class="dropdown-item" style="color: var(--color-danger);">Sign Out</a>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($extraHeaderAction)): ?>
  <div class="mobile-header-cta">
    <?= $extraHeaderAction ?>
  </div>
  <?php endif; ?>
</header>
