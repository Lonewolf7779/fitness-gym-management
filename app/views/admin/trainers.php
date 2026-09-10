<?php
/**
 * IRONCORE Admin Trainer & Coach Control Center
 * Bespoke live database-driven UI with real-time analytics, coach profiles, specialization tags, and roster management.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

AdminMiddleware::handle();

$svc = new GymManagementService();
$trainerStats = $svc->trainerModuleStats();
$allTrainers = $svc->trainers();
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trainer & Coach Management | IRONCORE</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'trainers';
    $pageHeading    = 'TRAINER & COACH MANAGEMENT';
    $pageSubtitle   = 'Manage certified coaches, specializations, assigned athletes, and workout programs';
    $extraHeaderAction = '<button class="btn btn-primary" id="open-add-trainer-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>+ ADD TRAINER</span></button>';
    require_once __DIR__ . '/../layouts/admin_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 4-COLUMN BESPOKE KPI GRID -->
        <section class="module-kpi-grid cols-4">
          <div class="module-kpi-card accent-red">
            <div class="kpi-top-row">
              <span class="kpi-label">Total Coaches</span>
              <div class="kpi-icon-wrap red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-total-trainers"><?= (int)$trainerStats['total_trainers'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Roster Capacity</span>
            </div>
          </div>

          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Coaches</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-active-trainers" style="color: #34D399;"><?= (int)$trainerStats['active_trainers'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">On Active Duty</span>
            </div>
          </div>

          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Assigned Athletes</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-assigned-athletes" style="color: #38BDF8;"><?= (int)$trainerStats['assigned_athletes'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Coached Members</span>
            </div>
          </div>

          <div class="module-kpi-card accent-purple">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Programs</span>
              <div class="kpi-icon-wrap purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-active-programs" style="color: #A78BFA;"><?= (int)$trainerStats['active_programs'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Routines Created</span>
            </div>
          </div>
        </section>

        <!-- 2. TOOLBAR & FILTER CONTROLS -->
        <div class="module-toolbar-wrap">
          <div class="toolbar-primary-row">
            <div class="toolbar-left-group">
              <div class="module-search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="trainer-search-input" placeholder="Search coach name, email, specialty, or bio..." autocomplete="off">
              </div>

              <select id="trainer-status-filter" class="form-control" style="width: 160px; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px;">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem;">
              <div class="view-toggle-btns">
                <button type="button" class="view-toggle-btn active" id="view-cards-btn" title="Cards View">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                  <span>Cards</span>
                </button>
                <button type="button" class="view-toggle-btn" id="view-table-btn" title="Table View">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                  <span>Table</span>
                </button>
              </div>

              <div id="trainer-count-indicator" style="font-size: 0.825rem; font-weight: 600; color: #8E8E9F;">
                Showing <span id="visible-trainer-count" style="color: #FFF;"><?= count($allTrainers) ?></span> of <?= count($allTrainers) ?> coaches
              </div>
            </div>
          </div>
        </div>

        <!-- 3. MAIN DIRECTORY: CARDS GRID VIEW -->
        <div id="trainers-grid-container" class="coach-card-grid">
          <?php if (empty($allTrainers)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; background: rgba(20,20,28,0.6); border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px;">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="1.5" style="margin-bottom: 1rem;"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
              <h3 style="color: #FFF; font-size: 1.1rem; margin-bottom: 0.5rem;">No Trainers Registered Yet</h3>
              <p style="color: #8E8E9F; font-size: 0.875rem;">Click "+ ADD TRAINER" above to register your first certified coach.</p>
            </div>
          <?php else: ?>
            <?php foreach ($allTrainers as $t): 
              $initials = '';
              $parts = explode(' ', trim($t['full_name'] ?? ''));
              foreach ($parts as $p) { if (!empty($p)) $initials .= strtoupper($p[0]); }
              $initials = substr($initials ?: 'TR', 0, 2);
              $status = strtolower($t['status'] ?? 'active');
              $specialization = !empty($t['specialization']) ? $t['specialization'] : 'General Fitness';
              $bio = !empty($t['bio']) ? $t['bio'] : 'Certified fitness professional dedicated to high-performance athlete conditioning.';
              $assignedAthletes = (int)($t['assigned_athletes'] ?? 0);
              $activePrograms = (int)($t['active_programs'] ?? 0);
            ?>
            <div class="coach-card trainer-item" 
                 data-id="<?= (int)$t['id'] ?>"
                 data-user-id="<?= (int)$t['user_id'] ?>"
                 data-name="<?= e($t['full_name']) ?>"
                 data-email="<?= e($t['email']) ?>"
                 data-phone="<?= e($t['phone'] ?? '') ?>"
                 data-specialization="<?= e($specialization) ?>"
                 data-bio="<?= e($bio) ?>"
                 data-status="<?= e($status) ?>"
                 data-assigned="<?= $assignedAthletes ?>"
                 data-programs="<?= $activePrograms ?>">
              
              <div>
                <div class="coach-header">
                  <div class="coach-avatar-wrap">
                    <div class="coach-avatar-lg"><?= e($initials) ?></div>
                  </div>
                  <div class="coach-header-info">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.2rem;">
                      <div class="coach-name" title="<?= e($t['full_name']) ?>"><?= e($t['full_name']) ?></div>
                      <span class="status-pill <?= $status === 'active' ? 'active' : 'inactive' ?>" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">
                        <?= e(strtoupper($status)) ?>
                      </span>
                    </div>
                    <div class="coach-email" title="<?= e($t['email']) ?>"><?= e($t['email']) ?></div>
                    <div class="coach-spec-pill" title="<?= e($specialization) ?>">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                      <span><?= e($specialization) ?></span>
                    </div>
                  </div>
                </div>

                <div class="coach-body">
                  <div class="coach-bio-text"><?= e($bio) ?></div>
                  
                  <div class="coach-stats-grid">
                    <div class="coach-stat-box">
                      <div class="c-stat-lbl">Assigned Athletes</div>
                      <div class="c-stat-val" style="color: #38BDF8;"><?= $assignedAthletes ?></div>
                    </div>
                    <div class="coach-stat-box">
                      <div class="c-stat-lbl">Active Programs</div>
                      <div class="c-stat-val" style="color: #A78BFA;"><?= $activePrograms ?></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="coach-actions">
                <button type="button" class="btn btn-secondary btn-view-360" style="flex: 1; padding: 0.5rem 0.75rem; font-size: 0.775rem; justify-content: center;" title="View 360 Profile">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <span>360° Profile</span>
                </button>
                <button type="button" class="btn btn-secondary btn-edit-trainer" style="padding: 0.5rem; color: #38BDF8;" title="Edit Coach Details">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button type="button" class="btn btn-secondary btn-pwd-trainer" style="padding: 0.5rem; color: #FBBF24;" title="Reset Password">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </button>
                <button type="button" class="btn btn-secondary btn-del-trainer" style="padding: 0.5rem; color: #F87171;" title="Delete Coach">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </div>

            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- 4. SECONDARY DIRECTORY: TABLE VIEW -->
        <div id="trainers-table-container" style="display: none;">
          <div class="panel-card" style="padding: 0; overflow: hidden;">
            <div class="table-responsive">
              <table class="data-table" id="trainers-table">
                <thead>
                  <tr>
                    <th>Coach Profile</th>
                    <th>Specialization</th>
                    <th>Phone</th>
                    <th>Assigned Athletes</th>
                    <th>Active Programs</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                  </tr>
                </thead>
                <tbody id="trainers-table-body">
                  <?php if (!empty($allTrainers)): ?>
                    <?php foreach ($allTrainers as $t): 
                      $initials = '';
                      $parts = explode(' ', trim($t['full_name'] ?? ''));
                      foreach ($parts as $p) { if (!empty($p)) $initials .= strtoupper($p[0]); }
                      $initials = substr($initials ?: 'TR', 0, 2);
                      $status = strtolower($t['status'] ?? 'active');
                      $specialization = !empty($t['specialization']) ? $t['specialization'] : 'General Fitness';
                      $assignedAthletes = (int)($t['assigned_athletes'] ?? 0);
                      $activePrograms = (int)($t['active_programs'] ?? 0);
                    ?>
                    <tr class="trainer-table-row"
                        data-id="<?= (int)$t['id'] ?>"
                        data-user-id="<?= (int)$t['user_id'] ?>"
                        data-name="<?= e($t['full_name']) ?>"
                        data-email="<?= e($t['email']) ?>"
                        data-phone="<?= e($t['phone'] ?? '') ?>"
                        data-specialization="<?= e($specialization) ?>"
                        data-bio="<?= e($t['bio'] ?? '') ?>"
                        data-status="<?= e($status) ?>"
                        data-assigned="<?= $assignedAthletes ?>"
                        data-programs="<?= $activePrograms ?>">
                      <td>
                        <div class="member-cell">
                          <div class="member-avatar" style="background: linear-gradient(135deg, #E50914 0%, #7F1D1D 100%); font-weight: 800;"><?= e($initials) ?></div>
                          <div>
                            <div class="member-info-name"><?= e($t['full_name']) ?></div>
                            <div class="member-info-email"><?= e($t['email']) ?></div>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="coach-spec-pill"><?= e($specialization) ?></span>
                      </td>
                      <td>
                        <span style="font-size: 0.85rem; color: #D1D5DB; font-family: monospace;"><?= e($t['phone'] ?: '—') ?></span>
                      </td>
                      <td>
                        <span class="badge-trend positive" style="font-size: 0.8rem;"><?= $assignedAthletes ?> Athletes</span>
                      </td>
                      <td>
                        <span class="badge-trend neutral" style="font-size: 0.8rem;"><?= $activePrograms ?> Programs</span>
                      </td>
                      <td>
                        <span class="status-pill <?= $status === 'active' ? 'active' : 'inactive' ?>">
                          <?= e(strtoupper($status)) ?>
                        </span>
                      </td>
                      <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 0.35rem;">
                          <button type="button" class="btn btn-secondary btn-view-360" style="padding: 0.35rem 0.6rem;" title="View 360 Profile">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                          </button>
                          <button type="button" class="btn btn-secondary btn-edit-trainer" style="padding: 0.35rem 0.6rem; color: #38BDF8;" title="Edit Coach">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                          </button>
                          <button type="button" class="btn btn-secondary btn-del-trainer" style="padding: 0.35rem 0.6rem; color: #F87171;" title="Delete Coach">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div id="no-trainer-match" style="display: none; text-align: center; padding: 4rem 2rem; background: rgba(20,20,28,0.6); border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px; margin-top: 1rem;">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="1.5" style="margin-bottom: 0.75rem;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <h4 style="color: #FFF; font-size: 1rem; margin-bottom: 0.25rem;">No Coaches Match Your Filter</h4>
          <p style="color: #8E8E9F; font-size: 0.825rem;">Try adjusting your search terms or status criteria.</p>
        </div>

      </main>
  </div>

  <!-- =========================================================================
       MODAL 1: 360° COACH PROFILE MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="trainer-detail-modal">
    <div class="modal-card" style="max-width: 580px;">
      <div class="modal-header">
        <h3 class="modal-title">360° COACH PROFILE</h3>
        <button type="button" class="modal-close" data-close="trainer-detail-modal">&times;</button>
      </div>
      <div class="modal-body" style="padding-top: 1rem;">
        
        <div class="profile-modal-hero">
          <div class="profile-hero-avatar" id="d-trainer-avatar">TR</div>
          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.25rem;">
              <h4 id="d-trainer-name" style="color: #FFF; font-size: 1.25rem; font-weight: 800; margin: 0;">—</h4>
              <span id="d-trainer-status-pill" class="status-pill active">ACTIVE</span>
            </div>
            <div id="d-trainer-email" style="font-size: 0.85rem; color: #8E8E9F; margin-bottom: 0.35rem;">—</div>
            <div id="d-trainer-spec" class="coach-spec-pill">—</div>
          </div>
        </div>

        <div class="profile-stat-strip">
          <div class="profile-strip-card">
            <div class="strip-lbl">Assigned Athletes</div>
            <div class="strip-val" id="d-trainer-athletes" style="color: #38BDF8;">0</div>
          </div>
          <div class="profile-strip-card">
            <div class="strip-lbl">Active Programs</div>
            <div class="strip-val" id="d-trainer-programs" style="color: #A78BFA;">0</div>
          </div>
          <div class="profile-strip-card">
            <div class="strip-lbl">Direct Phone</div>
            <div class="strip-val" id="d-trainer-phone" style="font-size: 0.9rem; font-family: monospace; color: #34D399; margin-top: 0.15rem;">—</div>
          </div>
        </div>

        <div style="background: rgba(10, 10, 14, 0.6); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
          <div style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #8E8E9F; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
            PROFESSIONAL BIO & EXPERTISE
          </div>
          <div id="d-trainer-bio" style="font-size: 0.875rem; color: #D1D5DB; line-height: 1.5;">
            —
          </div>
        </div>

      </div>
      <div class="modal-footer" style="display: flex; justify-content: space-between; gap: 0.75rem;">
        <button type="button" class="btn btn-secondary" id="d-btn-reset-pwd" style="color: #FBBF24;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <span>Reset Password</span>
        </button>
        <div style="display: flex; gap: 0.5rem;">
          <button type="button" class="btn btn-secondary" data-close="trainer-detail-modal">Close</button>
          <button type="button" class="btn btn-primary" id="d-btn-edit-coach">Edit Coach</button>
        </div>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 2: ADD NEW TRAINER MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="add-trainer-modal">
    <div class="modal-card" style="max-width: 580px;">
      <div class="modal-header">
        <h3 class="modal-title">REGISTER NEW COACH</h3>
        <button type="button" class="modal-close" data-close="add-trainer-modal">&times;</button>
      </div>
      <form id="add-trainer-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="modal-body">
          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Full Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Coach Elena Rostova" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Email Address *</label>
              <input type="email" name="email" class="form-control" placeholder="elena@ironcore.com" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Initial Password *</label>
              <input type="password" name="password" class="form-control" placeholder="Trainer@123" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Phone Number</label>
              <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Specialization *</label>
              <input type="text" name="specialization" class="form-control" placeholder="e.g. Strength & Conditioning" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Experience & Bio</label>
            <textarea name="bio" class="form-control" rows="3" placeholder="Overview of coaching credentials, specialties, and athletic certifications..." style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem; resize: vertical;"></textarea>
          </div>

          <div class="form-group">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Status</label>
            <select name="status" class="form-control" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
              <option value="active" selected>Active Duty</option>
              <option value="inactive">Inactive / On Leave</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-close="add-trainer-modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="add-trainer-submit-btn">Register Coach</button>
        </div>
      </form>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 3: EDIT TRAINER MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="edit-trainer-modal">
    <div class="modal-card" style="max-width: 580px;">
      <div class="modal-header">
        <h3 class="modal-title">UPDATE COACH PROFILE</h3>
        <button type="button" class="modal-close" data-close="edit-trainer-modal">&times;</button>
      </div>
      <form id="edit-trainer-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" id="edit-trainer-id" value="">
        <div class="modal-body">
          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Full Name *</label>
            <input type="text" name="name" id="edit-trainer-name" class="form-control" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Email Address *</label>
              <input type="email" name="email" id="edit-trainer-email" class="form-control" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Phone Number</label>
              <input type="text" name="phone" id="edit-trainer-phone" class="form-control" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Specialization *</label>
            <input type="text" name="specialization" id="edit-trainer-spec" class="form-control" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
          </div>

          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Experience & Bio</label>
            <textarea name="bio" id="edit-trainer-bio" class="form-control" rows="3" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem; resize: vertical;"></textarea>
          </div>

          <div class="form-group">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Status</label>
            <select name="status" id="edit-trainer-status" class="form-control" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-close="edit-trainer-modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="edit-trainer-submit-btn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 4: RESET PASSWORD MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="reset-pwd-modal">
    <div class="modal-card" style="max-width: 440px;">
      <div class="modal-header">
        <h3 class="modal-title">RESET CREDENTIALS</h3>
        <button type="button" class="modal-close" data-close="reset-pwd-modal">&times;</button>
      </div>
      <form id="reset-pwd-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="user_id" id="reset-pwd-user-id" value="">
        <div class="modal-body">
          <p style="font-size: 0.85rem; color: #8E8E9F; margin-bottom: 1rem;">
            Set a new secure password for <strong id="reset-pwd-coach-name" style="color: #FFF;">Coach</strong>.
          </p>
          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">New Password *</label>
            <input type="password" name="new_password" id="reset-new-password" class="form-control" required minlength="6" placeholder="Enter new password (min 6 chars)" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
          </div>
          <div class="form-group">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Confirm Password *</label>
            <input type="password" id="reset-confirm-password" class="form-control" required minlength="6" placeholder="Re-type new password" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-close="reset-pwd-modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="reset-pwd-submit-btn">Update Password</button>
        </div>
      </form>
    </div>
  </div>

  <!-- DASHBOARD FLOATING TOAST -->
  <div id="dashboard-toast" class="dashboard-toast" style="display: none;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="toast-message">Notification</span>
  </div>

  <script src="/assets/js/dashboard.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = '<?= e($csrf) ?>';

    // Elements
    const searchInput = document.getElementById('trainer-search-input');
    const statusFilter = document.getElementById('trainer-status-filter');
    const visibleCountElem = document.getElementById('visible-trainer-count');
    const noMatchElem = document.getElementById('no-trainer-match');
    const gridContainer = document.getElementById('trainers-grid-container');
    const tableContainer = document.getElementById('trainers-table-container');
    const viewCardsBtn = document.getElementById('view-cards-btn');
    const viewTableBtn = document.getElementById('view-table-btn');

    // Modals
    const detailModal = document.getElementById('trainer-detail-modal');
    const addModal = document.getElementById('add-trainer-modal');
    const editModal = document.getElementById('edit-trainer-modal');
    const resetPwdModal = document.getElementById('reset-pwd-modal');

    // KPI Counters
    const statTotal = document.getElementById('stat-total-trainers');
    const statActive = document.getElementById('stat-active-trainers');
    const statAssigned = document.getElementById('stat-assigned-athletes');
    const statPrograms = document.getElementById('stat-active-programs');

    // Toast helper
    const showToast = (msg) => {
      const toast = document.getElementById('dashboard-toast');
      const toastMsg = document.getElementById('toast-message');
      if (!toast || !toastMsg) return;
      toastMsg.textContent = msg;
      toast.style.display = 'flex';
      setTimeout(() => { toast.style.display = 'none'; }, 3500);
    };

    const openModal = (m) => { if (m) m.style.display = 'flex'; };
    const closeModal = (m) => { if (m) m.style.display = 'none'; };

    document.querySelectorAll('[data-close]').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-close');
        closeModal(document.getElementById(id));
      });
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal(overlay);
      });
    });

    // View Switching (Cards vs Table)
    viewCardsBtn?.addEventListener('click', () => {
      viewCardsBtn.classList.add('active');
      viewTableBtn?.classList.remove('active');
      gridContainer.style.display = 'grid';
      tableContainer.style.display = 'none';
    });

    viewTableBtn?.addEventListener('click', () => {
      viewTableBtn.classList.add('active');
      viewCardsBtn?.classList.remove('active');
      gridContainer.style.display = 'none';
      tableContainer.style.display = 'block';
    });

    // Live Search & Filter
    const filterTrainers = () => {
      const query = (searchInput?.value || '').toLowerCase().trim();
      const status = (statusFilter?.value || 'all').toLowerCase();

      const cardItems = gridContainer.querySelectorAll('.trainer-item');
      const tableRows = tableContainer.querySelectorAll('.trainer-table-row');

      let visible = 0;

      const checkMatch = (el) => {
        const name = (el.getAttribute('data-name') || '').toLowerCase();
        const email = (el.getAttribute('data-email') || '').toLowerCase();
        const phone = (el.getAttribute('data-phone') || '').toLowerCase();
        const spec = (el.getAttribute('data-specialization') || '').toLowerCase();
        const bio = (el.getAttribute('data-bio') || '').toLowerCase();
        const elStatus = (el.getAttribute('data-status') || '').toLowerCase();

        const matchesQuery = !query || name.includes(query) || email.includes(query) || phone.includes(query) || spec.includes(query) || bio.includes(query);
        const matchesStatus = (status === 'all') || (elStatus === status);

        return matchesQuery && matchesStatus;
      };

      cardItems.forEach(card => {
        const match = checkMatch(card);
        card.style.display = match ? 'flex' : 'none';
        if (match) visible++;
      });

      tableRows.forEach(row => {
        const match = checkMatch(row);
        row.style.display = match ? '' : 'none';
      });

      if (visibleCountElem) visibleCountElem.textContent = visible;
      if (noMatchElem) noMatchElem.style.display = (visible === 0 && cardItems.length > 0) ? 'block' : 'none';
    };

    searchInput?.addEventListener('input', filterTrainers);
    statusFilter?.addEventListener('change', filterTrainers);

    // Add Trainer Trigger
    document.getElementById('open-add-trainer-btn')?.addEventListener('click', () => {
      document.getElementById('add-trainer-form')?.reset();
      openModal(addModal);
    });

    // 360° Profile Detail View
    let currentDetailData = null;
    const bindDetailActions = (container) => {
      container.addEventListener('click', (e) => {
        const btn360 = e.target.closest('.btn-view-360');
        const btnEdit = e.target.closest('.btn-edit-trainer');
        const btnPwd = e.target.closest('.btn-pwd-trainer');
        const btnDel = e.target.closest('.btn-del-trainer');

        const item = e.target.closest('.trainer-item, .trainer-table-row');
        if (!item) return;

        const data = {
          id: item.getAttribute('data-id'),
          userId: item.getAttribute('data-user-id'),
          name: item.getAttribute('data-name'),
          email: item.getAttribute('data-email'),
          phone: item.getAttribute('data-phone'),
          specialization: item.getAttribute('data-specialization'),
          bio: item.getAttribute('data-bio'),
          status: item.getAttribute('data-status'),
          assigned: item.getAttribute('data-assigned'),
          programs: item.getAttribute('data-programs')
        };

        if (btn360) {
          currentDetailData = data;
          const initials = data.name.split(' ').filter(Boolean).map(p => p[0]).join('').substring(0, 2).toUpperCase() || 'TR';
          document.getElementById('d-trainer-avatar').textContent = initials;
          document.getElementById('d-trainer-name').textContent = data.name;
          document.getElementById('d-trainer-email').textContent = data.email;
          document.getElementById('d-trainer-spec').textContent = data.specialization;
          document.getElementById('d-trainer-phone').textContent = data.phone || 'No phone recorded';
          document.getElementById('d-trainer-bio').textContent = data.bio || 'No professional bio recorded.';
          document.getElementById('d-trainer-athletes').textContent = data.assigned;
          document.getElementById('d-trainer-programs').textContent = data.programs;
          
          const statusPill = document.getElementById('d-trainer-status-pill');
          statusPill.textContent = data.status.toUpperCase();
          statusPill.className = `status-pill ${data.status.toLowerCase() === 'active' ? 'active' : 'inactive'}`;

          openModal(detailModal);
        }

        if (btnEdit) {
          document.getElementById('edit-trainer-id').value = data.id;
          document.getElementById('edit-trainer-name').value = data.name;
          document.getElementById('edit-trainer-email').value = data.email;
          document.getElementById('edit-trainer-phone').value = data.phone || '';
          document.getElementById('edit-trainer-spec').value = data.specialization;
          document.getElementById('edit-trainer-bio').value = data.bio || '';
          document.getElementById('edit-trainer-status').value = data.status.toLowerCase();
          openModal(editModal);
        }

        if (btnPwd) {
          document.getElementById('reset-pwd-user-id').value = data.userId;
          document.getElementById('reset-pwd-coach-name').textContent = data.name;
          document.getElementById('reset-pwd-form')?.reset();
          openModal(resetPwdModal);
        }

        if (btnDel) {
          if (confirm(`Are you sure you want to remove coach "${data.name}"?\n\nAny athletes assigned to this coach will need to be reassigned.`)) {
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('id', data.id);

            fetch('/api.php?action=delete_trainer', {
              method: 'POST',
              body: formData
            })
            .then(r => r.json())
            .then(res => {
              if (res.success) {
                showToast(`Coach ${data.name} removed successfully.`);
                setTimeout(() => window.location.reload(), 800);
              } else {
                alert(res.message || 'Failed to remove coach.');
              }
            })
            .catch(() => alert('Network error while deleting coach.'));
          }
        }
      });
    };

    bindDetailActions(gridContainer);
    bindDetailActions(tableContainer);

    // Detail Modal Internal Action Links
    document.getElementById('d-btn-edit-coach')?.addEventListener('click', () => {
      closeModal(detailModal);
      if (currentDetailData) {
        document.getElementById('edit-trainer-id').value = currentDetailData.id;
        document.getElementById('edit-trainer-name').value = currentDetailData.name;
        document.getElementById('edit-trainer-email').value = currentDetailData.email;
        document.getElementById('edit-trainer-phone').value = currentDetailData.phone || '';
        document.getElementById('edit-trainer-spec').value = currentDetailData.specialization;
        document.getElementById('edit-trainer-bio').value = currentDetailData.bio || '';
        document.getElementById('edit-trainer-status').value = currentDetailData.status.toLowerCase();
        openModal(editModal);
      }
    });

    document.getElementById('d-btn-reset-pwd')?.addEventListener('click', () => {
      closeModal(detailModal);
      if (currentDetailData) {
        document.getElementById('reset-pwd-user-id').value = currentDetailData.userId;
        document.getElementById('reset-pwd-coach-name').textContent = currentDetailData.name;
        document.getElementById('reset-pwd-form')?.reset();
        openModal(resetPwdModal);
      }
    });

    // Add Trainer Form Submission
    document.getElementById('add-trainer-form')?.addEventListener('submit', (e) => {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('add-trainer-submit-btn');
      btn.disabled = true;
      btn.textContent = 'Registering...';

      const formData = new FormData(form);

      fetch('/api.php?action=create_trainer', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(res => {
        btn.disabled = false;
        btn.textContent = 'Register Coach';
        if (res.success) {
          closeModal(addModal);
          showToast('New coach successfully registered into roster.');
          setTimeout(() => window.location.reload(), 800);
        } else {
          alert(res.message || 'Failed to register coach.');
        }
      })
      .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Register Coach';
        alert('Network error while registering coach.');
      });
    });

    // Edit Trainer Form Submission
    document.getElementById('edit-trainer-form')?.addEventListener('submit', (e) => {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('edit-trainer-submit-btn');
      btn.disabled = true;
      btn.textContent = 'Saving...';

      const formData = new FormData(form);

      fetch('/api.php?action=update_trainer', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(res => {
        btn.disabled = false;
        btn.textContent = 'Save Changes';
        if (res.success) {
          closeModal(editModal);
          showToast('Coach profile updated successfully.');
          setTimeout(() => window.location.reload(), 800);
        } else {
          alert(res.message || 'Failed to update coach.');
        }
      })
      .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Save Changes';
        alert('Network error while updating coach.');
      });
    });

    // Reset Password Form Submission
    document.getElementById('reset-pwd-form')?.addEventListener('submit', (e) => {
      e.preventDefault();
      const p1 = document.getElementById('reset-new-password').value;
      const p2 = document.getElementById('reset-confirm-password').value;

      if (p1 !== p2) {
        alert('Passwords do not match. Please verify and try again.');
        return;
      }

      const btn = document.getElementById('reset-pwd-submit-btn');
      btn.disabled = true;
      btn.textContent = 'Updating...';

      const formData = new FormData(e.target);

      fetch('/api.php?action=reset_password', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(res => {
        btn.disabled = false;
        btn.textContent = 'Update Password';
        if (res.success) {
          closeModal(resetPwdModal);
          showToast('Coach login password successfully reset.');
        } else {
          alert(res.message || 'Failed to reset password.');
        }
      })
      .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Update Password';
        alert('Network error while resetting password.');
      });
    });

  });
  </script>
</body>
</html>
