<?php
/**
 * IRONCORE Unified Admin Module View
 * Handles Trainers, Memberships, Attendance, Payments, Workouts, and Reports with full database CRUD.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';

AdminMiddleware::handle();

$module = $module ?? 'reports';
$titles = [
    'trainers'    => 'TRAINER MANAGEMENT',
    'memberships' => 'MEMBERSHIP PLANS',
    'attendance'  => 'ATTENDANCE TRACKING',
    'payments'    => 'PAYMENTS & BILLING',
    'workouts'    => 'WORKOUT MANAGEMENT',
    'reports'     => 'REPORTS & ANALYTICS'
];
$title = $titles[$module] ?? 'ADMIN MODULE';
$csrf  = generateCsrfToken();
$adminName = $_SESSION['full_name'] ?? 'System Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?> | IRONCORE</title>
  
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <style>
    .module-toolbar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; justify-content: space-between; }
    .module-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 16px; margin-bottom: 20px; }
    .module-stat { padding: 20px; }
    .module-stat strong { display: block; font-size: 28px; margin-top: 6px; color: var(--color-accent); }
    .module-table { width: 100%; border-collapse: collapse; }
    .module-table th, .module-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.08); font-size: 0.875rem; }
    .module-table th { font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .module-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 24px; padding: 1.5rem; background: var(--color-surface-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-md); }
    .module-form label { display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem; }
    .module-form input, .module-form select, .module-form textarea { width: 100%; padding: 10px 12px; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: 6px; }
    .module-form textarea { min-height: 80px; resize: vertical; }
    .full { grid-column: 1 / -1; }
    .row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .row-actions button { padding: 5px 10px; font-size: 0.75rem; border-radius: 4px; font-weight: 700; cursor: pointer; }
    @media (max-width: 768px) {
      .module-table { font-size: 12px; }
    }
  </style>
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <div class="sidebar-overlay"></div>

    <aside class="sidebar">
      <div class="sidebar-brand">
        <a href="/index.php" class="brand-logo">
          <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/>
          </svg>
          <span>IRONCORE</span>
        </a>
        <span class="sidebar-badge">ADMINISTRATOR CONTROL</span>
      </div>

      <ul class="sidebar-nav">
        <li><a href="/admin/index.php" class="nav-item-link">Dashboard</a></li>
        <li><a href="/admin/members.php" class="nav-item-link">Members</a></li>
        <li><a href="/admin/trainers.php" class="nav-item-link <?= $module === 'trainers' ? 'active' : '' ?>">Trainers</a></li>
        <li><a href="/admin/memberships.php" class="nav-item-link <?= $module === 'memberships' ? 'active' : '' ?>">Memberships</a></li>
        <li><a href="/admin/attendance.php" class="nav-item-link <?= $module === 'attendance' ? 'active' : '' ?>">Attendance</a></li>
        <li><a href="/admin/payments.php" class="nav-item-link <?= $module === 'payments' ? 'active' : '' ?>">Payments</a></li>
        <li><a href="/admin/workouts.php" class="nav-item-link <?= $module === 'workouts' ? 'active' : '' ?>">Workouts</a></li>
        <li><a href="/admin/reports.php" class="nav-item-link <?= $module === 'reports' ? 'active' : '' ?>">Reports</a></li>
      </ul>

      <div class="sidebar-footer">
        <div class="user-profile-badge">
          <div class="avatar-circle"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
          <div class="user-info">
            <div class="user-name"><?= e($adminName) ?></div>
            <div class="user-role">Super Admin</div>
          </div>
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <a href="/admin/settings.php" class="btn btn-secondary" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; justify-content: center;">Settings</a>
          <a href="/logout.php" class="btn btn-primary" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; justify-content: center;">Logout</a>
        </div>
      </div>
    </aside>

    <div class="main-wrapper">
      <header class="dashboard-header">
        <div class="header-title-group">
          <h1><?= e($title) ?></h1>
          <p>Live operational database management and controls</p>
        </div>
        <div class="header-actions">
          <div class="header-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="admin-search-input" placeholder="Search..." aria-label="Search" autocomplete="off">
            <span class="search-kbd">⌘K</span>
            <div class="header-search-results" id="admin-search-results"></div>
          </div>
        </div>
      </header>

      <main class="dashboard-body">
        <div id="stats" class="module-grid"></div>

        <section class="panel-card">
          <div class="module-toolbar">
            <div style="display: flex; gap: 10px; align-items: center; flex: 1;">
              <input id="search" class="form-control" placeholder="Search records..." style="max-width: 300px;">
              <button id="refresh" class="btn btn-secondary">Refresh</button>
            </div>
            <button id="toggleForm" class="btn btn-primary">+ Add New</button>
          </div>

          <!-- Add / Edit Form -->
          <form id="form" class="module-form" style="display: none;">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="id" id="recordId">
            <div id="fields" class="module-form full" style="padding: 0; background: none; border: none; margin: 0;"></div>
            <div class="full" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
              <button class="btn btn-secondary" id="cancelEdit" type="button">Cancel</button>
              <button class="btn btn-primary" type="submit">Save Record</button>
            </div>
          </form>

          <!-- Table Container -->
          <div style="overflow-x: auto;">
            <table class="module-table">
              <thead id="head"></thead>
              <tbody id="body">
                <tr><td colspan="10" style="text-align: center; padding: 2rem;">Loading data...</td></tr>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
  </div>

  <script>
    const M = '<?= e($module) ?>';
    const API = '/api.php';
    const CSRF = '<?= e($csrf) ?>';
    const $ = id => document.getElementById(id);
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    const configs = {
      trainers: {
        action: 'trainers',
        create: 'create_trainer',
        update: 'update_trainer',
        cols: ['full_name', 'email', 'specialization', 'experience_years', 'hourly_rate', 'status'],
        fields: [
          ['name', 'Full Name', 'text', true],
          ['email', 'Email Address', 'email', true],
          ['specialization', 'Specialization Area', 'text', true],
          ['experience_years', 'Experience (Years)', 'number', true],
          ['hourly_rate', 'Hourly Rate (₹)', 'number', true],
          ['bio', 'Biography', 'textarea', false],
          ['password', 'Default Password', 'password', false]
        ],
        editKey: 'id'
      },
      memberships: {
        action: 'plans',
        create: 'create_plan',
        update: 'update_plan',
        cols: ['title', 'tag', 'price', 'billing_cycle', 'duration_days', 'status'],
        fields: [
          ['title', 'Plan Title', 'text', true],
          ['tag', 'Badge Tag', 'text', false],
          ['price', 'Price (₹)', 'number', true],
          ['billing_cycle', 'Billing Cycle', 'select', true, ['monthly', 'annual']],
          ['duration_days', 'Duration (Days)', 'number', true],
          ['description', 'Plan Description', 'textarea', false]
        ],
        editKey: 'id'
      },
      attendance: {
        action: 'attendance',
        create: 'check_in',
        cols: ['full_name', 'email', 'date', 'check_in_time', 'check_out_time', 'status'],
        fields: [
          ['member_id', 'Member ID', 'number', true],
          ['status', 'Attendance Status', 'select', true, ['present', 'late', 'excused']]
        ]
      },
      payments: {
        action: 'payments',
        create: 'create_payment',
        cols: ['full_name', 'amount', 'payment_method', 'transaction_id', 'status', 'payment_date'],
        fields: [
          ['member_id', 'Member ID', 'number', true],
          ['subscription_id', 'Subscription ID', 'number', false],
          ['amount', 'Amount (₹)', 'number', true],
          ['payment_method', 'Payment Method', 'select', true, ['UPI', 'Cash', 'Card', 'Bank Transfer']],
          ['transaction_id', 'Transaction ID (Optional)', 'text', false],
          ['status', 'Payment Status', 'select', true, ['paid', 'pending', 'failed', 'refunded']]
        ]
      },
      workouts: {
        action: 'workouts',
        create: 'create_workout',
        cols: ['title', 'member_name', 'trainer_name', 'goal', 'start_date', 'end_date'],
        fields: [
          ['member_id', 'Member ID', 'number', true],
          ['trainer_id', 'Trainer ID', 'number', false],
          ['title', 'Workout Program Title', 'text', true],
          ['goal', 'Primary Training Goal', 'text', false],
          ['start_date', 'Start Date', 'date', true],
          ['end_date', 'End Date', 'date', false]
        ]
      },
      reports: {
        action: 'reports',
        create: null,
        cols: [],
        fields: []
      }
    };

    const C = configs[M];

    function fields(values = {}) {
      $('fields').innerHTML = C.fields.map(([n, l, t, req, opts]) => {
        let value = values[n] ?? '';
        if (t === 'textarea') {
          return `<div><label>${esc(l)} ${req ? '*' : ''}</label><textarea name="${n}" ${req ? 'required' : ''}>${esc(value)}</textarea></div>`;
        }
        if (t === 'select') {
          return `<div><label>${esc(l)} ${req ? '*' : ''}</label><select name="${n}" ${req ? 'required' : ''}>${opts.map(o => `<option value="${esc(o)}" ${String(value) === o ? 'selected' : ''}>${esc(o)}</option>`).join('')}</select></div>`;
        }
        return `<div><label>${esc(l)} ${req ? '*' : ''}</label><input name="${n}" type="${t}" value="${esc(value)}" ${req ? 'required' : ''}></div>`;
      }).join('');
    }

    function openCreate() {
      if (!C.create) return;
      $('recordId').value = '';
      fields();
      $('form').style.display = 'grid';
      $('cancelEdit').style.display = 'inline-flex';
      $('toggleForm').textContent = 'Close Form';
    }

    function closeForm() {
      $('form').style.display = 'none';
      $('recordId').value = '';
      $('toggleForm').textContent = '+ Add New';
    }

    async function load() {
      let url = API + '?action=' + C.action;
      if (M === 'attendance') url += '&date=' + new Date().toISOString().slice(0, 10);
      if ($('search').value && M === 'trainers') url += '&q=' + encodeURIComponent($('search').value);

      try {
        const r = await fetch(url);
        const j = await r.json();

        if (!j.success) {
          $('body').innerHTML = '<tr><td colspan="10" style="color: var(--color-danger);">' + esc(j.message) + '</td></tr>';
          return;
        }

        let d = j.data;
        if (M === 'reports') {
          renderReportStats(d);
          $('head').innerHTML = '';
          $('body').innerHTML = '<tr><td style="padding: 2rem; text-align: center; color: var(--color-text-muted);">Real-time business performance and attendance KPIs are compiled from the MySQL database above.</td></tr>';
          return;
        }

        d = Array.isArray(d) ? d : [];
        let filterQ = ($('search').value || '').toLowerCase().trim();
        if (filterQ && M !== 'trainers') {
          d = d.filter(row => Object.values(row).some(v => String(v).toLowerCase().includes(filterQ)));
        }

        // Render table headers
        let headersHtml = '<tr>' + C.cols.map(c => '<th>' + esc(c.replaceAll('_', ' ')) + '</th>').join('');
        if (C.update || M === 'attendance' || M === 'memberships') {
          headersHtml += '<th style="text-align: right;">Actions</th>';
        }
        headersHtml += '</tr>';
        $('head').innerHTML = headersHtml;

        // Render table body
        if (d.length === 0) {
          $('body').innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 2.5rem; color: var(--color-text-muted);">No records found in database.</td></tr>';
        } else {
          $('body').innerHTML = d.map(x => {
            let rowHtml = '<tr>' + C.cols.map(c => {
              let val = x[c];
              if (c === 'price' || c === 'amount' || c === 'hourly_rate') val = '₹' + parseFloat(val || 0).toLocaleString();
              if (c === 'status') {
                let pillClass = (val === 'active' || val === 'paid' || val === 'present') ? 'active' : ((val === 'expired' || val === 'failed') ? 'danger' : 'warning');
                return `<td><span class="status-pill ${pillClass}"><span class="status-dot-sm"></span> ${esc(val)}</span></td>`;
              }
              return '<td>' + esc(val || '—') + '</td>';
            }).join('');

            let actionsHtml = '';
            if (C.update) {
              actionsHtml += `<button class="btn btn-secondary" onclick='editRecord(${JSON.stringify(x).replace(/'/g, "&#39;")})'>Edit</button>`;
            }
            if (M === 'attendance' && !x.check_out_time) {
              actionsHtml += `<button class="btn btn-primary" onclick='checkOutAttendance(${x.id})'>Check Out</button>`;
            }
            if (M === 'memberships') {
              actionsHtml += `<button class="btn btn-secondary" onclick='togglePlanStatus(${x.id})'>Toggle</button>`;
            }

            if (actionsHtml) {
              rowHtml += `<td style="text-align: right;"><div class="row-actions" style="justify-content: flex-end;">${actionsHtml}</div></td>`;
            }
            rowHtml += '</tr>';
            return rowHtml;
          }).join('');
        }

        renderStats({ ['Total ' + M]: d.length });
        window.currentRows = d;
      } catch (err) {
        $('body').innerHTML = '<tr><td colspan="10" style="color: var(--color-danger); text-align: center;">Unable to load data: ' + esc(err.message) + '</td></tr>';
      }
    }

    function renderStats(d) {
      $('stats').innerHTML = Object.entries(d).map(([k, v]) => `
        <div class="panel-card module-stat">
          <span>${esc(k.replaceAll('_', ' '))}</span>
          <strong>${typeof v === 'number' && k.includes('revenue') ? '₹' + v.toFixed(2) : esc(v)}</strong>
        </div>
      `).join('');
    }

    function renderReportStats(d) {
      $('stats').innerHTML = `
        <div class="panel-card module-stat"><span>Total Members</span><strong>${d.members ?? 0}</strong></div>
        <div class="panel-card module-stat"><span>Active Members</span><strong style="color: var(--color-accent);">${d.active_members ?? 0}</strong></div>
        <div class="panel-card module-stat"><span>Active Trainers</span><strong>${d.trainers ?? 0}</strong></div>
        <div class="panel-card module-stat"><span>Active Plans</span><strong>${d.plans ?? 0}</strong></div>
        <div class="panel-card module-stat"><span>Today's Check-ins</span><strong>${d.attendance_today ?? 0}</strong></div>
        <div class="panel-card module-stat"><span>Monthly Revenue</span><strong style="color: var(--color-accent);">₹${parseFloat(d.revenue ?? 0).toLocaleString()}</strong></div>
      `;
    }

    function editRecord(x) {
      if (!C.update) return;
      $('recordId').value = x[C.editKey];
      fields(x);
      $('form').style.display = 'grid';
      $('cancelEdit').style.display = 'inline-flex';
      $('toggleForm').textContent = 'Editing Record';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function checkOutAttendance(attId) {
      let fd = new FormData();
      fd.append('csrf_token', CSRF);
      fd.append('attendance_id', attId);
      let r = await fetch(API + '?action=check_out', { method: 'POST', body: fd });
      let j = await r.json();
      alert(j.message);
      if (j.success) load();
    }

    async function togglePlanStatus(planId) {
      let fd = new FormData();
      fd.append('csrf_token', CSRF);
      fd.append('id', planId);
      let r = await fetch(API + '?action=toggle_plan', { method: 'POST', body: fd });
      let j = await r.json();
      alert(j.message);
      if (j.success) load();
    }

    if (!C.create) $('toggleForm').style.display = 'none';
    $('toggleForm').onclick = () => { $('form').style.display === 'none' ? openCreate() : closeForm(); };
    $('cancelEdit').onclick = closeForm;
    $('refresh').onclick = load;
    $('search').oninput = () => { clearTimeout(window.st); window.st = setTimeout(load, 250); };

    $('form').onsubmit = async e => {
      e.preventDefault();
      let fd = new FormData(e.target);
      let id = $('recordId').value;
      let action = id && C.update ? C.update : C.create;
      if (!action) return;

      let r = await fetch(API + '?action=' + action, { method: 'POST', body: fd });
      let j = await r.json();
      alert(j.message);
      if (j.success) {
        e.target.reset();
        closeForm();
        load();
      }
    };

    load();
  </script>
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>
</body>
</html>