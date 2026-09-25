<?php
/**
 * IRONCORE Member - Attendance Ledger & Self Check-In
 * Live database-driven facility check-in logs, streak calculations, and session duration.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/MemberMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

MemberMiddleware::handle();

$userId = (int) ($_SESSION['user_id'] ?? 0);
$svc = new GymManagementService();
$memberData = $svc->memberDashboard($userId);
$member = $svc->getMemberByUserId($userId);
$memberId = $member ? (int) $member['id'] : 0;

// Fetch full attendance history
$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM attendance WHERE member_id = :mid ORDER BY date DESC, check_in_time DESC");
$stmt->execute(['mid' => $memberId]);
$attendanceLogs = $stmt->fetchAll();

// Check if checked in today
$todayLog = null;
foreach ($attendanceLogs as $log) {
    if ($log['date'] === date('Y-m-d')) {
        $todayLog = $log;
        break;
    }
}

$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance History | IRONCORE</title>
  
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
    $pageHeading    = 'ATTENDANCE & FACILITY ACCESS';
    $pageSubtitle   = 'Track workout consistency, streak progression, and verify gym visit logs';
    $extraHeaderAction = '<button class="btn btn-primary" id="self-checkin-btn" style="gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>CHECK IN TODAY</span></button>';
    require_once __DIR__ . '/../layouts/member_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 4-COLUMN KPI METRICS -->
        <section class="module-kpi-grid cols-4">
          <div class="module-kpi-card accent-red">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Streak</span>
              <div class="kpi-icon-wrap red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" style="color: #FF9F0A;"><?= (int)$memberData['attendance_streak'] ?> Days 🔥</div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Consistency</span>
            </div>
          </div>

          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Total Visits</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" style="color: #34D399;"><?= count($attendanceLogs) ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">All-Time Check-Ins</span>
            </div>
          </div>

          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Today's Status</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" style="font-size: 18px; color: <?= $todayLog ? '#34D399' : 'var(--color-text-muted)' ?>;">
              <?= $todayLog ? 'Checked In' : 'Not In Gym' ?>
            </div>
            <div class="kpi-sub-row">
              <span class="badge-trend <?= $todayLog ? 'positive' : 'neutral' ?>">
                <?= $todayLog ? 'Active Today' : 'Awaiting Visit' ?>
              </span>
            </div>
          </div>

          <div class="module-kpi-card accent-purple">
            <div class="kpi-top-row">
              <span class="kpi-label">Latest Session</span>
              <div class="kpi-icon-wrap purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" style="font-size: 16px; color: #A78BFA;">
              <?= !empty($attendanceLogs) ? htmlspecialchars(date('M d, Y', strtotime($attendanceLogs[0]['date']))) : 'No visits' ?>
            </div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Last Verified</span>
            </div>
          </div>
        </section>

        <!-- 2. ATTENDANCE HISTORY TABLE -->
        <section style="margin-top: var(--space-6);">
          <div class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); flex-wrap: wrap; gap: var(--space-3);">
              <div>
                <h3 style="font-size: 16px; font-weight: 700; letter-spacing: 0.5px; color: var(--color-text); text-transform: uppercase;">Facility Access Ledger</h3>
                <p style="font-size: 13px; color: var(--color-text-muted); margin: 0;">Verified check-in & check-out logs recorded at turnstile</p>
              </div>
            </div>

            <div class="table-container" style="border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow: hidden;">
              <table class="table" style="margin: 0; width: 100%;">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Check In Time</th>
                    <th>Check Out Time</th>
                    <th>Duration</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($attendanceLogs)): ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--color-text-muted); padding: var(--space-6);">No facility attendance recorded yet. Click "Check In Today" when you arrive at the gym!</td></tr>
                  <?php else: foreach ($attendanceLogs as $att): 
                    $checkIn = $att['check_in_time'];
                    $checkOut = $att['check_out_time'];
                    $duration = '--';
                    if ($checkIn && $checkOut) {
                      $t1 = strtotime($checkIn);
                      $t2 = strtotime($checkOut);
                      $diff = max(0, $t2 - $t1);
                      $hours = floor($diff / 3600);
                      $mins = floor(($diff % 3600) / 60);
                      $duration = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
                    } elseif ($checkIn) {
                      $duration = 'In Gym Now';
                    }
                  ?>
                    <tr>
                      <td style="font-weight: 600; color: var(--color-text);"><?= htmlspecialchars(date('M d, Y', strtotime($att['date']))) ?></td>
                      <td style="font-family: monospace; color: #34D399;"><?= htmlspecialchars(date('h:i A', strtotime($checkIn))) ?></td>
                      <td style="font-family: monospace; color: var(--color-text-muted);"><?= $checkOut ? htmlspecialchars(date('h:i A', strtotime($checkOut))) : '<span class="badge badge-primary" style="font-size: 11px;">ON FLOOR</span>' ?></td>
                      <td style="color: var(--color-text); font-weight: 600;"><?= $duration ?></td>
                      <td>
                        <span class="badge <?= $att['status'] === 'present' ? 'badge-success' : 'badge-secondary' ?>">
                          <?= strtoupper(htmlspecialchars($att['status'])) ?>
                        </span>
                      </td>
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

  <script>
    document.getElementById('self-checkin-btn')?.addEventListener('click', async () => {
      const btn = document.getElementById('self-checkin-btn');
      btn.disabled = true;
      btn.innerText = 'Checking In...';
      try {
        const res = await fetch('/api.php?action=member_self_checkin', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `csrf_token=<?= $csrf ?>`
        });
        const json = await res.json();
        if (json.success) {
          alert('Check-in recorded! Welcome to IRONCORE.');
          window.location.reload();
        } else {
          alert(json.message || 'Check-in failed.');
          btn.disabled = false;
          btn.innerText = 'CHECK IN TODAY';
        }
      } catch (err) {
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.innerText = 'CHECK IN TODAY';
      }
    });
  </script>

  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
