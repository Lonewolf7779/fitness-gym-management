<?php
/**
 * IRONCORE Admin Attendance Control Center
 * Bespoke live gym-floor tracking, instant check-in/out terminal, weekly attendance telemetry.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

AdminMiddleware::handle();

$svc = new GymManagementService();
$attendanceStats = $svc->attendanceModuleStats();
$todayAttendance = $svc->attendance('today');
$allMembers = $svc->members('', 'active');
$weeklyChartData = $svc->weeklyAttendanceChart();
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Control Center | IRONCORE</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'attendance';
    $pageHeading    = 'ATTENDANCE CONTROL CENTER';
    $pageSubtitle   = 'Real-time gym floor monitoring, check-ins, check-outs & member visit telemetry';
    $extraHeaderAction = '<button class="btn btn-primary" id="open-checkin-modal-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>+ CHECK IN ATHLETE</span></button>';
    require_once __DIR__ . '/../layouts/admin_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 4-COLUMN ATTENDANCE METRICS -->
        <section class="module-kpi-grid cols-4">
          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Today's Visits</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><polyline points="9 16 11 18 15 14"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-today-checkins" style="color: #38BDF8;"><?= (int)$attendanceStats['today_checkins'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Daily Turnout</span>
            </div>
          </div>

          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Currently On Floor</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="6" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-current-gym" style="color: #34D399;"><?= (int)$attendanceStats['currently_in_gym'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Active in Facility</span>
            </div>
          </div>

          <div class="module-kpi-card accent-amber">
            <div class="kpi-top-row">
              <span class="kpi-label">Weekly Check-ins</span>
              <div class="kpi-icon-wrap amber">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-weekly-visits" style="color: #FBBF24;"><?= (int)$attendanceStats['weekly_visits'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">This Calendar Week</span>
            </div>
          </div>

          <div class="module-kpi-card accent-purple">
            <div class="kpi-top-row">
              <span class="kpi-label">Monthly Volume</span>
              <div class="kpi-icon-wrap purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-monthly-visits" style="color: #A78BFA;"><?= (int)$attendanceStats['monthly_visits'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Total Month-to-Date</span>
            </div>
          </div>
        </section>

        <!-- 2. TOOLBAR & RANGE SELECTORS -->
        <div class="module-toolbar-wrap">
          <div class="toolbar-primary-row">
            <div class="toolbar-left-group">
              <div class="module-search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="att-search-input" placeholder="Search athlete name, email, phone..." autocomplete="off">
              </div>

              <!-- Custom Date Picker -->
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.75rem; color: #8E8E9F; font-weight: 700; text-transform: uppercase;">Date:</span>
                <input type="date" id="att-custom-date" class="form-control" style="width: 155px; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px;" value="<?= date('Y-m-d') ?>">
              </div>
            </div>

            <div id="att-count-indicator" style="font-size: 0.825rem; font-weight: 600; color: #8E8E9F;">
              Showing <span id="att-visible-count" style="color: #FFF;"><?= count($todayAttendance) ?></span> records
            </div>
          </div>

          <!-- Quick Range Filter Pills -->
          <div class="filter-pills-wrap" id="range-pills">
            <button type="button" class="filter-pill active" data-filter="today">
              <span>Today</span>
              <span class="pill-count"><?= (int)$attendanceStats['today_checkins'] ?></span>
            </button>
            <button type="button" class="filter-pill" data-filter="in_gym">
              <span class="status-dot-sm" style="background:#38BDF8; width:6px; height:6px; border-radius:50%; box-shadow:0 0 6px #38BDF8;"></span>
              <span>Currently On Floor</span>
              <span class="pill-count"><?= (int)$attendanceStats['currently_in_gym'] ?></span>
            </button>
            <button type="button" class="filter-pill" data-filter="yesterday">
              <span>Yesterday</span>
            </button>
            <button type="button" class="filter-pill" data-filter="this_week">
              <span>This Week</span>
            </button>
            <button type="button" class="filter-pill" data-filter="this_month">
              <span>This Month</span>
            </button>
          </div>
        </div>

        <!-- 3. SPLIT GRID: LIVE FEED + FLOOR TERMINAL & TELEMETRY -->
        <div class="module-split-grid">

          <!-- LEFT COLUMN: LIVE ATTENDANCE FEED -->
          <section class="module-panel-card">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between;">
              <div style="font-size: 0.9rem; font-weight: 800; color: #FFF; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
                <span class="status-dot-sm" style="background: #10B981; box-shadow: 0 0 8px #10B981;"></span>
                <span>Live Activity Stream</span>
              </div>
              <button type="button" class="btn-mini" id="refresh-att-btn" style="padding: 0.25rem 0.6rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                <span>Live Refresh</span>
              </button>
            </div>

            <div class="table-glass-wrap">
              <table class="module-data-table">
                <thead>
                  <tr>
                    <th>Athlete</th>
                    <th>Plan</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Floor Status</th>
                    <th style="text-align: right;">Action</th>
                  </tr>
                </thead>
                <tbody id="attendance-table-body">
                  <?php if (!empty($todayAttendance)): ?>
                    <?php foreach ($todayAttendance as $att): 
                      $initials = '';
                      $parts = explode(' ', trim($att['full_name']));
                      foreach ($parts as $p) { if (!empty($p)) $initials .= strtoupper($p[0]); }
                      $initials = substr($initials ?: 'MB', 0, 2);

                      $planRaw = strtolower($att['plan_title'] ?? '');
                      $planClass = str_contains($planRaw, 'elite') ? 'elite' : (str_contains($planRaw, 'pro') ? 'pro' : 'starter');
                      $planTitle = !empty($att['plan_title']) ? $att['plan_title'] : 'NO PLAN';

                      $isInGym = empty($att['check_out_time']);
                      $checkInFmt = !empty($att['check_in_time']) ? date('h:i A', strtotime($att['check_in_time'])) : '—';
                      $checkOutFmt = !empty($att['check_out_time']) ? date('h:i A', strtotime($att['check_out_time'])) : '—';
                      $dateFmt = !empty($att['date']) ? date('M d, Y', strtotime($att['date'])) : '—';
                    ?>
                    <tr id="att-row-<?= (int)$att['id'] ?>"
                        data-id="<?= (int)$att['id'] ?>"
                        data-member-id="<?= (int)$att['member_id'] ?>"
                        data-name="<?= e(strtolower($att['full_name'])) ?>"
                        data-email="<?= e(strtolower($att['email'])) ?>"
                        data-phone="<?= e(strtolower($att['phone'] ?? '')) ?>"
                        data-is-in-gym="<?= $isInGym ? '1' : '0' ?>"
                        data-date="<?= e($att['date']) ?>">
                      <td>
                        <div class="entity-cell">
                          <div class="entity-avatar"><?= e($initials) ?></div>
                          <div class="entity-meta">
                            <div class="entity-title"><?= e($att['full_name']) ?></div>
                            <div class="entity-subtitle"><?= e($att['email']) ?></div>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="plan-chip <?= e($planClass) ?>">
                          <?= e(strtoupper($planTitle)) ?>
                        </span>
                      </td>
                      <td><span style="font-size: 0.8rem; color: #9CA3AF;"><?= e($dateFmt) ?></span></td>
                      <td>
                        <span style="font-weight: 700; color: #FFF; font-size: 0.85rem; font-family: monospace;">
                          <?= e($checkInFmt) ?>
                        </span>
                      </td>
                      <td>
                        <?php if ($isInGym): ?>
                          <span style="color: #38BDF8; font-size: 0.775rem; font-style: italic; font-weight: 600;">Active Session</span>
                        <?php else: ?>
                          <span style="font-weight: 700; color: #9CA3AF; font-size: 0.85rem; font-family: monospace;">
                            <?= e($checkOutFmt) ?>
                          </span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($isInGym): ?>
                          <span class="status-pill in-gym"><span class="status-dot-sm"></span> On Floor</span>
                        <?php else: ?>
                          <span class="status-pill completed"><span class="status-dot-sm"></span> Completed</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="row-actions-group">
                          <?php if ($isInGym): ?>
                            <button type="button" class="btn-mini checkout checkout-btn" data-id="<?= (int)$att['id'] ?>" data-name="<?= e($att['full_name']) ?>">
                              Check Out
                            </button>
                          <?php endif; ?>
                          <button type="button" class="btn-mini danger delete-att-btn" data-id="<?= (int)$att['id'] ?>" title="Delete Entry">
                            &times;
                          </button>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>

                  <tr id="no-att-row" style="display: <?= empty($todayAttendance) ? '' : 'none' ?>;">
                    <td colspan="7" style="text-align: center; padding: 3.5rem 1rem; color: #8E8E9F;">
                      <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.75rem; color: #4B5563;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                      <div style="font-size: 1rem; font-weight: 700; color: #FFF; margin-bottom: 0.25rem;">No attendance records found</div>
                      <div style="font-size: 0.825rem;">Check in athletes on the right terminal or adjust filters.</div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- RIGHT COLUMN: QUICK CHECK-IN TERMINAL & TELEMETRY -->
          <div class="module-side-col">

            <!-- 1. Quick Check-In Terminal -->
            <div class="module-widget-card">
              <div class="widget-header">
                <span class="widget-title">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                  <span>Quick Check-In Terminal</span>
                </span>
                <span style="font-size: 0.7rem; color: #34D399; font-weight: 700;">LIVE</span>
              </div>
              <form id="quick-checkin-form" class="quick-terminal-form">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <div class="terminal-select-wrap">
                  <label class="form-label" style="font-size: 0.75rem;">Select Athlete *</label>
                  <select name="member_id" id="quick-member-select" required>
                    <option value="">-- Choose Member --</option>
                    <?php foreach ($allMembers as $m): ?>
                      <option value="<?= (int)$m['id'] ?>">
                        <?= e($m['full_name']) ?> (<?= e($m['plan_title'] ?? 'No Plan') ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label class="form-label" style="font-size: 0.75rem;">Session Status</label>
                  <select name="status" class="form-control" style="background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.12); color: #FFF; border-radius: 8px;">
                    <option value="present" selected>Present (Normal Workout)</option>
                    <option value="late">Late Arrival</option>
                    <option value="excused">Guest / Trial Session</option>
                  </select>
                </div>
                <div id="quick-checkin-error" class="status-pill danger" style="display: none; width: 100%; border-radius: 6px; font-size: 0.75rem;"></div>
                <button type="submit" class="terminal-submit-btn" id="quick-submit-btn">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <span>Check In Athlete Now</span>
                </button>
              </form>
            </div>

            <!-- 2. Weekly Attendance SVG Mini-Chart -->
            <div class="module-widget-card">
              <div class="widget-header">
                <span class="widget-title">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                  <span>Weekly Attendance</span>
                </span>
                <span style="font-size: 0.725rem; color: #8E8E9F;">7-Day Flow</span>
              </div>
              
              <!-- SVG Bar Chart -->
              <div style="padding: 0.5rem 0;">
                <?php
                  $series = $weeklyChartData['values'] ?? $weeklyChartData['series'] ?? [0,0,0,0,0,0,0];
                  $labels = $weeklyChartData['labels'] ?? ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                  $maxCount = max(1, max($series));
                ?>
                <div style="display: flex; align-items: flex-end; justify-content: space-between; height: 110px; padding: 0.5rem 0.25rem 0; border-bottom: 1px solid rgba(255,255,255,0.08); gap: 8px;">
                  <?php foreach ($series as $idx => $val): 
                    $pct = max(10, round(($val / $maxCount) * 100));
                    $isToday = ($idx === count($series) - 1);
                  ?>
                  <div style="flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end;">
                    <span style="font-size: 0.7rem; font-weight: 700; color: <?= $isToday ? '#38BDF8' : '#9CA3AF' ?>; margin-bottom: 4px;"><?= (int)$val ?></span>
                    <div style="width: 100%; height: <?= $pct ?>%; background: <?= $isToday ? 'linear-gradient(180deg, #38BDF8, #0284C7)' : 'linear-gradient(180deg, #E50914, #7F1D1D)' ?>; border-radius: 4px 4px 0 0; min-height: 6px; box-shadow: <?= $isToday ? '0 0 10px rgba(56, 189, 248, 0.4)' : 'none' ?>;"></div>
                  </div>
                  <?php endforeach; ?>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 6px; padding: 0 0.25rem;">
                  <?php foreach ($labels as $idx => $lbl): 
                    $isToday = ($idx === count($labels) - 1);
                  ?>
                    <span style="flex: 1; text-align: center; font-size: 0.7rem; color: <?= $isToday ? '#38BDF8' : '#6B7280' ?>; font-weight: <?= $isToday ? '800' : '500' ?>;"><?= e($lbl) ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <!-- 3. Gym Floor Peak Hours Info -->
            <div class="module-widget-card" style="margin-bottom: 0;">
              <div class="widget-header">
                <span class="widget-title">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FBBF24" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <span>Peak Floor Hours</span>
                </span>
              </div>
              <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.8rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <span style="color: #9CA3AF;">Morning Rush (06:00 - 09:00 AM)</span>
                  <span class="badge-trend positive" style="font-size: 0.7rem;">High Volume</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <span style="color: #9CA3AF;">Afternoon Flow (12:00 - 03:00 PM)</span>
                  <span class="badge-trend neutral" style="font-size: 0.7rem;">Moderate</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <span style="color: #9CA3AF;">Evening Prime (05:00 - 09:00 PM)</span>
                  <span class="badge-trend danger" style="font-size: 0.7rem;">Peak Density</span>
                </div>
              </div>
            </div>

          </div>

        </div>

      </main>
    </div>
  </div>

  <!-- =========================================================================
       MODAL: MANUAL ATHLETE CHECK-IN
       ========================================================================= -->
  <div class="modal-overlay" id="manual-checkin-modal">
    <div class="modal-card" style="max-width: 480px;">
      <div class="modal-header">
        <h3>LOG ATHLETE ATTENDANCE</h3>
        <button type="button" class="modal-close" id="close-checkin-modal">&times;</button>
      </div>
      <form id="modal-checkin-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="modal-body" style="padding: 1.5rem;">
          <div id="modal-checkin-error" class="status-pill danger" style="display: none; margin-bottom: 1rem; width: 100%; border-radius: 6px;"></div>
          
          <div>
            <label class="form-label">Athlete *</label>
            <select name="member_id" class="form-control" required style="background: rgba(10, 10, 14, 0.8); color: #FFF;">
              <option value="">-- Choose Member --</option>
              <?php foreach ($allMembers as $m): ?>
                <option value="<?= (int)$m['id'] ?>">
                  <?= e($m['full_name']) ?> (<?= e($m['plan_title'] ?? 'No Plan') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="margin-top: 1rem;">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" style="background: rgba(10, 10, 14, 0.8); color: #FFF;">
              <option value="present" selected>Present</option>
              <option value="late">Late</option>
              <option value="excused">Excused / Guest</option>
            </select>
          </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
          <button type="button" class="btn btn-secondary" id="cancel-checkin-btn">Cancel</button>
          <button type="submit" class="btn btn-primary">+ RECORD ATTENDANCE</button>
        </div>
      </form>
    </div>
  </div>

  <!-- FLOATING TOAST -->
  <div id="module-toast" style="position: fixed; bottom: 2rem; right: 2rem; z-index: 9999; background: #16161D; border: 1px solid #06B6D4; padding: 0.9rem 1.4rem; border-radius: 8px; box-shadow: 0 12px 36px rgba(0,0,0,0.8); display: none; align-items: center; gap: 0.75rem;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="toast-msg" style="font-weight: 700; font-size: 0.9rem; color: #FFF;"></span>
  </div>

  <!-- System Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>

  <!-- Bespoke Attendance Script -->
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

    const searchInput = document.getElementById('att-search-input');
    const customDateInput = document.getElementById('att-custom-date');
    const rangePills = document.querySelectorAll('#range-pills .filter-pill');
    let currentFilter = 'today';

    function filterTableClient() {
      const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
      const rows = document.querySelectorAll('#attendance-table-body tr[id^="att-row-"]');
      let visibleCount = 0;

      rows.forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const phone = row.dataset.phone || '';
        const isInGym = row.dataset.isInGym === '1';

        const matchesQuery = !query || name.includes(query) || email.includes(query) || phone.includes(query);
        let matchesFilter = true;
        if (currentFilter === 'in_gym') {
          matchesFilter = isInGym;
        }

        if (matchesQuery && matchesFilter) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });

      const noRow = document.getElementById('no-att-row');
      if (noRow) noRow.style.display = visibleCount === 0 ? '' : 'none';
      const countEl = document.getElementById('att-visible-count');
      if (countEl) countEl.textContent = visibleCount;
    }

    if (searchInput) searchInput.addEventListener('input', filterTableClient);

    function fetchAttendanceData(filterParam) {
      fetch('/api.php?action=attendance&filter=' + encodeURIComponent(filterParam))
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            renderAttendanceRows(res.data || []);
          }
        })
        .catch(err => console.error('Error fetching attendance:', err));
    }

    function renderAttendanceRows(data) {
      const tbody = document.getElementById('attendance-table-body');
      if (!tbody) return;

      if (!data || data.length === 0) {
        tbody.innerHTML = `
          <tr id="no-att-row">
            <td colspan="7" style="text-align: center; padding: 3.5rem 1rem; color: #8E8E9F;">
              <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.75rem; color: #4B5563;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
              <div style="font-size: 1rem; font-weight: 700; color: #FFF; margin-bottom: 0.25rem;">No attendance records found</div>
              <div style="font-size: 0.825rem;">Check in athletes on the right terminal or adjust filters.</div>
            </td>
          </tr>
        `;
        const countEl = document.getElementById('att-visible-count');
        if (countEl) countEl.textContent = '0';
        return;
      }

      let html = '';
      data.forEach(att => {
        const parts = (att.full_name || '').trim().split(' ');
        let initials = '';
        parts.forEach(p => { if (p) initials += p[0].toUpperCase(); });
        initials = initials.substring(0, 2) || 'MB';

        const planRaw = (att.plan_title || '').toLowerCase();
        const planClass = planRaw.includes('elite') ? 'elite' : (planRaw.includes('pro') ? 'pro' : 'starter');
        const planTitle = att.plan_title ? att.plan_title.toUpperCase() : 'NO PLAN';

        const isInGym = !att.check_out_time;
        const checkInFmt = att.check_in_time ? att.check_in_time : '—';
        const checkOutFmt = att.check_out_time ? att.check_out_time : '—';
        const dateFmt = att.date || '—';

        html += `
          <tr id="att-row-${att.id}"
              data-id="${att.id}"
              data-member-id="${att.member_id}"
              data-name="${(att.full_name || '').toLowerCase()}"
              data-email="${(att.email || '').toLowerCase()}"
              data-phone="${(att.phone || '').toLowerCase()}"
              data-is-in-gym="${isInGym ? '1' : '0'}"
              data-date="${att.date}">
            <td>
              <div class="entity-cell">
                <div class="entity-avatar">${initials}</div>
                <div class="entity-meta">
                  <div class="entity-title">${att.full_name}</div>
                  <div class="entity-subtitle">${att.email}</div>
                </div>
              </div>
            </td>
            <td>
              <span class="plan-chip ${planClass}">${planTitle}</span>
            </td>
            <td><span style="font-size: 0.8rem; color: #9CA3AF;">${dateFmt}</span></td>
            <td><span style="font-weight: 700; color: #FFF; font-size: 0.85rem; font-family: monospace;">${checkInFmt}</span></td>
            <td>
              ${isInGym ? '<span style="color: #38BDF8; font-size: 0.775rem; font-style: italic; font-weight: 600;">Active Session</span>' : `<span style="font-weight: 700; color: #9CA3AF; font-size: 0.85rem; font-family: monospace;">${checkOutFmt}</span>`}
            </td>
            <td>
              ${isInGym ? '<span class="status-pill in-gym"><span class="status-dot-sm"></span> On Floor</span>' : '<span class="status-pill completed"><span class="status-dot-sm"></span> Completed</span>'}
            </td>
            <td>
              <div class="row-actions-group">
                ${isInGym ? `<button type="button" class="btn-mini checkout checkout-btn" data-id="${att.id}" data-name="${att.full_name}">Check Out</button>` : ''}
                <button type="button" class="btn-mini danger delete-att-btn" data-id="${att.id}" title="Delete Entry">&times;</button>
              </div>
            </td>
          </tr>
        `;
      });

      tbody.innerHTML = html;
      const countEl = document.getElementById('att-visible-count');
      if (countEl) countEl.textContent = data.length;
      filterTableClient();
    }

    rangePills.forEach(pill => {
      pill.addEventListener('click', () => {
        rangePills.forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        currentFilter = pill.dataset.filter || 'today';
        fetchAttendanceData(currentFilter);
      });
    });

    if (customDateInput) {
      customDateInput.addEventListener('change', () => {
        rangePills.forEach(p => p.classList.remove('active'));
        currentFilter = customDateInput.value;
        fetchAttendanceData(currentFilter);
      });
    }

    document.getElementById('refresh-att-btn')?.addEventListener('click', () => {
      fetchAttendanceData(currentFilter);
      showToast('Live attendance feed updated.');
    });

    document.getElementById('attendance-table-body')?.addEventListener('click', function(e) {
      const target = e.target.closest('button');
      if (!target) return;

      if (target.classList.contains('checkout-btn')) {
        const attId = target.dataset.id;
        const name = target.dataset.name;
        
        const fd = new FormData();
        fd.append('csrf_token', '<?= e($csrf) ?>');
        fd.append('attendance_id', attId);

        fetch('/api.php?action=check_out', {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast(name + ' checked out successfully.');
            fetchAttendanceData(currentFilter);
            fetch('/api.php?action=attendance_stats')
              .then(r => r.json())
              .then(s => {
                if (s.success && s.data) {
                  document.getElementById('stat-current-gym').textContent = s.data.currently_in_gym;
                }
              });
          } else {
            alert(res.message || 'Error checking out athlete.');
          }
        })
        .catch(err => alert('Network error during check out.'));
      }

      if (target.classList.contains('delete-att-btn')) {
        const attId = target.dataset.id;
        if (!confirm('Delete this attendance entry?')) return;

        const fd = new FormData();
        fd.append('csrf_token', '<?= e($csrf) ?>');
        fd.append('id', attId);

        fetch('/api.php?action=delete_attendance', {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            const row = document.getElementById('att-row-' + attId);
            if (row) row.remove();
            showToast('Attendance record deleted.');
            filterTableClient();
          } else {
            alert(res.message || 'Error deleting record.');
          }
        })
        .catch(err => alert('Network error during deletion.'));
      }
    });

    const quickForm = document.getElementById('quick-checkin-form');
    if (quickForm) {
      quickForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const errDiv = document.getElementById('quick-checkin-error');
        errDiv.style.display = 'none';

        const fd = new FormData(quickForm);

        fetch('/api.php?action=check_in', {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast('Athlete checked in successfully!');
            quickForm.reset();
            fetchAttendanceData(currentFilter);
            fetch('/api.php?action=attendance_stats')
              .then(r => r.json())
              .then(s => {
                if (s.success && s.data) {
                  document.getElementById('stat-today-checkins').textContent = s.data.today_checkins;
                  document.getElementById('stat-current-gym').textContent = s.data.currently_in_gym;
                  document.getElementById('stat-weekly-visits').textContent = s.data.weekly_visits;
                  document.getElementById('stat-monthly-visits').textContent = s.data.monthly_visits;
                }
              });
          } else {
            errDiv.textContent = res.message || 'Check-in failed.';
            errDiv.style.display = 'block';
          }
        })
        .catch(err => {
          errDiv.textContent = 'Server connection error.';
          errDiv.style.display = 'block';
        });
      });
    }

    const modal = document.getElementById('manual-checkin-modal');
    document.getElementById('open-checkin-modal-btn')?.addEventListener('click', () => modal?.classList.add('active'));
    document.getElementById('close-checkin-modal')?.addEventListener('click', () => modal?.classList.remove('active'));
    document.getElementById('cancel-checkin-btn')?.addEventListener('click', () => modal?.classList.remove('active'));

    const modalForm = document.getElementById('modal-checkin-form');
    if (modalForm) {
      modalForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const errDiv = document.getElementById('modal-checkin-error');
        errDiv.style.display = 'none';

        const fd = new FormData(modalForm);

        fetch('/api.php?action=check_in', {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast('Athlete checked in successfully!');
            modal?.classList.remove('active');
            modalForm.reset();
            fetchAttendanceData(currentFilter);
            setTimeout(() => { window.location.reload(); }, 600);
          } else {
            errDiv.textContent = res.message || 'Check-in failed.';
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
