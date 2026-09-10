<?php
/**
 * IRONCORE Member - Body Metrics & Progress Tracking
 * Live database-driven biometric history, body fat monitoring, and metric logging.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

AuthMiddleware::handle();

$userId = (int) ($_SESSION['user_id'] ?? 0);
$svc = new GymManagementService();
$memberData = $svc->memberDashboard($userId);
$progressLogs = $memberData['progress_logs'];
$latestLog = !empty($progressLogs) ? $progressLogs[0] : null;
$firstLog = !empty($progressLogs) ? end($progressLogs) : null;

$weightDelta = ($latestLog && $firstLog && count($progressLogs) > 1) 
  ? round((float)$latestLog['weight_kg'] - (float)$firstLog['weight_kg'], 1) 
  : 0.0;

$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Body Metrics & Progress | IRONCORE</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'progress';
    $pageHeading    = 'BODY COMPOSITION & METRICS';
    $pageSubtitle   = 'Log body weight, track body fat percentage, and visualize physique transformation';
    $extraHeaderAction = '<button class="btn btn-primary" id="open-log-modal-btn" style="gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>+ LOG METRICS</span></button>';
    require_once __DIR__ . '/../layouts/member_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 4-COLUMN KPI METRICS -->
        <section class="module-kpi-grid cols-4">
          <div class="module-kpi-card accent-red">
            <div class="kpi-top-row">
              <span class="kpi-label">Current Weight</span>
              <div class="kpi-icon-wrap red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
              </div>
            </div>
            <div class="kpi-main-val"><?= $latestLog ? (float)$latestLog['weight_kg'] . ' kg' : '--' ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend <?= $weightDelta <= 0 ? 'positive' : 'neutral' ?>">
                <?= $weightDelta != 0 ? ($weightDelta > 0 ? "+{$weightDelta} kg overall" : "{$weightDelta} kg overall") : 'Baseline' ?>
              </span>
            </div>
          </div>

          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Body Fat %</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" style="color: #34D399;"><?= ($latestLog && !empty($latestLog['body_fat_pct'])) ? (float)$latestLog['body_fat_pct'] . '%' : '--' ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Composition</span>
            </div>
          </div>

          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Logged Entries</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" style="color: #38BDF8;"><?= count($progressLogs) ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Total Checkpoints</span>
            </div>
          </div>

          <div class="module-kpi-card accent-purple">
            <div class="kpi-top-row">
              <span class="kpi-label">Latest Checkpoint</span>
              <div class="kpi-icon-wrap purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" style="font-size: 16px; color: #A78BFA;"><?= $latestLog ? htmlspecialchars(date('M d, Y', strtotime($latestLog['log_date']))) : 'No records' ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Last Logged</span>
            </div>
          </div>
        </section>

        <!-- 2. HISTORICAL LOGS TABLE -->
        <section style="margin-top: var(--space-6);">
          <div class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); flex-wrap: wrap; gap: var(--space-3);">
              <div>
                <h3 style="font-size: 16px; font-weight: 700; letter-spacing: 0.5px; color: var(--color-text); text-transform: uppercase;">Biometric History</h3>
                <p style="font-size: 13px; color: var(--color-text-muted); margin: 0;">Verified athlete measurements over time</p>
              </div>
              <button class="btn btn-primary btn-sm" onclick="openLogModal()">+ Log Checkpoint</button>
            </div>

            <div class="table-container" style="border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow: hidden;">
              <table class="table" style="margin: 0; width: 100%;">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Weight</th>
                    <th>Body Fat %</th>
                    <th>Chest (cm)</th>
                    <th>Waist (cm)</th>
                    <th>Arms (cm)</th>
                    <th>Athlete Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($progressLogs)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: var(--space-6);">No progress checkpoints logged yet. Click "+ Log Metrics" to record your baseline!</td></tr>
                  <?php else: foreach ($progressLogs as $p): ?>
                    <tr>
                      <td style="font-weight: 600; color: var(--color-text);"><?= htmlspecialchars(date('M d, Y', strtotime($p['log_date']))) ?></td>
                      <td style="font-weight: 700; color: var(--color-primary);"><?= (float)$p['weight_kg'] ?> kg</td>
                      <td style="color: #34D399; font-weight: 600;"><?= !empty($p['body_fat_pct']) ? (float)$p['body_fat_pct'] . '%' : '--' ?></td>
                      <td style="color: var(--color-text-muted);"><?= !empty($p['chest_cm']) ? (float)$p['chest_cm'] . ' cm' : '--' ?></td>
                      <td style="color: var(--color-text-muted);"><?= !empty($p['waist_cm']) ? (float)$p['waist_cm'] . ' cm' : '--' ?></td>
                      <td style="color: var(--color-text-muted);"><?= !empty($p['arms_cm']) ? (float)$p['arms_cm'] . ' cm' : '--' ?></td>
                      <td style="color: var(--color-text-muted); font-size: 13px; max-width: 200px;"><?= htmlspecialchars($p['notes'] ?: 'No notes') ?></td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

      </main>
    </div>
  </div>

  <!-- LOG METRICS MODAL -->
  <div class="modal-backdrop" id="log-metrics-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 1000; align-items: center; justify-content: center; padding: var(--space-4);">
    <div class="modal-box" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl); max-width: 520px; width: 100%; max-height: 90vh; overflow-y: auto; padding: var(--space-6);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-3);">
        <h3 style="font-size: 18px; font-weight: 700; color: var(--color-text); margin: 0;">LOG PROGRESS CHECKPOINT</h3>
        <button class="icon-btn" onclick="closeLogModal()" style="border: none; background: transparent; color: var(--color-text-muted); cursor: pointer;">✕</button>
      </div>

      <form id="member-progress-form" onsubmit="submitProgressForm(event)">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        
        <div style="display: flex; flex-direction: column; gap: var(--space-4);">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
            <div>
              <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Weight (kg) *</label>
              <input type="number" step="0.1" name="weight_kg" class="form-input" placeholder="e.g. 78.5" required style="width: 100%;">
            </div>
            <div>
              <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Body Fat %</label>
              <input type="number" step="0.1" name="body_fat_pct" class="form-input" placeholder="e.g. 14.2" style="width: 100%;">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--space-3);">
            <div>
              <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Chest (cm)</label>
              <input type="number" step="0.1" name="chest_cm" class="form-input" placeholder="102" style="width: 100%;">
            </div>
            <div>
              <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Waist (cm)</label>
              <input type="number" step="0.1" name="waist_cm" class="form-input" placeholder="82" style="width: 100%;">
            </div>
            <div>
              <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Arms (cm)</label>
              <input type="number" step="0.1" name="arms_cm" class="form-input" placeholder="38" style="width: 100%;">
            </div>
          </div>

          <div>
            <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Log Date</label>
            <input type="date" name="log_date" class="form-input" value="<?= date('Y-m-d') ?>" style="width: 100%;">
          </div>

          <div>
            <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Notes / PRs</label>
            <textarea name="notes" class="form-input" rows="2" placeholder="Energy levels, new PRs, diet compliance..." style="width: 100%;"></textarea>
          </div>

          <div id="progress-alert" style="display: none; padding: var(--space-3); border-radius: var(--radius-md); font-size: 13px;"></div>

          <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-4);">
            <button type="button" class="btn btn-secondary" onclick="closeLogModal()">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-progress-btn">Save Checkpoint</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openLogModal() {
      document.getElementById('log-metrics-modal').style.display = 'flex';
    }

    function closeLogModal() {
      document.getElementById('log-metrics-modal').style.display = 'none';
    }

    document.getElementById('open-log-modal-btn')?.addEventListener('click', openLogModal);

    async function submitProgressForm(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('save-progress-btn');
      const alertBox = document.getElementById('progress-alert');
      
      btn.disabled = true;
      btn.innerText = 'Saving...';
      alertBox.style.display = 'none';

      const formData = new FormData(form);
      try {
        const res = await fetch('/api.php?action=member_add_progress', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();
        if (json.success) {
          alertBox.className = 'badge badge-success';
          alertBox.style.display = 'block';
          alertBox.innerText = 'Metrics logged successfully!';
          setTimeout(() => { window.location.reload(); }, 800);
        } else {
          alertBox.className = 'badge badge-warning';
          alertBox.style.display = 'block';
          alertBox.innerText = json.message || 'Error recording metrics.';
          btn.disabled = false;
          btn.innerText = 'Save Checkpoint';
        }
      } catch (err) {
        alertBox.className = 'badge badge-warning';
        alertBox.style.display = 'block';
        alertBox.innerText = 'Network error. Please try again.';
        btn.disabled = false;
        btn.innerText = 'Save Checkpoint';
      }
    }
  </script>

  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
