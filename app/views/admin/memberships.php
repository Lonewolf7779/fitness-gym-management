<?php
/**
 * IRONCORE Admin Membership Plans & Packages Control Center
 * Bespoke live database-driven UI with real-time subscriber metrics, tier cards, pricing rules, and duration terms.
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

$planStats = $svc->membershipModuleStats();
$allPlans = $svc->plans(false);
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Membership Plans & Pricing | IRONCORE</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'memberships';
    $pageHeading    = 'MEMBERSHIP PLANS & PACKAGES';
    $pageSubtitle   = 'Configure subscription tiers, billing cycles, pricing rules, and access durations';
    $extraHeaderAction = '<button class="btn btn-primary" id="open-create-plan-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>+ CREATE PLAN</span></button>';
    require_once __DIR__ . '/../layouts/admin_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. 4-COLUMN BESPOKE KPI GRID -->
        <section class="module-kpi-grid cols-4">
          <div class="module-kpi-card accent-red">
            <div class="kpi-top-row">
              <span class="kpi-label">Total Packages</span>
              <div class="kpi-icon-wrap red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-total-plans"><?= (int)$planStats['total_plans'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend neutral">Subscription Tiers</span>
            </div>
          </div>

          <div class="module-kpi-card accent-emerald">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Plans</span>
              <div class="kpi-icon-wrap emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-active-plans" style="color: #34D399;"><?= (int)$planStats['active_plans'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Publicly Enrollable</span>
            </div>
          </div>

          <div class="module-kpi-card accent-cyan">
            <div class="kpi-top-row">
              <span class="kpi-label">Active Subscriptions</span>
              <div class="kpi-icon-wrap cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-active-subs" style="color: #38BDF8;"><?= (int)$planStats['total_active_subs'] ?></div>
            <div class="kpi-sub-row">
              <span class="badge-trend positive">Athletes Enrolled</span>
            </div>
          </div>

          <div class="module-kpi-card accent-amber">
            <div class="kpi-top-row">
              <span class="kpi-label">Top Tier</span>
              <div class="kpi-icon-wrap amber">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              </div>
            </div>
            <div class="kpi-main-val" id="stat-popular-plan" style="color: #FBBF24; font-size: 1.45rem; text-transform: uppercase;">
              <?= e($planStats['most_popular_plan'] ?: 'None yet') ?>
            </div>
            <div class="kpi-sub-row">
              <span class="badge-trend warning">Highest Enrollment</span>
            </div>
          </div>
        </section>

        <!-- 2. TOOLBAR & FILTER CONTROLS -->
        <div class="module-toolbar-wrap">
          <div class="toolbar-primary-row">
            <div class="toolbar-left-group">
              <div class="module-search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="plan-search-input" placeholder="Search tier name, price, or benefits..." autocomplete="off">
              </div>

              <select id="plan-status-filter" class="form-control" style="width: 150px; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px;">
                <option value="all">All Status</option>
                <option value="active">Active Tiers</option>
                <option value="inactive">Archived / Inactive</option>
              </select>

              <select id="plan-duration-filter" class="form-control" style="width: 170px; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px;">
                <option value="all">All Durations</option>
                <option value="monthly">Monthly (&le; 31 Days)</option>
                <option value="quarterly">Quarterly (32–90 Days)</option>
                <option value="annual">Annual (&gt; 90 Days)</option>
              </select>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem;">
              <div class="view-toggle-btns">
                <button type="button" class="view-toggle-btn active" id="view-cards-btn" title="Tier Cards Grid">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                  <span>Cards</span>
                </button>
                <button type="button" class="view-toggle-btn" id="view-table-btn" title="Dense Table">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                  <span>Table</span>
                </button>
              </div>

              <div id="plan-count-indicator" style="font-size: 0.825rem; font-weight: 600; color: #8E8E9F;">
                Showing <span id="visible-plan-count" style="color: #FFF;"><?= count($allPlans) ?></span> of <?= count($allPlans) ?> plans
              </div>
            </div>
          </div>
        </div>

        <!-- 3. MAIN DIRECTORY: CARDS GRID VIEW -->
        <div id="plans-grid-container" class="plan-cards-grid">
          <?php if (empty($allPlans)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; background: rgba(20,20,28,0.6); border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px;">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="1.5" style="margin-bottom: 1rem;"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              <h3 style="color: #FFF; font-size: 1.1rem; margin-bottom: 0.5rem;">No Membership Plans Found</h3>
              <p style="color: #8E8E9F; font-size: 0.875rem;">Click "+ CREATE PLAN" above to set up subscription packages.</p>
            </div>
          <?php else: ?>
            <?php foreach ($allPlans as $p): 
              $isPopular = !empty($p['is_recommended']) || str_contains(strtoupper($p['title']), 'PRO') || ($p['tag'] === 'POPULAR');
              $status = strtolower($p['status'] ?? 'active');
              $durationDays = (int)($p['duration_days'] ?? 30);
              $cycle = strtolower($p['billing_cycle'] ?? 'monthly');
              $activeSubs = (int)($p['active_subscribers'] ?? 0);
              
              $features = [];
              if (!empty($p['description'])) {
                $lines = preg_split('/[\r\n,]+/', $p['description']);
                foreach ($lines as $line) {
                  $clean = trim($line);
                  if (!empty($clean)) $features[] = $clean;
                }
              }
              if (empty($features)) {
                $features = ['Full facility gym & weight floor access', 'Locker & shower amenity privileges', 'IRONCORE Athlete Hub tracking'];
              }

              $durationCategory = ($durationDays <= 31) ? 'monthly' : (($durationDays <= 90) ? 'quarterly' : 'annual');
            ?>
            <div class="plan-tier-card plan-item <?= $isPopular ? 'featured' : '' ?>"
                 data-id="<?= (int)$p['id'] ?>"
                 data-title="<?= e($p['title']) ?>"
                 data-tag="<?= e($p['tag'] ?? '') ?>"
                 data-price="<?= (float)$p['price'] ?>"
                 data-days="<?= $durationDays ?>"
                 data-cycle="<?= e($cycle) ?>"
                 data-duration-cat="<?= e($durationCategory) ?>"
                 data-description="<?= e($p['description'] ?? '') ?>"
                 data-recommended="<?= $isPopular ? '1' : '0' ?>"
                 data-status="<?= e($status) ?>"
                 data-subscribers="<?= $activeSubs ?>">
              
              <?php if (!empty($p['tag']) || $isPopular): ?>
                <div class="plan-ribbon"><?= e($p['tag'] ?: 'MOST POPULAR') ?></div>
              <?php endif; ?>

              <div>
                <div class="plan-tier-name"><?= e($p['title']) ?></div>
                <div class="plan-tier-desc">
                  <?= e($durationDays) ?> days access &bull; <?= e(ucfirst($cycle)) ?> billing
                </div>

                <div class="plan-price-hero">
                  <span class="plan-currency"><?= e($currSymbol) ?></span>
                  <span class="plan-price-val"><?= number_format((float)$p['price'], 0) ?></span>
                  <span class="plan-billing-cycle">/ <?= $durationDays ?>d</span>
                </div>

                <div class="plan-sub-count-strip">
                  <span class="sub-lbl">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    Active Subscribers
                  </span>
                  <span class="sub-num"><?= $activeSubs ?> Athletes</span>
                </div>

                <ul class="plan-features-ul">
                  <?php foreach (array_slice($features, 0, 4) as $feat): ?>
                    <li>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                      <span><?= e($feat) ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>

              <div class="plan-card-footer">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <button type="button" class="btn btn-secondary btn-plan-toggle" style="padding: 0.4rem 0.65rem; font-size: 0.75rem;" title="Toggle Tier Availability">
                    <span class="status-pill-mini <?= $status === 'active' ? 'active' : 'inactive' ?>" style="font-size: 0.7rem;">
                      <?= e(strtoupper($status)) ?>
                    </span>
                  </button>
                </div>

                <div style="display: flex; align-items: center; gap: 0.35rem;">
                  <button type="button" class="btn btn-secondary btn-view-plan" style="padding: 0.45rem 0.6rem;" title="View Tier Details">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                  <button type="button" class="btn btn-secondary btn-edit-plan" style="padding: 0.45rem 0.6rem; color: #38BDF8;" title="Edit Plan Tier">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button type="button" class="btn btn-secondary btn-del-plan" style="padding: 0.45rem 0.6rem; color: #F87171;" title="Delete Plan">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </div>

            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- 4. SECONDARY DIRECTORY: DENSE TABLE VIEW -->
        <div id="plans-table-container" style="display: none;">
          <div class="panel-card" style="padding: 0; overflow: hidden;">
            <div class="table-responsive">
              <table class="data-table" id="plans-table">
                <thead>
                  <tr>
                    <th>Package / Tier</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Billing Cycle</th>
                    <th>Active Subscribers</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                  </tr>
                </thead>
                <tbody id="plans-table-body">
                  <?php if (!empty($allPlans)): ?>
                    <?php foreach ($allPlans as $p): 
                      $isPopular = !empty($p['is_recommended']) || str_contains(strtoupper($p['title']), 'PRO') || ($p['tag'] === 'POPULAR');
                      $status = strtolower($p['status'] ?? 'active');
                      $durationDays = (int)($p['duration_days'] ?? 30);
                      $cycle = strtolower($p['billing_cycle'] ?? 'monthly');
                      $activeSubs = (int)($p['active_subscribers'] ?? 0);
                      $durationCategory = ($durationDays <= 31) ? 'monthly' : (($durationDays <= 90) ? 'quarterly' : 'annual');
                    ?>
                    <tr class="plan-table-row"
                        data-id="<?= (int)$p['id'] ?>"
                        data-title="<?= e($p['title']) ?>"
                        data-tag="<?= e($p['tag'] ?? '') ?>"
                        data-price="<?= (float)$p['price'] ?>"
                        data-days="<?= $durationDays ?>"
                        data-cycle="<?= e($cycle) ?>"
                        data-duration-cat="<?= e($durationCategory) ?>"
                        data-description="<?= e($p['description'] ?? '') ?>"
                        data-recommended="<?= $isPopular ? '1' : '0' ?>"
                        data-status="<?= e($status) ?>"
                        data-subscribers="<?= $activeSubs ?>">
                      <td>
                        <div style="font-weight: 800; color: #FFF; font-size: 0.95rem;">
                          <?= e($p['title']) ?>
                          <?php if (!empty($p['tag'])): ?>
                            <span class="badge-trend warning" style="margin-left: 0.4rem; font-size: 0.675rem;"><?= e($p['tag']) ?></span>
                          <?php endif; ?>
                        </div>
                        <div style="font-size: 0.775rem; color: #8E8E9F;"><?= e(substr($p['description'] ?? '', 0, 55)) ?>...</div>
                      </td>
                      <td>
                        <span style="font-weight: 800; color: #34D399; font-size: 1.05rem; font-family: monospace;">
                          <?= e($currSymbol) ?><?= number_format((float)$p['price'], 2) ?>
                        </span>
                      </td>
                      <td>
                        <span style="font-weight: 700; color: #FFF;"><?= $durationDays ?> Days</span>
                      </td>
                      <td>
                        <span style="text-transform: capitalize; color: #D1D5DB;"><?= e($cycle) ?></span>
                      </td>
                      <td>
                        <span class="badge-trend positive" style="font-size: 0.8rem;"><?= $activeSubs ?> Athletes</span>
                      </td>
                      <td>
                        <span class="status-pill <?= $status === 'active' ? 'active' : 'inactive' ?>">
                          <?= e(strtoupper($status)) ?>
                        </span>
                      </td>
                      <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 0.35rem;">
                          <button type="button" class="btn btn-secondary btn-view-plan" style="padding: 0.35rem 0.6rem;" title="View Details">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                          </button>
                          <button type="button" class="btn btn-secondary btn-edit-plan" style="padding: 0.35rem 0.6rem; color: #38BDF8;" title="Edit Plan">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                          </button>
                          <button type="button" class="btn btn-secondary btn-del-plan" style="padding: 0.35rem 0.6rem; color: #F87171;" title="Delete Plan">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div id="no-plan-match" style="display: none; text-align: center; padding: 4rem 2rem; background: rgba(20,20,28,0.6); border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px; margin-top: 1rem;">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="1.5" style="margin-bottom: 0.75rem;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <h4 style="color: #FFF; font-size: 1rem; margin-bottom: 0.25rem;">No Membership Packages Match Filter</h4>
          <p style="color: #8E8E9F; font-size: 0.825rem;">Try adjusting your search terms or duration criteria.</p>
        </div>

      </main>
  </div>

  <!-- =========================================================================
       MODAL 1: 360° PLAN DETAIL MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="plan-detail-modal">
    <div class="modal-card" style="max-width: 540px;">
      <div class="modal-header">
        <h3 class="modal-title">MEMBERSHIP TIER BREAKDOWN</h3>
        <button type="button" class="modal-close" data-close="plan-detail-modal">&times;</button>
      </div>
      <div class="modal-body" style="padding-top: 1rem;">
        
        <div class="profile-modal-hero">
          <div class="profile-hero-avatar" style="background: linear-gradient(135deg, #E50914 0%, #1E1B4B 100%);">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.25rem;">
              <h4 id="d-plan-title" style="color: #FFF; font-size: 1.35rem; font-weight: 900; margin: 0;">—</h4>
              <span id="d-plan-status-pill" class="status-pill active">ACTIVE</span>
            </div>
            <div style="font-size: 1.5rem; font-weight: 900; color: #34D399; font-family: monospace;">
              <?= e($currSymbol) ?><span id="d-plan-price">0</span>
              <span style="font-size: 0.85rem; color: #8E8E9F; font-weight: 600;">/ <span id="d-plan-days">30</span> Days</span>
            </div>
          </div>
        </div>

        <div class="profile-stat-strip">
          <div class="profile-strip-card">
            <div class="strip-lbl">Active Subscribers</div>
            <div class="strip-val" id="d-plan-subs" style="color: #38BDF8;">0</div>
          </div>
          <div class="profile-strip-card">
            <div class="strip-lbl">Billing Cycle</div>
            <div class="strip-val" id="d-plan-cycle" style="color: #A78BFA; font-size: 0.95rem; text-transform: capitalize;">Monthly</div>
          </div>
          <div class="profile-strip-card">
            <div class="strip-lbl">Tag / Badge</div>
            <div class="strip-val" id="d-plan-tag" style="color: #FBBF24; font-size: 0.85rem;">Standard</div>
          </div>
        </div>

        <div style="background: rgba(10, 10, 14, 0.6); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 8px; padding: 1.1rem; margin-bottom: 1.5rem;">
          <div style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #8E8E9F; letter-spacing: 0.05em; margin-bottom: 0.65rem;">
            INCLUDED FEATURES & PERKS
          </div>
          <ul id="d-plan-features" class="plan-features-ul" style="margin: 0;">
            <!-- Rendered dynamically -->
          </ul>
        </div>

      </div>
      <div class="modal-footer" style="display: flex; justify-content: space-between; gap: 0.75rem;">
        <button type="button" class="btn btn-secondary" id="d-btn-toggle-status">Toggle Active</button>
        <div style="display: flex; gap: 0.5rem;">
          <button type="button" class="btn btn-secondary" data-close="plan-detail-modal">Close</button>
          <button type="button" class="btn btn-primary" id="d-btn-edit-plan">Edit Plan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 2: CREATE NEW PLAN MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="create-plan-modal">
    <div class="modal-card" style="max-width: 540px;">
      <div class="modal-header">
        <h3 class="modal-title">CREATE MEMBERSHIP TIER</h3>
        <button type="button" class="modal-close" data-close="create-plan-modal">&times;</button>
      </div>
      <form id="create-plan-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="modal-body">
          
          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Tier Name *</label>
            <input type="text" name="title" class="form-control" placeholder="e.g. IronCore Elite Pro" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Price (<?= e($currSymbol) ?>) *</label>
              <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="3499.00" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Duration (Days) *</label>
              <input type="number" min="1" name="duration_days" class="form-control" value="30" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Billing Cycle</label>
              <select name="billing_cycle" class="form-control" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
                <option value="monthly" selected>Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="semi-annual">Semi-Annual</option>
                <option value="annual">Annual</option>
                <option value="custom">Custom Term</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Badge / Ribbon Tag</label>
              <input type="text" name="tag" class="form-control" placeholder="e.g. POPULAR, BEST VALUE" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Features & Description (One benefit per line)</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Full gym & weight floor access&#10;Locker & sauna access&#10;1 Monthly Trainer Consultation" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem; resize: vertical;"></textarea>
          </div>

          <div style="display: flex; gap: 1.5rem; align-items: center;">
            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #FFF; cursor: pointer;">
              <input type="checkbox" name="is_recommended" value="1">
              <span>Mark as Featured Tier</span>
            </label>

            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #FFF; cursor: pointer;">
              <input type="checkbox" name="status" value="active" checked>
              <span>Publicly Active</span>
            </label>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-close="create-plan-modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="create-plan-submit-btn">Save Package</button>
        </div>
      </form>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 3: EDIT PLAN MODAL
       ========================================================================= -->
  <div class="modal-overlay" id="edit-plan-modal">
    <div class="modal-card" style="max-width: 540px;">
      <div class="modal-header">
        <h3 class="modal-title">UPDATE MEMBERSHIP TIER</h3>
        <button type="button" class="modal-close" data-close="edit-plan-modal">&times;</button>
      </div>
      <form id="edit-plan-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" id="edit-plan-id" value="">
        <div class="modal-body">
          
          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Tier Name *</label>
            <input type="text" name="title" id="edit-plan-title" class="form-control" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Price (<?= e($currSymbol) ?>) *</label>
              <input type="number" step="0.01" min="0" name="price" id="edit-plan-price" class="form-control" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Duration (Days) *</label>
              <input type="number" min="1" name="duration_days" id="edit-plan-days" class="form-control" required style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Billing Cycle</label>
              <select name="billing_cycle" id="edit-plan-cycle" class="form-control" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="semi-annual">Semi-Annual</option>
                <option value="annual">Annual</option>
                <option value="custom">Custom Term</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Badge / Ribbon Tag</label>
              <input type="text" name="tag" id="edit-plan-tag" class="form-control" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem;">
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display: block; font-size: 0.775rem; font-weight: 700; color: #8E8E9F; margin-bottom: 0.35rem; text-transform: uppercase;">Features & Description</label>
            <textarea name="description" id="edit-plan-description" class="form-control" rows="4" style="width: 100%; background: rgba(10, 10, 14, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #FFF; border-radius: 8px; padding: 0.65rem 0.85rem; resize: vertical;"></textarea>
          </div>

          <div style="display: flex; gap: 1.5rem; align-items: center;">
            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #FFF; cursor: pointer;">
              <input type="checkbox" name="is_recommended" id="edit-plan-recommended" value="1">
              <span>Mark as Featured Tier</span>
            </label>

            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #FFF; cursor: pointer;">
              <input type="checkbox" name="status" id="edit-plan-status" value="active">
              <span>Publicly Active</span>
            </label>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-close="edit-plan-modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="edit-plan-submit-btn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- DASHBOARD FLOATING TOAST -->
  <div id="dashboard-toast" class="dashboard-toast" style="display: none;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="toast-message">Notification</span>
  </div>

  <script src="/assets/js/dashboard.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = '<?= e($csrf) ?>';
    const currSymbol = '<?= e($currSymbol) ?>';

    // Elements
    const searchInput = document.getElementById('plan-search-input');
    const statusFilter = document.getElementById('plan-status-filter');
    const durationFilter = document.getElementById('plan-duration-filter');
    const visibleCountElem = document.getElementById('visible-plan-count');
    const noMatchElem = document.getElementById('no-plan-match');
    const gridContainer = document.getElementById('plans-grid-container');
    const tableContainer = document.getElementById('plans-table-container');
    const viewCardsBtn = document.getElementById('view-cards-btn');
    const viewTableBtn = document.getElementById('view-table-btn');

    // Modals
    const detailModal = document.getElementById('plan-detail-modal');
    const createModal = document.getElementById('create-plan-modal');
    const editModal = document.getElementById('edit-plan-modal');

    // Toast helper
    const showToast = (msg) => {
      const toast = document.getElementById('dashboard-toast');
      const toastMsg = document.getElementById('toast-message');
      if (!toast || !toastMsg) return;
      toastMsg.textContent = msg;
      toast.style.display = 'flex';
      setTimeout(() => { toast.style.display = 'none'; }, 3500);
    };

    const openModal = (m) => { if (m) m.style.display = 'flex'; };
    const closeModal = (m) => { if (m) m.style.display = 'none'; };

    document.querySelectorAll('[data-close]').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-close');
        closeModal(document.getElementById(id));
      });
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal(overlay);
      });
    });

    // View Switching (Cards vs Table)
    viewCardsBtn?.addEventListener('click', () => {
      viewCardsBtn.classList.add('active');
      viewTableBtn?.classList.remove('active');
      gridContainer.style.display = 'grid';
      tableContainer.style.display = 'none';
    });

    viewTableBtn?.addEventListener('click', () => {
      viewTableBtn.classList.add('active');
      viewCardsBtn?.classList.remove('active');
      gridContainer.style.display = 'none';
      tableContainer.style.display = 'block';
    });

    // Live Search & Multi-Criteria Filtering
    const filterPlans = () => {
      const query = (searchInput?.value || '').toLowerCase().trim();
      const status = (statusFilter?.value || 'all').toLowerCase();
      const duration = (durationFilter?.value || 'all').toLowerCase();

      const cardItems = gridContainer.querySelectorAll('.plan-item');
      const tableRows = tableContainer.querySelectorAll('.plan-table-row');

      let visible = 0;

      const checkMatch = (el) => {
        const title = (el.getAttribute('data-title') || '').toLowerCase();
        const tag = (el.getAttribute('data-tag') || '').toLowerCase();
        const desc = (el.getAttribute('data-description') || '').toLowerCase();
        const price = (el.getAttribute('data-price') || '').toLowerCase();
        const elStatus = (el.getAttribute('data-status') || '').toLowerCase();
        const durationCat = (el.getAttribute('data-duration-cat') || '').toLowerCase();

        const matchesQuery = !query || title.includes(query) || tag.includes(query) || desc.includes(query) || price.includes(query);
        const matchesStatus = (status === 'all') || (elStatus === status);
        const matchesDuration = (duration === 'all') || (durationCat === duration);

        return matchesQuery && matchesStatus && matchesDuration;
      };

      cardItems.forEach(card => {
        const match = checkMatch(card);
        card.style.display = match ? 'flex' : 'none';
        if (match) visible++;
      });

      tableRows.forEach(row => {
        const match = checkMatch(row);
        row.style.display = match ? '' : 'none';
      });

      if (visibleCountElem) visibleCountElem.textContent = visible;
      if (noMatchElem) noMatchElem.style.display = (visible === 0 && cardItems.length > 0) ? 'block' : 'none';
    };

    searchInput?.addEventListener('input', filterPlans);
    statusFilter?.addEventListener('change', filterPlans);
    durationFilter?.addEventListener('change', filterPlans);

    // Create Plan Trigger
    document.getElementById('open-create-plan-btn')?.addEventListener('click', () => {
      document.getElementById('create-plan-form')?.reset();
      openModal(createModal);
    });

    // Plan Actions (View, Edit, Toggle, Delete)
    let currentDetailData = null;
    const bindPlanActions = (container) => {
      container.addEventListener('click', (e) => {
        const btnView = e.target.closest('.btn-view-plan');
        const btnEdit = e.target.closest('.btn-edit-plan');
        const btnToggle = e.target.closest('.btn-plan-toggle');
        const btnDel = e.target.closest('.btn-del-plan');

        const item = e.target.closest('.plan-item, .plan-table-row');
        if (!item) return;

        const data = {
          id: item.getAttribute('data-id'),
          title: item.getAttribute('data-title'),
          tag: item.getAttribute('data-tag'),
          price: item.getAttribute('data-price'),
          days: item.getAttribute('data-days'),
          cycle: item.getAttribute('data-cycle'),
          description: item.getAttribute('data-description'),
          recommended: item.getAttribute('data-recommended') === '1',
          status: item.getAttribute('data-status'),
          subscribers: item.getAttribute('data-subscribers')
        };

        if (btnView) {
          currentDetailData = data;
          document.getElementById('d-plan-title').textContent = data.title;
          document.getElementById('d-plan-price').textContent = parseFloat(data.price).toFixed(2);
          document.getElementById('d-plan-days').textContent = data.days;
          document.getElementById('d-plan-subs').textContent = data.subscribers;
          document.getElementById('d-plan-cycle').textContent = data.cycle;
          document.getElementById('d-plan-tag').textContent = data.tag || 'Standard';

          const statusPill = document.getElementById('d-plan-status-pill');
          statusPill.textContent = data.status.toUpperCase();
          statusPill.className = `status-pill ${data.status.toLowerCase() === 'active' ? 'active' : 'inactive'}`;

          const featUl = document.getElementById('d-plan-features');
          featUl.innerHTML = '';
          const lines = data.description ? data.description.split(/[\r\n,]+/) : [];
          if (lines.length > 0) {
            lines.forEach(l => {
              if (l.trim()) {
                featUl.innerHTML += `<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>${l.trim()}</span></li>`;
              }
            });
          } else {
            featUl.innerHTML = `<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>Full gym access & facilities</span></li>`;
          }

          openModal(detailModal);
        }

        if (btnEdit) {
          document.getElementById('edit-plan-id').value = data.id;
          document.getElementById('edit-plan-title').value = data.title;
          document.getElementById('edit-plan-price').value = data.price;
          document.getElementById('edit-plan-days').value = data.days;
          document.getElementById('edit-plan-cycle').value = data.cycle;
          document.getElementById('edit-plan-tag').value = data.tag || '';
          document.getElementById('edit-plan-description').value = data.description || '';
          document.getElementById('edit-plan-recommended').checked = data.recommended;
          document.getElementById('edit-plan-status').checked = (data.status.toLowerCase() === 'active');
          openModal(editModal);
        }

        if (btnToggle) {
          const formData = new FormData();
          formData.append('csrf_token', csrfToken);
          formData.append('id', data.id);

          fetch('/api.php?action=toggle_plan', {
            method: 'POST',
            body: formData
          })
          .then(r => r.json())
          .then(res => {
            if (res.success) {
              showToast(`Plan ${data.title} status updated.`);
              setTimeout(() => window.location.reload(), 600);
            } else {
              alert(res.message || 'Failed to toggle plan status.');
            }
          })
          .catch(() => alert('Network error while toggling plan.'));
        }

        if (btnDel) {
          const subCount = parseInt(data.subscribers, 10) || 0;
          let confirmMsg = `Are you sure you want to delete "${data.title}"?`;
          if (subCount > 0) {
            confirmMsg = `WARNING: There are ${subCount} active subscribers currently on this tier.\n\nDeleting this package will unlink existing subscriptions.\n\nAre you sure you want to proceed?`;
          }

          if (confirm(confirmMsg)) {
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('id', data.id);

            fetch('/api.php?action=delete_plan', {
              method: 'POST',
              body: formData
            })
            .then(r => r.json())
            .then(res => {
              if (res.success) {
                showToast(`Plan ${data.title} removed.`);
                setTimeout(() => window.location.reload(), 600);
              } else {
                alert(res.message || 'Failed to delete plan.');
              }
            })
            .catch(() => alert('Network error while deleting plan.'));
          }
        }
      });
    };

    bindPlanActions(gridContainer);
    bindPlanActions(tableContainer);

    // Detail Modal Internal Action Links
    document.getElementById('d-btn-edit-plan')?.addEventListener('click', () => {
      closeModal(detailModal);
      if (currentDetailData) {
        document.getElementById('edit-plan-id').value = currentDetailData.id;
        document.getElementById('edit-plan-title').value = currentDetailData.title;
        document.getElementById('edit-plan-price').value = currentDetailData.price;
        document.getElementById('edit-plan-days').value = currentDetailData.days;
        document.getElementById('edit-plan-cycle').value = currentDetailData.cycle;
        document.getElementById('edit-plan-tag').value = currentDetailData.tag || '';
        document.getElementById('edit-plan-description').value = currentDetailData.description || '';
        document.getElementById('edit-plan-recommended').checked = currentDetailData.recommended;
        document.getElementById('edit-plan-status').checked = (currentDetailData.status.toLowerCase() === 'active');
        openModal(editModal);
      }
    });

    document.getElementById('d-btn-toggle-status')?.addEventListener('click', () => {
      if (currentDetailData) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('id', currentDetailData.id);

        fetch('/api.php?action=toggle_plan', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            closeModal(detailModal);
            showToast(`Plan status updated.`);
            setTimeout(() => window.location.reload(), 600);
          } else {
            alert(res.message || 'Failed to toggle plan status.');
          }
        })
        .catch(() => alert('Network error while toggling plan.'));
      }
    });

    // Create Plan Form Submission
    document.getElementById('create-plan-form')?.addEventListener('submit', (e) => {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('create-plan-submit-btn');
      btn.disabled = true;
      btn.textContent = 'Saving...';

      const formData = new FormData(form);
      if (!formData.get('status')) {
        formData.append('status', 'inactive');
      }

      fetch('/api.php?action=create_plan', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(res => {
        btn.disabled = false;
        btn.textContent = 'Save Package';
        if (res.success) {
          closeModal(createModal);
          showToast('New membership package created successfully.');
          setTimeout(() => window.location.reload(), 800);
        } else {
          alert(res.message || 'Failed to create plan.');
        }
      })
      .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Save Package';
        alert('Network error while creating plan.');
      });
    });

    // Edit Plan Form Submission
    document.getElementById('edit-plan-form')?.addEventListener('submit', (e) => {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('edit-plan-submit-btn');
      btn.disabled = true;
      btn.textContent = 'Saving...';

      const formData = new FormData(form);
      if (!formData.get('status')) {
        formData.append('status', 'inactive');
      }

      fetch('/api.php?action=update_plan', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(res => {
        btn.disabled = false;
        btn.textContent = 'Save Changes';
        if (res.success) {
          closeModal(editModal);
          showToast('Membership tier updated successfully.');
          setTimeout(() => window.location.reload(), 800);
        } else {
          alert(res.message || 'Failed to update plan.');
        }
      })
      .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Save Changes';
        alert('Network error while updating plan.');
      });
    });

  });
  </script>
</body>
</html>
