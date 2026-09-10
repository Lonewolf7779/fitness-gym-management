<?php
/**
 * IRONCORE Trainer - My Athletes Roster View
 * Live database-driven athlete directory, compliance tracker, and routine launcher.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/TrainerMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

TrainerMiddleware::handle();

$userId = (int) ($_SESSION['user_id'] ?? 0);
$svc = new GymManagementService();
$trainerData = $svc->trainerDashboard($userId);
$clients = $trainerData['clients'];
$allExercises = $svc->exercises();
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Athletes Roster | IRONCORE Trainer</title>
  
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
    $pageHeading    = 'MY ATHLETES ROSTER';
    $pageSubtitle   = 'Assigned athlete directory, training compliance, and routine tracking';
    $extraHeaderAction = '<a href="/trainer/workouts.php" class="btn btn-primary" style="gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>+ ASSIGN WORKOUT</span></a>';
    require_once __DIR__ . '/../layouts/trainer_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 4-COLUMN KPI GRID -->
        <section class="module-kpi-grid cols-4">
          <div class="module-kpi-card accent-red">
            <div class="kpi-top-row">
              <span class="kpi-label">Assigned Athletes</span>
              <div class="kpi-icon-wrap red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-main-val"><?= count($clients) ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Active Client Roster</span>
            </div>
          </div>

          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Programs</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" style="color: #34D399;"><?= $trainerData['active_programs_count'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Routines Active</span>
            </div>
          </div>

          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Sessions This Week</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" style="color: #38BDF8;"><?= $trainerData['sessions_this_week'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Facility Attendance</span>
            </div>
          </div>

          <div class="module-kpi-card accent-purple">
            <div class="kpi-top-row">
              <span class="kpi-label">Needing Review</span>
              <div class="kpi-icon-wrap purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" style="color: #A78BFA;"><?= $trainerData['clients_needing_attention'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Inactive or No Routine</span>
            </div>
          </div>
        </section>

        <!-- 2. SEARCH & FILTER ACTION BAR -->
        <section class="module-action-bar" style="margin-top: var(--space-6);">
          <div class="search-input-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="athlete-search-input" placeholder="Search athletes by name, email, plan..." oninput="filterAthletes()">
          </div>

          <div class="filter-controls-group">
            <select id="program-filter" class="form-select" onchange="filterAthletes()">
              <option value="all">All Routine Statuses</option>
              <option value="with_program">With Active Routine</option>
              <option value="no_program">Needs Routine</option>
            </select>
          </div>
        </section>

        <!-- 3. ATHLETES LIST / TABLE -->
        <section class="modern-card" style="margin-top: var(--space-6); padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
            <h3 style="font-size: 16px; font-weight: 700; letter-spacing: 0.5px; color: var(--color-text); text-transform: uppercase;">Athlete Roster</h3>
            <span style="font-size: 13px; color: var(--color-text-muted);"><span id="athlete-count-badge"><?= count($clients) ?></span> Active Athletes</span>
          </div>

          <div class="table-container" style="border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow: hidden;">
            <table class="table" style="margin: 0; width: 100%;" id="athletes-table">
              <thead>
                <tr>
                  <th>Athlete</th>
                  <th>Contact</th>
                  <th>Membership Plan</th>
                  <th>Active Routine</th>
                  <th>Total Check-ins</th>
                  <th>Last Seen</th>
                  <th style="text-align: right;">Action</th>
                </tr>
              </thead>
              <tbody id="athlete-table-body">
                <?php if (empty($clients)): ?>
                  <tr><td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: var(--space-6);">No athletes assigned yet</td></tr>
                <?php else: foreach ($clients as $c): 
                  $initial = strtoupper(substr($c['full_name'], 0, 1)) ?: 'A';
                  $hasProgram = !empty($c['workout_title']);
                ?>
                  <tr class="athlete-row" 
                      data-name="<?= htmlspecialchars(strtolower($c['full_name'])) ?>" 
                      data-email="<?= htmlspecialchars(strtolower($c['email'])) ?>"
                      data-plan="<?= htmlspecialchars(strtolower($c['plan_title'] ?? '')) ?>"
                      data-has-program="<?= $hasProgram ? 'yes' : 'no' ?>">
                    <td>
                      <div style="display: flex; align-items: center; gap: var(--space-3);">
                        <div class="avatar-circle" style="width: 36px; height: 36px; font-size: 14px; font-weight: 700; background: rgba(229, 9, 20, 0.15); color: var(--color-primary); border: 1px solid rgba(229, 9, 20, 0.3);">
                          <?= htmlspecialchars($initial) ?>
                        </div>
                        <div>
                          <div style="font-weight: 700; color: var(--color-text); font-size: 14px;"><?= htmlspecialchars($c['full_name']) ?></div>
                          <div style="font-size: 12px; color: var(--color-text-muted);">Joined <?= date('M Y', strtotime($c['join_date'] ?? 'now')) ?></div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div style="font-size: 13px; color: var(--color-text);"><?= htmlspecialchars($c['email']) ?></div>
                      <div style="font-size: 12px; color: var(--color-text-muted);"><?= htmlspecialchars($c['phone'] ?? 'No Phone') ?></div>
                    </td>
                    <td>
                      <span class="badge badge-secondary"><?= htmlspecialchars($c['plan_title'] ?? 'Standard') ?></span>
                    </td>
                    <td>
                      <?php if ($hasProgram): ?>
                        <span class="badge badge-success" style="font-weight: 600;"><?= htmlspecialchars($c['workout_title']) ?></span>
                      <?php else: ?>
                        <span class="badge badge-warning">No Routine</span>
                      <?php endif; ?>
                    </td>
                    <td style="font-weight: 700; color: var(--color-text);"><?= (int)$c['total_checkins'] ?></td>
                    <td style="color: var(--color-text-muted); font-size: 13px;"><?= htmlspecialchars($c['last_seen'] ? date('M d, H:i', strtotime($c['last_seen'])) : 'Never') ?></td>
                    <td style="text-align: right;">
                      <a href="/trainer/workouts.php" class="btn btn-secondary btn-sm" style="font-size: 12px; padding: 4px 10px;">Assign Routine</a>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </section>

      </main>
    </div>
  </div>

  <script>
    function filterAthletes() {
      const query = (document.getElementById('athlete-search-input').value || '').toLowerCase().trim();
      const progFilter = document.getElementById('program-filter').value;
      const rows = document.querySelectorAll('.athlete-row');
      let visibleCount = 0;

      rows.forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const plan = row.dataset.plan || '';
        const hasProgram = row.dataset.hasProgram;

        const matchesQuery = !query || name.includes(query) || email.includes(query) || plan.includes(query);
        let matchesFilter = true;
        if (progFilter === 'with_program' && hasProgram !== 'yes') matchesFilter = false;
        if (progFilter === 'no_program' && hasProgram === 'yes') matchesFilter = false;

        if (matchesQuery && matchesFilter) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });

      const badge = document.getElementById('athlete-count-badge');
      if (badge) badge.innerText = visibleCount;
    }
  </script>

  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
