<?php
/**
 * IRONCORE Admin Member Management View
 * Live database-driven UI with search, filtering, add/edit/view modals & subscription tracking.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

AdminMiddleware::handle();

$adminName  = $_SESSION['full_name'] ?? 'System Admin';
$adminEmail = $_SESSION['email'] ?? 'admin@ironcore.com';

$svc = new GymManagementService();
$allMembers = $svc->members();
$allPlans = $svc->plans(true);
$csrf = generateCsrfToken();

$totalCount = count($allMembers);
$activeCount = count(array_filter($allMembers, fn($m) => ($m['status'] ?? '') === 'active'));
$withPlanCount = count(array_filter($allMembers, fn($m) => !empty($m['plan_title'])));
$expiredCount = count(array_filter($allMembers, fn($m) => ($m['subscription_status'] ?? '') === 'expired'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Management | IRONCORE</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <div class="sidebar-overlay"></div>

    <!-- ==========================================================================
         A. SIDEBAR NAVIGATION
         ========================================================================== -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <a href="/index.php" class="brand-logo" aria-label="IRONCORE Home">
          <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
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

      <!-- TOP HEADER BAR -->
      <header class="dashboard-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <button class="admin-mobile-toggle" aria-label="Toggle navigation drawer">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>

          <div class="header-title-group">
            <h1>MEMBER DIRECTORY & MANAGEMENT</h1>
            <p>Live athlete records, membership statuses, and subscription lifecycles</p>
          </div>
        </div>

        <div class="header-actions">
          <div class="header-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="admin-search-input" placeholder="Search members, trainers..." aria-label="Global Admin Search" autocomplete="off">
            <span class="search-kbd">⌘K</span>
            <div class="header-search-results" id="admin-search-results"></div>
          </div>

          <button class="btn btn-primary" id="open-add-modal-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ ADD MEMBER</span>
          </button>
        </div>
      </header>

      <!-- MAIN DASHBOARD CONTENT AREA -->
      <main class="dashboard-body">

        <!-- METRIC CARDS -->
        <section class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Total Athletes</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="kpi-value" id="stat-total-count"><?= $totalCount ?></div>
            <div class="kpi-foot"><span>Registered Directory</span></div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Active Members</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="kpi-value" id="stat-active-count" style="color: var(--color-accent);"><?= $activeCount ?></div>
            <div class="kpi-foot" style="color: var(--color-accent);"><span>Active Access Status</span></div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Active Subscriptions</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div class="kpi-value"><?= $withPlanCount ?></div>
            <div class="kpi-foot"><span>Assigned Membership Plans</span></div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Expired Plans</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
            </div>
            <div class="kpi-value" style="color: var(--color-danger);"><?= $expiredCount ?></div>
            <div class="kpi-foot" style="color: var(--color-danger);"><span>Requires Renewal</span></div>
          </div>
        </section>

        <!-- MEMBER TABLE & CONTROLS CARD -->
        <section class="panel-card">
          <div class="table-controls-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; flex: 1;">
              <div style="position: relative; width: 300px; max-width: 100%;">
                <input type="text" id="member-search-input" class="form-control" placeholder="Search name, email, phone..." style="width: 100%; padding-left: 2.2rem;">
                <svg style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--color-text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              </div>

              <select id="status-filter" class="form-control" style="width: 140px;">
                <option value="all">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>

              <select id="plan-filter" class="form-control" style="width: 150px;">
                <option value="all">All Plans</option>
                <option value="starter">Starter</option>
                <option value="pro">Pro</option>
                <option value="elite">Elite</option>
              </select>
            </div>

            <div id="member-count-indicator" style="font-size: 0.85rem; color: var(--color-text-muted);">
              Showing <?= $totalCount ?> of <?= $totalCount ?> members
            </div>
          </div>

          <!-- Members Table -->
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Phone</th>
                  <th>Membership Plan</th>
                  <th>Joined Date</th>
                  <th>Plan Expiry</th>
                  <th>Status</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody id="members-table-body">
                <?php if (!empty($allMembers)): ?>
                  <?php foreach ($allMembers as $m): 
                    $initials = '';
                    $parts = explode(' ', trim($m['full_name']));
                    foreach ($parts as $p) { if (!empty($p)) $initials .= strtoupper($p[0]); }
                    $initials = substr($initials ?: 'MB', 0, 2);
                    $planTitle = strtoupper($m['plan_title'] ?? 'NO PLAN');
                    $planColor = str_contains($planTitle, 'PRO') ? 'var(--color-accent)' : (str_contains($planTitle, 'ELITE') ? '#FFF' : 'var(--color-text-muted)');
                    $status = strtolower($m['status'] ?? 'active');
                    $pillClass = ($status === 'expired' || $status === 'inactive' || $status === 'suspended') ? $status : 'active';
                    $expiryStr = !empty($m['end_date']) ? date('Y-m-d', strtotime($m['end_date'])) : '—';
                    $joinStr = !empty($m['join_date']) ? date('Y-m-d', strtotime($m['join_date'])) : date('Y-m-d');
                  ?>
                  <tr id="member-row-<?= (int)$m['id'] ?>"
                      data-id="<?= (int)$m['id'] ?>"
                      data-name="<?= e(strtolower($m['full_name'])) ?>"
                      data-email="<?= e(strtolower($m['email'])) ?>"
                      data-phone="<?= e(strtolower($m['phone'] ?? '')) ?>"
                      data-status="<?= e($status) ?>"
                      data-plan="<?= e(strtolower($m['plan_title'] ?? '')) ?>">
                    <td>
                      <div class="member-cell">
                        <div class="member-avatar" id="row-avatar-<?= (int)$m['id'] ?>"><?= e($initials) ?></div>
                        <div>
                          <div class="member-info-name" id="row-name-<?= (int)$m['id'] ?>"><?= e($m['full_name']) ?></div>
                          <div class="member-info-email" id="row-email-<?= (int)$m['id'] ?>"><?= e($m['email']) ?></div>
                        </div>
                      </div>
                    </td>
                    <td><span style="font-family: monospace; font-size: 0.825rem; color: var(--color-text-muted);" id="row-phone-<?= (int)$m['id'] ?>"><?= e($m['phone'] ?? '—') ?></span></td>
                    <td><span id="row-plan-<?= (int)$m['id'] ?>" style="font-weight: 700; color: <?= $planColor ?>;"><?= e($planTitle) ?></span></td>
                    <td><span style="font-size: 0.8rem; color: var(--color-text-muted);" id="row-joined-<?= (int)$m['id'] ?>"><?= e($joinStr) ?></span></td>
                    <td><span style="font-size: 0.8rem; color: var(--color-text-muted);" id="row-expiry-<?= (int)$m['id'] ?>"><?= e($expiryStr) ?></span></td>
                    <td>
                      <span class="status-pill <?= e($pillClass) ?>" id="row-status-pill-<?= (int)$m['id'] ?>">
                        <span class="status-dot-sm"></span> <?= e(ucfirst($status)) ?>
                      </span>
                    </td>
                    <td style="text-align: right;">
                      <div class="table-actions" style="justify-content: flex-end;">
                        <button type="button" class="btn-action-sm view-member-btn" 
                                data-id="<?= (int)$m['id'] ?>" 
                                data-name="<?= e($m['full_name']) ?>" 
                                data-email="<?= e($m['email']) ?>" 
                                data-phone="<?= e($m['phone'] ?? '') ?>" 
                                data-emergency="<?= e($m['emergency_contact'] ?? '') ?>"
                                data-gender="<?= e($m['gender'] ?? '') ?>"
                                data-dob="<?= e($m['dob'] ?? '') ?>"
                                data-address="<?= e($m['address'] ?? '') ?>"
                                data-plan="<?= e($m['plan_title'] ?? 'No Plan') ?>" 
                                data-status="<?= e($status) ?>" 
                                data-joined="<?= e($joinStr) ?>" 
                                data-expiry="<?= e($expiryStr) ?>">View</button>

                        <button type="button" class="btn-action-sm edit-member-btn" 
                                data-id="<?= (int)$m['id'] ?>" 
                                data-name="<?= e($m['full_name']) ?>" 
                                data-email="<?= e($m['email']) ?>" 
                                data-phone="<?= e($m['phone'] ?? '') ?>" 
                                data-emergency="<?= e($m['emergency_contact'] ?? '') ?>"
                                data-gender="<?= e($m['gender'] ?? '') ?>"
                                data-dob="<?= e($m['dob'] ?? '') ?>"
                                data-address="<?= e($m['address'] ?? '') ?>"
                                data-plan="<?= e($m['plan_id'] ?? '') ?>" 
                                data-status="<?= e($status) ?>" 
                                data-joined="<?= e($joinStr) ?>" 
                                data-expiry="<?= e($expiryStr) ?>">Edit</button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>

                <tr id="no-members-row" style="display: <?= empty($allMembers) ? '' : 'none' ?>;">
                  <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--color-text-muted);">
                    <div style="font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem;">No members found</div>
                    <div style="font-size: 0.825rem;">Try adjusting your search query or filters.</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

      </main>
    </div>
  </div>

  <!-- =========================================================================
       ADD MEMBER MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="add-member-modal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>REGISTER NEW ATHLETE</h3>
        <button type="button" class="modal-close" id="close-add-modal-btn">&times;</button>
      </div>
      <form id="add-member-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="modal-body">
          <div id="add-form-error" class="status-pill danger" style="display: none; margin-bottom: 1rem; width: 100%; border-radius: 4px;"></div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
              <label class="form-label">First Name *</label>
              <input type="text" id="new-first-name" name="first_name" class="form-control" required placeholder="Alex">
            </div>
            <div>
              <label class="form-label">Last Name *</label>
              <input type="text" id="new-last-name" name="last_name" class="form-control" required placeholder="Rivera">
            </div>
          </div>

          <div style="margin-top: 1rem;">
            <label class="form-label">Email Address *</label>
            <input type="email" id="new-email" name="email" class="form-control" required placeholder="alex@gmail.com">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <div>
              <label class="form-label">Phone Number *</label>
              <input type="tel" id="new-phone" name="phone" class="form-control" required placeholder="+91 98765 43210">
            </div>
            <div>
              <label class="form-label">Membership Plan</label>
              <select id="new-plan" name="plan_id" class="form-control">
                <option value="">No Plan Assigned</option>
                <?php foreach ($allPlans as $p): ?>
                  <option value="<?= (int)$p['id'] ?>" <?= $p['id'] == 2 ? 'selected' : '' ?>><?= e($p['title']) ?> (₹<?= e($p['price']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <div>
              <label class="form-label">Account Status</label>
              <select id="new-status" name="status" class="form-control">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
            <div>
              <label class="form-label">Start Date</label>
              <input type="date" id="new-start-date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
          </div>

          <div style="margin-top: 1rem;">
            <label class="form-label">Default Password</label>
            <input type="password" id="new-password" name="password" class="form-control" placeholder="Member@123 (Defaults to Member@123)">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancel-add-modal-btn">Cancel</button>
          <button type="submit" class="btn btn-primary">+ ADD MEMBER</button>
        </div>
      </form>
    </div>
  </div>

  <!-- =========================================================================
       VIEW MEMBER MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="member-view-modal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>ATHLETE PROFILE DETAILS</h3>
        <button type="button" class="modal-close" id="close-view-modal-btn">&times;</button>
      </div>
      <div class="modal-body">
        <div style="display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--color-border);">
          <div class="member-avatar" id="view-avatar" style="width: 54px; height: 54px; font-size: 1.25rem; font-weight: 800;">AR</div>
          <div>
            <div id="view-name" style="font-size: 1.2rem; font-weight: 800; color: #FFF;">Alex Rivera</div>
            <div id="view-email" style="font-size: 0.85rem; color: var(--color-text-muted);">alex@gmail.com</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
          <div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Phone Number</div>
            <div id="view-phone" style="font-weight: 700; color: #FFF; margin-top: 0.25rem;">+91 9876543210</div>
          </div>
          <div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Account Status</div>
            <div style="margin-top: 0.25rem;"><span class="status-pill active" id="view-status-pill"><span class="status-dot-sm"></span> Active</span></div>
          </div>
          <div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Current Plan</div>
            <div id="view-plan" style="font-weight: 800; color: var(--color-accent); margin-top: 0.25rem;">PRO PLAN</div>
          </div>
          <div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Joined Date</div>
            <div id="view-joined" style="font-weight: 700; color: #FFF; margin-top: 0.25rem;">2026-08-12</div>
          </div>
          <div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Subscription Expiry</div>
            <div id="view-expiry" style="font-weight: 700; color: #FFF; margin-top: 0.25rem;">2026-09-28</div>
          </div>
          <div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Emergency Contact</div>
            <div id="view-emergency" style="font-weight: 700; color: #FFF; margin-top: 0.25rem;">—</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="close-view-footer-btn">Close</button>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       EDIT MEMBER MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="member-edit-modal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>EDIT ATHLETE PROFILE</h3>
        <button type="button" class="modal-close" id="close-edit-modal-btn">&times;</button>
      </div>
      <form id="edit-member-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" id="edit-member-id" name="id">
        <div class="modal-body">
          <div id="edit-form-error" class="status-pill danger" style="display: none; margin-bottom: 1rem; width: 100%; border-radius: 4px;"></div>
          
          <div>
            <label class="form-label">Full Name *</label>
            <input type="text" id="edit-full-name" name="name" class="form-control" required>
          </div>

          <div style="margin-top: 1rem;">
            <label class="form-label">Email Address *</label>
            <input type="email" id="edit-email" name="email" class="form-control" required>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <div>
              <label class="form-label">Phone Number *</label>
              <input type="tel" id="edit-phone" name="phone" class="form-control" required>
            </div>
            <div>
              <label class="form-label">Membership Plan</label>
              <select id="edit-plan" name="plan_id" class="form-control">
                <option value="">No Plan Assigned</option>
                <?php foreach ($allPlans as $p): ?>
                  <option value="<?= (int)$p['id'] ?>"><?= e($p['title']) ?> (₹<?= e($p['price']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <div>
              <label class="form-label">Account Status</label>
              <select id="edit-status" name="status" class="form-control">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
            <div>
              <label class="form-label">Emergency Contact</label>
              <input type="text" id="edit-emergency" name="emergency_contact" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancel-edit-modal-btn">Cancel</button>
          <button type="submit" class="btn btn-primary">SAVE CHANGES</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast Container -->
  <div id="dashboard-toast" style="position: fixed; bottom: 2rem; right: 2rem; z-index: 999; background: var(--color-surface-card); border: 1px solid var(--color-accent); padding: 0.85rem 1.25rem; border-radius: var(--radius-md); box-shadow: 0 10px 30px rgba(0,0,0,0.8); display: none; align-items: center; gap: 0.75rem;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="toast-message" style="font-weight: 700; font-size: 0.9rem; color: #FFF;"></span>
  </div>

  <!-- Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
