<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

AdminMiddleware::handle();

$svc = new GymManagementService();
$stats = $svc->paymentModuleStats();
$chart = $svc->monthlyRevenueChart();
$payments = array_slice($svc->payments(), 0, 8);
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Revenue Intelligence | IRONCORE</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">
<div class="dashboard-shell">
<?php
$currentSection = 'revenue';
$pageHeading = 'REVENUE INTELLIGENCE';
$pageSubtitle = 'Live collections, pending receivables, payment activity and monthly performance';
$extraHeaderAction = '<a class="btn btn-primary" href="/admin/payments.php">OPEN PAYMENTS</a>';
require_once __DIR__ . '/../layouts/admin_nav.php';
?>
<main class="dashboard-body">
  <section class="module-kpi-grid cols-4">
    <article class="module-kpi-card accent-emerald">
      <div class="kpi-top-row"><span class="kpi-label">Today</span><span class="kpi-icon-wrap emerald">₹</span></div>
      <div class="kpi-main-val">₹<?= number_format((float)$stats['today_revenue'], 2) ?></div>
      <div class="kpi-sub-row"><span class="badge-trend positive">Paid today</span></div>
    </article>
    <article class="module-kpi-card accent-cyan">
      <div class="kpi-top-row"><span class="kpi-label">Month to Date</span><span class="kpi-icon-wrap cyan">₹</span></div>
      <div class="kpi-main-val">₹<?= number_format((float)$stats['month_revenue'], 2) ?></div>
      <div class="kpi-sub-row"><span class="badge-trend positive">Current month</span></div>
    </article>
    <article class="module-kpi-card accent-red">
      <div class="kpi-top-row"><span class="kpi-label">Lifetime</span><span class="kpi-icon-wrap red">₹</span></div>
      <div class="kpi-main-val">₹<?= number_format((float)$stats['total_revenue'], 2) ?></div>
      <div class="kpi-sub-row"><span class="badge-trend neutral">All paid transactions</span></div>
    </article>
    <article class="module-kpi-card accent-amber">
      <div class="kpi-top-row"><span class="kpi-label">Pending</span><span class="kpi-icon-wrap amber">!</span></div>
      <div class="kpi-main-val">₹<?= number_format((float)$stats['pending_amount'], 2) ?></div>
      <div class="kpi-sub-row"><span class="badge-trend warning">Outstanding</span></div>
    </article>
  </section>

  <section class="module-split-grid revenue-layout">
    <article class="module-widget-card">
      <div class="widget-header">
        <span class="widget-title">Monthly Revenue</span>
        <span class="widget-meta">Last 8 months</span>
      </div>
      <div class="revenue-bars">
        <?php
          $values = $chart['values'] ?? [];
          $labels = $chart['labels'] ?? [];
          $max = max(1, max($values ?: [1]));
          foreach ($values as $i => $value):
            $height = max(5, round(((float)$value / $max) * 100));
        ?>
          <div class="revenue-bar-column">
            <span>₹<?= number_format((float)$value, 0) ?></span>
            <div class="revenue-bar-track"><i style="height: <?= $height ?>%;"></i></div>
            <small><?= e($labels[$i] ?? '') ?></small>
          </div>
        <?php endforeach; ?>
      </div>
    </article>

    <article class="module-widget-card">
      <div class="widget-header">
        <span class="widget-title">Revenue Health</span>
        <span class="widget-meta">Live database</span>
      </div>
      <div class="revenue-health-list">
        <div><span>Paid transactions</span><strong><?= count(array_filter($payments, fn($p) => ($p['status'] ?? '') === 'paid')) ?></strong></div>
        <div><span>Pending amount</span><strong>₹<?= number_format((float)$stats['pending_amount'], 2) ?></strong></div>
        <div><span>Latest activity</span><strong><?= !empty($payments) ? e(date('d M Y', strtotime($payments[0]['payment_date']))) : 'No activity' ?></strong></div>
      </div>
      <a href="/admin/payments.php" class="btn btn-secondary revenue-full-btn">Manage transactions</a>
    </article>
  </section>

  <section class="panel-card revenue-transactions">
    <div class="panel-header">
      <div>
        <h3>RECENT TRANSACTIONS</h3>
        <p style="margin-top:0.25rem;color:var(--color-text-muted);font-size:0.78rem;">Only database records are shown.</p>
      </div>
      <a href="/admin/payments.php" class="panel-link">All Payments</a>
    </div>
    <?php if (empty($payments)): ?>
      <div class="empty-state-card">
        <strong>No revenue records yet</strong>
        <span>Create a member, subscription and payment from the admin tools. This page will update from the database.</span>
      </div>
    <?php else: ?>
      <div class="revenue-transaction-grid">
        <?php foreach ($payments as $payment): ?>
          <div class="revenue-transaction">
            <div>
              <strong><?= e($payment['full_name'] ?? 'Member') ?></strong>
              <span><?= e($payment['transaction_id'] ?? 'No transaction ID') ?></span>
            </div>
            <div class="revenue-transaction-right">
              <strong>₹<?= number_format((float)($payment['amount'] ?? 0), 2) ?></strong>
              <span><?= e(strtoupper($payment['status'] ?? 'unknown')) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
</div>
<script src="/assets/js/main.js"></script>
<script src="/assets/js/dashboard.js"></script>
</body>
</html>
