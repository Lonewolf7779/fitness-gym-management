<?php
/**
 * IRONCORE Admin Dashboard View Template
 * Section: Phase 3.1 Admin UI Foundation & Live Database Integration
 * Premium UI/UX Redesign & Responsive Architecture Pass
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

// Execute Role Authorization Guard
AdminMiddleware::handle();

$adminName  = $_SESSION['full_name'] ?? 'Admin';
$adminEmail = $_SESSION['email'] ?? '';

// Query Live Database Statistics & Records
$svc = new GymManagementService();
$stats = $svc->dashboardStats();
$recentMembers = $svc->recentMembers(5);
$expiries = $svc->approachingExpiries(7);
$overview = $svc->adminOverviewStats();
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
  <link rel="stylesheet" href="/assets/css/admin-dashboard.css">
</head>
<body style="background-color: var(--color-bg, #0A0A0E);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'dashboard';
    $pageHeading    = 'OPERATIONAL CONTROL CENTER';
    $pageSubtitle   = 'Real-time gym metrics, active subscriptions, & facility status';
    require_once __DIR__ . '/../layouts/admin_nav.php';
    ?>

      <!-- MAIN DASHBOARD CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. HERO COMMAND HEADER -->
        <section class="admin-command-hero">
          <div class="hero-content">
            <div class="hero-badge-row">
              <span class="hero-status-pill">
                <span class="pulse-dot"></span> Facility Operational
              </span>
              <span class="hero-subtag">System Core v2.4</span>
            </div>
            <h1 class="hero-title">OPERATIONAL CONTROL CENTER</h1>
            <p class="hero-subtitle">Real-time facility telemetry, athlete capacity & revenue pipeline</p>
          </div>
          <div class="hero-meta-strip">
            <div class="hero-meta-item">
              <span class="meta-label">Facility Shift</span>
              <span class="meta-value text-accent">Active Operational</span>
            </div>
            <div class="hero-meta-divider"></div>
            <div class="hero-meta-item">
              <span class="meta-label">Timezone</span>
              <span class="meta-value">IST (UTC+5:30)</span>
            </div>
            <div class="hero-meta-divider"></div>
            <div class="hero-meta-item">
              <span class="meta-label">Sync Status</span>
              <span class="meta-value text-success">Live MySQL</span>
            </div>
          </div>
        </section>

        <!-- 2. PRIMARY KPI METRICS (4 DOMINANT CARDS) -->
        <section class="admin-primary-kpis" aria-label="Primary Performance Indicators">
          <!-- Total Members -->
          <article class="primary-kpi-card">
            <div class="kpi-top">
              <div class="kpi-label-group">
                <span class="kpi-eyebrow">Roster Total</span>
                <h2 class="kpi-heading">Total Members</h2>
              </div>
              <div class="kpi-icon-bubble">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-body">
              <div class="kpi-main-number"><?= e($stats['total_members']) ?></div>
              <div class="kpi-detail-row">
                <span class="kpi-detail-text">Registered Athletes</span>
                <span class="kpi-pill kpi-pill-neutral">Enrolled</span>
              </div>
            </div>
            <div class="kpi-accent-bar"></div>
          </article>

          <!-- Active Members -->
          <article class="primary-kpi-card accent-glow">
            <div class="kpi-top">
              <div class="kpi-label-group">
                <span class="kpi-eyebrow">Valid Passes</span>
                <h2 class="kpi-heading">Active Members</h2>
              </div>
              <div class="kpi-icon-bubble accent-bg">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
            </div>
            <div class="kpi-body">
              <div class="kpi-main-number text-accent"><?= e($stats['active_members']) ?></div>
              <div class="kpi-detail-row">
                <span class="kpi-detail-text"><?= e($stats['active_pct']) ?>% Active Subscriptions</span>
                <span class="kpi-pill kpi-pill-accent">Current</span>
              </div>
            </div>
            <div class="kpi-accent-bar"></div>
          </article>

          <!-- Today's Attendance -->
          <article class="primary-kpi-card">
            <div class="kpi-top">
              <div class="kpi-label-group">
                <span class="kpi-eyebrow">Live Traffic</span>
                <h2 class="kpi-heading">Today's Attendance</h2>
              </div>
              <div class="kpi-icon-bubble">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><polyline points="9 16 11 18 15 14"/></svg>
              </div>
            </div>
            <div class="kpi-body">
              <div class="kpi-main-number"><?= e($stats['today_attendance']) ?></div>
              <div class="kpi-detail-row">
                <span class="kpi-detail-text">Daily Turnstile Check-ins</span>
                <span class="kpi-pill kpi-pill-cyan">Floor Traffic</span>
              </div>
            </div>
            <div class="kpi-accent-bar"></div>
          </article>

          <!-- Monthly Revenue -->
          <article class="primary-kpi-card">
            <div class="kpi-top">
              <div class="kpi-label-group">
                <span class="kpi-eyebrow">Billing Cycle</span>
                <h2 class="kpi-heading">Monthly Revenue</h2>
              </div>
              <div class="kpi-icon-bubble">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              </div>
            </div>
            <div class="kpi-body">
              <div class="kpi-main-number">₹<?= e($stats['monthly_revenue']) ?></div>
              <div class="kpi-detail-row">
                <span class="kpi-detail-text">Current Calendar Month</span>
                <span class="kpi-pill kpi-pill-emerald">Collected</span>
              </div>
            </div>
            <div class="kpi-accent-bar"></div>
          </article>
        </section>

        <!-- 3. SECONDARY METRICS (6 COMPACT CARDS) -->
        <section class="admin-secondary-metrics" aria-label="Secondary Facility Metrics">
          <article class="secondary-metric-card">
            <div class="sec-metric-top">
              <span class="sec-label">Trainer Roster</span>
              <span class="sec-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg></span>
            </div>
            <div>
              <span class="sec-val"><?= (int)$overview['active_trainers'] ?> <span class="sec-dim">/ <?= (int)$overview['total_trainers'] ?></span></span>
              <span class="sec-sub">active / total coaches</span>
            </div>
          </article>

          <article class="secondary-metric-card">
            <div class="sec-metric-top">
              <span class="sec-label">Active Passes</span>
              <span class="sec-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
            </div>
            <div>
              <span class="sec-val"><?= (int)$overview['active_subscriptions'] ?></span>
              <span class="sec-sub">valid memberships</span>
            </div>
          </article>

          <article class="secondary-metric-card">
            <div class="sec-metric-top">
              <span class="sec-label">On Floor Now</span>
              <span class="sec-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span>
            </div>
            <div>
              <span class="sec-val"><?= (int)$overview['currently_in_gym'] ?></span>
              <span class="sec-sub"><?= (int)$overview['today_checkins'] ?> checked in today</span>
            </div>
          </article>

          <article class="secondary-metric-card">
            <div class="sec-metric-top">
              <span class="sec-label">Workout Plans</span>
              <span class="sec-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg></span>
            </div>
            <div>
              <span class="sec-val"><?= (int)$overview['workout_programs'] ?></span>
              <span class="sec-sub">assigned routines</span>
            </div>
          </article>

          <article class="secondary-metric-card">
            <div class="sec-metric-top">
              <span class="sec-label">Exercise Library</span>
              <span class="sec-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg></span>
            </div>
            <div>
              <span class="sec-val"><?= (int)$overview['exercise_catalog'] ?></span>
              <span class="sec-sub">catalog exercises</span>
            </div>
          </article>

          <article class="secondary-metric-card">
            <div class="sec-metric-top">
              <span class="sec-label">Expiring Soon</span>
              <span class="sec-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
            </div>
            <div>
              <span class="sec-val <?= (int)$overview['expiring_7d'] > 0 ? 'text-warning' : '' ?>"><?= (int)$overview['expiring_7d'] ?></span>
              <span class="sec-sub">next 7 calendar days</span>
            </div>
          </article>
        </section>

        <!-- 4. COMPACT LIVE ADMIN TELEMETRY & COMMAND SHORTCUTS -->
        <section class="admin-telemetry-panel" aria-label="Operational Live Telemetry">
          <div class="telemetry-left">
            <div class="telemetry-header-row">
              <div class="telemetry-beacon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
              </div>
              <div class="telemetry-header-text">
                <h3>LIVE ADMIN TELEMETRY</h3>
                <p>Synchronized live statistics from MySQL datastore</p>
              </div>
            </div>
            <div class="telemetry-counters-strip">
              <div class="telemetry-stat-box">
                <span class="telemetry-stat-label">Athletes</span>
                <span class="telemetry-stat-number"><?= (int)$stats['total_members'] ?></span>
              </div>
              <div class="telemetry-stat-box">
                <span class="telemetry-stat-label">On Floor</span>
                <span class="telemetry-stat-number highlight"><?= (int)$overview['currently_in_gym'] ?></span>
              </div>
              <div class="telemetry-stat-box">
                <span class="telemetry-stat-label">Trainers</span>
                <span class="telemetry-stat-number"><?= (int)$overview['active_trainers'] ?></span>
              </div>
              <div class="telemetry-stat-box">
                <span class="telemetry-stat-label">Exercises</span>
                <span class="telemetry-stat-number"><?= (int)$overview['exercise_catalog'] ?></span>
              </div>
            </div>
          </div>
          <div class="telemetry-right">
            <span class="telemetry-actions-label">Quick Facility Actions</span>
            <div class="telemetry-actions-grid">
              <a href="/admin/members.php" class="telemetry-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <span>Manage Members</span>
              </a>
              <a href="/admin/trainers.php" class="telemetry-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/></svg>
                <span>Manage Trainers</span>
              </a>
              <a href="/admin/workouts.php" class="telemetry-btn primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Build Workout</span>
              </a>
              <a href="/admin/revenue.php" class="telemetry-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Open Revenue</span>
              </a>
            </div>
          </div>
        </section>

        <!-- 5. FINANCIAL & ATHLETE PERFORMANCE SECTION (CHART) -->
        <section class="admin-chart-card" aria-label="Financial and Attendance Performance">
          <div class="chart-header-row">
            <div class="chart-title-group">
              <h2>FINANCIAL & ATHLETE PERFORMANCE OVERVIEW</h2>
              <p>Monthly revenue distribution & weekly check-in trends</p>
            </div>
            <div class="chart-tabs" role="tablist">
              <button class="chart-tab-btn switch-btn active" data-view="revenue" role="tab" aria-selected="true">REVENUE</button>
              <button class="chart-tab-btn switch-btn" data-view="attendance" role="tab" aria-selected="false">CHECK-INS</button>
            </div>
          </div>

          <!-- SVG Visual Render Box -->
          <div class="admin-chart-stage svg-chart-wrapper" id="admin-chart-svg">
            <!-- Rendered dynamically by dashboard.js using live API data -->
          </div>
        </section>

        <!-- 6. LOWER CONTENT SPLIT GRID (RECENT MEMBERS & EXPIRING PASSES) -->
        <div class="admin-lower-split">
          
          <!-- RECENT REGISTERED MEMBERS -->
          <section class="admin-panel-card" aria-label="Recent Members">
            <div class="panel-header-row">
              <h3 class="panel-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <span>RECENT REGISTERED MEMBERS</span>
              </h3>
              <a href="/admin/members.php" class="panel-action-link">View All Members &rarr;</a>
            </div>

            <div class="recent-members-table-wrap">
              <?php if (!empty($recentMembers)): ?>
                <table class="recent-members-table">
                  <thead>
                    <tr>
                      <th scope="col">Athlete</th>
                      <th scope="col">Membership</th>
                      <th scope="col">Joined</th>
                      <th scope="col">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($recentMembers as $rm): 
                      $initials = '';
                      $parts = explode(' ', trim($rm['full_name']));
                      foreach ($parts as $p) { if (!empty($p)) $initials .= strtoupper($p[0]); }
                      $initials = substr($initials ?: 'MB', 0, 2);
                      $planTitle = strtoupper($rm['plan_title'] ?? 'NO PLAN');
                      $planClass = str_contains($planTitle, 'PRO') ? 'pro' : (str_contains($planTitle, 'ELITE') ? 'elite' : 'starter');
                      $status = strtolower($rm['status'] ?? 'active');
                      $pillClass = ($status === 'expired' || $status === 'inactive' || $status === 'suspended') ? $status : 'active';
                    ?>
                    <tr>
                      <td>
                        <div class="member-profile-cell">
                          <div class="member-avatar-chip"><?= e($initials) ?></div>
                          <div class="member-name-block">
                            <div class="member-name-text"><?= e($rm['full_name']) ?></div>
                            <div class="member-email-text"><?= e($rm['email']) ?></div>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="plan-badge-cell <?= $planClass ?>"><?= e($planTitle) ?></span>
                      </td>
                      <td><?= e(date('d M Y', strtotime($rm['join_date'] ?? 'now'))) ?></td>
                      <td>
                        <span class="status-pill <?= e($pillClass) ?>">
                          <span class="status-dot-sm"></span> <?= e(ucfirst($status)) ?>
                        </span>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <div class="panel-empty-state">
                  <div class="panel-empty-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                  </div>
                  <h4>NO MEMBERS YET</h4>
                  <p>There are currently no registered members in the system. Add your first athlete to activate facility operations.</p>
                  <a href="/admin/members.php?action=add" class="btn btn-primary" style="margin-top: 0.5rem; font-size: 0.78rem;">Add Member</a>
                </div>
              <?php endif; ?>
            </div>
          </section>

          <!-- APPROACHING EXPIRY -->
          <section class="admin-panel-card" aria-label="Approaching Expiration">
            <div class="panel-header-row">
              <h3 class="panel-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>APPROACHING EXPIRY</span>
              </h3>
              <a href="/admin/memberships.php" class="panel-action-link">Manage Plans &rarr;</a>
            </div>

            <?php if (!empty($expiries)): ?>
              <ul class="expiry-stack-list">
                <?php foreach ($expiries as $ex): 
                  $daysLeft = (int) $ex['days_left'];
                  $pillClass = ($daysLeft <= 2) ? 'danger' : 'warning';
                  $label = ($daysLeft === 0) ? 'Expires Today' : (($daysLeft === 1) ? 'Expires Tomorrow' : "In {$daysLeft} Days");
                ?>
                <li class="expiry-stack-item">
                  <div class="expiry-user-block">
                    <div class="expiry-user-name"><?= e($ex['full_name']) ?></div>
                    <div class="expiry-user-meta"><?= e($ex['plan_title']) ?> &bull; <?= e(date('M d, Y', strtotime($ex['end_date']))) ?></div>
                  </div>
                  <div class="expiry-right-block">
                    <span class="days-left-pill <?= $pillClass ?>"><?= e($label) ?></span>
                    <a href="/admin/memberships.php" class="expiry-manage-link">MANAGE PASS</a>
                  </div>
                </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="panel-empty-state">
                <div class="panel-empty-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                </div>
                <h4>NO EXPIRING MEMBERS</h4>
                <p>All athlete subscriptions are in good standing. Passes expiring within the next 7 days will be flagged here.</p>
                <a href="/admin/memberships.php" class="btn btn-secondary" style="margin-top: 0.5rem; font-size: 0.78rem;">View Subscriptions</a>
              </div>
            <?php endif; ?>
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
