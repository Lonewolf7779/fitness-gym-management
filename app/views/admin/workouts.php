<?php
/**
 * IRONCORE Admin Workout Management Control Center
 * Bespoke live database-driven UI with real-time routine telemetry, exercise catalogs, and dynamic program builder.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

AdminMiddleware::handle();

$svc = new GymManagementService();
$workoutStats = $svc->workoutModuleStats();
$allWorkouts = $svc->workouts();
$allMembers = $svc->members('', 'active');
$allTrainers = $svc->trainers();
$allExercises = $svc->exercises();
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Workout Programs & Routines | IRONCORE</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'workouts';
    $pageHeading    = 'WORKOUT MANAGEMENT CENTER';
    $pageSubtitle   = 'Design training programs, exercise catalogs, routine schedules, and athlete assignments';
    $extraHeaderAction = '<button class="btn btn-primary" id="open-assign-workout-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>+ ASSIGN WORKOUT</span></button>';
    require_once __DIR__ . '/../layouts/admin_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 4-COLUMN BESPOKE KPI GRID -->
        <section class="module-kpi-grid cols-4">
          <div class="module-kpi-card accent-red">
            <div class="kpi-top-row">
              <span class="kpi-label">Total Programs</span>
              <div class="kpi-icon-wrap red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-total-programs"><?= (int)$workoutStats['total_programs'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Routines Created</span>
            </div>
          </div>

          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Workouts</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-active-programs" style="color: #34D399;"><?= (int)$workoutStats['active_programs'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">In Active Cycles</span>
            </div>
          </div>

          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Coached Athletes</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-assigned-athletes" style="color: #38BDF8;"><?= (int)$workoutStats['assigned_athletes'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">With Active Plans</span>
            </div>
          </div>

          <div class="module-kpi-card accent-purple">
            <div class="kpi-top-row">
              <span class="kpi-label">Exercise Library</span>
              <div class="kpi-icon-wrap purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-total-exercises" style="color: #A78BFA;"><?= (int)$workoutStats['total_exercises'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Movements Cataloged</span>
            </div>
          </div>
        </section>

        <!-- 2. TOOLBAR & FILTER CONTROLS -->
        <div class="module-toolbar-wrap">
          <div class="toolbar-primary-row">
            <div class="toolbar-left-group">
              <div class="module-search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="workout-search-input" placeholder="Search program title, athlete, coach, or goal..." autocomplete="off">
              </div>

              <select id="workout-coach-filter" class="form-control" style="width: 170px; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px;">
                <option value="all">All Coaches</option>
                <?php foreach ($allTrainers as $t): ?>
                  <option value="<?= e(strtolower($t['full_name'])) ?>"><?= e($t['full_name']) ?></option>
                <?php endforeach; ?>
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

              <div id="workout-count-indicator" style="font-size: 0.825rem; font-weight: 600; color: #8E8E9F;">
                Showing <span id="visible-workout-count" style="color: #FFF;"><?= count($allWorkouts) ?></span> of <?= count($allWorkouts) ?> programs
              </div>
            </div>
          </div>
        </div>

        <!-- 3. MAIN DIRECTORY: CARDS GRID VIEW -->
        <div id="workouts-grid-container" class="coach-card-grid">
          <?php if (empty($allWorkouts)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; background: rgba(20,20,28,0.6); border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px;">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="1.5" style="margin-bottom: 1rem;"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
              <h3 style="color: #FFF; font-size: 1.1rem; margin-bottom: 0.5rem;">No Workout Programs Created Yet</h3>
              <p style="color: #8E8E9F; font-size: 0.875rem;">Click "+ ASSIGN WORKOUT" above to author your first training routine.</p>
            </div>
          <?php else: ?>
            <?php foreach ($allWorkouts as $w): 
              $initials = '';
              $parts = explode(' ', trim($w['member_name'] ?? ''));
              foreach ($parts as $p) { if (!empty($p)) $initials .= strtoupper($p[0]); }
              $initials = substr($initials ?: 'MB', 0, 2);
              $coachName = !empty($w['trainer_name']) ? $w['trainer_name'] : 'Not assigned';
              $goal = !empty($w['goal']) ? $w['goal'] : 'General athletic power & conditioning.';
              $startFmt = !empty($w['start_date']) ? date('M d, Y', strtotime($w['start_date'])) : 'Immediate';
              $endFmt = !empty($w['end_date']) ? date('M d, Y', strtotime($w['end_date'])) : 'Continuous';
            ?>
            <div class="coach-card workout-item"
                 data-id="<?= (int)$w['id'] ?>"
                 data-title="<?= e($w['title']) ?>"
                 data-member="<?= e($w['member_name']) ?>"
                 data-coach="<?= e($coachName) ?>"
                 data-goal="<?= e($goal) ?>"
                 data-start="<?= e($startFmt) ?>"
                 data-end="<?= e($endFmt) ?>">
              
              <div>
                <div class="coach-header">
                  <div class="coach-avatar-wrap">
                    <div class="coach-avatar-lg" style="background: linear-gradient(135deg, #A855F7 0%, #4C1D95 100%);">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
                    </div>
                  </div>
                  <div class="coach-header-info">
                    <div class="coach-name" title="<?= e($w['title']) ?>"><?= e($w['title']) ?></div>
                    <div style="font-size: 0.8rem; color: #8E8E9F; margin-bottom: 0.35rem;">
                      Athlete: <strong style="color: #FFF;"><?= e($w['member_name']) ?></strong>
                    </div>
                    <div class="coach-spec-pill" style="background: rgba(168, 85, 247, 0.12); color: #C084FC; border-color: rgba(168, 85, 247, 0.25);">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>
                      <span>Coach: <?= e($coachName) ?></span>
                    </div>
                  </div>
                </div>

                <div class="coach-body">
                  <div class="coach-bio-text"><?= e($goal) ?></div>
                  
                  <div class="coach-stats-grid">
                    <div class="coach-stat-box">
                      <div class="c-stat-lbl">Start Date</div>
                      <div style="font-size: 0.85rem; font-weight: 700; color: #34D399;"><?= e($startFmt) ?></div>
                    </div>
                    <div class="coach-stat-box">
                      <div class="c-stat-lbl">End Target</div>
                      <div style="font-size: 0.85rem; font-weight: 700; color: #D1D5DB;"><?= e($endFmt) ?></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="coach-actions">
                <button type="button" class="btn btn-secondary btn-view-workout" style="flex: 1; padding: 0.5rem 0.75rem; font-size: 0.775rem; justify-content: center;" title="View Routine Breakdown">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <span>360° Routine</span>
                </button>
                <button type="button" class="btn btn-secondary btn-del-workout" style="padding: 0.5rem; color: #F87171;" title="Delete Program">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </div>

            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- 4. SECONDARY DIRECTORY: TABLE VIEW -->
        <div id="workouts-table-container" style="display: none;">
          <div class="panel-card" style="padding: 0; overflow: hidden;">
            <div class="table-responsive">
              <table class="data-table" id="workouts-table">
                <thead>
                  <tr>
                    <th>Program Title</th>
                    <th>Assigned Athlete</th>
                    <th>Coaching Trainer</th>
                    <th>Primary Goal</th>
                    <th>Active Term</th>
                    <th style="text-align: right;">Actions</th>
                  </tr>
                </thead>
                <tbody id="workouts-table-body">
                  <?php if (!empty($allWorkouts)): ?>
                    <?php foreach ($allWorkouts as $w): 
                      $coachName = !empty($w['trainer_name']) ? $w['trainer_name'] : 'Not assigned';
                      $startFmt = !empty($w['start_date']) ? date('M d, Y', strtotime($w['start_date'])) : 'Immediate';
                      $endFmt = !empty($w['end_date']) ? date('M d, Y', strtotime($w['end_date'])) : 'Continuous';
                    ?>
                    <tr class="workout-table-row"
                        data-id="<?= (int)$w['id'] ?>"
                        data-title="<?= e($w['title']) ?>"
                        data-member="<?= e($w['member_name']) ?>"
                        data-coach="<?= e($coachName) ?>"
                        data-goal="<?= e($w['goal'] ?? '') ?>"
                        data-start="<?= e($startFmt) ?>"
                        data-end="<?= e($endFmt) ?>">
                      <td>
                        <div style="font-weight: 800; color: #FFF; font-size: 0.95rem;"><?= e($w['title']) ?></div>
                      </td>
                      <td>
                        <span style="font-weight: 700; color: #38BDF8;"><?= e($w['member_name']) ?></span>
                      </td>
                      <td>
                        <span class="coach-spec-pill" style="font-size: 0.75rem;"><?= e($coachName) ?></span>
                      </td>
                      <td>
                        <span style="font-size: 0.825rem; color: #9CA3AF;"><?= e(substr($w['goal'] ?? 'General fitness', 0, 45)) ?>...</span>
                      </td>
                      <td>
                        <span style="font-size: 0.8rem; color: #D1D5DB;"><?= e($startFmt) ?> &rarr; <?= e($endFmt) ?></span>
                      </td>
                      <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 0.35rem;">
                          <button type="button" class="btn btn-secondary btn-view-workout" style="padding: 0.35rem 0.6rem;" title="View Routine">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                          </button>
                          <button type="button" class="btn btn-secondary btn-del-workout" style="padding: 0.35rem 0.6rem; color: #F87171;" title="Delete Program">
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

        <div id="no-workout-match" style="display: none; text-align: center; padding: 4rem 2rem; background: rgba(20,20,28,0.6); border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px; margin-top: 1rem;">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="1.5" style="margin-bottom: 0.75rem;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <h4 style="color: #FFF; font-size: 1rem; margin-bottom: 0.25rem;">No Workout Programs Match Filter</h4>
          <p style="color: #8E8E9F; font-size: 0.825rem;">Try adjusting your search terms or coach filter.</p>
        </div>

      </main>
  </div>

  <!-- =========================================================================
       MODAL 1: 360° WORKOUT ROUTINE BREAKDOWN
       ========================================================================= -->
  <div class="modal-overlay" id="workout-detail-modal">
    <div class="modal-card" style="max-width: 620px;">
      <div class="modal-header">
        <h3 class="modal-title">360° WORKOUT PROGRAM ROUTINE</h3>
        <button type="button" class="modal-close" data-close="workout-detail-modal">&times;</button>
      </div>
      <div class="modal-body" style="padding-top: 1rem;">
        
        <div class="profile-modal-hero">
          <div class="profile-hero-avatar" style="background: linear-gradient(135deg, #A855F7 0%, #4C1D95 100%);">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
          </div>
          <div style="flex: 1; min-width: 0;">
            <h4 id="d-workout-title" style="color: #FFF; font-size: 1.25rem; font-weight: 800; margin-bottom: 0.25rem;">—</h4>
            <div style="font-size: 0.85rem; color: #8E8E9F; margin-bottom: 0.25rem;">
              Athlete: <strong id="d-workout-athlete" style="color: #38BDF8;">—</strong>
            </div>
            <div id="d-workout-coach" class="coach-spec-pill" style="font-size: 0.75rem;">Coach: —</div>
          </div>
        </div>

        <div style="background: rgba(10, 10, 14, 0.6); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
          <div style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #8E8E9F; letter-spacing: 0.05em; margin-bottom: 0.35rem;">
            TRAINING GOAL & INSTRUCTIONS
          </div>
          <div id="d-workout-goal" style="font-size: 0.875rem; color: #D1D5DB; line-height: 1.45;">—</div>
        </div>

        <div style="margin-bottom: 1.25rem;">
          <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #8E8E9F; letter-spacing: 0.05em; margin-bottom: 0.65rem;">
            EXERCISE ROUTINE SCHEDULE
          </div>
          <div id="d-workout-exercises-list" style="display: flex; flex-direction: column; gap: 0.65rem;">
            <!-- Loaded dynamically -->
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-close="workout-detail-modal">Close Breakdown</button>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 2: ASSIGN WORKOUT PROGRAM (RESPONSIVE SINGLE/DUAL COLUMN + DYNAMIC EXERCISE BUILDER)
       ========================================================================= -->
  <div class="modal-overlay" id="assign-workout-modal">
    <div class="modal-card" style="max-width: 680px;">
      <div class="modal-header">
        <h3 class="modal-title">ASSIGN WORKOUT PROGRAM</h3>
        <button type="button" class="modal-close" data-close="assign-workout-modal">&times;</button>
      </div>
      <form id="assign-workout-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="modal-body">

          <!-- Row 1: Athlete & Coach -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Select Athlete *</label>
              <select name="member_id" class="form-control" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
                <option value="">-- Choose Member Athlete --</option>
                <?php foreach ($allMembers as $m): ?>
                  <option value="<?= (int)$m['id'] ?>">
                    <?= e($m['full_name']) ?> (<?= e($m['email']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Assigning Coach *</label>
              <select name="trainer_id" class="form-control" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
                <?php foreach ($allTrainers as $t): ?>
                  <option value="<?= (int)$t['id'] ?>"><?= e($t['full_name']) ?> (<?= e($t['specialization'] ?: 'Coach') ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Row 2: Program Title & Difficulty -->
          <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Program Title *</label>
              <input type="text" name="title" class="form-control" placeholder="e.g. Upper Body Hypertrophy & Power Foundation" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Difficulty *</label>
              <select name="difficulty" class="form-control" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
                <option value="Beginner">Beginner</option>
                <option value="Intermediate" selected>Intermediate</option>
                <option value="Advanced">Advanced</option>
              </select>
            </div>
          </div>

          <!-- Row 3: Dates -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Start Date *</label>
              <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">End Date (Optional)</label>
              <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
          </div>

          <!-- Row 4: Goal & Description -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Training Goal</label>
              <textarea name="goal" rows="2" class="form-control" placeholder="Primary athletic objective, focus areas..." style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem; resize: vertical;"></textarea>
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Program Description</label>
              <textarea name="description" rows="2" class="form-control" placeholder="Instructions, rest periods, warm-up sets..." style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem; resize: vertical;"></textarea>
            </div>
          </div>

          <!-- Row 5: Dynamic Exercises Builder -->
          <div style="background: rgba(10, 10, 14, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
              <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #8E8E9F; letter-spacing: 0.05em;">
                EXERCISES IN PROGRAM
              </span>
              <button type="button" class="btn btn-secondary" id="btn-add-exercise-row" style="padding: 0.3rem 0.65rem; font-size: 0.75rem; color: #38BDF8;">
                + Add Exercise
              </button>
            </div>

            <div id="exercise-rows-container" style="display: flex; flex-direction: column; gap: 0.75rem;">
              <!-- Initial Exercise Row -->
              <div class="exercise-builder-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 32px; gap: 0.5rem; align-items: center;">
                <select name="exercises[0][exercise_id]" required style="background: rgba(20, 20, 28, 0.9); border: 1px solid rgba(255,255,255,0.12); color: #FFF; border-radius: 6px; padding: 0.5rem; font-size: 0.825rem;">
                  <?php foreach ($allExercises as $ex): ?>
                    <option value="<?= (int)$ex['id'] ?>"><?= e($ex['name']) ?> (<?= e($ex['muscle_group']) ?>)</option>
                  <?php endforeach; ?>
                </select>
                <input type="number" name="exercises[0][sets]" value="4" min="1" max="15" placeholder="Sets" required style="background: rgba(20, 20, 28, 0.9); border: 1px solid rgba(255,255,255,0.12); color: #FFF; border-radius: 6px; padding: 0.5rem; font-size: 0.825rem;" title="Sets">
                <input type="text" name="exercises[0][reps]" value="8-10" placeholder="Reps" required style="background: rgba(20, 20, 28, 0.9); border: 1px solid rgba(255,255,255,0.12); color: #FFF; border-radius: 6px; padding: 0.5rem; font-size: 0.825rem;" title="Reps">
                <select name="exercises[0][day]" style="background: rgba(20, 20, 28, 0.9); border: 1px solid rgba(255,255,255,0.12); color: #FFF; border-radius: 6px; padding: 0.5rem; font-size: 0.825rem;">
                  <option value="Mon">Mon</option>
                  <option value="Tue">Tue</option>
                  <option value="Wed">Wed</option>
                  <option value="Thu">Thu</option>
                  <option value="Fri">Fri</option>
                  <option value="Sat">Sat</option>
                  <option value="Sun">Sun</option>
                </select>
                <div></div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-close="assign-workout-modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="assign-workout-submit-btn">Assign Program</button>
        </div>
      </form>
    </div>
  </div>

  <!-- DASHBOARD FLOATING TOAST -->
  <div id="dashboard-toast" class="dashboard-toast" style="display: none;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="toast-message">Notification</span>
  </div>

  <!-- Catalog Options Template for JS Row Clone -->
  <template id="exercise-select-options">
    <?php foreach ($allExercises as $ex): ?>
      <option value="<?= (int)$ex['id'] ?>"><?= e($ex['name']) ?> (<?= e($ex['muscle_group']) ?>)</option>
    <?php endforeach; ?>
  </template>

  <script src="/assets/js/dashboard.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = '<?= e($csrf) ?>';

    // Elements
    const searchInput = document.getElementById('workout-search-input');
    const coachFilter = document.getElementById('workout-coach-filter');
    const visibleCountElem = document.getElementById('visible-workout-count');
    const noMatchElem = document.getElementById('no-workout-match');
    const gridContainer = document.getElementById('workouts-grid-container');
    const tableContainer = document.getElementById('workouts-table-container');
    const viewCardsBtn = document.getElementById('view-cards-btn');
    const viewTableBtn = document.getElementById('view-table-btn');

    // Modals
    const detailModal = document.getElementById('workout-detail-modal');
    const assignModal = document.getElementById('assign-workout-modal');

    // Dynamic Exercise Rows
    const rowsContainer = document.getElementById('exercise-rows-container');
    const btnAddRow = document.getElementById('btn-add-exercise-row');
    const selectOptionsHtml = document.getElementById('exercise-select-options').innerHTML;
    let rowIndex = 1;

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

    // View Switching
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
    const filterWorkouts = () => {
      const query = (searchInput?.value || '').toLowerCase().trim();
      const coach = (coachFilter?.value || 'all').toLowerCase();

      const cardItems = gridContainer.querySelectorAll('.workout-item');
      const tableRows = tableContainer.querySelectorAll('.workout-table-row');

      let visible = 0;

      const checkMatch = (el) => {
        const title = (el.getAttribute('data-title') || '').toLowerCase();
        const member = (el.getAttribute('data-member') || '').toLowerCase();
        const elCoach = (el.getAttribute('data-coach') || '').toLowerCase();
        const goal = (el.getAttribute('data-goal') || '').toLowerCase();

        const matchesQuery = !query || title.includes(query) || member.includes(query) || elCoach.includes(query) || goal.includes(query);
        const matchesCoach = (coach === 'all') || elCoach.includes(coach);

        return matchesQuery && matchesCoach;
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

    searchInput?.addEventListener('input', filterWorkouts);
    coachFilter?.addEventListener('change', filterWorkouts);

    // Open Assign Modal
    document.getElementById('open-assign-workout-btn')?.addEventListener('click', () => {
      document.getElementById('assign-workout-form')?.reset();
      openModal(assignModal);
    });

    // Dynamic Exercise Row Addition
    btnAddRow?.addEventListener('click', () => {
      const newRow = document.createElement('div');
      newRow.className = 'exercise-builder-row';
      newRow.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 32px; gap: 0.5rem; align-items: center;';
      newRow.innerHTML = `
        <select name="exercises[${rowIndex}][exercise_id]" required style="background: rgba(20, 20, 28, 0.9); border: 1px solid rgba(255,255,255,0.12); color: #FFF; border-radius: 6px; padding: 0.5rem; font-size: 0.825rem;">
          ${selectOptionsHtml}
        </select>
        <input type="number" name="exercises[${rowIndex}][sets]" value="3" min="1" max="15" placeholder="Sets" required style="background: rgba(20, 20, 28, 0.9); border: 1px solid rgba(255,255,255,0.12); color: #FFF; border-radius: 6px; padding: 0.5rem; font-size: 0.825rem;">
        <input type="text" name="exercises[${rowIndex}][reps]" value="10-12" placeholder="Reps" required style="background: rgba(20, 20, 28, 0.9); border: 1px solid rgba(255,255,255,0.12); color: #FFF; border-radius: 6px; padding: 0.5rem; font-size: 0.825rem;">
        <select name="exercises[${rowIndex}][day]" style="background: rgba(20, 20, 28, 0.9); border: 1px solid rgba(255,255,255,0.12); color: #FFF; border-radius: 6px; padding: 0.5rem; font-size: 0.825rem;">
          <option value="Mon">Mon</option>
          <option value="Tue">Tue</option>
          <option value="Wed">Wed</option>
          <option value="Thu">Thu</option>
          <option value="Fri">Fri</option>
          <option value="Sat">Sat</option>
          <option value="Sun">Sun</option>
        </select>
        <button type="button" class="btn-remove-row" style="background: none; border: none; color: #F87171; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
      `;
      rowsContainer.appendChild(newRow);
      rowIndex++;
    });

    rowsContainer?.addEventListener('click', (e) => {
      if (e.target.closest('.btn-remove-row')) {
        e.target.closest('.exercise-builder-row')?.remove();
      }
    });

    // 360° Routine View and Delete Actions
    const bindWorkoutActions = (container) => {
      container.addEventListener('click', (e) => {
        const btnView = e.target.closest('.btn-view-workout');
        const btnDel = e.target.closest('.btn-del-workout');

        const item = e.target.closest('.workout-item, .workout-table-row');
        if (!item) return;

        const id = item.getAttribute('data-id');
        const title = item.getAttribute('data-title');
        const member = item.getAttribute('data-member');
        const coach = item.getAttribute('data-coach');
        const goal = item.getAttribute('data-goal');

        if (btnView) {
          document.getElementById('d-workout-title').textContent = title;
          document.getElementById('d-workout-athlete').textContent = member;
          document.getElementById('d-workout-coach').textContent = `Coach: ${coach}`;
          document.getElementById('d-workout-goal').textContent = goal || 'General fitness progression.';

          const exList = document.getElementById('d-workout-exercises-list');
          exList.innerHTML = '<div style="color: #8E8E9F; font-size: 0.85rem;">Loading exercises...</div>';

          fetch(`/api.php?action=workout_details&id=${id}`)
            .then(r => r.json())
            .then(res => {
              if (res.success && res.data && Array.isArray(res.data.exercises)) {
                if (res.data.exercises.length === 0) {
                  exList.innerHTML = '<div style="color: #8E8E9F; font-size: 0.85rem;">No exercises added to this routine yet.</div>';
                } else {
                  exList.innerHTML = res.data.exercises.map(ex => `
                    <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; padding: 0.65rem 0.85rem;">
                      <div>
                        <div style="font-weight: 700; color: #FFF; font-size: 0.875rem;">${ex.exercise_name}</div>
                        <div style="font-size: 0.75rem; color: #8E8E9F;">${ex.sets} sets &times; ${ex.reps} reps &bull; ${ex.muscle_group} (Rest: ${ex.rest_seconds}s)</div>
                      </div>
                      <span class="badge-trend positive" style="font-size: 0.725rem;">${ex.day_of_week}</span>
                    </div>
                  `).join('');
                }
              }
            })
            .catch(() => {
              exList.innerHTML = '<div style="color: #F87171; font-size: 0.85rem;">Failed to load exercise details.</div>';
            });

          openModal(detailModal);
        }

        if (btnDel) {
          if (confirm(`Are you sure you want to delete the workout routine "${title}"?`)) {
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('id', id);

            fetch('/api.php?action=delete_workout', {
              method: 'POST',
              body: formData
            })
            .then(r => r.json())
            .then(res => {
              if (res.success) {
                showToast(`Workout routine "${title}" deleted.`);
                setTimeout(() => window.location.reload(), 600);
              } else {
                alert(res.message || 'Failed to delete workout.');
              }
            })
            .catch(() => alert('Network error while deleting workout.'));
          }
        }
      });
    };

    bindWorkoutActions(gridContainer);
    bindWorkoutActions(tableContainer);

    // Form Submission: Create Workout + Exercises
    document.getElementById('assign-workout-form')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('assign-workout-submit-btn');
      btn.disabled = true;
      btn.textContent = 'Saving Program...';

      const formData = new FormData(form);

      try {
        const createRes = await fetch('/api.php?action=create_workout', {
          method: 'POST',
          body: formData
        }).then(r => r.json());

        if (!createRes.success || !createRes.id) {
          alert(createRes.message || 'Failed to create workout plan.');
          btn.disabled = false;
          btn.textContent = 'Assign Program';
          return;
        }

        const planId = createRes.id;

        // Add each exercise
        const rowEls = document.querySelectorAll('.exercise-builder-row');
        for (let row of rowEls) {
          const exSelect = row.querySelector('select[name*="[exercise_id]"]');
          const setsInput = row.querySelector('input[name*="[sets]"]');
          const repsInput = row.querySelector('input[name*="[reps]"]');
          const daySelect = row.querySelector('select[name*="[day]"]');

          if (exSelect && setsInput && repsInput && daySelect) {
            const exData = new FormData();
            exData.append('csrf_token', csrfToken);
            exData.append('plan_id', planId);
            exData.append('exercise_id', exSelect.value);
            exData.append('sets', setsInput.value);
            exData.append('reps', repsInput.value);
            exData.append('day_of_week', daySelect.value);

            await fetch('/api.php?action=add_workout_exercise', {
              method: 'POST',
              body: exData
            });
          }
        }

        closeModal(assignModal);
        showToast('Workout routine successfully created and assigned.');
        setTimeout(() => window.location.reload(), 800);

      } catch (err) {
        btn.disabled = false;
        btn.textContent = 'Assign Program';
        alert('Network error while creating workout: ' + err.message);
      }
    });

  });
  </script>
</body>
</html>
