<?php
/**
 * IRONCORE Member Dashboard View Template
 * Section: Phase 3.2 Member Dashboard Refinement
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Execute Member Authorization Guard
AuthMiddleware::handle();

$memberName  = $_SESSION['full_name'] ?? 'Alex Rivera';
$memberEmail = $_SESSION['email'] ?? 'alex@gmail.com';

// UI Placeholder Data for Member Dashboard (Structured for future PHP/MySQL data binding)
$memberSummary = [
    'current_plan'   => 'PRO PLAN',
    'days_remaining' => 47,
    'workout_streak' => 12,
    'attendance_rate'=> 86
];

$todayWorkout = [
    'title'     => 'UPPER BODY STRENGTH & HYPERTROPHY',
    'exercises' => [
        [
            'name'     => 'Barbell Bench Press',
            'details'  => '4 sets × 10 reps (80 kg)',
            'status'   => 'Completed'
        ],
        [
            'name'     => 'Lat Pulldown',
            'details'  => '3 sets × 12 reps (65 kg)',
            'status'   => 'Completed'
        ],
        [
            'name'     => 'Dumbbell Shoulder Press',
            'details'  => '3 sets × 10 reps (24 kg)',
            'status'   => 'In Progress'
        ],
        [
            'name'     => 'Seated Cable Row',
            'details'  => '3 sets × 12 reps (55 kg)',
            'status'   => 'Pending'
        ]
    ]
];

$weeklyStats = [
    'workouts_completed' => '5 / 6',
    'calories_burned'    => '3,420 kcal',
    'training_time'      => '4h 35m',
    'weekly_attendance'  => '5 Days'
];

$upcomingSession = [
    'trainer'  => 'Marcus Vance',
    'role'     => 'Senior Strength Coach',
    'session'  => '1-on-1 Personal Training',
    'date'     => 'Tomorrow at 6:00 PM',
    'status'   => 'Confirmed'
];

$recentActivity = [
    [
        'title' => 'Workout completed',
        'desc'  => 'Upper Body Hypertrophy Session',
        'time'  => 'Yesterday'
    ],
    [
        'title' => 'Body weight updated',
        'desc'  => '78.5 kg (-1.2 kg this month)',
        'time'  => '2 days ago'
    ],
    [
        'title' => 'Personal training session',
        'desc'  => 'Form check & squat technique with Marcus',
        'time'  => '3 days ago'
    ],
    [
        'title' => 'Streak milestone reached',
        'desc'  => 'Achieved 10-day active workout streak!',
        'time'  => 'Last week'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Portal | IRONCORE Fitness</title>
  
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
        <span class="sidebar-badge">MEMBER PORTAL</span>
      </div>

      <ul class="sidebar-nav">
        <li>
          <a href="/member/index.php" class="nav-item-link active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span>My Dashboard</span>
          </a>
        </li>
        <li>
          <a href="#workout-plan" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
            <span>Workout Plan</span>
          </a>
        </li>
        <li>
          <a href="#progress-logs" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span>Progress Logs</span>
          </a>
        </li>
        <li>
          <a href="#attendance-history" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <span>Attendance History</span>
          </a>
        </li>
        <li>
          <a href="#subscription" class="nav-item-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Subscription</span>
          </a>
        </li>
      </ul>

      <div class="sidebar-footer">
        <div class="user-profile-badge">
          <div class="avatar-circle"><?= strtoupper(substr($memberName, 0, 1)) ?></div>
          <div class="user-info">
            <div class="user-name"><?= e($memberName) ?></div>
            <div class="user-role">Pro Athlete</div>
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
            <h1>ATHLETE PERFORMANCE HUB</h1>
            <p>Welcome back, <?= e($memberName) ?>. Track your workout programs, streak, & transformation.</p>
          </div>
        </div>

        <div class="header-actions">
          <!-- Notification Dropdown -->
          <div class="dropdown-menu-wrapper">
            <button class="icon-btn" id="notif-btn" aria-label="Notifications">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <span class="notification-badge"></span>
            </button>
            <div class="dropdown-panel" id="notif-dropdown">
              <div class="dropdown-header">
                <span>ATHLETE ALERTS</span>
                <span style="font-size: 0.75rem; color: var(--color-accent);">1 New</span>
              </div>
              <div class="dropdown-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                  <div style="font-weight: 700; color: #FFF;">Personal Training Tomorrow</div>
                  <div style="font-size: 0.75rem; color: var(--color-text-muted);">Session with Marcus Vance at 6:00 PM</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Profile Dropdown -->
          <div class="dropdown-menu-wrapper">
            <button class="icon-btn" id="profile-btn" aria-label="User menu" style="padding: 0; overflow: hidden;">
              <div class="avatar-circle" style="width: 100%; height: 100%; border-radius: 0; font-size: 0.85rem;"><?= strtoupper(substr($memberName, 0, 1)) ?></div>
            </button>
            <div class="dropdown-panel" id="profile-dropdown" style="width: 220px;">
              <div style="padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-border); margin-bottom: 0.5rem;">
                <div style="font-weight: 700; color: #FFF; font-size: 0.9rem;"><?= e($memberName) ?></div>
                <div style="font-size: 0.75rem; color: var(--color-text-muted);"><?= e($memberEmail) ?></div>
              </div>
              <a href="/logout.php" class="dropdown-item" style="color: var(--color-danger);">Sign Out</a>
            </div>
          </div>
        </div>
      </header>

      <!-- DASHBOARD BODY CONTENT -->
      <main class="dashboard-body">

        <!-- MEMBER SUMMARY KPIs -->
        <section class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Current Plan</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div class="kpi-value" style="color: var(--color-accent); font-size: 1.8rem;"><?= e($memberSummary['current_plan']) ?></div>
            <div class="kpi-foot" style="color: var(--color-success);">
              <span>Status: Active</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Days Remaining</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="kpi-value"><?= e($memberSummary['days_remaining']) ?> Days</div>
            <div class="kpi-foot">
              <span>Auto-renews Oct 15, 2026</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Workout Streak</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="kpi-value" style="color: #FF9F0A;"><?= e($memberSummary['workout_streak']) ?> Days 🔥</div>
            <div class="kpi-foot" style="color: #FF9F0A;">
              <span>Personal Best Streak!</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Monthly Attendance</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="kpi-value"><?= e($memberSummary['attendance_rate']) ?>%</div>
            <div class="kpi-foot">
              <span>18 Check-ins this month</span>
            </div>
          </div>
        </section>

        <!-- TWO COLUMN SPLIT GRID -->
        <div class="dashboard-split-grid">

          <!-- TODAY'S WORKOUT PROGRAM -->
          <section class="panel-card">
            <div class="panel-header">
              <div>
                <h3>TODAY'S WORKOUT PROGRAM</h3>
                <div style="font-size: 0.775rem; color: var(--color-accent); font-weight: 700; margin-top: 0.2rem;"><?= e($todayWorkout['title']) ?></div>
              </div>
              <a href="#full-routine" class="panel-link">Full Routine</a>
            </div>

            <ul class="expiry-list">
              <?php foreach ($todayWorkout['exercises'] as $ex): ?>
                <li class="expiry-item">
                  <div>
                    <div class="expiry-user-title"><?= e($ex['name']) ?></div>
                    <div class="expiry-user-sub"><?= e($ex['details']) ?></div>
                  </div>
                  <div>
                    <?php if ($ex['status'] === 'Completed'): ?>
                      <span class="status-pill active"><span class="status-dot-sm"></span> Completed</span>
                    <?php elseif ($ex['status'] === 'In Progress'): ?>
                      <span class="status-pill warning"><span class="status-dot-sm"></span> In Progress</span>
                    <?php else: ?>
                      <span class="status-pill pending"><span class="status-dot-sm"></span> Pending</span>
                    <?php endif; ?>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          </section>

          <!-- WEEKLY PROGRESS & UPCOMING SESSION -->
          <div style="display: flex; flex-direction: column; gap: 1.75rem;">
            
            <!-- UPCOMING SESSION CARD -->
            <section class="panel-card" style="background: linear-gradient(135deg, #181818 0%, #1E1E1E 100%); border-color: rgba(232, 255, 0, 0.3);">
              <div class="panel-header">
                <h3>UPCOMING 1-ON-1 SESSION</h3>
                <span class="status-pill active">Confirmed</span>
              </div>
              <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem;">
                <div class="avatar-circle" style="width: 44px; height: 44px; font-size: 1.1rem;">MV</div>
                <div>
                  <div style="font-weight: 800; font-size: 1rem; color: #FFF;"><?= e($upcomingSession['trainer']) ?></div>
                  <div style="font-size: 0.8rem; color: var(--color-text-muted);"><?= e($upcomingSession['role']) ?></div>
                  <div style="font-size: 0.825rem; font-weight: 700; color: var(--color-accent); margin-top: 0.35rem;"><?= e($upcomingSession['date']) ?></div>
                </div>
              </div>
            </section>

            <!-- WEEKLY ANALYTICS SUMMARY -->
            <section class="panel-card">
              <div class="panel-header">
                <h3>WEEKLY PERFORMANCE ANALYTICS</h3>
              </div>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="background-color: var(--color-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                  <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Workouts</div>
                  <div style="font-size: 1.3rem; font-weight: 800; color: #FFF; font-family: var(--font-heading);"><?= e($weeklyStats['workouts_completed']) ?></div>
                </div>

                <div style="background-color: var(--color-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                  <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Calories Burned</div>
                  <div style="font-size: 1.3rem; font-weight: 800; color: var(--color-accent); font-family: var(--font-heading);"><?= e($weeklyStats['calories_burned']) ?></div>
                </div>

                <div style="background-color: var(--color-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                  <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Training Time</div>
                  <div style="font-size: 1.3rem; font-weight: 800; color: #FFF; font-family: var(--font-heading);"><?= e($weeklyStats['training_time']) ?></div>
                </div>

                <div style="background-color: var(--color-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                  <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Attendance</div>
                  <div style="font-size: 1.3rem; font-weight: 800; color: var(--color-success); font-family: var(--font-heading);"><?= e($weeklyStats['weekly_attendance']) ?></div>
                </div>
              </div>
            </section>

          </div>
        </div>

        <!-- RECENT ACTIVITY FEED -->
        <section class="panel-card">
          <div class="panel-header">
            <h3>RECENT ATHLETE ACTIVITY</h3>
          </div>
          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
            <?php foreach ($recentActivity as $act): ?>
              <div style="background-color: var(--color-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                <div style="font-weight: 700; font-size: 0.85rem; color: #FFF; margin-bottom: 0.25rem;"><?= e($act['title']) ?></div>
                <div style="font-size: 0.775rem; color: var(--color-text-muted); margin-bottom: 0.5rem;"><?= e($act['desc']) ?></div>
                <div style="font-size: 0.725rem; font-weight: 700; color: var(--color-accent);"><?= e($act['time']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

      </main>
    </div>
  </div>

  <!-- Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
