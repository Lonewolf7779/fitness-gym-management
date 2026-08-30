<?php
/**
 * IRONCORE Admin Dashboard View Template
 * Section: Phase 3.1 Admin UI Foundation
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';

// Execute Role Authorization Guard
AdminMiddleware::handle();

$adminName  = $_SESSION['full_name'] ?? 'System Admin';
$adminEmail = $_SESSION['email'] ?? 'admin@ironcore.com';

// UI Placeholder Metric Values (Structured for future PHP/MySQL data binding)
$stats = [
    'total_members'     => 642,
    'active_members'    => 518,
    'today_attendance'  => 387,
    'monthly_revenue'   => '2.84L'
];
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
                <span style="font-size: 0.75rem; color: var(--color-accent);">3 New</span>
              </div>
              <div class="dropdown-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                  <div style="font-weight: 700; color: #FFF;">3 Memberships Expiring</div>
                  <div style="font-size: 0.75rem; color: var(--color-text-muted);">Action required for renewal</div>
                </div>
              </div>
              <div class="dropdown-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                  <div style="font-weight: 700; color: #FFF;">Payment Received</div>
                  <div style="font-size: 0.75rem; color: var(--color-text-muted);">₹1,999 received via UPI</div>
                </div>
              </div>
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
              <a href="/admin/members.php" class="dropdown-item">My Members</a>
              <a href="/admin/settings.php" class="dropdown-item">System Settings</a>
              <a href="/logout.php" class="dropdown-item" style="color: var(--color-danger);">Sign Out</a>
            </div>
          </div>
        </div>
      </header>

      <!-- DASHBOARD BODY CONTENT -->
      <main class="dashboard-body">

        <!-- G. QUICK ACTIONS STRIP -->
        <div class="quick-actions-strip">
          <a href="/admin/members.php?action=add" class="action-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Member
          </a>
          <a href="/admin/trainers.php" class="action-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Trainer
          </a>
          <a href="/admin/memberships.php" class="action-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Create Membership
          </a>
          <a href="/admin/payments.php" class="action-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Record Payment
          </a>
        </div>

        <!-- C. KPI / SUMMARY AREA -->
        <section class="kpi-grid">
          <!-- Total Members -->
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Total Members</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="kpi-value"><?= e($stats['total_members']) ?></div>
            <div class="kpi-foot">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
              <span>+14% vs last month</span>
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
              <span>80.6% Active Subscriptions</span>
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
              <span>Peak: 6:00 PM – 9:00 PM</span>
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
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
              <span>+18.5% Growth</span>
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
            <!-- Rendered dynamically by dashboard.js -->
          </div>
        </section>

        <!-- E & F. TWO-COLUMN SPLIT GRID (RECENT MEMBERS & MEMBERSHIP EXPIRY) -->
        <div class="dashboard-split-grid">
          
          <!-- E. RECENT REGISTERED MEMBERS TABLE -->
          <section class="panel-card">
            <div class="panel-header">
              <h3>RECENT REGISTERED MEMBERS</h3>
              <a href="#members" class="panel-link">View All</a>
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
                  <tr>
                    <td>
                      <div class="member-cell">
                        <div class="member-avatar">AR</div>
                        <div>
                          <div class="member-info-name">Alex Rivera</div>
                          <div class="member-info-email">alex@gmail.com</div>
                        </div>
                      </div>
                    </td>
                    <td><span style="font-weight: 700; color: var(--color-accent);">PRO PLAN</span></td>
                    <td>15 Aug 2026</td>
                    <td><span class="status-pill active"><span class="status-dot-sm"></span> Active</span></td>
                  </tr>

                  <tr>
                    <td>
                      <div class="member-cell">
                        <div class="member-avatar">ER</div>
                        <div>
                          <div class="member-info-name">Elena Rostova</div>
                          <div class="member-info-email">elena@ironcore.com</div>
                        </div>
                      </div>
                    </td>
                    <td><span style="font-weight: 700; color: #FFF;">ELITE PLAN</span></td>
                    <td>18 Aug 2026</td>
                    <td><span class="status-pill active"><span class="status-dot-sm"></span> Active</span></td>
                  </tr>

                  <tr>
                    <td>
                      <div class="member-cell">
                        <div class="member-avatar">SC</div>
                        <div>
                          <div class="member-info-name">Sarah Connor</div>
                          <div class="member-info-email">sarah@gmail.com</div>
                        </div>
                      </div>
                    </td>
                    <td><span style="font-weight: 600; color: var(--color-text-muted);">STARTER</span></td>
                    <td>22 Aug 2026</td>
                    <td><span class="status-pill pending"><span class="status-dot-sm"></span> Pending</span></td>
                  </tr>

                  <tr>
                    <td>
                      <div class="member-cell">
                        <div class="member-avatar">MC</div>
                        <div>
                          <div class="member-info-name">Michael Chang</div>
                          <div class="member-info-email">michael@gmail.com</div>
                        </div>
                      </div>
                    </td>
                    <td><span style="font-weight: 700; color: var(--color-accent);">PRO PLAN</span></td>
                    <td>25 Aug 2026</td>
                    <td><span class="status-pill active"><span class="status-dot-sm"></span> Active</span></td>
                  </tr>

                  <tr>
                    <td>
                      <div class="member-cell">
                        <div class="member-avatar">DB</div>
                        <div>
                          <div class="member-info-name">David Black</div>
                          <div class="member-info-email">david@gmail.com</div>
                        </div>
                      </div>
                    </td>
                    <td><span style="font-weight: 600; color: var(--color-text-muted);">STARTER</span></td>
                    <td>27 Aug 2026</td>
                    <td><span class="status-pill danger"><span class="status-dot-sm"></span> Expired</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- F. MEMBERSHIP APPROACHING EXPIRY -->
          <section class="panel-card">
            <div class="panel-header">
              <h3>APPROACHING EXPIRY</h3>
              <a href="#memberships" class="panel-link">Manage Plans</a>
            </div>

            <ul class="expiry-list">
              <li class="expiry-item">
                <div>
                  <div class="expiry-user-title">Viktor Vance</div>
                  <div class="expiry-user-sub">Pro Plan • Expires Aug 31, 2026</div>
                </div>
                <div style="text-align: right;">
                  <span class="status-pill warning" style="margin-bottom: 0.35rem; display: inline-block;">In 2 Days</span>
                  <div><a href="#renew" style="font-size: 0.75rem; font-weight: 700; color: var(--color-accent);">RENEW</a></div>
                </div>
              </li>

              <li class="expiry-item">
                <div>
                  <div class="expiry-user-title">Anita Sharma</div>
                  <div class="expiry-user-sub">Elite Plan • Expires Aug 30, 2026</div>
                </div>
                <div style="text-align: right;">
                  <span class="status-pill danger" style="margin-bottom: 0.35rem; display: inline-block;">Tomorrow</span>
                  <div><a href="#renew" style="font-size: 0.75rem; font-weight: 700; color: var(--color-accent);">RENEW</a></div>
                </div>
              </li>

              <li class="expiry-item">
                <div>
                  <div class="expiry-user-title">Rahul Kapoor</div>
                  <div class="expiry-user-sub">Starter Plan • Expires Sep 02, 2026</div>
                </div>
                <div style="text-align: right;">
                  <span class="status-pill warning" style="margin-bottom: 0.35rem; display: inline-block;">In 4 Days</span>
                  <div><a href="#renew" style="font-size: 0.75rem; font-weight: 700; color: var(--color-accent);">RENEW</a></div>
                </div>
              </li>

              <li class="expiry-item">
                <div>
                  <div class="expiry-user-title">Priya Singh</div>
                  <div class="expiry-user-sub">Pro Plan • Expires Sep 03, 2026</div>
                </div>
                <div style="text-align: right;">
                  <span class="status-pill warning" style="margin-bottom: 0.35rem; display: inline-block;">In 5 Days</span>
                  <div><a href="#renew" style="font-size: 0.75rem; font-weight: 700; color: var(--color-accent);">RENEW</a></div>
                </div>
              </li>
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
