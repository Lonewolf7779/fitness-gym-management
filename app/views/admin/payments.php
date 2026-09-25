<?php
/**
 * IRONCORE Admin Payments & Financial Operations Center
 * Bespoke live financial tracking, monthly revenue trends, method distribution & digital receipt generator.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';
require_once __DIR__ . '/../../services/SettingsService.php';

AdminMiddleware::handle();

$svc = new GymManagementService();
$settingsSvc = new SettingsService();
$gymSettings = $settingsSvc->all();
$currency = $gymSettings['currency'] ?? 'INR';
$currSymbol = ($currency === 'USD' || $currency === '$') ? '$' : (($currency === 'EUR' || $currency === '€') ? '€' : '₹');

$payStats = $svc->paymentModuleStats();
$allPayments = $svc->payments();
$allMembers = $svc->members('', 'active');
$allPlans = $svc->plans(true);
$revChartData = $svc->monthlyRevenueChart();
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payments & Financial Hub | IRONCORE</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'payments';
    $pageHeading    = 'PAYMENTS & FINANCIAL OPERATIONS';
    $pageSubtitle   = 'Revenue streams, transaction logs, invoices and membership billing';
    $extraHeaderAction = '<button class="btn btn-primary" id="open-payment-modal-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span>+ RECORD PAYMENT</span></button>';
    require_once __DIR__ . '/../layouts/admin_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 4-COLUMN FINANCIAL KPI GRID -->
        <section class="module-kpi-grid cols-4">
          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Today's Revenue</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-today-rev" style="color: #34D399;"><?= $currSymbol ?><?= number_format((float)$payStats['today_revenue'], 2) ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Settled Today</span>
            </div>
          </div>

          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Month-to-Date</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-month-rev" style="color: #38BDF8;"><?= $currSymbol ?><?= number_format((float)$payStats['month_revenue'], 2) ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Current Billing Cycle</span>
            </div>
          </div>

          <div class="module-kpi-card accent-red">
            <div class="kpi-top-row">
              <span class="kpi-label">Lifetime Revenue</span>
              <div class="kpi-icon-wrap red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-total-rev" style="color: #FFF;"><?= $currSymbol ?><?= number_format((float)$payStats['total_revenue'], 2) ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">All-Time Collections</span>
            </div>
          </div>

          <div class="module-kpi-card accent-amber">
            <div class="kpi-top-row">
              <span class="kpi-label">Pending Invoices</span>
              <div class="kpi-icon-wrap amber">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-pending-rev" style="color: #FBBF24;"><?= $currSymbol ?><?= number_format((float)$payStats['pending_amount'], 2) ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend warning">Outstanding Receivables</span>
            </div>
          </div>
        </section>

        <!-- 2. REVENUE TREND & PAYMENT METHODS OVERVIEW -->
        <div class="module-split-grid" style="margin-bottom: 1.75rem;">

          <!-- LEFT: MONTHLY REVENUE SVG CHART -->
          <div class="module-widget-card" style="margin-bottom: 0;">
            <div class="widget-header">
              <span class="widget-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Monthly Revenue Trajectory</span>
              </span>
              <span style="font-size: 0.725rem; color: #8E8E9F;">Past 8 Months</span>
            </div>
            <div style="padding: 0.75rem 0 0.25rem;">
              <?php
                $revSeries = $revChartData['series'] ?? [0,0,0,0,0,0,0,0];
                $revLabels = $revChartData['labels'] ?? [];
                $maxRev = max(1000, max($revSeries));
              ?>
              <div style="display: flex; align-items: flex-end; justify-content: space-between; height: 140px; padding: 0.5rem 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.08); gap: 10px;">
                <?php foreach ($revSeries as $idx => $amount): 
                  $pct = max(8, round(($amount / $maxRev) * 100));
                  $isLatest = ($idx === count($revSeries) - 1);
                ?>
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end;">
                  <span style="font-size: 0.65rem; font-weight: 700; color: <?= $isLatest ? '#34D399' : '#9CA3AF' ?>; margin-bottom: 4px;"><?= $amount >= 1000 ? round($amount/1000, 1) . 'k' : (int)$amount ?></span>
                  <div style="width: 100%; height: <?= $pct ?>%; background: <?= $isLatest ? 'linear-gradient(180deg, #10B981, #047857)' : 'linear-gradient(180deg, #E50914, #7F1D1D)' ?>; border-radius: 4px 4px 0 0; min-height: 6px; box-shadow: <?= $isLatest ? '0 0 12px rgba(16, 185, 129, 0.4)' : 'none' ?>;"></div>
                </div>
                <?php endforeach; ?>
              </div>
              <div style="display: flex; justify-content: space-between; margin-top: 8px; padding: 0 0.5rem;">
                <?php foreach ($revLabels as $idx => $lbl): 
                  $isLatest = ($idx === count($revLabels) - 1);
                ?>
                  <span style="flex: 1; text-align: center; font-size: 0.7rem; color: <?= $isLatest ? '#34D399' : '#6B7280' ?>; font-weight: <?= $isLatest ? '800' : '500' ?>;"><?= e($lbl) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- RIGHT: PAYMENT METHODS DISTRIBUTION -->
          <div class="module-widget-card" style="margin-bottom: 0;">
            <div class="widget-header">
              <span class="widget-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                <span>Payment Channels</span>
              </span>
              <span style="font-size: 0.725rem; color: #8E8E9F;">Volume Share</span>
            </div>
            
            <div class="method-breakdown-list">
              <?php 
                $methodsList = $payStats['methods'] ?? [];
                $totalPaidVolume = (float)$payStats['total_revenue'] ?: 1;
                
                if (empty($methodsList)) {
                    $methodsList = [
                        ['payment_method' => 'UPI', 'total_amount' => 0, 'txn_count' => 0],
                        ['payment_method' => 'Credit Card', 'total_amount' => 0, 'txn_count' => 0],
                        ['payment_method' => 'Cash', 'total_amount' => 0, 'txn_count' => 0]
                    ];
                }

                foreach ($methodsList as $m): 
                  $mName = $m['payment_method'] ?: 'Other';
                  $mAmt = (float)$m['total_amount'];
                  $mCount = (int)$m['txn_count'];
                  $sharePct = min(100, round(($mAmt / $totalPaidVolume) * 100));
              ?>
              <div class="method-row">
                <div class="method-info-row">
                  <span class="method-name">
                    <span class="status-dot-sm" style="background: #38BDF8;"></span>
                    <span><?= e($mName) ?> (<?= $mCount ?> txns)</span>
                  </span>
                  <span class="method-val"><?= $currSymbol ?><?= number_format($mAmt, 2) ?> <span style="color:#8E8E9F; font-size:0.7rem;">(<?= $sharePct ?>%)</span></span>
                </div>
                <div class="method-progress-track">
                  <div class="method-progress-bar" style="width: <?= max(5, $sharePct) ?>%;"></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

        </div>

        <!-- 3. TOOLBAR & CONTROLS -->
        <div class="module-toolbar-wrap">
          <div class="toolbar-primary-row">
            <div class="toolbar-left-group">
              <div class="module-search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="pay-search-input" placeholder="Search Txn ID, athlete name, phone..." autocomplete="off">
              </div>

              <select id="method-filter" class="form-control" style="width: 160px; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px;">
                <option value="all">All Methods</option>
                <option value="upi">UPI / QR</option>
                <option value="card">Credit / Debit Card</option>
                <option value="cash">Cash</option>
                <option value="bank">Bank Transfer</option>
              </select>
            </div>

            <div id="pay-count-indicator" style="font-size: 0.825rem; font-weight: 600; color: #8E8E9F;">
              Showing <span id="pay-visible-count" style="color: #FFF;"><?= count($allPayments) ?></span> of <?= count($allPayments) ?> transactions
            </div>
          </div>

          <!-- Status Filter Pills -->
          <div class="filter-pills-wrap" id="status-pills">
            <button type="button" class="filter-pill active" data-status="all">
              <span>All Transactions</span>
              <span class="pill-count"><?= count($allPayments) ?></span>
            </button>
            <button type="button" class="filter-pill" data-status="paid">
              <span class="status-dot-sm" style="background:#34D399; width:6px; height:6px; border-radius:50%;"></span>
              <span>Paid / Settled</span>
            </button>
            <button type="button" class="filter-pill" data-status="pending">
              <span class="status-dot-sm" style="background:#FBBF24; width:6px; height:6px; border-radius:50%;"></span>
              <span>Pending</span>
            </button>
            <button type="button" class="filter-pill" data-status="failed">
              <span class="status-dot-sm" style="background:#F87171; width:6px; height:6px; border-radius:50%;"></span>
              <span>Failed</span>
            </button>
          </div>
        </div>

        <!-- 4. TRANSACTIONS DATA TABLE PANEL -->
        <section class="module-panel-card">
          <div class="table-glass-wrap">
            <table class="module-data-table">
              <thead>
                <tr>
                  <th>Transaction ID</th>
                  <th>Athlete</th>
                  <th>Membership Plan</th>
                  <th>Amount</th>
                  <th>Method</th>
                  <th>Date & Time</th>
                  <th>Status</th>
                  <th style="text-align: right;">Invoice</th>
                </tr>
              </thead>
              <tbody id="payments-table-body">
                <?php if (!empty($allPayments)): ?>
                  <?php foreach ($allPayments as $p): 
                    $initials = '';
                    $parts = explode(' ', trim($p['full_name']));
                    foreach ($parts as $part) { if (!empty($part)) $initials .= strtoupper($part[0]); }
                    $initials = substr($initials ?: 'MB', 0, 2);

                    $planRaw = strtolower($p['plan_title'] ?? '');
                    $planClass = str_contains($planRaw, 'elite') ? 'elite' : (str_contains($planRaw, 'pro') ? 'pro' : 'starter');
                    $planTitle = !empty($p['plan_title']) ? $p['plan_title'] : 'MEMBERSHIP';

                    $status = strtolower($p['status'] ?? 'paid');
                    $method = strtoupper($p['payment_method'] ?? 'UPI');
                    $dateFmt = !empty($p['payment_date']) ? date('M d, Y h:i A', strtotime($p['payment_date'])) : '—';
                    $dateShort = !empty($p['payment_date']) ? date('Y-m-d', strtotime($p['payment_date'])) : date('Y-m-d');
                  ?>
                  <tr id="pay-row-<?= (int)$p['id'] ?>"
                      data-id="<?= (int)$p['id'] ?>"
                      data-txn="<?= e(strtolower($p['transaction_id'])) ?>"
                      data-name="<?= e(strtolower($p['full_name'])) ?>"
                      data-email="<?= e(strtolower($p['email'])) ?>"
                      data-phone="<?= e(strtolower($p['phone'] ?? '')) ?>"
                      data-method="<?= e(strtolower($method)) ?>"
                      data-status="<?= e($status) ?>">
                    <td>
                      <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span class="receipt-txn-badge" style="margin-top: 0;"><?= e($p['transaction_id']) ?></span>
                      </div>
                    </td>
                    <td>
                      <div class="entity-cell">
                        <div class="entity-avatar"><?= e($initials) ?></div>
                        <div class="entity-meta">
                          <div class="entity-title"><?= e($p['full_name']) ?></div>
                          <div class="entity-subtitle"><?= e($p['email']) ?></div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="plan-chip <?= e($planClass) ?>">
                        <?= e(strtoupper($planTitle)) ?>
                      </span>
                    </td>
                    <td>
                      <span style="font-size: 0.95rem; font-weight: 800; color: #34D399; font-family: monospace;">
                        <?= $currSymbol ?><?= number_format((float)$p['amount'], 2) ?>
                      </span>
                    </td>
                    <td>
                      <span style="font-size: 0.8rem; font-weight: 700; color: #D1D5DB; background: rgba(255,255,255,0.06); padding: 0.2rem 0.5rem; border-radius: 4px;">
                        <?= e($method) ?>
                      </span>
                    </td>
                    <td>
                      <span style="font-size: 0.8rem; color: #9CA3AF;"><?= e($dateFmt) ?></span>
                    </td>
                    <td>
                      <?php if ($status === 'paid'): ?>
                        <span class="status-pill paid"><span class="status-dot-sm"></span> Paid</span>
                      <?php elseif ($status === 'pending'): ?>
                        <span class="status-pill pending"><span class="status-dot-sm"></span> Pending</span>
                      <?php else: ?>
                        <span class="status-pill failed"><span class="status-dot-sm"></span> <?= e(ucfirst($status)) ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="row-actions-group">
                        <button type="button" class="btn-mini receipt-btn"
                                data-id="<?= (int)$p['id'] ?>"
                                data-txn="<?= e($p['transaction_id']) ?>"
                                data-name="<?= e($p['full_name']) ?>"
                                data-email="<?= e($p['email']) ?>"
                                data-phone="<?= e($p['phone'] ?? '') ?>"
                                data-plan="<?= e($planTitle) ?>"
                                data-amount="<?= e(number_format((float)$p['amount'], 2)) ?>"
                                data-method="<?= e($method) ?>"
                                data-status="<?= e($status) ?>"
                                data-date="<?= e($dateFmt) ?>">
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                          <span>Receipt</span>
                        </button>
                        <button type="button" class="btn-mini danger delete-pay-btn" data-id="<?= (int)$p['id'] ?>" title="Delete Record">
                          &times;
                        </button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>

                <tr id="no-pay-row" style="display: <?= empty($allPayments) ? '' : 'none' ?>;">
                  <td colspan="8" style="text-align: center; padding: 3.5rem 1rem; color: #8E8E9F;">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.75rem; color: #4B5563;"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <div style="font-size: 1rem; font-weight: 700; color: #FFF; margin-bottom: 0.25rem;">No transaction records found</div>
                    <div style="font-size: 0.825rem;">Try adjusting your search criteria or record a new transaction.</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

      </main>
    </div>
  </div>

            <div style="font-size: 0.7rem; text-transform: uppercase; color: #8E8E9F; font-weight: 700;">Payment Receipt</div>
            <div class="receipt-txn-badge" id="rcpt-txn-id">No transaction selected</div>
            <div id="rcpt-date" style="margin-top: 0.25rem; font-size: 0.75rem;">Select a transaction</div>
          </div>
        </div>

        <!-- Billed To Details Grid -->
        <div class="receipt-details-grid">
          <div class="receipt-kv">
            <div class="lbl">Athlete / Customer</div>
            <div class="val" id="rcpt-name">No transaction selected</div>
            <div style="font-size: 0.75rem; color: #8E8E9F;" id="rcpt-email">Not available</div>
          </div>
          <div class="receipt-kv">
            <div class="lbl">Payment Channel</div>
            <div class="val" id="rcpt-method">Not selected</div>
            <div style="font-size: 0.75rem; color: #34D399; font-weight: 700;" id="rcpt-status">STATUS: —</div>
          </div>
        </div>

        <!-- Itemized Table -->
        <div style="margin-bottom: 1.5rem;">
          <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: #8E8E9F; font-size: 0.7rem; text-transform: uppercase;">
                <th style="text-align: left; padding: 0.5rem 0;">Description</th>
                <th style="text-align: right; padding: 0.5rem 0;">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <td style="padding: 0.75rem 0; color: #FFF; font-weight: 600;" id="rcpt-item-title">Select a transaction</td>
                <td style="text-align: right; padding: 0.75rem 0; color: #FFF; font-weight: 700; font-family: monospace;" id="rcpt-item-amount"><?= $currSymbol ?>0.00</td>
              </tr>
              <tr style="font-size: 0.775rem; color: #8E8E9F;">
                <td style="padding: 0.4rem 0;">Payment details are populated from the selected record.</td>
                <td style="text-align: right; padding: 0.4rem 0;"><?= $currSymbol ?>0.00</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Total Box -->
        <div class="receipt-total-box">
          <span class="total-lbl">Total Paid</span>
          <span class="total-amount" id="rcpt-total-amount"><?= $currSymbol ?>4,999.00</span>  <!-- =========================================================================
       2. RECORD PAYMENT MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="add-payment-modal">
    <div class="modal-card" style="max-width: 520px;">
      <div class="modal-header">
        <h3>RECORD NEW TRANSACTION</h3>
        <button type="button" class="modal-close" id="close-payment-modal">&times;</button>
      </div>
      <form id="add-payment-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="modal-body" style="padding: 1.5rem;">
          <div id="add-payment-error" class="status-pill danger" style="display: none; margin-bottom: 1rem; width: 100%; border-radius: 6px;"></div>
          
          <div>
            <label class="form-label">Athlete *</label>
            <select name="member_id" id="pay-member-select" class="form-control" required style="background: rgba(10, 10, 14, 0.8); color: #FFF;">
              <option value="">-- Select Member --</option>
              <?php foreach ($allMembers as $m): ?>
                <option value="<?= (int)$m['id'] ?>" data-plan-id="<?= (int)($m['plan_id'] ?? 0) ?>" data-sub-id="<?= (int)($m['subscription_id'] ?? 0) ?>" data-price="<?= e($m['plan_price'] ?? '0') ?>">
                  <?= e($m['full_name']) ?> (<?= e($m['plan_title'] ?? 'No Plan') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <div>
              <label class="form-label">Amount (<?= $currSymbol ?>) *</label>
              <input type="number" step="0.01" min="1" id="pay-amount" name="amount" class="form-control" required placeholder="4999.00">
            </div>
            <div>
              <label class="form-label">Payment Method</label>
              <select name="payment_method" class="form-control" style="background: rgba(10, 10, 14, 0.8); color: #FFF;">
                <option value="UPI" selected>UPI / QR</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Debit Card">Debit Card</option>
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
              </select>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <div>
              <label class="form-label">Status</label>
              <select name="status" class="form-control" style="background: rgba(10, 10, 14, 0.8); color: #FFF;">
                <option value="paid" selected>Paid / Settled</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
              </select>
            </div>
            <div>
              <label class="form-label">Custom Txn Ref (Optional)</label>
              <input type="text" name="transaction_id" class="form-control" placeholder="Auto-generated if blank">
            </div>
          </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
          <button type="button" class="btn btn-secondary" id="cancel-payment-btn">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submit-payment-btn">+ SAVE PAYMENT</button>
        </div>
      </form>
    </div>
  </div>

  <!-- FLOATING TOAST -->
  <div id="module-toast" style="position: fixed; bottom: 2rem; right: 2rem; z-index: 9999; background: #16161D; border: 1px solid #10B981; padding: 0.9rem 1.4rem; border-radius: 8px; box-shadow: 0 12px 36px rgba(0,0,0,0.8); display: none; align-items: center; gap: 0.75rem;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="toast-msg" style="font-weight: 700; font-size: 0.9rem; color: #FFF;"></span>
  </div>

  <!-- System Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>

  <!-- Bespoke Payments Script -->
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

    const searchInput = document.getElementById('pay-search-input');
    const methodFilter = document.getElementById('method-filter');
    const statusPills = document.querySelectorAll('#status-pills .filter-pill');
    let currentStatus = 'all';

    function filterPayments() {
      const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
      const method = (methodFilter ? methodFilter.value : 'all').toLowerCase();
      const rows = document.querySelectorAll('#payments-table-body tr[id^="pay-row-"]');
      let visibleCount = 0;

      rows.forEach(row => {
        const txn = row.dataset.txn || '';
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const phone = row.dataset.phone || '';
        const rowMethod = row.dataset.method || '';
        const rowStatus = row.dataset.status || '';

        const matchesQuery = !query || txn.includes(query) || name.includes(query) || email.includes(query) || phone.includes(query);
        const matchesMethod = method === 'all' || rowMethod.includes(method);
        const matchesStatus = currentStatus === 'all' || rowStatus === currentStatus;

        if (matchesQuery && matchesMethod && matchesStatus) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });

      const noRow = document.getElementById('no-pay-row');
      if (noRow) noRow.style.display = visibleCount === 0 ? '' : 'none';
      const countEl = document.getElementById('pay-visible-count');
      if (countEl) countEl.textContent = visibleCount;
    }

    if (searchInput) searchInput.addEventListener('input', filterPayments);
    if (methodFilter) methodFilter.addEventListener('change', filterPayments);

    statusPills.forEach(pill => {
      pill.addEventListener('click', () => {
        statusPills.forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        currentStatus = pill.dataset.status || 'all';
        filterPayments();
      });
    });

    const memberSelect = document.getElementById('pay-member-select');
    if (memberSelect) {
      memberSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const price = selected.dataset.price;
        if (price && parseFloat(price) > 0) {
          document.getElementById('pay-amount').value = price;
        }
      });
    }

    const receiptModal = document.getElementById('receipt-modal');
    const paymentModal = document.getElementById('add-payment-modal');

    document.getElementById('open-payment-modal-btn')?.addEventListener('click', () => paymentModal?.classList.add('active'));
    document.getElementById('close-payment-modal')?.addEventListener('click', () => paymentModal?.classList.remove('active'));
    document.getElementById('cancel-payment-btn')?.addEventListener('click', () => paymentModal?.classList.remove('active'));

    document.getElementById('close-receipt-btn')?.addEventListener('click', () => receiptModal?.classList.remove('active'));
    receiptModal?.addEventListener('click', (e) => {
      if (e.target === receiptModal) receiptModal.classList.remove('active');
    });

    document.getElementById('payments-table-body')?.addEventListener('click', function(e) {
      const target = e.target.closest('button');
      if (!target) return;

      if (target.classList.contains('receipt-btn')) {
        const d = target.dataset;
        document.getElementById('rcpt-txn-id').textContent = d.txn;
        document.getElementById('rcpt-date').textContent = d.date;
        document.getElementById('rcpt-name').textContent = d.name;
        document.getElementById('rcpt-email').textContent = d.email;
        document.getElementById('rcpt-method').textContent = d.method;
        document.getElementById('rcpt-status').textContent = 'STATUS: ' + d.status.toUpperCase();
        document.getElementById('rcpt-item-title').textContent = d.plan + ' MEMBERSHIP';
        document.getElementById('rcpt-item-amount').textContent = '<?= $currSymbol ?>' + d.amount;
        document.getElementById('rcpt-total-amount').textContent = '<?= $currSymbol ?>' + d.amount;

        receiptModal?.classList.add('active');
      }

      if (target.classList.contains('delete-pay-btn')) {
        const payId = target.dataset.id;
        if (!confirm('Are you sure you want to delete this payment record?')) return;

        const fd = new FormData();
        fd.append('csrf_token', '<?= e($csrf) ?>');
        fd.append('id', payId);

        fetch('/api.php?action=delete_payment', {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            const row = document.getElementById('pay-row-' + payId);
            if (row) row.remove();
            showToast('Payment record deleted.');
            filterPayments();
          } else {
            alert(res.message || 'Error deleting payment.');
          }
        })
        .catch(err => alert('Network error during deletion.'));
      }
    });

    const payForm = document.getElementById('add-payment-form');
    if (payForm) {
      payForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const errDiv = document.getElementById('add-payment-error');
        errDiv.style.display = 'none';

        const fd = new FormData(payForm);

        fetch('/api.php?action=create_payment', {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast('Payment transaction recorded successfully!');
            paymentModal?.classList.remove('active');
            payForm.reset();
            setTimeout(() => { window.location.reload(); }, 700);
          } else {
            errDiv.textContent = res.message || 'Failed to record payment.';
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
