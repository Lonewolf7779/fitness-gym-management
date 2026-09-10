<?php
/**
 * IRONCORE Trainer Dashboard View Template
 * Section: Phase 3.2 Trainer Dashboard - 100% Live Database Integration
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/TrainerMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

// Execute Trainer Authorization Guard
TrainerMiddleware::handle();

$trainerName  = $_SESSION['full_name'] ?? 'Marcus Vance';
$trainerEmail = $_SESSION['email'] ?? 'marcus@ironcore.com';
$userId       = (int) ($_SESSION['user_id'] ?? 0);

$svc = new GymManagementService();
$trainerData = $svc->trainerDashboard($userId);
$allMembers  = $svc->members();
$allExercises = $svc->exercises();
$csrf        = generateCsrfToken();

$summaryStats = [
    'assigned_clients'          => $trainerData['assigned_clients_count'],
    'active_programs'           => $trainerData['active_programs_count'],
    'sessions_this_week'        => $trainerData['sessions_this_week'],
    'clients_needing_attention' => $trainerData['clients_needing_attention_count']
];

$clientsList   = $trainerData['clients'];
$todaySchedule = $trainerData['today_schedule'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trainer Portal | IRONCORE Fitness</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection    = 'dashboard';
    $pageHeading       = 'TRAINER ATHLETE HUB';
    $pageSubtitle      = 'Live assigned athletes, weekly session metrics, & routine assignments';
    $extraHeaderAction = '<button class="btn btn-primary" id="open-workout-modal-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>+ ASSIGN WORKOUT</span></button>';
    require_once __DIR__ . '/../layouts/trainer_nav.php';
    ?>

      <!-- DASHBOARD BODY CONTENT -->
      <main class="dashboard-body">

        <!-- TRAINER SUMMARY KPIs -->
        <section class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Assigned Clients</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="kpi-value"><?= e($summaryStats['assigned_clients']) ?></div>
            <div class="kpi-foot">
              <span>Active client roster</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Active Programs</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
            </div>
            <div class="kpi-value" style="color: var(--color-accent);"><?= e($summaryStats['active_programs']) ?></div>
            <div class="kpi-foot" style="color: var(--color-accent);">
              <span>Assigned training routines</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Sessions This Week</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="kpi-value"><?= e($summaryStats['sessions_this_week']) ?></div>
            <div class="kpi-foot">
              <span>Current week attendance</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Needing Attention</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="kpi-value" style="color: var(--color-danger);"><?= e($summaryStats['clients_needing_attention']) ?></div>
            <div class="kpi-foot" style="color: var(--color-danger);">
              <span>Expiring plans or low check-ins</span>
            </div>
          </div>
        </section>

        <!-- TWO COLUMN SPLIT GRID -->
        <div class="dashboard-split-grid">

          <!-- MY CLIENTS TABLE -->
          <section class="panel-card">
            <div class="panel-header">
              <h3>ATHLETE ROSTER & PROGRAM STATUS</h3>
              <span style="font-size: 0.8rem; color: var(--color-text-muted);"><?= count($clientsList) ?> Athletes</span>
            </div>

            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Client</th>
                    <th>Assigned Program</th>
                    <th>Total Check-ins</th>
                    <th>Membership Plan</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($clientsList)): ?>
                    <?php foreach ($clientsList as $client): 
                      $initials = '';
                      $parts = explode(' ', trim($client['full_name']));
                      foreach ($parts as $p) { if (!empty($p)) $initials .= strtoupper($p[0]); }
                      $initials = substr($initials ?: 'MB', 0, 2);
                      $workoutTitle = $client['workout_title'] ?? 'No Workout Assigned';
                      $planTitle = strtoupper($client['plan_title'] ?? 'NO PLAN');
                      $planColor = str_contains($planTitle, 'PRO') ? 'var(--color-accent)' : (str_contains($planTitle, 'ELITE') ? '#FFF' : 'var(--color-text-muted)');
                      $status = strtolower($client['status'] ?? 'active');
                    ?>
                      <tr>
                        <td>
                          <div class="member-cell">
                            <div class="member-avatar"><?= e($initials) ?></div>
                            <div>
                              <div class="member-info-name"><?= e($client['full_name']) ?></div>
                              <div class="member-info-email"><?= e($client['email']) ?></div>
                            </div>
                          </div>
                        </td>
                        <td><span style="font-weight: 600; color: #FFF;"><?= e($workoutTitle) ?></span></td>
                        <td><span style="font-family: monospace; font-size: 0.85rem; font-weight: 700; color: var(--color-accent);"><?= (int)$client['total_checkins'] ?> Check-ins</span></td>
                        <td><span style="font-size: 0.8rem; font-weight: 700; color: <?= $planColor ?>;"><?= e($planTitle) ?></span></td>
                        <td><span class="status-pill active"><span class="status-dot-sm"></span> <?= e(ucfirst($status)) ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--color-text-muted);">No athletes registered in roster yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </section>

          <!-- TODAY'S SCHEDULE -->
          <section class="panel-card">
            <div class="panel-header">
              <h3>TODAY'S ATHLETE CHECK-INS</h3>
              <span style="font-size: 0.775rem; color: var(--color-accent); font-weight: 700;"><?= count($todaySchedule) ?> CHECKED IN</span>
            </div>

            <ul class="expiry-list">
              <?php if (!empty($todaySchedule)): ?>
                <?php foreach ($todaySchedule as $item): ?>
                  <li class="expiry-item">
                    <div style="display: flex; align-items: center; gap: 0.85rem;">
                      <div style="font-family: var(--font-heading); font-weight: 800; font-size: 0.85rem; color: var(--color-accent); min-width: 65px;"><?= e(date('h:i A', strtotime($item['check_in_time']))) ?></div>
                      <div>
                        <div class="expiry-user-title"><?= e($item['full_name']) ?></div>
                        <div class="expiry-user-sub"><?= e($item['email']) ?></div>
                      </div>
                    </div>
                    <div>
                      <span class="status-pill active">Present</span>
                    </div>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li style="text-align: center; padding: 3rem 1rem; color: var(--color-text-muted); font-size: 0.85rem;">
                  No check-ins recorded today yet.
                </li>
              <?php endif; ?>
            </ul>
          </section>

        </div>

      </main>
    </div>
  </div>

  <!-- =========================================================================
       ASSIGN WORKOUT MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="workout-modal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>ASSIGN WORKOUT PROGRAM</h3>
        <button type="button" class="modal-close" id="close-workout-modal-btn">&times;</button>
      </div>
      <form id="trainer-workout-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="modal-body">
          <div>
            <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Select Athlete *</label>
            <select name="member_id" class="form-control" required style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
              <?php foreach ($allMembers as $m): ?>
                <option value="<?= (int)$m['id'] ?>"><?= e($m['full_name']) ?> (<?= e($m['email']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="margin-top: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Program Title *</label>
            <input type="text" name="title" class="form-control" required placeholder="e.g. Upper Body Hypertrophy & Power" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
          </div>

          <div style="margin-top: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Training Goal / Notes</label>
            <textarea name="goal" rows="2" class="form-control" placeholder="Target muscle groups, recovery guidelines..." style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);"></textarea>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Start Date</label>
              <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
            </div>
            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">End Date (Optional)</label>
              <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancel-workout-modal-btn">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Program</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>
  <script>
    const workoutModal = document.getElementById('workout-modal');
    const openWorkoutBtn = document.getElementById('open-workout-modal-btn');
    const closeWorkoutBtn = document.getElementById('close-workout-modal-btn');
    const cancelWorkoutBtn = document.getElementById('cancel-workout-modal-btn');
    const workoutForm = document.getElementById('trainer-workout-form');

    const toggleWorkoutModal = (show) => {
      if (workoutModal) {
        if (show) workoutModal.classList.add('show');
        else workoutModal.classList.remove('show');
      }
    };

    openWorkoutBtn?.addEventListener('click', () => toggleWorkoutModal(true));
    closeWorkoutBtn?.addEventListener('click', () => toggleWorkoutModal(false));
    cancelWorkoutBtn?.addEventListener('click', () => toggleWorkoutModal(false));

    workoutForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(workoutForm);
      try {
        const res = await fetch('/api.php?action=create_workout', { method: 'POST', body: fd });
        const json = await res.json();
        alert(json.message);
        if (json.success) {
          window.location.reload();
        }
      } catch (err) {
        alert('Failed to save workout: ' + err.message);
      }
    });
  </script>
</body>
</html>
