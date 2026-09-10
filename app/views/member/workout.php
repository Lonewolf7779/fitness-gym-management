<?php
/**
 * IRONCORE Member - Workout Protocol & Exercise Tracker
 * Live database-driven routine breakdown, exercise checklists, and instruction guides.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

AuthMiddleware::handle();

$userId = (int) ($_SESSION['user_id'] ?? 0);
$svc = new GymManagementService();
$memberData = $svc->memberDashboard($userId);
$workoutPlan = $memberData['workout_plan'];
$exercises = $memberData['workout_exercises'];
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Workout Protocol | IRONCORE</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'workout';
    $pageHeading    = 'MY WORKOUT PROTOCOL';
    $pageSubtitle   = 'Assigned exercise routines, movement splits, and progressive training directives';
    require_once __DIR__ . '/../layouts/member_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <?php if (!$workoutPlan): ?>
          <!-- No Workout Assigned State -->
          <div class="modern-card" style="padding: var(--space-8); text-align: center; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl); margin-top: var(--space-6);">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(229, 9, 20, 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
            </div>
            <h2 style="font-size: 20px; font-weight: 800; color: var(--color-text); margin-bottom: var(--space-2);">NO ACTIVE WORKOUT ROUTINE</h2>
            <p style="font-size: 14px; color: var(--color-text-muted); max-width: 460px; margin: 0 auto var(--space-6);">
              Your coach has not assigned a personalized workout plan yet. Speak to your trainer or front desk to get a tailored training split.
            </p>
          </div>
        <?php else: ?>

          <!-- 1. 4-COLUMN KPI METRICS -->
          <section class="module-kpi-grid cols-4">
            <div class="module-kpi-card accent-red">
              <div class="kpi-top-row">
                <span class="kpi-label">Program Goal</span>
                <div class="kpi-icon-wrap red">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                </div>
              </div>
              <div class="kpi-main-val" style="font-size: 20px;"><?= htmlspecialchars($workoutPlan['goal'] ?? 'Strength') ?></div>
              <div class="kpi-sub-row">
                <span class="badge-trend positive">Target Objective</span>
              </div>
            </div>

            <div class="module-kpi-card accent-emerald">
              <div class="kpi-top-row">
                <span class="kpi-label">Total Movements</span>
                <div class="kpi-icon-wrap emerald">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
              </div>
              <div class="kpi-main-val" style="color: #34D399;"><?= count($exercises) ?></div>
              <div class="kpi-sub-row">
                <span class="badge-trend positive">Assigned Exercises</span>
              </div>
            </div>

            <div class="module-kpi-card accent-cyan">
              <div class="kpi-top-row">
                <span class="kpi-label">Assigned Coach</span>
                <div class="kpi-icon-wrap cyan">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
              </div>
              <div class="kpi-main-val" style="font-size: 18px; color: #38BDF8;"><?= htmlspecialchars($workoutPlan['trainer_name'] ?? 'Head Coach') ?></div>
              <div class="kpi-sub-row">
                <span class="badge-trend positive">Trainer</span>
              </div>
            </div>

            <div class="module-kpi-card accent-purple">
              <div class="kpi-top-row">
                <span class="kpi-label">Difficulty</span>
                <div class="kpi-icon-wrap purple">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
              </div>
              <div class="kpi-main-val" style="font-size: 18px; color: #A78BFA;"><?= htmlspecialchars($workoutPlan['difficulty'] ?? 'Intermediate') ?></div>
              <div class="kpi-sub-row">
                <span class="badge-trend neutral">Program Intensity</span>
              </div>
            </div>
          </section>

          <!-- 2. ROUTINE OVERVIEW BANNER -->
          <section class="modern-card" style="margin-top: var(--space-6); padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-4);">
              <div>
                <span class="badge badge-primary" style="margin-bottom: var(--space-2); display: inline-block;">ACTIVE PROGRAM</span>
                <h2 style="font-size: 22px; font-weight: 800; color: var(--color-text); margin: 0;"><?= htmlspecialchars($workoutPlan['title']) ?></h2>
                <p style="font-size: 14px; color: var(--color-text-muted); margin: var(--space-2) 0 0 0; line-height: 1.5; max-width: 700px;">
                  <?= htmlspecialchars($workoutPlan['description'] ?: 'Perform all prescribed sets with strict technique and controlled tempo. Rest 60-90s between sets.') ?>
                </p>
              </div>
            </div>
          </section>

          <!-- 3. INTERACTIVE EXERCISE CHECKLIST -->
          <section style="margin-top: var(--space-6);">
            <div class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text); margin: 0; text-transform: uppercase;">Exercise Execution Schedule</h3>
                <span style="font-size: 13px; color: var(--color-text-muted);">Check off sets as you complete them</span>
              </div>

              <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                <?php if (empty($exercises)): ?>
                  <p style="color: var(--color-text-muted); font-size: 14px; text-align: center; padding: var(--space-6);">No exercise movements added to this routine yet.</p>
                <?php else: foreach ($exercises as $idx => $ex): ?>
                  <div class="exercise-item-card" style="display: flex; align-items: center; justify-content: space-between; padding: var(--space-4); background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-lg); flex-wrap: wrap; gap: var(--space-3);">
                    <div style="display: flex; align-items: center; gap: var(--space-3);">
                      <input type="checkbox" id="ex-check-<?= $idx ?>" style="width: 20px; height: 20px; cursor: pointer; accent-color: var(--color-primary);" onchange="toggleExerciseCompletion(this)">
                      <div>
                        <label for="ex-check-<?= $idx ?>" style="font-size: 15px; font-weight: 700; color: var(--color-text); cursor: pointer;"><?= htmlspecialchars($ex['exercise_name']) ?></label>
                        <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;">
                          <?= htmlspecialchars($ex['muscle_group'] ?? 'General') ?> • <?= htmlspecialchars($ex['category'] ?? 'Strength') ?> • Day: <?= htmlspecialchars($ex['day_of_week'] ?? 'Mon') ?>
                        </div>
                      </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: var(--space-4);">
                      <div style="text-align: right;">
                        <div style="font-size: 15px; font-weight: 800; color: var(--color-primary);"><?= (int)$ex['sets'] ?> Sets × <?= htmlspecialchars($ex['reps']) ?> Reps</div>
                        <div style="font-size: 11px; color: var(--color-text-muted);">Rest: <?= (int)($ex['rest_seconds'] ?? 60) ?>s</div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; endif; ?>
              </div>
            </div>
          </section>

        <?php endif; ?>

      </main>
    </div>
  </div>

  <script>
    function toggleExerciseCompletion(cb) {
      const parent = cb.closest('.exercise-item-card');
      if (cb.checked) {
        parent.style.opacity = '0.5';
        parent.style.textDecoration = 'line-through';
      } else {
        parent.style.opacity = '1';
        parent.style.textDecoration = 'none';
      }
    }
  </script>

  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
