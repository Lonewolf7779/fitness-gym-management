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
    <?php
    $currentSection = 'dashboard';
    $pageHeading    = 'OPERATIONAL CONTROL CENTER';
    $pageSubtitle   = 'Real-time gym metrics, active subscriptions, & facility status';
    require_once __DIR__ . '/../layouts/admin_nav.php';
    ?>

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
