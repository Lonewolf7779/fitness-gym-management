<?php
/**
 * IRONCORE Admin Dashboard View Template
 * Section: Phase 3.1 Admin UI Foundation & Live Database Integration
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

// Execute Role Authorization Guard
AdminMiddleware::handle();

$adminName  = $_SESSION['full_name'] ?? 'System Admin';
$adminEmail = $_SESSION['email'] ?? 'admin@ironcore.com';

// Query Live Database Statistics & Records
$svc = new GymManagementService();
$stats = $svc->dashboardStats();
$recentMembers = $svc->recentMembers(5);
$expiries = $svc->approachingExpiries(7);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | IRONCORE Gym Management</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <!-- Backdrop Overlay for Mobile Drawer -->
    <div class="sidebar-overlay"></div>

    <!-- ==========================================================================
         A. SIDEBAR NAVIGATION
         ========================================================================== -->
    <aside class="sidebar">
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
          <a href="/admin/index.php" class="nav-item-link active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span>Dashboard</span>
          </a>
        </li>
        <li>
          <a href="/admin/members.php" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Members</span>
          </a>
        </li>
        <li>
          <a href="/admin/trainers.php" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
            <span>Trainers</span>
          </a>
        </li>
        <li>
          <a href="/admin/memberships.php" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Memberships</span>
          </a>
        </li>
        <li>
          <a href="/admin/attendance.php" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><polyline points="9 16 11 18 15 14"/></svg>
            <span>Attendance</span>
          </a>
        </li>
        <li>
          <a href="/admin/payments.php" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span>Payments</span>
          </a>
        </li>
        <li>
          <a href="/admin/workouts.php" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
            <span>Workouts</span>
          </a>
        </li>
        <li>
          <a href="/admin/reports.php" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span>Reports</span>
          </a>
        </li>
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

    <!-- ==========================================================================
         MAIN CONTENT WRAPPER
         ========================================================================== -->
    <div class="main-wrapper">

      <!-- B. TOP HEADER BAR -->
      <header class="dashboard-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <button class="admin-mobile-toggle" aria-label="Toggle navigation drawer">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>

          <div class="header-title-group">
            <h1>OPERATIONAL CONTROL CENTER</h1>
            <p>Real-time gym metrics, active subscriptions, & facility status</p>
          </div>
        </div>

        <div class="header-actions">
          <!-- Search Input Affordance -->
          <div class="header-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="admin-search-input" placeholder="Search members, trainers..." aria-label="Global Admin Search" autocomplete="off">
            <span class="search-kbd">⌘K</span>
            <div class="header-search-results" id="admin-search-results"></div>
          </div>

          <!-- Notification Icon Dropdown -->
          <div class="dropdown-menu-wrapper">
            <button class="icon-btn" id="notif-btn" aria-label="Notifications">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <span class="notification-badge"></span>
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
            <button class="icon-btn" id="profile-btn" aria-label="User menu" style="padding: 0; overflow: hidden;">
              <div class="avatar-circle" style="width: 100%; height: 100%; border-radius: 0; font-size: 0.85rem;"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
            </button>
            <div class="dropdown-panel" id="profile-dropdown" style="width: 220px;">
              <div style="padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-border); margin-bottom: 0.5rem;">
                <div style="font-weight: 700; color: #FFF; font-size: 0.9rem;"><?= e($adminName) ?></div>
                <div style="font-size: 0.75rem; color: var(--color-text-muted);"><?= e($adminEmail) ?></div>
              </div>
              <a href="/admin/settings.php" class="dropdown-item">System Settings</a>
              <a href="/logout.php" class="dropdown-item" style="color: var(--color-danger);">Sign Out</a>
            </div>
          </div>
        </div>
      </header>

      <!-- MAIN DASHBOARD CONTENT AREA -->
      <main class="dashboard-body">

        <!-- C. TOP 4 SUMMARY METRIC CARDS (KPIs) -->
        <section class="kpi-grid">
          <!-- Total Members -->
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Total Members</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="kpi-value"><?= e($stats['total_members']) ?></div>
            <div class="kpi-foot">
              <span>Registered Athletes</span>
            </div>
          </div>

          <!-- Active Members -->
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Active Members</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="kpi-value" style="color: var(--color-accent);"><?= e($stats['active_members']) ?></div>
            <div class="kpi-foot" style="color: var(--color-accent);">
              <span><?= e($stats['active_pct']) ?>% Active Subscriptions</span>
            </div>
          </div>

          <!-- Today's Attendance -->
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Today's Attendance</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><polyline points="9 16 11 18 15 14"/></svg>
            </div>
            <div class="kpi-value"><?= e($stats['today_attendance']) ?></div>
            <div class="kpi-foot">
              <span>Live Check-in Count</span>
            </div>
          </div>

          <!-- Monthly Revenue -->
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Monthly Revenue</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="kpi-value">₹<?= e($stats['monthly_revenue']) ?></div>
            <div class="kpi-foot">
              <span>Current Billing Cycle</span>
            </div>
          </div>
        </section>

        <!-- D. REVENUE & ATTENDANCE OVERVIEW (VISUAL CHART) -->
        <section class="chart-section-card">
          <div class="chart-card-header">
            <div class="chart-title-area">
              <h2>FINANCIAL & ATHLETE PERFORMANCE OVERVIEW</h2>
              <p>Monthly revenue distribution & weekly check-in trends</p>
            </div>
            <div class="chart-controls">
              <div class="chart-view-switch">
                <button class="switch-btn active" data-view="revenue">REVENUE</button>
                <button class="switch-btn" data-view="attendance">CHECK-INS</button>
              </div>
            </div>
          </div>

          <!-- SVG Visual Render Box -->
          <div class="svg-chart-wrapper" id="admin-chart-svg">
            <!-- Rendered dynamically by dashboard.js using live API data -->
          </div>
        </section>

        <!-- E & F. TWO-COLUMN SPLIT GRID (RECENT MEMBERS & MEMBERSHIP EXPIRY) -->
        <div class="dashboard-split-grid">
          
          <!-- E. RECENT REGISTERED MEMBERS TABLE -->
          <section class="panel-card">
            <div class="panel-header">
              <h3>RECENT REGISTERED MEMBERS</h3>
              <a href="/admin/members.php" class="panel-link">View All</a>
            </div>

            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Member</th>
                    <th>Membership</th>
                    <th>Joined</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($recentMembers)): ?>
                    <?php foreach ($recentMembers as $rm): 
                      $initials = '';
                      $parts = explode(' ', trim($rm['full_name']));
                      foreach ($parts as $p) { if (!empty($p)) $initials .= strtoupper($p[0]); }
                      $initials = substr($initials ?: 'MB', 0, 2);
                      $planTitle = strtoupper($rm['plan_title'] ?? 'NO PLAN');
                      $planColor = str_contains($planTitle, 'PRO') ? 'var(--color-accent)' : (str_contains($planTitle, 'ELITE') ? '#FFF' : 'var(--color-text-muted)');
                      $status = strtolower($rm['status'] ?? 'active');
                      $pillClass = ($status === 'expired' || $status === 'inactive' || $status === 'suspended') ? $status : 'active';
                    ?>
                    <tr>
                      <td>
                        <div class="member-cell">
                          <div class="member-avatar"><?= e($initials) ?></div>
                          <div>
                            <div class="member-info-name"><?= e($rm['full_name']) ?></div>
                            <div class="member-info-email"><?= e($rm['email']) ?></div>
                          </div>
                        </div>
                      </td>
                      <td><span style="font-weight: 700; color: <?= $planColor ?>;"><?= e($planTitle) ?></span></td>
                      <td><?= e(date('d M Y', strtotime($rm['join_date'] ?? 'now'))) ?></td>
                      <td><span class="status-pill <?= e($pillClass) ?>"><span class="status-dot-sm"></span> <?= e(ucfirst($status)) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">No members registered yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </section>

          <!-- F. MEMBERSHIP APPROACHING EXPIRY -->
          <section class="panel-card">
            <div class="panel-header">
              <h3>APPROACHING EXPIRY</h3>
              <a href="/admin/memberships.php" class="panel-link">Manage Plans</a>
            </div>

            <ul class="expiry-list">
              <?php if (!empty($expiries)): ?>
                <?php foreach ($expiries as $ex): 
                  $daysLeft = (int) $ex['days_left'];
                  $pillClass = ($daysLeft <= 2) ? 'danger' : 'warning';
                  $label = ($daysLeft === 0) ? 'Today' : (($daysLeft === 1) ? 'Tomorrow' : "In {$daysLeft} Days");
                ?>
                <li class="expiry-item">
                  <div>
                    <div class="expiry-user-title"><?= e($ex['full_name']) ?></div>
                    <div class="expiry-user-sub"><?= e($ex['plan_title']) ?> • Expires <?= e(date('M d, Y', strtotime($ex['end_date']))) ?></div>
                  </div>
                  <div style="text-align: right;">
                    <span class="status-pill <?= $pillClass ?>" style="margin-bottom: 0.35rem; display: inline-block;"><?= e($label) ?></span>
                    <div><a href="/admin/memberships.php" style="font-size: 0.75rem; font-weight: 700; color: var(--color-accent);">MANAGE</a></div>
                  </div>
                </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li style="text-align: center; padding: 2.5rem 1rem; color: var(--color-text-muted); font-size: 0.85rem;">
                  No memberships approaching expiration this week.
                </li>
              <?php endif; ?>
            </ul>
          </section>

        </div>

      </main>
    </div>
  </div>

  <!-- Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
