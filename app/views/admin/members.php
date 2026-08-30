<?php
/**
 * IRONCORE Admin Member Management View Template
 * Section: Phase 2A Admin Member Management UI
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';

// Execute Role Authorization Guard
AdminMiddleware::handle();

$adminName  = $_SESSION['full_name'] ?? 'System Admin';
$adminEmail = $_SESSION['email'] ?? 'admin@ironcore.com';

// Sample Member Data Structure for Layout Rendering (Client-side interactive array)
$membersList = [
    [
        'id'         => 1,
        'name'       => 'Alex Rivera',
        'email'      => 'alex@gmail.com',
        'phone'      => '+91 9876543210',
        'avatar'     => 'AR',
        'plan'       => 'Pro Plan',
        'joined'     => '12 Aug 2026',
        'expiry'     => '28 Sep 2026',
        'status'     => 'active'
    ],
    [
        'id'         => 2,
        'name'       => 'Daniel Carter',
        'email'      => 'daniel@gmail.com',
        'phone'      => '+91 9876543211',
        'avatar'     => 'DC',
        'plan'       => 'Starter Plan',
        'joined'     => '05 Jul 2026',
        'expiry'     => '05 Sep 2026',
        'status'     => 'active'
    ],
    [
        'id'         => 3,
        'name'       => 'Sophia Miller',
        'email'      => 'sophia@gmail.com',
        'phone'      => '+91 9876543212',
        'avatar'     => 'SM',
        'plan'       => 'Elite Plan',
        'joined'     => '18 Jun 2026',
        'expiry'     => '18 Sep 2026',
        'status'     => 'active'
    ],
    [
        'id'         => 4,
        'name'       => 'Ryan Brooks',
        'email'      => 'ryan@gmail.com',
        'phone'      => '+91 9876543213',
        'avatar'     => 'RB',
        'plan'       => 'Pro Plan',
        'joined'     => '02 May 2026',
        'expiry'     => '02 Sep 2026',
        'status'     => 'expired'
    ],
    [
        'id'         => 5,
        'name'       => 'Elena Rostova',
        'email'      => 'elena@ironcore.com',
        'phone'      => '+91 9876543214',
        'avatar'     => 'ER',
        'plan'       => 'Elite Plan',
        'joined'     => '10 Jan 2026',
        'expiry'     => '10 Jan 2027',
        'status'     => 'active'
    ],
    [
        'id'         => 6,
        'name'       => 'Sarah Connor',
        'email'      => 'sarah@gmail.com',
        'phone'      => '+91 9876543215',
        'avatar'     => 'SC',
        'plan'       => 'Starter Plan',
        'joined'     => '22 Aug 2026',
        'expiry'     => '22 Sep 2026',
        'status'     => 'inactive'
    ],
    [
        'id'         => 7,
        'name'       => 'David Black',
        'email'      => 'david@gmail.com',
        'phone'      => '+91 9876543216',
        'avatar'     => 'DB',
        'plan'       => 'Starter Plan',
        'joined'     => '01 Mar 2026',
        'expiry'     => '01 Apr 2026',
        'status'     => 'suspended'
    ],
    [
        'id'         => 8,
        'name'       => 'Viktor Vance',
        'email'      => 'viktor@gmail.com',
        'phone'      => '+91 9876543217',
        'avatar'     => 'VV',
        'plan'       => 'Pro Plan',
        'joined'     => '31 Jul 2026',
        'expiry'     => '31 Aug 2026',
        'status'     => 'expired'
    ],
    [
        'id'         => 9,
        'name'       => 'Anita Sharma',
        'email'      => 'anita@gmail.com',
        'phone'      => '+91 9876543218',
        'avatar'     => 'AS',
        'plan'       => 'Elite Plan',
        'joined'     => '30 Jul 2026',
        'expiry'     => '30 Aug 2026',
        'status'     => 'expired'
    ],
    [
        'id'         => 10,
        'name'       => 'Rahul Kapoor',
        'email'      => 'kapoor@gmail.com',
        'phone'      => '+91 9876543219',
        'avatar'     => 'RK',
        'plan'       => 'Starter Plan',
        'joined'     => '02 Aug 2026',
        'expiry'     => '02 Sep 2026',
        'status'     => 'active'
    ],
    [
        'id'         => 11,
        'name'       => 'Priya Singh',
        'email'      => 'priya@gmail.com',
        'phone'      => '+91 9876543220',
        'avatar'     => 'PS',
        'plan'       => 'Pro Plan',
        'joined'     => '03 Aug 2026',
        'expiry'     => '03 Sep 2026',
        'status'     => 'active'
    ],
    [
        'id'         => 12,
        'name'       => 'Marcus Vance',
        'email'      => 'marcus@ironcore.com',
        'phone'      => '+91 9876543221',
        'avatar'     => 'MV',
        'plan'       => 'Elite Plan',
        'joined'     => '15 Jan 2026',
        'expiry'     => '15 Jan 2027',
        'status'     => 'active'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Management | IRONCORE Gym Management</title>
  
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
         SIDEBAR NAVIGATION
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
          <a href="/admin/index.php" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span>Dashboard</span>
          </a>
        </li>
        <li>
          <a href="/admin/members.php" class="nav-item-link active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Members</span>
          </a>
        </li>
        <li>
          <a href="#trainers" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
            <span>Trainers</span>
          </a>
        </li>
        <li>
          <a href="#memberships" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Memberships</span>
          </a>
        </li>
        <li>
          <a href="#attendance" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><polyline points="9 16 11 18 15 14"/></svg>
            <span>Attendance</span>
          </a>
        </li>
        <li>
          <a href="#payments" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span>Payments</span>
          </a>
        </li>
        <li>
          <a href="#workouts" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
            <span>Workouts</span>
          </a>
        </li>
        <li>
          <a href="#reports" class="nav-item-link">
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
          <a href="#settings" class="btn btn-secondary" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; justify-content: center;">Settings</a>
          <a href="/logout.php" class="btn btn-primary" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; justify-content: center;">Logout</a>
        </div>
      </div>
    </aside>

    <!-- ==========================================================================
         MAIN CONTENT WRAPPER
         ========================================================================== -->
    <div class="main-wrapper">

      <!-- TOP HEADER BAR -->
      <header class="dashboard-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <button class="admin-mobile-toggle" aria-label="Toggle navigation drawer">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>

          <div class="header-title-group">
            <h1>MEMBER MANAGEMENT</h1>
            <p>Manage gym members, memberships, account status, and member information from one place.</p>
          </div>
        </div>

        <div class="header-actions">
          <button class="btn btn-primary" id="open-add-modal-btn" style="padding: 0.6rem 1.25rem; font-size: 0.85rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Member
          </button>

          <!-- Notification Dropdown -->
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
            </div>
          </div>

          <!-- Profile Dropdown -->
          <div class="dropdown-menu-wrapper">
            <button class="icon-btn" id="profile-btn" aria-label="User menu" style="padding: 0; overflow: hidden;">
              <div class="avatar-circle" style="width: 100%; height: 100%; border-radius: 0; font-size: 0.85rem;"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
            </button>
            <div class="dropdown-panel" id="profile-dropdown" style="width: 220px;">
              <div style="padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-border); margin-bottom: 0.5rem;">
                <div style="font-weight: 700; color: #FFF; font-size: 0.9rem;"><?= e($adminName) ?></div>
                <div style="font-size: 0.75rem; color: var(--color-text-muted);"><?= e($adminEmail) ?></div>
              </div>
              <a href="/logout.php" class="dropdown-item" style="color: var(--color-danger);">Sign Out</a>
            </div>
          </div>
        </div>
      </header>

      <!-- DASHBOARD BODY CONTENT -->
      <main class="dashboard-body">

        <!-- COMPACT MEMBER SUMMARY KPIs -->
        <section class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Total Members</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="kpi-value" id="stat-total-count">248</div>
            <div class="kpi-foot">
              <span>Registered Gym Athletes</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Active Members</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="kpi-value" style="color: var(--color-accent);" id="stat-active-count">213</div>
            <div class="kpi-foot" style="color: var(--color-accent);">
              <span>85.8% Active Subscriptions</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Expiring Soon</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="kpi-value" style="color: #FF9F0A;">17</div>
            <div class="kpi-foot" style="color: #FF9F0A;">
              <span>Renewal notice sent</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Inactive / Expired</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="kpi-value" style="color: var(--color-danger);">18</div>
            <div class="kpi-foot" style="color: var(--color-danger);">
              <span>Action required for follow up</span>
            </div>
          </div>
        </section>

        <!-- SEARCH & FILTER TOOLBAR -->
        <section class="filters-bar">
          <div class="filters-group-left">
            <div class="filter-search-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input type="text" id="member-search-input" placeholder="Search members by name, email, or phone..." aria-label="Search members">
            </div>

            <select class="filter-select" id="status-filter" aria-label="Filter by account status">
              <option value="all">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
              <option value="expired">Expired</option>
            </select>

            <select class="filter-select" id="plan-filter" aria-label="Filter by membership plan">
              <option value="all">All Plans</option>
              <option value="starter">Starter Plan</option>
              <option value="pro">Pro Plan</option>
              <option value="elite">Elite Plan</option>
            </select>
          </div>

          <div style="font-size: 0.8rem; color: var(--color-text-muted); font-weight: 600;" id="member-count-indicator">
            Showing <?= count($membersList) ?> members
          </div>
        </section>

        <!-- MEMBER TABLE PANEL -->
        <section class="panel-card">
          <div class="table-responsive">
            <table class="data-table" id="members-table">
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Contact</th>
                  <th>Membership</th>
                  <th>Joined Date</th>
                  <th>Expiry Date</th>
                  <th>Status</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody id="members-table-body">
                <?php foreach ($membersList as $m): ?>
                  <tr data-name="<?= strtolower(e($m['name'])) ?>" 
                      data-email="<?= strtolower(e($m['email'])) ?>" 
                      data-phone="<?= strtolower(e($m['phone'])) ?>"
                      data-status="<?= strtolower(e($m['status'])) ?>"
                      data-plan="<?= strtolower(e($m['plan'])) ?>">
                    <td>
                      <div class="member-cell">
                        <div class="member-avatar"><?= e($m['avatar']) ?></div>
                        <div>
                          <div class="member-info-name"><?= e($m['name']) ?></div>
                          <div class="member-info-email"><?= e($m['email']) ?></div>
                        </div>
                      </div>
                    </td>
                    <td><span style="font-family: monospace; font-size: 0.825rem; color: var(--color-text-muted);"><?= e($m['phone']) ?></span></td>
                    <td>
                      <?php if (strpos(strtolower($m['plan']), 'pro') !== false): ?>
                        <span style="font-weight: 700; color: var(--color-accent);"><?= e(strtoupper($m['plan'])) ?></span>
                      <?php elseif (strpos(strtolower($m['plan']), 'elite') !== false): ?>
                        <span style="font-weight: 700; color: #FFF;"><?= e(strtoupper($m['plan'])) ?></span>
                      <?php else: ?>
                        <span style="font-weight: 600; color: var(--color-text-muted);"><?= e(strtoupper($m['plan'])) ?></span>
                      <?php endif; ?>
                    </td>
                    <td><span style="font-size: 0.8rem; color: var(--color-text-muted);"><?= e($m['joined']) ?></span></td>
                    <td><span style="font-size: 0.8rem; color: var(--color-text-muted);"><?= e($m['expiry']) ?></span></td>
                    <td>
                      <?php
                        $statusClass = 'active';
                        if ($m['status'] === 'expired') $statusClass = 'expired';
                        elseif ($m['status'] === 'inactive') $statusClass = 'inactive';
                        elseif ($m['status'] === 'suspended') $statusClass = 'suspended';
                      ?>
                      <span class="status-pill <?= $statusClass ?>">
                        <span class="status-dot-sm"></span> <?= e(ucfirst($m['status'])) ?>
                      </span>
                    </td>
                    <td style="text-align: right;">
                      <div class="table-actions" style="justify-content: flex-end;">
                        <button class="btn-action-sm view-member-btn" data-id="<?= $m['id'] ?>" data-name="<?= e($m['name']) ?>" data-email="<?= e($m['email']) ?>" data-phone="<?= e($m['phone']) ?>" data-plan="<?= e($m['plan']) ?>" data-status="<?= e($m['status']) ?>" data-joined="<?= e($m['joined']) ?>" data-expiry="<?= e($m['expiry']) ?>">View</button>
                        <button class="btn-action-sm edit-member-btn" data-id="<?= $m['id'] ?>" data-name="<?= e($m['name']) ?>" data-email="<?= e($m['email']) ?>" data-phone="<?= e($m['phone']) ?>" data-plan="<?= e($m['plan']) ?>" data-status="<?= e($m['status']) ?>">Edit</button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

      </main>
    </div>
  </div>

  <!-- ==========================================================================
       ADD MEMBER MODAL (UI ONLY)
       ========================================================================== -->
  <div class="modal-overlay" id="add-member-modal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>+ ADD NEW GYM MEMBER</h3>
        <button class="modal-close-btn" id="close-add-modal-btn" aria-label="Close modal">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <form id="add-member-form">
        <div class="modal-body">
          <div class="form-grid-2">
            <div class="form-group">
              <label for="new-first-name" class="form-label">First Name</label>
              <input type="text" id="new-first-name" class="form-control" placeholder="e.g. Rahul" required>
            </div>
            <div class="form-group">
              <label for="new-last-name" class="form-label">Last Name</label>
              <input type="text" id="new-last-name" class="form-control" placeholder="e.g. Verma" required>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="new-email" class="form-label">Email Address</label>
              <input type="email" id="new-email" class="form-control" placeholder="rahul@example.com" required>
            </div>
            <div class="form-group">
              <label for="new-phone" class="form-label">Phone Number</label>
              <input type="tel" id="new-phone" class="form-control" placeholder="+91 9876543210" required>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="new-plan" class="form-label">Membership Plan</label>
              <select id="new-plan" class="form-control">
                <option value="Starter Plan">Starter Plan (₹999/mo)</option>
                <option value="Pro Plan" selected>Pro Plan (₹1,999/mo)</option>
                <option value="Elite Plan">Elite Plan (₹2,999/mo)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="new-trainer" class="form-label">Assigned Trainer</label>
              <select id="new-trainer" class="form-control">
                <option value="Marcus Vance">Marcus Vance (Head Strength)</option>
                <option value="Elena Rostova">Elena Rostova (Conditioning)</option>
                <option value="None">Unassigned</option>
              </select>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="new-start-date" class="form-label">Start Date</label>
              <input type="date" id="new-start-date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
              <label for="new-status" class="form-label">Account Status</label>
              <select id="new-status" class="form-control">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancel-add-modal-btn">Cancel</button>
          <button type="submit" class="btn btn-primary">SAVE MEMBER</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ==========================================================================
       VIEW / EDIT MEMBER DETAIL MODAL (UI ONLY)
       ========================================================================== -->
  <div class="modal-overlay" id="member-detail-modal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 id="detail-modal-title">MEMBER PROFILE</h3>
        <button class="modal-close-btn" id="close-detail-modal-btn" aria-label="Close modal">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <div class="modal-body">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--color-border);">
          <div class="avatar-circle" id="detail-avatar" style="width: 54px; height: 54px; font-size: 1.3rem;">AR</div>
          <div>
            <div style="font-size: 1.2rem; font-weight: 800; color: #FFF;" id="detail-name">Alex Rivera</div>
            <div style="font-size: 0.85rem; color: var(--color-text-muted);" id="detail-email">alex@gmail.com</div>
            <div style="margin-top: 0.35rem;"><span class="status-pill active" id="detail-status-pill"><span class="status-dot-sm"></span> Active</span></div>
          </div>
        </div>

        <div class="form-grid-2" style="margin-bottom: 1rem;">
          <div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Phone Number</div>
            <div style="font-size: 0.95rem; font-weight: 700; color: #FFF;" id="detail-phone">+91 9876543210</div>
          </div>
          <div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Membership Plan</div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--color-accent);" id="detail-plan">Pro Plan</div>
          </div>
        </div>

        <div class="form-grid-2">
          <div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Joined Date</div>
            <div style="font-size: 0.9rem; color: var(--color-text-main);" id="detail-joined">12 Aug 2026</div>
          </div>
          <div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Subscription Expiry</div>
            <div style="font-size: 0.9rem; color: var(--color-text-main);" id="detail-expiry">28 Sep 2026</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="close-detail-footer-btn">Close</button>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
