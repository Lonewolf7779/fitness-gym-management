<?php
/**
 * IRONCORE Trainer Dashboard View Template
 * Section: Phase 3.2 Trainer Dashboard Refinement
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/TrainerMiddleware.php';

// Execute Trainer Authorization Guard
TrainerMiddleware::handle();

$trainerName  = $_SESSION['full_name'] ?? 'Marcus Vance';
$trainerEmail = $_SESSION['email'] ?? 'marcus@ironcore.com';

// UI Placeholder Data for Trainer Dashboard (Structured for future PHP/MySQL data binding)
$summaryStats = [
    'assigned_clients'         => 24,
    'active_programs'          => 18,
    'sessions_this_week'       => 32,
    'clients_needing_attention'=> 5
];

$clientsList = [
    [
        'name'         => 'Alex Rivera',
        'email'        => 'alex@gmail.com',
        'avatar'       => 'AR',
        'goal'         => 'Fat Loss & Conditioning',
        'progress'     => 68,
        'next_session' => 'Today (11:00 AM)',
        'status'       => 'Active'
    ],
    [
        'name'         => 'Daniel Carter',
        'email'        => 'daniel@gmail.com',
        'avatar'       => 'DC',
        'goal'         => 'Muscle Gain & Strength',
        'progress'     => 54,
        'next_session' => 'Tomorrow (09:00 AM)',
        'status'       => 'Active'
    ],
    [
        'name'         => 'Sophia Miller',
        'email'        => 'sophia@gmail.com',
        'avatar'       => 'SM',
        'goal'         => 'Powerlifting & Technique',
        'progress'     => 82,
        'next_session' => 'Friday (04:30 PM)',
        'status'       => 'Active'
    ],
    [
        'name'         => 'Ryan Brooks',
        'email'        => 'ryan@gmail.com',
        'avatar'       => 'RB',
        'goal'         => 'Endurance & Mobility',
        'progress'     => 61,
        'next_session' => 'Saturday (06:00 PM)',
        'status'       => 'Active'
    ]
];

$todaySchedule = [
    [
        'time'     => '09:00 AM',
        'client'   => 'Alex Rivera',
        'type'     => 'Personal Training',
        'status'   => 'Completed'
    ],
    [
        'time'     => '11:00 AM',
        'client'   => 'Daniel Carter',
        'type'     => 'Strength Training',
        'status'   => 'Upcoming'
    ],
    [
        'time'     => '04:30 PM',
        'client'   => 'Sophia Miller',
        'type'     => 'Progress Session',
        'status'   => 'Upcoming'
    ],
    [
        'time'     => '06:00 PM',
        'client'   => 'Ryan Brooks',
        'type'     => 'Cardio Session',
        'status'   => 'Upcoming'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trainer Portal | IRONCORE Fitness</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <!-- Backdrop Overlay for Mobile Drawer -->
    <div class="sidebar-overlay"></div>

    <!-- ==========================================================================
         SIDEBAR NAVIGATION
         ========================================================================== -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <a href="/index.php" class="brand-logo" aria-label="IRONCORE Home">
          <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/>
          </svg>
          <span>IRONCORE</span>
        </a>
        <span class="sidebar-badge">TRAINER PORTAL</span>
      </div>

      <ul class="sidebar-nav">
        <li>
          <a href="/trainer/index.php" class="nav-item-link active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>My Clients</span>
          </a>
        </li>
        <li>
          <a href="#programs" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
            <span>Workout Programs</span>
          </a>
        </li>
        <li>
          <a href="#schedule" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <span>Today's Schedule</span>
          </a>
        </li>
        <li>
          <a href="#progress" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span>Client Progress</span>
          </a>
        </li>
        <li>
          <a href="#messages" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>Messages</span>
          </a>
        </li>
      </ul>

      <div class="sidebar-footer">
        <div class="user-profile-badge">
          <div class="avatar-circle"><?= strtoupper(substr($trainerName, 0, 1)) ?></div>
          <div class="user-info">
            <div class="user-name"><?= e($trainerName) ?></div>
            <div class="user-role">Head Coach</div>
          </div>
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <a href="#settings" class="btn btn-secondary" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; justify-content: center;">Settings</a>
          <a href="/logout.php" class="btn btn-primary" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; justify-content: center;">Logout</a>
        </div>
      </div>
    </aside>

    <!-- ==========================================================================
         MAIN CONTENT WRAPPER
         ========================================================================== -->
    <div class="main-wrapper">

      <!-- TOP HEADER BAR -->
      <header class="dashboard-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <button class="admin-mobile-toggle" aria-label="Toggle navigation drawer">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>

          <div class="header-title-group">
            <h1>TRAINER ATHLETE HUB</h1>
            <p>Assigned athletes, daily workout schedules, & performance tracking</p>
          </div>
        </div>

        <div class="header-actions">
          <div class="header-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="admin-search-input" placeholder="Search client name..." aria-label="Search Clients">
            <span class="search-kbd">⌘K</span>
          </div>

          <!-- Notification Dropdown -->
          <div class="dropdown-menu-wrapper">
            <button class="icon-btn" id="notif-btn" aria-label="Notifications">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <span class="notification-badge"></span>
            </button>
            <div class="dropdown-panel" id="notif-dropdown">
              <div class="dropdown-header">
                <span>TRAINER NOTIFICATIONS</span>
                <span style="font-size: 0.75rem; color: var(--color-accent);">2 New</span>
              </div>
              <div class="dropdown-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                  <div style="font-weight: 700; color: #FFF;">Alex Rivera completed workout</div>
                  <div style="font-size: 0.75rem; color: var(--color-text-muted);">Upper Body Strength Session</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Profile Dropdown -->
          <div class="dropdown-menu-wrapper">
            <button class="icon-btn" id="profile-btn" aria-label="User menu" style="padding: 0; overflow: hidden;">
              <div class="avatar-circle" style="width: 100%; height: 100%; border-radius: 0; font-size: 0.85rem;"><?= strtoupper(substr($trainerName, 0, 1)) ?></div>
            </button>
            <div class="dropdown-panel" id="profile-dropdown" style="width: 220px;">
              <div style="padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-border); margin-bottom: 0.5rem;">
                <div style="font-weight: 700; color: #FFF; font-size: 0.9rem;"><?= e($trainerName) ?></div>
                <div style="font-size: 0.75rem; color: var(--color-text-muted);"><?= e($trainerEmail) ?></div>
              </div>
              <a href="/logout.php" class="dropdown-item" style="color: var(--color-danger);">Sign Out</a>
            </div>
          </div>
        </div>
      </header>

      <!-- DASHBOARD BODY CONTENT -->
      <main class="dashboard-body">

        <!-- TRAINER SUMMARY KPIs -->
        <section class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Assigned Clients</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="kpi-value"><?= e($summaryStats['assigned_clients']) ?></div>
            <div class="kpi-foot">
              <span>Active client Roster</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Active Programs</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
            </div>
            <div class="kpi-value" style="color: var(--color-accent);"><?= e($summaryStats['active_programs']) ?></div>
            <div class="kpi-foot" style="color: var(--color-accent);">
              <span>Custom Training Routines</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Sessions This Week</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="kpi-value"><?= e($summaryStats['sessions_this_week']) ?></div>
            <div class="kpi-foot">
              <span>85% Completion Rate</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Needing Attention</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="kpi-value" style="color: var(--color-danger);"><?= e($summaryStats['clients_needing_attention']) ?></div>
            <div class="kpi-foot" style="color: var(--color-danger);">
              <span>Check-in or plan update required</span>
            </div>
          </div>
        </section>

        <!-- TWO COLUMN SPLIT GRID -->
        <div class="dashboard-split-grid">

          <!-- MY CLIENTS TABLE -->
          <section class="panel-card">
            <div class="panel-header">
              <h3>MY ASSIGNED ATHLETS & CLIENTS</h3>
              <a href="#all-clients" class="panel-link">View All</a>
            </div>

            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Client</th>
                    <th>Fitness Goal</th>
                    <th>Program Progress</th>
                    <th>Next Session</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($clientsList as $client): ?>
                    <tr>
                      <td>
                        <div class="member-cell">
                          <div class="member-avatar"><?= e($client['avatar']) ?></div>
                          <div>
                            <div class="member-info-name"><?= e($client['name']) ?></div>
                            <div class="member-info-email"><?= e($client['email']) ?></div>
                          </div>
                        </div>
                      </td>
                      <td><span style="font-weight: 600; color: #FFF;"><?= e($client['goal']) ?></span></td>
                      <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                          <div style="flex-grow: 1; height: 6px; background-color: var(--color-surface-elevated); border-radius: 3px; overflow: hidden; max-width: 80px;">
                            <div style="width: <?= $client['progress'] ?>%; height: 100%; background-color: var(--color-accent);"></div>
                          </div>
                          <span style="font-size: 0.775rem; font-weight: 700;"><?= $client['progress'] ?>%</span>
                        </div>
                      </td>
                      <td><span style="font-size: 0.8rem; color: var(--color-text-muted);"><?= e($client['next_session']) ?></span></td>
                      <td><span class="status-pill active"><span class="status-dot-sm"></span> <?= e($client['status']) ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>

          <!-- TODAY'S SCHEDULE -->
          <section class="panel-card">
            <div class="panel-header">
              <h3>TODAY'S SCHEDULE</h3>
              <span style="font-size: 0.775rem; color: var(--color-accent); font-weight: 700;">4 SESSIONS</span>
            </div>

            <ul class="expiry-list">
              <?php foreach ($todaySchedule as $item): ?>
                <li class="expiry-item">
                  <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <div style="font-family: var(--font-heading); font-weight: 800; font-size: 0.85rem; color: var(--color-accent); min-width: 65px;"><?= e($item['time']) ?></div>
                    <div>
                      <div class="expiry-user-title"><?= e($item['client']) ?></div>
                      <div class="expiry-user-sub"><?= e($item['type']) ?></div>
                    </div>
                  </div>
                  <div>
                    <?php if ($item['status'] === 'Completed'): ?>
                      <span class="status-pill active">Done</span>
                    <?php else: ?>
                      <span class="status-pill pending">Upcoming</span>
                    <?php endif; ?>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          </section>

        </div>

      </main>
    </div>
  </div>

  <!-- Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
