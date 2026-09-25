<?php
/**
 * IRONCORE Admin Member Management Control Center
 * Bespoke live database-driven UI with real-time analytics, 360 athlete profiles, and subscription tracking.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';
require_once __DIR__ . '/../../services/SettingsService.php';

AdminMiddleware::handle();

$svc = new GymManagementService();
$settingsSvc = new SettingsService();
$gymSettings = $settingsSvc->all();
$currency = $gymSettings['currency'] ?? 'INR';
$currSymbol = ($currency === 'USD' || $currency === '$') ? '$' : (($currency === 'EUR' || $currency === '€') ? '€' : '₹');

$membersStats = $svc->memberModuleStats();
$allMembers = $svc->members();
$allPlans = $svc->plans(true);
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Directory & Management | IRONCORE</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'members';
    $pageHeading    = 'MEMBER DIRECTORY & MANAGEMENT';
    $pageSubtitle   = 'Live athlete records, membership statuses, and subscription lifecycles';
    $extraHeaderAction = '<button class="btn btn-primary" id="open-add-modal-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>+ ADD MEMBER</span></button>';
    require_once __DIR__ . '/../layouts/admin_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 5-COLUMN BESPOKE KPI GRID -->
        <section class="module-kpi-grid cols-5">
          <div class="module-kpi-card accent-red">
            <div class="kpi-top-row">
              <span class="kpi-label">Total Athletes</span>
              <div class="kpi-icon-wrap red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-total"><?= (int)$membersStats['total_members'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Registered Directory</span>
            </div>
          </div>

          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Members</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-active" style="color: #34D399;"><?= (int)$membersStats['active_members'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Active Status</span>
            </div>
          </div>

          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Subscriptions</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-subs" style="color: #38BDF8;"><?= (int)$membersStats['active_subscriptions'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Valid Plans</span>
            </div>
          </div>

          <div class="module-kpi-card accent-amber">
            <div class="kpi-top-row">
              <span class="kpi-label">Expiring Soon</span>
              <div class="kpi-icon-wrap amber">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-expiring" style="color: #FBBF24;"><?= (int)$membersStats['expiring_soon'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend warning">&le; 7 Days Remaining</span>
            </div>
          </div>

          <div class="module-kpi-card accent-purple">
            <div class="kpi-top-row">
              <span class="kpi-label">Expired Plans</span>
              <div class="kpi-icon-wrap purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-expired" style="color: #F87171;"><?= (int)$membersStats['expired'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend danger">Requires Renewal</span>
            </div>
          </div>
        </section>

        <!-- 2. TOOLBAR & FILTER CONTROLS -->
        <div class="module-toolbar-wrap">
          <div class="toolbar-primary-row">
            <div class="toolbar-left-group">
              <div class="module-search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="member-search-input" placeholder="Search name, email, phone or trainer..." autocomplete="off">
              </div>

              <select id="plan-filter" class="form-control" style="width: 170px; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px;">
                <option value="all">All Plans</option>
                <?php foreach ($allPlans as $p): ?>
                  <option value="<?= e(strtolower($p['title'])) ?>"><?= e($p['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div id="member-count-indicator" style="font-size: 0.825rem; font-weight: 600; color: #8E8E9F;">
              Showing <span id="visible-count" style="color: #FFF;"><?= count($allMembers) ?></span> of <?= count($allMembers) ?> members
            </div>
          </div>

          <!-- Status Filter Pills -->
          <div class="filter-pills-wrap" id="status-pills">
            <button type="button" class="filter-pill active" data-status="all">
              <span>All Members</span>
              <span class="pill-count"><?= count($allMembers) ?></span>
            </button>
            <button type="button" class="filter-pill" data-status="active">
              <span class="status-dot-sm" style="background:#34D399; width:6px; height:6px; border-radius:50%;"></span>
              <span>Active</span>
              <span class="pill-count"><?= (int)$membersStats['active_members'] ?></span>
            </button>
            <button type="button" class="filter-pill" data-status="expiring">
              <span class="status-dot-sm" style="background:#FBBF24; width:6px; height:6px; border-radius:50%;"></span>
              <span>Expiring Soon</span>
              <span class="pill-count"><?= (int)$membersStats['expiring_soon'] ?></span>
            </button>
            <button type="button" class="filter-pill" data-status="expired">
              <span class="status-dot-sm" style="background:#F87171; width:6px; height:6px; border-radius:50%;"></span>
              <span>Expired</span>
              <span class="pill-count"><?= (int)$membersStats['expired'] ?></span>
            </button>
            <button type="button" class="filter-pill" data-status="inactive">
              <span>Inactive</span>
            </button>
          </div>
        </div>

        <!-- 3. MEMBERS DATA TABLE PANEL -->
        <section class="module-panel-card">
          <div class="table-glass-wrap">
            <table class="module-data-table">
              <thead>
                <tr>
                  <th>Member Athlete</th>
                  <th>Contact</th>
                  <th>Membership Plan</th>
                  <th>Trainer & Program</th>
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
                    
                    $planRaw = strtolower($m['plan_title'] ?? '');
                    $planClass = str_contains($planRaw, 'elite') ? 'elite' : (str_contains($planRaw, 'pro') ? 'pro' : 'starter');
                    $planTitle = !empty($m['plan_title']) ? $m['plan_title'] : 'NO PLAN';

                    $userStatus = strtolower($m['status'] ?? 'active');
                    $subStatus = strtolower($m['subscription_status'] ?? '');
                    
                    // Expiry calculation
                    $isExpiringSoon = false;
                    $isExpired = false;
                    $daysLeft = null;
                    if (!empty($m['end_date'])) {
                        $now = new DateTime();
                        $exp = new DateTime($m['end_date']);
                        $diff = (int)$now->diff($exp)->format('%r%a');
                        $daysLeft = $diff;
                        if ($diff < 0) {
                            $isExpired = true;
                        } elseif ($diff <= 7) {
                            $isExpiringSoon = true;
                        }
                    }

                    $computedStatus = $userStatus;
                    if ($userStatus === 'active') {
                        if ($isExpiringSoon) $computedStatus = 'expiring';
                        if ($isExpired) $computedStatus = 'expired';
                    }

                    $joinStr = !empty($m['join_date']) ? date('M d, Y', strtotime($m['join_date'])) : '—';
                    $expStr = !empty($m['end_date']) ? date('M d, Y', strtotime($m['end_date'])) : '—';
                  ?>
                  <tr id="member-row-<?= (int)$m['id'] ?>"
                      data-id="<?= (int)$m['id'] ?>"
                      data-user-id="<?= (int)$m['user_id'] ?>"
                      data-name="<?= e(strtolower($m['full_name'])) ?>"
                      data-email="<?= e(strtolower($m['email'])) ?>"
                      data-phone="<?= e(strtolower($m['phone'] ?? '')) ?>"
                      data-trainer="<?= e(strtolower($m['trainer_name'] ?? '')) ?>"
                      data-status="<?= e($userStatus) ?>"
                      data-computed-status="<?= e($computedStatus) ?>"
                      data-plan="<?= e($planRaw) ?>">
                    <td>
                      <div class="entity-cell">
                        <div class="entity-avatar"><?= e($initials) ?></div>
                        <div class="entity-meta">
                          <div class="entity-title" id="name-val-<?= (int)$m['id'] ?>"><?= e($m['full_name']) ?></div>
                          <div class="entity-subtitle" id="email-val-<?= (int)$m['id'] ?>"><?= e($m['email']) ?></div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span style="font-family: monospace; font-size: 0.85rem; color: #9CA3AF;" id="phone-val-<?= (int)$m['id'] ?>"><?= e($m['phone'] ?? '—') ?></span>
                    </td>
                    <td>
                      <?php if (!empty($m['plan_title'])): ?>
                        <span class="plan-chip <?= e($planClass) ?>" id="plan-badge-<?= (int)$m['id'] ?>">
                          <?= e(strtoupper($planTitle)) ?>
                        </span>
                      <?php else: ?>
                        <span style="color: #6B7280; font-size: 0.775rem; font-style: italic;">No Plan</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div>
                        <div style="font-weight: 600; color: #FFF; font-size: 0.825rem;"><?= e($m['trainer_name'] ?? 'No Coach') ?></div>
                        <div style="font-size: 0.75rem; color: #8E8E9F;"><?= e($m['workout_title'] ?? 'Standard Program') ?></div>
                      </div>
                    </td>
                    <td><span style="font-size: 0.8rem; color: #9CA3AF;"><?= e($joinStr) ?></span></td>
                    <td>
                      <div>
                        <span style="font-size: 0.8rem; color: <?= $isExpired ? '#F87171' : ($isExpiringSoon ? '#FBBF24' : '#9CA3AF') ?>; font-weight: <?= ($isExpired || $isExpiringSoon) ? '700' : '400' ?>;">
                          <?= e($expStr) ?>
                        </span>
                        <?php if ($daysLeft !== null): ?>
                          <?php if ($daysLeft >= 0): ?>
                            <div style="font-size: 0.7rem; color: <?= $daysLeft <= 7 ? '#FBBF24' : '#6B7280' ?>;"><?= (int)$daysLeft ?> days left</div>
                          <?php else: ?>
                            <div style="font-size: 0.7rem; color: #F87171;">Expired <?= abs((int)$daysLeft) ?>d ago</div>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td>
                      <?php if ($computedStatus === 'expiring'): ?>
                        <span class="status-pill expiring"><span class="status-dot-sm"></span> Expiring</span>
                      <?php elseif ($computedStatus === 'expired'): ?>
                        <span class="status-pill expired"><span class="status-dot-sm"></span> Expired</span>
                      <?php elseif ($userStatus === 'active'): ?>
                        <span class="status-pill active"><span class="status-dot-sm"></span> Active</span>
                      <?php else: ?>
                        <span class="status-pill inactive"><span class="status-dot-sm"></span> <?= e(ucfirst($userStatus)) ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="row-actions-group">
                        <button type="button" class="btn-mini view-btn"
                                data-id="<?= (int)$m['id'] ?>"
                                data-user-id="<?= (int)$m['user_id'] ?>"
                                data-name="<?= e($m['full_name']) ?>"
                                data-email="<?= e($m['email']) ?>"
                                data-phone="<?= e($m['phone'] ?? '') ?>"
                                data-emergency="<?= e($m['emergency_contact'] ?? '') ?>"
                                data-gender="<?= e($m['gender'] ?? '') ?>"
                                data-dob="<?= e($m['dob'] ?? '') ?>"
                                data-address="<?= e($m['address'] ?? '') ?>"
                                data-plan="<?= e($planTitle) ?>"
                                data-price="<?= e($m['plan_price'] ?? '0') ?>"
                                data-status="<?= e($userStatus) ?>"
                                data-joined="<?= e($joinStr) ?>"
                                data-expiry="<?= e($expStr) ?>"
                                data-days-left="<?= $daysLeft !== null ? (int)$daysLeft : '' ?>"
                                data-trainer="<?= e($m['trainer_name'] ?? 'None') ?>"
                                data-workout="<?= e($m['workout_title'] ?? 'None') ?>"
                                data-checkins="<?= (int)($m['total_checkins'] ?? 0) ?>"
                                data-last-checkin="<?= e($m['last_checkin'] ?? 'Never') ?>">
                          View
                        </button>
                        <button type="button" class="btn-mini edit-btn"
                                data-id="<?= (int)$m['id'] ?>"
                                data-user-id="<?= (int)$m['user_id'] ?>"
                                data-name="<?= e($m['full_name']) ?>"
                                data-email="<?= e($m['email']) ?>"
                                data-phone="<?= e($m['phone'] ?? '') ?>"
                                data-emergency="<?= e($m['emergency_contact'] ?? '') ?>"
                                data-gender="<?= e($m['gender'] ?? '') ?>"
                                data-dob="<?= e($m['dob'] ?? '') ?>"
                                data-address="<?= e($m['address'] ?? '') ?>"
                                data-plan="<?= e($m['plan_id'] ?? '') ?>"
                                data-status="<?= e($userStatus) ?>">
                          Edit
                        </button>
                        <button type="button" class="btn-mini key-btn"
                                data-user-id="<?= (int)$m['user_id'] ?>"
                                data-name="<?= e($m['full_name']) ?>"
                                data-email="<?= e($m['email']) ?>"
                                title="Reset Password" style="color: #FBBF24; border-color: rgba(245,158,11,0.3);">
                          Key
                        </button>
                        <button type="button" class="btn-mini danger delete-btn"
                                data-id="<?= (int)$m['id'] ?>"
                                data-name="<?= e($m['full_name']) ?>"
                                title="Delete Athlete">
                          Del
                        </button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>

                <tr id="no-members-row" style="display: <?= empty($allMembers) ? '' : 'none' ?>;">
                  <td colspan="8" style="text-align: center; padding: 3.5rem 1rem; color: #8E8E9F;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.75rem; color: #4B5563;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <div style="font-size: 1.05rem; font-weight: 700; color: #FFF; margin-bottom: 0.25rem;">No matching athletes found</div>
                    <div style="font-size: 0.85rem;">Try adjusting your search keywords or filter selection.</div>
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
       1. 360 ATHLETE PROFILE MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="view-profile-modal">
    <div class="modal-card" style="max-width: 580px;">
      <div class="modal-header">
        <h3>360° ATHLETE PROFILE</h3>
        <button type="button" class="modal-close" id="close-view-modal">&times;</button>
      </div>
      <div class="modal-body" style="padding: 1.5rem;">
        
        <!-- Athlete Hero -->
        <div class="profile-modal-hero">
          <div class="profile-hero-avatar" id="modal-avatar">--</div>
          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.25rem;">
              <h2 id="modal-name" style="font-size: 1.25rem; font-weight: 800; color: #FFF; margin: 0;">No member selected</h2>
              <span id="modal-status-pill" class="status-pill"><span class="status-dot-sm"></span> —</span>
            </div>
            <div id="modal-email" style="font-size: 0.825rem; color: #9CA3AF; margin-bottom: 0.2rem;">—</div>
            <div id="modal-phone" style="font-size: 0.825rem; color: #38BDF8; font-family: monospace;">—</div>
          </div>
        </div>

        <!-- Metric Stat Strip -->
        <div class="profile-stat-strip">
          <div class="profile-strip-card">
            <div class="strip-lbl">Active Plan</div>
            <div class="strip-val" id="modal-plan" style="color: #FF4D4D; font-size: 0.95rem;">Not specified</div>
          </div>
          <div class="profile-strip-card">
            <div class="strip-lbl">Days Left</div>
            <div class="strip-val" id="modal-days-left" style="color: #34D399;">—</div>
          </div>
          <div class="profile-strip-card">
            <div class="strip-lbl">Total Check-ins</div>
            <div class="strip-val" id="modal-checkins" style="color: #38BDF8;">—</div>
          </div>
        </div>

        <!-- Detail Attributes Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; background: rgba(10,10,14,0.5); padding: 1.25rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.06); font-size: 0.825rem;">
          <div>
            <span style="color: #8E8E9F; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Assigned Coach</span>
            <div id="modal-trainer" style="color: #FFF; font-weight: 700; margin-top: 0.2rem;">Not assigned</div>
          </div>
          <div>
            <span style="color: #8E8E9F; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Workout Plan</span>
            <div id="modal-workout" style="color: #FFF; font-weight: 700; margin-top: 0.2rem;">Not assigned</div>
          </div>
          <div>
            <span style="color: #8E8E9F; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Member Since</span>
            <div id="modal-joined" style="color: #FFF; font-weight: 700; margin-top: 0.2rem;">—</div>
          </div>
          <div>
            <span style="color: #8E8E9F; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Plan Expiry Date</span>
            <div id="modal-expiry" style="color: #FFF; font-weight: 700; margin-top: 0.2rem;">—</div>
          </div>
          <div>
            <span style="color: #8E8E9F; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Last Check-In</span>
            <div id="modal-last-checkin" style="color: #FFF; font-weight: 700; margin-top: 0.2rem;">—</div>
          </div>
          <div>
            <span style="color: #8E8E9F; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Emergency Contact</span>
            <div id="modal-emergency" style="color: #FFF; font-weight: 700; margin-top: 0.2rem;">—</div>
          </div>
          <div style="grid-column: 1 / -1;">
            <span style="color: #8E8E9F; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Address</span>
            <div id="modal-address" style="color: #FFF; margin-top: 0.2rem;">—</div>
          </div>
        </div>

      </div>
      <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
        <button type="button" class="btn btn-secondary" id="close-view-footer-btn">Close</button>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       2. REGISTER NEW MEMBER MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="add-member-modal">
    <div class="modal-card" style="max-width: 540px;">
      <div class="modal-header">
        <h3>REGISTER NEW ATHLETE</h3>
        <button type="button" class="modal-close" id="close-add-modal">&times;</button>
      </div>
      <form id="add-member-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="modal-body" style="padding: 1.5rem;">
          <div id="add-form-error" class="status-pill danger" style="display: none; margin-bottom: 1rem; width: 100%; border-radius: 6px;"></div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
              <label class="form-label">First Name *</label>
              <input type="text" id="add-first-name" name="first_name" class="form-control" required placeholder="First Name">
            </div>
            <div>
              <label class="form-label">Last Name *</label>
              <input type="text" id="add-last-name" name="last_name" class="form-control" required placeholder="Last Name">
            </div>
          </div>

          <div style="margin-top: 1rem;">
            <label class="form-label">Email Address *</label>
            <input type="email" id="add-email" name="email" class="form-control" required placeholder="member@example.com">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <div>
              <label class="form-label">Phone Number *</label>
              <input type="tel" id="add-phone" name="phone" class="form-control" required placeholder="+91 98765 43210">
            </div>
            <div>
              <label class="form-label">Membership Plan</label>
              <select id="add-plan" name="plan_id" class="form-control">
                <option value="">No Plan Assigned</option>
                <?php foreach ($allPlans as $p): ?>
                  <option value="<?= (int)$p['id'] ?>" <?= $p['id'] == 2 ? 'selected' : '' ?>><?= e($p['title']) ?> (<?= $currSymbol ?><?= e($p['price']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <div>
              <label class="form-label">Account Status</label>
              <select id="add-status" name="status" class="form-control">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
            <div>
              <label class="form-label">Membership Start Date</label>
              <input type="date" id="add-start-date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
          </div>

          <div style="margin-top: 1rem;">
            <label class="form-label">Default Password</label>
            <input type="password" id="add-password" name="password" class="form-control" placeholder="Member@123 (Defaults to Member@123)">
          </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
          <button type="button" class="btn btn-secondary" id="cancel-add-btn">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submit-add-btn">+ REGISTER ATHLETE</button>
        </div>
      </form>
    </div>
  </div>

  <!-- =========================================================================
       3. EDIT MEMBER MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="edit-member-modal">
    <div class="modal-card" style="max-width: 540px;">
      <div class="modal-header">
        <h3>EDIT ATHLETE PROFILE</h3>
        <button type="button" class="modal-close" id="close-edit-modal">&times;</button>
      </div>
      <form id="edit-member-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" id="edit-member-id" name="id">
        <div class="modal-body" style="padding: 1.5rem;">
          <div id="edit-form-error" class="status-pill danger" style="display: none; margin-bottom: 1rem; width: 100%; border-radius: 6px;"></div>
          
          <div>
            <label class="form-label">Full Name *</label>
            <input type="text" id="edit-name" name="name" class="form-control" required>
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
                  <option value="<?= (int)$p['id'] ?>"><?= e($p['title']) ?> (<?= $currSymbol ?><?= e($p['price']) ?>)</option>
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

          <div style="margin-top: 1rem;">
            <label class="form-label">Address</label>
            <input type="text" id="edit-address" name="address" class="form-control">
          </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
          <button type="button" class="btn btn-secondary" id="cancel-edit-btn">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submit-edit-btn">SAVE CHANGES</button>
        </div>
      </form>
    </div>
  </div>

  <!-- =========================================================================
       4. RESET PASSWORD MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="reset-password-modal">
    <div class="modal-card" style="max-width: 440px;">
      <div class="modal-header">
        <h3>RESET MEMBER PASSWORD</h3>
        <button type="button" class="modal-close" id="close-reset-modal">&times;</button>
      </div>
      <form id="reset-password-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" id="reset-user-id" name="user_id">
        <div class="modal-body" style="padding: 1.5rem;">
          <div id="reset-form-error" class="status-pill danger" style="display: none; margin-bottom: 1rem; width: 100%; border-radius: 6px;"></div>
          <p style="color: #9CA3AF; font-size: 0.85rem; margin-bottom: 1rem;">
            Set a new access password for <strong id="reset-user-name" style="color: #FFF;"></strong>.
          </p>
          <div>
            <label class="form-label">New Password *</label>
            <input type="password" id="reset-new-password" name="new_password" class="form-control" required placeholder="Minimum 6 characters" minlength="6">
          </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
          <button type="button" class="btn btn-secondary" id="cancel-reset-btn">Cancel</button>
          <button type="submit" class="btn btn-primary">UPDATE PASSWORD</button>
        </div>
      </form>
    </div>
  </div>

  <!-- =========================================================================
       5. FLOATING TOAST NOTIFICATION
       ========================================================================= -->
  <div id="module-toast" style="position: fixed; bottom: 2rem; right: 2rem; z-index: 9999; background: #16161D; border: 1px solid #E50914; padding: 0.9rem 1.4rem; border-radius: 8px; box-shadow: 0 12px 36px rgba(0,0,0,0.8); display: none; align-items: center; gap: 0.75rem;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#E50914" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="toast-msg" style="font-weight: 700; font-size: 0.9rem; color: #FFF;"></span>
  </div>

  <!-- Main System Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>

  <!-- Bespoke Member Management Script -->
  <script>
  (function() {
    function showToast(msg) {
      const toast = document.getElementById('module-toast');
      const text = document.getElementById('toast-msg');
      if (!toast || !text) return;
      text.textContent = msg;
      toast.style.display = 'flex';
      setTimeout(() => { toast.style.display = 'none'; }, 3500);
    }

    const searchInput = document.getElementById('member-search-input');
    const planFilter = document.getElementById('plan-filter');
    const statusPills = document.querySelectorAll('#status-pills .filter-pill');
    let currentStatus = 'all';

    function filterTable() {
      const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
      const plan = (planFilter ? planFilter.value : 'all').toLowerCase();
      const rows = document.querySelectorAll('#members-table-body tr[id^="member-row-"]');
      let visibleCount = 0;

      rows.forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const phone = row.dataset.phone || '';
        const trainer = row.dataset.trainer || '';
        const rowPlan = row.dataset.plan || '';
        const rowStatus = row.dataset.status || '';
        const compStatus = row.dataset.computedStatus || rowStatus;

        const matchesQuery = !query || name.includes(query) || email.includes(query) || phone.includes(query) || trainer.includes(query);
        const matchesPlan = plan === 'all' || rowPlan.includes(plan);
        let matchesStatus = true;
        if (currentStatus !== 'all') {
          if (currentStatus === 'expiring') {
            matchesStatus = (compStatus === 'expiring');
          } else if (currentStatus === 'expired') {
            matchesStatus = (compStatus === 'expired');
          } else {
            matchesStatus = (rowStatus === currentStatus);
          }
        }

        if (matchesQuery && matchesPlan && matchesStatus) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });

      const noRow = document.getElementById('no-members-row');
      if (noRow) noRow.style.display = visibleCount === 0 ? '' : 'none';
      const visCountEl = document.getElementById('visible-count');
      if (visCountEl) visCountEl.textContent = visibleCount;
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (planFilter) planFilter.addEventListener('change', filterTable);

    statusPills.forEach(pill => {
      pill.addEventListener('click', () => {
        statusPills.forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        currentStatus = pill.dataset.status || 'all';
        filterTable();
      });
    });

    function openModal(id) {
      const el = document.getElementById(id);
      if (el) el.classList.add('active');
    }
    function closeModal(id) {
      const el = document.getElementById(id);
      if (el) el.classList.remove('active');
    }

    ['view-profile-modal', 'add-member-modal', 'edit-member-modal', 'reset-password-modal'].forEach(mId => {
      const modal = document.getElementById(mId);
      if (!modal) return;
      modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal(mId);
      });
    });

    document.getElementById('close-view-modal')?.addEventListener('click', () => closeModal('view-profile-modal'));
    document.getElementById('close-view-footer-btn')?.addEventListener('click', () => closeModal('view-profile-modal'));
    
    document.getElementById('open-add-modal-btn')?.addEventListener('click', () => openModal('add-member-modal'));
    document.getElementById('close-add-modal')?.addEventListener('click', () => closeModal('add-member-modal'));
    document.getElementById('cancel-add-btn')?.addEventListener('click', () => closeModal('add-member-modal'));

    document.getElementById('close-edit-modal')?.addEventListener('click', () => closeModal('edit-member-modal'));
    document.getElementById('cancel-edit-btn')?.addEventListener('click', () => closeModal('edit-member-modal'));

    document.getElementById('close-reset-modal')?.addEventListener('click', () => closeModal('reset-password-modal'));
    document.getElementById('cancel-reset-btn')?.addEventListener('click', () => closeModal('reset-password-modal'));

    document.getElementById('members-table-body')?.addEventListener('click', function(e) {
      const target = e.target.closest('button');
      if (!target) return;

      if (target.classList.contains('view-btn')) {
        const d = target.dataset;
        const initials = d.name ? d.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'MB';
        
        document.getElementById('modal-avatar').textContent = initials;
        document.getElementById('modal-name').textContent = d.name || 'Athlete';
        document.getElementById('modal-email').textContent = d.email || '—';
        document.getElementById('modal-phone').textContent = d.phone || '—';
        document.getElementById('modal-plan').textContent = (d.plan || 'NO PLAN').toUpperCase();
        document.getElementById('modal-trainer').textContent = d.trainer || 'No Coach';
        document.getElementById('modal-workout').textContent = d.workout || 'Standard Program';
        document.getElementById('modal-joined').textContent = d.joined || '—';
        document.getElementById('modal-expiry').textContent = d.expiry || '—';
        document.getElementById('modal-emergency').textContent = d.emergency || '—';
        document.getElementById('modal-address').textContent = d.address || '—';
        document.getElementById('modal-checkins').textContent = (d.checkins || '0') + ' Visits';
        document.getElementById('modal-last-checkin').textContent = d.lastCheckin || 'Never';

        const days = d.daysLeft;
        const daysEl = document.getElementById('modal-days-left');
        if (days !== '') {
          const numDays = parseInt(days, 10);
          if (numDays < 0) {
            daysEl.textContent = 'Expired (' + Math.abs(numDays) + 'd ago)';
            daysEl.style.color = '#F87171';
          } else {
            daysEl.textContent = numDays + ' Days Left';
            daysEl.style.color = numDays <= 7 ? '#FBBF24' : '#34D399';
          }
        } else {
          daysEl.textContent = 'No Subscription';
          daysEl.style.color = '#8E8E9F';
        }

        const pill = document.getElementById('modal-status-pill');
        pill.className = 'status-pill ' + (d.status === 'active' ? 'active' : 'inactive');
        pill.innerHTML = '<span class="status-dot-sm"></span> ' + (d.status ? d.status.toUpperCase() : 'ACTIVE');

        openModal('view-profile-modal');
      }

      if (target.classList.contains('edit-btn')) {
        const d = target.dataset;
        document.getElementById('edit-member-id').value = d.id;
        document.getElementById('edit-name').value = d.name || '';
        document.getElementById('edit-email').value = d.email || '';
        document.getElementById('edit-phone').value = d.phone || '';
        document.getElementById('edit-plan').value = d.plan || '';
        document.getElementById('edit-status').value = d.status || 'active';
        document.getElementById('edit-emergency').value = d.emergency || '';
        document.getElementById('edit-address').value = d.address || '';
        openModal('edit-member-modal');
      }

      if (target.classList.contains('key-btn')) {
        const d = target.dataset;
        document.getElementById('reset-user-id').value = d.userId;
        document.getElementById('reset-user-name').textContent = d.name;
        document.getElementById('reset-new-password').value = '';
        openModal('reset-password-modal');
      }

      if (target.classList.contains('delete-btn')) {
        const memberId = target.dataset.id;
        const name = target.dataset.name;
        if (!confirm('Are you sure you want to delete member "' + name + '"? This will permanently remove all subscriptions and workouts.')) {
          return;
        }

        const formData = new FormData();
        formData.append('csrf_token', '<?= e($csrf) ?>');
        formData.append('id', memberId);

        fetch('/api.php?action=delete_member', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            const row = document.getElementById('member-row-' + memberId);
            if (row) row.remove();
            showToast('Member deleted successfully.');
            filterTable();
          } else {
            alert(res.message || 'Error deleting member.');
          }
        })
        .catch(err => {
          alert('Network error while deleting member.');
        });
      }
    });

    const addForm = document.getElementById('add-member-form');
    if (addForm) {
      addForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const errDiv = document.getElementById('add-form-error');
        errDiv.style.display = 'none';

        const firstName = document.getElementById('add-first-name').value.trim();
        const lastName = document.getElementById('add-last-name').value.trim();
        const fullName = (firstName + ' ' + lastName).trim();

        const formData = new FormData(addForm);
        formData.append('name', fullName);

        fetch('/api.php?action=create_member', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast('Athlete "' + fullName + '" registered successfully!');
            closeModal('add-member-modal');
            setTimeout(() => { window.location.reload(); }, 800);
          } else {
            errDiv.textContent = res.message || 'Failed to create athlete.';
            errDiv.style.display = 'block';
          }
        })
        .catch(err => {
          errDiv.textContent = 'Server connection error.';
          errDiv.style.display = 'block';
        });
      });
    }

    const editForm = document.getElementById('edit-member-form');
    if (editForm) {
      editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const errDiv = document.getElementById('edit-form-error');
        errDiv.style.display = 'none';

        const formData = new FormData(editForm);

        fetch('/api.php?action=update_member', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast('Athlete details updated successfully!');
            closeModal('edit-member-modal');
            setTimeout(() => { window.location.reload(); }, 800);
          } else {
            errDiv.textContent = res.message || 'Failed to update athlete.';
            errDiv.style.display = 'block';
          }
        })
        .catch(err => {
          errDiv.textContent = 'Server connection error.';
          errDiv.style.display = 'block';
        });
      });
    }

    const resetForm = document.getElementById('reset-password-form');
    if (resetForm) {
      resetForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const errDiv = document.getElementById('reset-form-error');
        errDiv.style.display = 'none';

        const formData = new FormData(resetForm);

        fetch('/api.php?action=reset_password', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast('Password updated successfully.');
            closeModal('reset-password-modal');
          } else {
            errDiv.textContent = res.message || 'Failed to update password.';
            errDiv.style.display = 'block';
          }
        })
        .catch(err => {
          errDiv.textContent = 'Server connection error.';
          errDiv.style.display = 'block';
        });
      });
    }

  })();
  </script>
</body>
</html>
