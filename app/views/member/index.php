<?php
/**
 * IRONCORE Member Dashboard View Template
 * Section: Phase 3.2 Member Dashboard - 100% Live Database Integration
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/MemberMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

// Execute Member Authorization Guard
MemberMiddleware::handle();

$memberName  = $_SESSION['full_name'] ?? 'Member';
$memberEmail = $_SESSION['email'] ?? '';
$userId      = (int) ($_SESSION['user_id'] ?? 0);

$svc = new GymManagementService();
$memberData = $svc->memberDashboard($userId);
$csrf = generateCsrfToken();

$sub = $memberData['subscription'];
$planTitle = $sub ? strtoupper($sub['plan_title']) : 'NO ACTIVE PLAN';
$planStatus = $sub ? ucfirst($sub['status']) : 'Inactive';
$planColor = str_contains($planTitle, 'PRO') ? 'var(--color-accent)' : (str_contains($planTitle, 'ELITE') ? '#FFF' : 'var(--color-text-muted)');
$daysRemaining = $memberData['days_remaining'];
$streak = $memberData['attendance_streak'];
$totalCheckins = $memberData['total_checkins'];
$workoutPlan = $memberData['workout_plan'];
$exercises = $memberData['workout_exercises'];
$progressLogs = $memberData['progress_logs'];
$weeklyStats = $memberData['weekly_stats'];
$recentActivity = $memberData['recent_activity'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Portal | IRONCORE Fitness</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection    = 'dashboard';
    $pageHeading       = 'ATHLETE PERFORMANCE HUB';
    $pageSubtitle      = 'Welcome back, ' . e($memberName) . '. Live workout programs, attendance streak, & body metrics.';
    $extraHeaderAction = '<button class="btn btn-secondary" id="open-progress-modal-btn"><span>+ LOG METRICS</span></button><button class="btn btn-primary" id="self-checkin-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>CHECK IN TODAY</span></button>';
    require_once __DIR__ . '/../layouts/member_nav.php';
    ?>

      <!-- DASHBOARD BODY CONTENT -->
      <main class="dashboard-body">

        <!-- MEMBER SUMMARY KPIs -->
        <section class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Current Membership</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div class="kpi-value" style="color: <?= $planColor ?>; font-size: 1.6rem;"><?= e($planTitle) ?></div>
            <div class="kpi-foot" style="color: <?= $planStatus === 'Active' ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
              <span>Status: <?= e($planStatus) ?></span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Days Remaining</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="kpi-value"><?= e($daysRemaining) ?> Days</div>
            <div class="kpi-foot">
              <span><?= $sub && !empty($sub['end_date']) ? 'Expires ' . date('M d, Y', strtotime($sub['end_date'])) : 'No active cycle' ?></span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Active Streak</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="kpi-value" style="color: #FF9F0A;"><?= e($streak) ?> Days 🔥</div>
            <div class="kpi-foot" style="color: #FF9F0A;">
              <span>Consistent Training</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Total Check-ins</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="kpi-value"><?= e($totalCheckins) ?></div>
            <div class="kpi-foot">
              <span>All-time Gym Visits</span>
            </div>
          </div>
        </section>

        <!-- TWO COLUMN SPLIT GRID -->
        <div class="dashboard-split-grid">

          <!-- TODAY'S WORKOUT PROGRAM -->
          <section class="panel-card">
            <div class="panel-header">
              <div>
                <h3>ASSIGNED WORKOUT PROGRAM</h3>
                <div style="font-size: 0.775rem; color: var(--color-accent); font-weight: 700; margin-top: 0.2rem;">
                  <?= $workoutPlan ? e($workoutPlan['title']) : 'No Workout Assigned' ?>
                </div>
              </div>
              <?php if ($workoutPlan && !empty($workoutPlan['trainer_name'])): ?>
                <span style="font-size: 0.75rem; color: var(--color-text-muted);">Coach: <?= e($workoutPlan['trainer_name']) ?></span>
              <?php endif; ?>
            </div>

            <?php if (!empty($exercises)): ?>
              <ul class="expiry-list">
                <?php foreach ($exercises as $ex): ?>
                  <li class="expiry-item">
                    <div>
                      <div class="expiry-user-title"><?= e($ex['exercise_name']) ?> <span class="status-pill active" style="font-size: 0.65rem; padding: 0.1rem 0.4rem; margin-left: 0.5rem;"><?= e($ex['day_of_week']) ?></span></div>
                      <div class="expiry-user-sub"><?= e($ex['sets']) ?> sets × <?= e($ex['reps']) ?> reps · <?= e($ex['muscle_group']) ?> (Rest: <?= (int)$ex['rest_seconds'] ?>s)</div>
                    </div>
                    <div>
                      <span class="status-pill pending">Active</span>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                <div style="font-weight: 700; font-size: 0.95rem; color: #FFF; margin-bottom: 0.35rem;">
                  <?= $workoutPlan ? 'No Exercises Added to Plan Yet' : 'No Workout Plan Assigned Yet' ?>
                </div>
                <div style="font-size: 0.8rem;">
                  <?= $workoutPlan ? 'Your assigned trainer will configure individual sets and repetitions for this plan soon.' : 'Your certified trainer will prepare a customized fitness routine for you shortly.' ?>
                </div>
              </div>
            <?php endif; ?>
          </section>

          <!-- WEEKLY PROGRESS & BODY METRICS -->
          <div style="display: flex; flex-direction: column; gap: 1.75rem;">
            
            <!-- BODY METRICS SUMMARY CARD -->
            <section class="panel-card" style="background: linear-gradient(135deg, #181818 0%, #1E1E1E 100%); border-color: rgba(232, 255, 0, 0.3);">
              <div class="panel-header">
                <h3>BODY METRICS & COMPOSITION</h3>
                <span class="status-pill active"><?= count($progressLogs) ?> Logs</span>
              </div>
              <?php if (!empty($progressLogs)): 
                $latest = $progressLogs[0];
              ?>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 0.5rem;">
                  <div>
                    <div style="font-size: 0.725rem; color: var(--color-text-muted); text-transform: uppercase;">Body Weight</div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: #FFF; font-family: var(--font-heading);"><?= e($latest['weight_kg'] ?? '—') ?> kg</div>
                  </div>
                  <div>
                    <div style="font-size: 0.725rem; color: var(--color-text-muted); text-transform: uppercase;">Body Fat %</div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-accent); font-family: var(--font-heading);"><?= e($latest['body_fat_pct'] ? $latest['body_fat_pct'] . '%' : '—') ?></div>
                  </div>
                  <div>
                    <div style="font-size: 0.725rem; color: var(--color-text-muted); text-transform: uppercase;">Last Logged</div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #FFF; margin-top: 0.25rem;"><?= e(date('M d, Y', strtotime($latest['log_date']))) ?></div>
                  </div>
                </div>
              <?php else: ?>
                <div style="padding: 1rem 0; font-size: 0.825rem; color: var(--color-text-muted);">
                  No body measurements recorded yet. Click <strong>+ LOG METRICS</strong> above to record your weight.
                </div>
              <?php endif; ?>
            </section>

            <!-- WEEKLY ANALYTICS SUMMARY -->
            <section class="panel-card">
              <div class="panel-header">
                <h3>WEEKLY PERFORMANCE OVERVIEW</h3>
              </div>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="background-color: var(--color-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                  <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Assigned Exercises</div>
                  <div style="font-size: 1.15rem; font-weight: 800; color: #FFF; font-family: var(--font-heading);"><?= e($weeklyStats['workouts_completed']) ?></div>
                </div>

                <div style="background-color: var(--color-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                  <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Estimated Training</div>
                  <div style="font-size: 1.15rem; font-weight: 800; color: var(--color-accent); font-family: var(--font-heading);"><?= e($weeklyStats['training_time']) ?></div>
                </div>

                <div style="background-color: var(--color-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); grid-column: 1 / -1;">
                  <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Weekly Attendance Days</div>
                  <div style="font-size: 1.15rem; font-weight: 800; color: var(--color-success); font-family: var(--font-heading);"><?= e($weeklyStats['weekly_attendance']) ?></div>
                </div>
              </div>
            </section>

          </div>
        </div>

        <!-- RECENT ACTIVITY FEED -->
        <section class="panel-card">
          <div class="panel-header">
            <h3>RECENT ATHLETE ACTIVITY & CHECK-INS</h3>
          </div>
          <?php if (!empty($recentActivity)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
              <?php foreach ($recentActivity as $act): ?>
                <div style="background-color: var(--color-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                  <div style="font-weight: 700; font-size: 0.85rem; color: #FFF; margin-bottom: 0.25rem;"><?= e($act['title']) ?></div>
                  <div style="font-size: 0.775rem; color: var(--color-text-muted); margin-bottom: 0.5rem;"><?= e($act['desc']) ?></div>
                  <div style="font-size: 0.725rem; font-weight: 700; color: var(--color-accent);"><?= e($act['time']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--color-text-muted); font-size: 0.85rem;">
              No check-in or workout activity logged yet. Check in today to start your streak!
            </div>
          <?php endif; ?>
        </section>

      </main>
    </div>
  </div>

  <!-- =========================================================================
       LOG PROGRESS METRICS MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="progress-modal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>LOG BODY MEASUREMENTS</h3>
        <button type="button" class="modal-close" id="close-progress-modal-btn">&times;</button>
      </div>
      <form id="member-progress-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="modal-body">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Weight (kg) *</label>
              <input type="number" step="0.1" name="weight_kg" required class="form-control" placeholder="78.5" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
            </div>
            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Body Fat %</label>
              <input type="number" step="0.1" name="body_fat_pct" class="form-control" placeholder="18.5" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1rem;">
            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Chest (cm)</label>
              <input type="number" step="0.5" name="chest_cm" class="form-control" placeholder="102" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
            </div>
            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Waist (cm)</label>
              <input type="number" step="0.5" name="waist_cm" class="form-control" placeholder="82" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
            </div>
            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Biceps (cm)</label>
              <input type="number" step="0.5" name="biceps_cm" class="form-control" placeholder="36" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
            </div>
          </div>

          <div style="margin-top: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Date</label>
            <input type="date" name="log_date" value="<?= date('Y-m-d') ?>" class="form-control" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
          </div>

          <div style="margin-top: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Training Notes</label>
            <textarea name="notes" rows="2" class="form-control" placeholder="Felt strong, increase bench press weight next week..." style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancel-progress-modal-btn">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Measurement</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>
  <script>
    const progressModal = document.getElementById('progress-modal');
    const closeProgressBtn = document.getElementById('close-progress-modal-btn');
    const cancelProgressBtn = document.getElementById('cancel-progress-modal-btn');
    const progressForm = document.getElementById('member-progress-form');

    const toggleProgressModal = (show) => {
      if (progressModal) {
        if (show) progressModal.classList.add('show');
        else progressModal.classList.remove('show');
      }
    };

    document.addEventListener('click', (e) => {
      if (e.target.closest('#open-progress-modal-btn')) {
        e.preventDefault();
        toggleProgressModal(true);
      }
      if (e.target.closest('#self-checkin-btn')) {
        e.preventDefault();
        performSelfCheckin();
      }
    });

    closeProgressBtn?.addEventListener('click', () => toggleProgressModal(false));
    cancelProgressBtn?.addEventListener('click', () => toggleProgressModal(false));

    progressForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(progressForm);
      try {
        const res = await fetch('/api.php?action=member_add_progress', { method: 'POST', body: fd });
        const json = await res.json();
        alert(json.message);
        if (json.success) window.location.reload();
      } catch (err) {
        alert('Failed to log metrics: ' + err.message);
      }
    });

    async function performSelfCheckin() {
      const fd = new FormData();
      fd.append('csrf_token', '<?= e($csrf) ?>');
      try {
        const res = await fetch('/api.php?action=member_self_checkin', { method: 'POST', body: fd });
        const json = await res.json();
        alert(json.message);
        if (json.success) window.location.reload();
      } catch (err) {
        alert('Check-in error: ' + err.message);
      }
    }
  </script>
</body>
</html>
