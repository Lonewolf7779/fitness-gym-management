<?php
/**
 * IRONCORE Trainer - Workout Programs Management View
 * Live database-driven routine builder, athlete workout assignments, and routine library.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/TrainerMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

TrainerMiddleware::handle();

$userId = (int) ($_SESSION['user_id'] ?? 0);
$svc = new GymManagementService();
$trainerData = $svc->trainerDashboard($userId);
$trainer = $trainerData['trainer'];
$trainerId = $trainer ? (int) $trainer['id'] : null;
$trainerClients = $trainerData['clients'];
$allExercises = $trainerId ? $svc->trainerExercises($trainerId) : [];
$workoutStats = $svc->workoutModuleStats($trainerId);
$allWorkouts = $workoutStats['workouts'];
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Workout Programs | IRONCORE Trainer</title>
  
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
    $pageHeading    = 'WORKOUT PROGRAMS & ASSIGNMENTS';
    $pageSubtitle   = 'Design tailored training routines, manage exercise splits, and track athlete progress';
    $extraHeaderAction = '<button class="btn btn-primary" id="open-workout-modal-btn" style="gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>+ ASSIGN WORKOUT</span></button>';
    require_once __DIR__ . '/../layouts/trainer_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 4-COLUMN KPI GRID -->
        <section class="module-kpi-grid cols-4">
          <div class="module-kpi-card accent-red">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Programs</span>
              <div class="kpi-icon-wrap red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-total-workouts"><?= (int)$workoutStats['total_workouts'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Assigned Routines</span>
            </div>
          </div>

          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Athletes Enrolled</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-active-athletes" style="color: #34D399;"><?= (int)$workoutStats['assigned_members_count'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Coached Roster</span>
            </div>
          </div>

          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Exercise Library</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-exercise-library" style="color: #38BDF8;"><?= (int)$workoutStats['exercise_library_count'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Exercises Available</span>
            </div>
          </div>

          <div class="module-kpi-card accent-purple">
            <div class="kpi-top-row">
              <span class="kpi-label">Weekly Check-Ins</span>
              <div class="kpi-icon-wrap purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-recent-logs" style="color: #A78BFA;"><?= (int)$trainerData['sessions_this_week'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Completed Sessions</span>
            </div>
          </div>
        </section>

        <!-- 2. SEARCH & ACTION BAR -->
        <section class="module-action-bar" style="margin-top: var(--space-6);">
          <div class="search-input-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="workout-search-input" placeholder="Search by routine title, athlete name, or goal..." oninput="filterWorkouts()">
          </div>

          <div class="filter-controls-group">
            <select id="goal-filter" class="form-select" onchange="filterWorkouts()">
              <option value="all">All Goals</option>
              <option value="strength">Strength / Hypertrophy</option>
              <option value="endurance">Endurance / Conditioning</option>
              <option value="fat_loss">Fat Loss / Tone</option>
            </select>
          </div>
        </section>

        <!-- 3. WORKOUTS GRID -->
        <section style="margin-top: var(--space-6);">
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--space-6);" id="workouts-grid-container">
            <?php if (empty($allWorkouts)): ?>
              <div class="modern-card" style="grid-column: 1 / -1; padding: var(--space-8); text-align: center; color: var(--color-text-muted);">
                <p style="font-size: 16px; margin-bottom: var(--space-4);">No workout routines found in the system</p>
                <button class="btn btn-primary" onclick="openWorkoutModal()">Create First Routine</button>
              </div>
            <?php else: foreach ($allWorkouts as $wo): ?>
              <div class="modern-card workout-card-item" 
                   data-title="<?= htmlspecialchars(strtolower($wo['title'])) ?>"
                   data-member="<?= htmlspecialchars(strtolower($wo['member_name'] ?? '')) ?>"
                   data-goal="<?= htmlspecialchars(strtolower($wo['goal'] ?? '')) ?>"
                   style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                  <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-3);">
                    <div>
                      <span class="badge badge-primary" style="font-size: 11px; margin-bottom: var(--space-2); display: inline-block;"><?= htmlspecialchars($wo['goal'] ?? 'General') ?></span>
                      <h3 style="font-size: 18px; font-weight: 700; color: var(--color-text); margin: 0;"><?= htmlspecialchars($wo['title']) ?></h3>
                    </div>
                  </div>
                  <p style="font-size: 13px; color: var(--color-text-muted); line-height: 1.5; margin-bottom: var(--space-4);">
                    <?= htmlspecialchars($wo['description'] ?: 'Tailored resistance and progressive overload protocol.') ?>
                  </p>
                  
                  <div style="padding: var(--space-3); background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-md); margin-bottom: var(--space-4);">
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                      <span style="color: var(--color-text-muted);">Assigned Athlete:</span>
                      <span style="font-weight: 700; color: var(--color-text);"><?= htmlspecialchars($wo['member_name'] ?? 'Unassigned') ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px;">
                      <span style="color: var(--color-text-muted);">Exercises:</span>
                      <span style="font-weight: 700; color: var(--color-primary);"><?= (int)$wo['exercise_count'] ?> Movements</span>
                    </div>
                  </div>
                </div>

                <div style="display: flex; gap: var(--space-2);">
                  <button class="btn btn-secondary btn-sm" style="flex: 1;" onclick="viewWorkoutDetails(<?= (int)$wo['id'] ?>)">View Routine</button>
                </div>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </section>

      </main>
    </div>
  </div>

  <!-- ASSIGN WORKOUT MODAL -->
  <div class="modal-backdrop" id="assign-workout-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 1000; align-items: center; justify-content: center; padding: var(--space-4);">
    <div class="modal-box" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl); max-width: 650px; width: 100%; max-height: 90vh; overflow-y: auto; padding: var(--space-6);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-3);">
        <h3 style="font-size: 18px; font-weight: 700; color: var(--color-text); margin: 0;">ASSIGN WORKOUT ROUTINE</h3>
        <button class="icon-btn" onclick="closeWorkoutModal()" style="border: none; background: transparent; color: var(--color-text-muted); cursor: pointer;">✕</button>
      </div>

      <form id="trainer-assign-workout-form" onsubmit="submitWorkoutForm(event)">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        
        <div style="display: flex; flex-direction: column; gap: var(--space-4);">
          <div>
            <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Select Target Athlete *</label>
            <select name="member_id" class="form-select" required style="width: 100%;">
              <option value="">-- Choose Athlete --</option>
              <?php foreach ($trainerClients as $m): ?>
                <option value="<?= (int)$m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['email']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Program Title *</label>
            <input type="text" name="title" class="form-input" placeholder="e.g., Hypertrophy Push Protocol" required style="width: 100%;">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
            <div>
              <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Primary Goal *</label>
              <select name="goal" class="form-select" required style="width: 100%;">
                <option value="Strength">Strength & Power</option>
                <option value="Hypertrophy">Hypertrophy / Muscle Building</option>
                <option value="Fat Loss">Fat Loss & Conditioning</option>
                <option value="Endurance">Cardio & Endurance</option>
              </select>
            </div>
            <div>
              <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Difficulty</label>
              <select name="difficulty" class="form-select" style="width: 100%;">
                <option value="Beginner">Beginner</option>
                <option value="Intermediate" selected>Intermediate</option>
                <option value="Advanced">Advanced / Elite</option>
              </select>
            </div>
          </div>

          <div>
            <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Routine Overview / Notes</label>
            <textarea name="description" class="form-input" rows="2" placeholder="Instructions, rest periods, warm-up sets..." style="width: 100%;"></textarea>
          </div>

          <!-- Dynamic Exercise Row Builder -->
          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2);">
              <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin: 0;">Exercise Movements</label>
              <button type="button" class="btn btn-secondary btn-sm" onclick="addExerciseRow()" style="font-size: 12px; padding: 3px 8px;">+ Add Movement</button>
            </div>
            <div id="exercise-rows-container" style="display: flex; flex-direction: column; gap: var(--space-2);">
              <div class="exercise-row-item" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: var(--space-2); align-items: center;">
                <select name="exercises[0][exercise_id]" class="form-select" required>
                  <option value="">-- Choose Exercise --</option>
                  <?php foreach ($allExercises as $ex): ?>
                    <option value="<?= (int)$ex['exercise_id'] ?>"><?= htmlspecialchars($ex['name']) ?> (<?= htmlspecialchars($ex['category']) ?>)</option>
                  <?php endforeach; ?>
                </select>
                <input type="number" name="exercises[0][sets]" class="form-input" placeholder="Sets" value="3" min="1" required>
                <input type="text" name="exercises[0][reps]" class="form-input" placeholder="Reps" value="10-12" required>
                <button type="button" class="icon-btn" onclick="this.parentElement.remove()" style="color: var(--color-danger); border: none; background: transparent;">✕</button>
              </div>
            </div>
          </div>

          <div id="form-alert" style="display: none; padding: var(--space-3); border-radius: var(--radius-md); font-size: 13px;"></div>

          <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-4);">
            <button type="button" class="btn btn-secondary" onclick="closeWorkoutModal()">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-workout-btn">Save & Assign Routine</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- VIEW WORKOUT DETAILS MODAL -->
  <div class="modal-backdrop" id="view-workout-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 1000; align-items: center; justify-content: center; padding: var(--space-4);">
    <div class="modal-box" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl); max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto; padding: var(--space-6);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-3);">
        <h3 style="font-size: 18px; font-weight: 700; color: var(--color-text); margin: 0;" id="vw-modal-title">Routine Breakdown</h3>
        <button class="icon-btn" onclick="closeViewModal()" style="border: none; background: transparent; color: var(--color-text-muted); cursor: pointer;">✕</button>
      </div>
      <div id="vw-modal-content">
        <!-- Loaded via JS -->
      </div>
    </div>
  </div>

  <script>
    let exerciseIndex = 1;
    const exerciseOptions = `<?php foreach ($allExercises as $ex): ?><option value="<?= (int)$ex['id'] ?>"><?= htmlspecialchars(addslashes($ex['name'])) ?> (<?= htmlspecialchars(addslashes($ex['category'])) ?>)</option><?php endforeach; ?>`;

    function addExerciseRow() {
      const container = document.getElementById('exercise-rows-container');
      const row = document.createElement('div');
      row.className = 'exercise-row-item';
      row.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: var(--space-2); align-items: center;';
      row.innerHTML = `
        <select name="exercises[${exerciseIndex}][exercise_id]" class="form-select" required>
          <option value="">-- Choose Exercise --</option>
          ${exerciseOptions}
        </select>
        <input type="number" name="exercises[${exerciseIndex}][sets]" class="form-input" placeholder="Sets" value="3" min="1" required>
        <input type="text" name="exercises[${exerciseIndex}][reps]" class="form-input" placeholder="Reps" value="10-12" required>
        <button type="button" class="icon-btn" onclick="this.parentElement.remove()" style="color: var(--color-danger); border: none; background: transparent;">✕</button>
      `;
      container.appendChild(row);
      exerciseIndex++;
    }

    function openWorkoutModal() {
      document.getElementById('assign-workout-modal').style.display = 'flex';
    }

    function closeWorkoutModal() {
      document.getElementById('assign-workout-modal').style.display = 'none';
    }

    function closeViewModal() {
      document.getElementById('view-workout-modal').style.display = 'none';
    }

    document.getElementById('open-workout-modal-btn')?.addEventListener('click', openWorkoutModal);

    function filterWorkouts() {
      const query = (document.getElementById('workout-search-input').value || '').toLowerCase().trim();
      const goalFilter = document.getElementById('goal-filter').value;
      const cards = document.querySelectorAll('.workout-card-item');

      cards.forEach(card => {
        const title = card.dataset.title || '';
        const member = card.dataset.member || '';
        const goal = card.dataset.goal || '';

        const matchesQuery = !query || title.includes(query) || member.includes(query) || goal.includes(query);
        let matchesGoal = true;
        if (goalFilter === 'strength' && !goal.includes('strength') && !goal.includes('hypertrophy')) matchesGoal = false;
        if (goalFilter === 'endurance' && !goal.includes('endurance') && !goal.includes('cardio')) matchesGoal = false;
        if (goalFilter === 'fat_loss' && !goal.includes('fat') && !goal.includes('loss')) matchesGoal = false;

        card.style.display = (matchesQuery && matchesGoal) ? '' : 'none';
      });
    }

    async function viewWorkoutDetails(workoutId) {
      try {
        const res = await fetch(`/api.php?action=workout_details&id=${workoutId}`);
        const data = await res.json();
        if (data.status === 'success' && data.data) {
          const w = data.data;
          document.getElementById('vw-modal-title').innerText = w.title;
          let html = `
            <div style="margin-bottom: var(--space-4);">
              <div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-2);">
                <span class="badge badge-primary">${w.goal || 'General'}</span>
                <span class="badge badge-secondary">${w.difficulty || 'Intermediate'}</span>
              </div>
              <p style="font-size: 13px; color: var(--color-text-muted);">${w.description || 'No special notes.'}</p>
              <div style="font-size: 13px; color: var(--color-text); margin-top: var(--space-2);">
                <strong>Athlete:</strong> ${w.member_name || 'Unassigned'}
              </div>
            </div>
            <h4 style="font-size: 14px; font-weight: 700; color: var(--color-text); margin-bottom: var(--space-3); border-bottom: 1px solid var(--color-border); padding-bottom: 4px;">Exercise Protocol</h4>
            <div style="display: flex; flex-direction: column; gap: var(--space-2);">
          `;
          if (w.exercises && w.exercises.length > 0) {
            w.exercises.forEach(ex => {
              html += `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-2) var(--space-3); background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                  <div>
                    <div style="font-weight: 600; color: var(--color-text); font-size: 14px;">${ex.exercise_name || 'Exercise'}</div>
                    <div style="font-size: 12px; color: var(--color-text-muted);">${ex.category || 'General'}</div>
                  </div>
                  <div style="font-weight: 700; color: var(--color-primary); font-size: 13px;">${ex.sets} Sets × ${ex.reps}</div>
                </div>
              `;
            });
          } else {
            html += `<p style="font-size: 13px; color: var(--color-text-muted);">No movements added to this routine yet.</p>`;
          }
          html += `</div>`;
          document.getElementById('vw-modal-content').innerHTML = html;
          document.getElementById('view-workout-modal').style.display = 'flex';
        }
      } catch (err) {
        console.error('Failed to load workout details:', err);
      }
    }

    async function submitWorkoutForm(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('save-workout-btn');
      const alertBox = document.getElementById('form-alert');
      
      btn.disabled = true;
      btn.innerText = 'Saving...';
      alertBox.style.display = 'none';

      const formData = new FormData(form);
      try {
        const res = await fetch('/api.php?action=create_workout_plan', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();
        if (json.status === 'success') {
          alertBox.className = 'badge badge-success';
          alertBox.style.display = 'block';
          alertBox.innerText = 'Routine assigned successfully!';
          setTimeout(() => { window.location.reload(); }, 800);
        } else {
          alertBox.className = 'badge badge-warning';
          alertBox.style.display = 'block';
          alertBox.innerText = json.message || 'Error creating workout plan.';
          btn.disabled = false;
          btn.innerText = 'Save & Assign Routine';
        }
      } catch (err) {
        alertBox.className = 'badge badge-warning';
        alertBox.style.display = 'block';
        alertBox.innerText = 'Network error. Please try again.';
        btn.disabled = false;
        btn.innerText = 'Save & Assign Routine';
      }
    }
  </script>

  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
