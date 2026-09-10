<?php
/**
 * IRONCORE Admin Reports & Analytics Intelligence Center
 * Bespoke live database-driven financial, retention, and attendance analytics.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

AdminMiddleware::handle();

$svc = new GymManagementService();
$stats = $svc->reportStats();
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports & Financial Analytics | IRONCORE</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'reports';
    $pageHeading    = 'EXECUTIVE INTELLIGENCE & REPORTS';
    $pageSubtitle   = 'Real-time financial performance, member retention analytics, and facility utilization';
    $extraHeaderAction = '<button class="btn btn-secondary" onclick="window.print()" style="gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg><span>PRINT REPORT</span></button>';
    require_once __DIR__ . '/../layouts/admin_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 4-COLUMN BESPOKE KPI GRID -->
        <section class="module-kpi-grid cols-4">
          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Monthly Revenue</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-monthly-rev" style="color: #34D399;"><?= formatCurrency($stats['revenue']) ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Total: <?= formatCurrency($stats['total_revenue_alltime']) ?></span>
            </div>
          </div>

          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Retention</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-retention" style="color: #38BDF8;"><?= $stats['retention_rate'] ?>%</div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive"><?= (int)$stats['active_members'] ?> / <?= (int)$stats['members'] ?> Active Members</span>
            </div>
          </div>

          <div class="module-kpi-card accent-purple">
            <div class="kpi-top-row">
              <span class="kpi-label">Weekly Check-Ins</span>
              <div class="kpi-icon-wrap purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-weekly-checkins" style="color: #A78BFA;"><?= (int)$stats['weekly_checkins'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Today: <?= (int)$stats['attendance_today'] ?> Athletes</span>
            </div>
          </div>

          <div class="module-kpi-card accent-red">
            <div class="kpi-top-row">
              <span class="kpi-label">Staff & Plans</span>
              <div class="kpi-icon-wrap red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-staff-plans" style="color: var(--color-primary);"><?= (int)$stats['trainers'] ?> <span style="font-size: 16px; color: var(--color-text-muted); font-weight: 500;">Trainers</span> / <?= (int)$stats['plans'] ?> <span style="font-size: 16px; color: var(--color-text-muted); font-weight: 500;">Plans</span></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Operational Assets</span>
            </div>
          </div>
        </section>

        <!-- 2. ANALYTICS DUAL GRID: PLAN DISTRIBUTION & REVENUE SUMMARY -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-6); margin-top: var(--space-6);">
          
          <!-- Plan Distribution -->
          <div class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
              <h3 style="font-size: 16px; font-weight: 700; letter-spacing: 0.5px; color: var(--color-text); text-transform: uppercase;">Membership Distribution</h3>
              <span style="font-size: 12px; color: var(--color-text-muted);">Active Subscriptions</span>
            </div>
            <div class="table-container" style="border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow: hidden;">
              <table class="table" style="margin: 0; width: 100%;">
                <thead>
                  <tr>
                    <th>Plan Tier</th>
                    <th style="text-align: right;">Subscribers</th>
                    <th style="text-align: right;">Share</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($stats['plans_distribution'])): ?>
                    <tr><td colspan="3" style="text-align: center; color: var(--color-text-muted); padding: var(--space-4);">No active membership data found</td></tr>
                  <?php else: 
                    $totalSubs = array_sum(array_column($stats['plans_distribution'], 'subscriber_count'));
                    foreach ($stats['plans_distribution'] as $plan): 
                      $pct = ($totalSubs > 0) ? round(($plan['subscriber_count'] / $totalSubs) * 100, 1) : 0;
                  ?>
                    <tr>
                      <td style="font-weight: 600; color: var(--color-text);"><?= htmlspecialchars($plan['title']) ?></td>
                      <td style="text-align: right; font-weight: 700; color: var(--color-primary);"><?= (int)$plan['subscriber_count'] ?></td>
                      <td style="text-align: right; color: var(--color-text-muted); font-size: 13px;"><?= $pct ?>%</td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Financial Health Breakdown -->
          <div class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
              <h3 style="font-size: 16px; font-weight: 700; letter-spacing: 0.5px; color: var(--color-text); text-transform: uppercase;">Revenue & Cashflow Summary</h3>
              <span style="font-size: 12px; color: var(--color-text-muted);">Settled Payments</span>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: var(--space-4);">
              <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-3) var(--space-4); background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <span style="font-size: 14px; color: var(--color-text-muted);">Current Month Collected</span>
                <span style="font-size: 16px; font-weight: 700; color: #34D399;"><?= formatCurrency($stats['revenue']) ?></span>
              </div>

              <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-3) var(--space-4); background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <span style="font-size: 14px; color: var(--color-text-muted);">All-Time Gross Volume</span>
                <span style="font-size: 16px; font-weight: 700; color: var(--color-text);"><?= formatCurrency($stats['total_revenue_alltime']) ?></span>
              </div>

              <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-3) var(--space-4); background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <span style="font-size: 14px; color: var(--color-text-muted);">Avg. Revenue Per Active Athlete</span>
                <span style="font-size: 16px; font-weight: 700; color: #38BDF8;">
                  <?= $stats['active_members'] > 0 ? formatCurrency($stats['revenue'] / $stats['active_members']) : formatCurrency(0) ?>
                </span>
              </div>
            </div>
          </div>

        </div>

        <!-- 3. RECENT TRANSACTIONS AUDIT TABLE -->
        <section style="margin-top: var(--space-6);">
          <div class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); flex-wrap: wrap; gap: var(--space-3);">
              <div>
                <h3 style="font-size: 16px; font-weight: 700; letter-spacing: 0.5px; color: var(--color-text); text-transform: uppercase;">Recent Transaction Ledger</h3>
                <p style="font-size: 13px; color: var(--color-text-muted); margin: 0;">Verified payment logs processed through system</p>
              </div>
              <button class="btn btn-secondary btn-sm" id="export-csv-btn" onclick="exportTransactionsCSV()" style="gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>EXPORT CSV</span>
              </button>
            </div>

            <div class="table-container" style="border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow: hidden;">
              <table class="table" style="margin: 0; width: 100%;" id="transactions-table">
                <thead>
                  <tr>
                    <th>Tx ID</th>
                    <th>Member</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($stats['recent_payments'])): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: var(--space-6);">No transaction records recorded yet</td></tr>
                  <?php else: foreach ($stats['recent_payments'] as $tx): ?>
                    <tr>
                      <td style="font-family: monospace; font-size: 12px; color: var(--color-text-muted);">#TX-<?= str_pad((string)$tx['id'], 5, '0', STR_PAD_LEFT) ?></td>
                      <td style="font-weight: 600; color: var(--color-text);"><?= htmlspecialchars($tx['member_name'] ?? 'Athlete') ?></td>
                      <td><span class="badge badge-secondary"><?= htmlspecialchars($tx['plan_title'] ?? 'Standard') ?></span></td>
                      <td style="font-weight: 700; color: #34D399;"><?= formatCurrency($tx['amount']) ?></td>
                      <td style="text-transform: capitalize; color: var(--color-text-muted);"><?= htmlspecialchars($tx['payment_method'] ?? 'cash') ?></td>
                      <td style="color: var(--color-text-muted); font-size: 13px;"><?= htmlspecialchars(date('M d, Y', strtotime($tx['payment_date']))) ?></td>
                      <td>
                        <span class="badge <?= $tx['status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                          <?= strtoupper(htmlspecialchars($tx['status'])) ?>
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
    function exportTransactionsCSV() {
      const table = document.getElementById('transactions-table');
      if (!table) return;
      let csv = [];
      const rows = table.querySelectorAll('tr');
      for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
          let text = cols[j].innerText.replace(/"/g, '""').trim();
          row.push('"' + text + '"');
        }
        csv.push(row.join(','));
      }
      const csvString = csv.join('\n');
      const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.setAttribute('href', url);
      a.setAttribute('download', 'ironcore_transactions_' + new Date().toISOString().slice(0,10) + '.csv');
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }
  </script>

  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
