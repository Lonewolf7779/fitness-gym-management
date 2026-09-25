<?php
/**
 * IRONCORE Fitness & Gym Management System
 * Public Landing Page Entry Point
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/services/GymManagementService.php';

$publicStats = [
    'active_members' => 0,
    'trainer_count' => 0,
    'active_pct' => 0,
    'monthly_revenue' => '0.00',
    'today_attendance' => 0
];
$publicPlans = [];

try {
    $publicService = new GymManagementService();
    $dashboardStats = $publicService->dashboardStats();
    $publicStats['active_members'] = (int) ($dashboardStats['active_members'] ?? 0);
    $publicStats['trainer_count'] = count($publicService->trainers('', 'active'));
    $publicStats['active_pct'] = (float) ($dashboardStats['active_pct'] ?? 0);
    $publicStats['monthly_revenue'] = (string) ($dashboardStats['monthly_revenue'] ?? '0.00');
    $publicStats['today_attendance'] = (int) ($dashboardStats['today_attendance'] ?? 0);
    $publicPlans = $publicService->plans(true);
} catch (Throwable $e) {
    if (APP_DEBUG) {
        error_log('Landing page live metrics unavailable: ' . $e->getMessage());
    }
}

$pageTitle = "IRONCORE | Train. Track. Transform. Gym Management Platform";
$metaDesc  = "Complete Fitness & Gym Management System. Manage members, trainers, attendance, workouts, payments, and progress tracking seamlessly.";

// Include Header Layout
require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<!-- ==========================================================================
     HERO SECTION
     ========================================================================== -->
<section class="hero-section" id="home">
  <div class="hero-bg-overlay"></div>
  <div class="container hero-grid">
    <div class="hero-content reveal">
      <div class="hero-badge">
        <span class="hero-badge-dot"></span> Next-Gen Gym Management
      </div>
      <h1 class="hero-title">
        TRAIN.<br>
        TRACK.<br>
        <span class="highlight">TRANSFORM.</span>
      </h1>
      <p class="hero-subtitle">A smarter way to manage your fitness journey.</p>
      <p class="hero-description">
        Manage memberships, workouts, attendance, trainers and progress from one powerful unified platform.
      </p>
      <div class="hero-cta-group">
        <a href="/register.php" class="btn btn-primary">
          GET STARTED
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="#features" class="btn btn-secondary">EXPLORE FEATURES</a>
      </div>

      <div class="hero-stats-row">
        <div class="hero-stat-item">
          <span class="hero-stat-value" data-counter data-target="<?= $publicStats['active_members'] ?>">0</span>
          <span class="hero-stat-label">Active Members</span>
        </div>
        <div class="hero-stat-item">
          <span class="hero-stat-value" data-counter data-target="<?= $publicStats['trainer_count'] ?>">0</span>
          <span class="hero-stat-label">Active Trainers</span>
        </div>
        <div class="hero-stat-item">
          <span class="hero-stat-value" data-counter data-target="<?= $publicStats['active_pct'] ?>" data-suffix="%">0%</span>
          <span class="hero-stat-label">Active Member Rate</span>
        </div>
      </div>
    </div>

    <div class="hero-visual-wrapper reveal">
      <div class="hero-visual-card">
        <img src="/assets/images/hero_bg.jpg" alt="IRONCORE Athletic Facility" loading="eager">
        <div class="hero-overlay-tag">
          <div class="hero-tag-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
          </div>
          <div>
            <div style="font-weight: 800; font-size: 0.95rem; color: #FFF;">REAL-TIME ANALYTICS</div>
            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Active Workout Sessions Monitored Live</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     PRODUCT VALUE SECTION (Editorial Asymmetrical Layout)
     ========================================================================== -->
<section class="value-section" id="about">
  <div class="container">
    <div class="section-header text-center reveal" style="max-width: 640px; margin-left: auto; margin-right: auto; text-align: center;">
      <span class="section-tag">UNIFIED SOLUTION</span>
      <h2 class="section-title">EVERYTHING YOUR GYM NEEDS. <span class="text-accent">ONE PLATFORM.</span></h2>
    </div>

    <div class="value-grid">
      <div class="value-block span-7 reveal">
        <div class="value-num">01 / MEMBERS</div>
        <h3 class="value-title">MEMBER MANAGEMENT</h3>
        <p class="value-desc">
          Manage member profiles, active subscriptions, payment status, and individual fitness history in a centralized database built for high scalability.
        </p>
      </div>

      <div class="value-block span-5 reveal">
        <div class="value-num">02 / TRAINERS</div>
        <h3 class="value-title">TRAINER ASSIGNMENTS</h3>
        <p class="value-desc">
          Assign certified trainers, coordinate client schedules, track workout plan adherence, and support member transformation goals.
        </p>
      </div>

      <div class="value-block span-5 reveal">
        <div class="value-num">03 / ATTENDANCE</div>
        <h3 class="value-title">ATTENDANCE TRACKING</h3>
        <p class="value-desc">
          Instant digital check-in records, time logging, and historical peak-hour analytics to manage facility capacity effectively.
        </p>
      </div>

      <div class="value-block span-7 reveal">
        <div class="value-num">04 / PAYMENTS</div>
        <h3 class="value-title">SUBSCRIPTIONS & PAYMENTS</h3>
        <p class="value-desc">
          Automate plan renewals, manage UPI and cash transaction logs, handle billing cycles, and keep revenue streams transparent and organized.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     FEATURE SECTION (BUILT FOR THE WHOLE GYM)
     ========================================================================== -->
<section class="features-section" id="features">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-tag">CAPABILITIES</span>
      <h2 class="section-title">BUILT FOR THE WHOLE GYM</h2>
    </div>

    <div class="feature-cards-grid">
      <div class="feature-card reveal">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h3>Member Management</h3>
        <p>Keep member information, memberships, contact emergency records, and activity status clean and organized.</p>
      </div>

      <div class="feature-card reveal">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <h3>Attendance Tracking</h3>
        <p>Track daily gym check-ins, record arrival timestamps, and generate historical attendance frequency reports.</p>
      </div>

      <div class="feature-card reveal">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
        </div>
        <h3>Workout Plans</h3>
        <p>Create, assign, and customize detailed workout programs complete with exercise sets, reps, and rest periods.</p>
      </div>

      <div class="feature-card reveal">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        </div>
        <h3>Progress Tracking</h3>
        <p>Monitor body metrics over time including weight progression, body fat percentages, and physical measurements.</p>
      </div>

      <div class="feature-card reveal">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
        </div>
        <h3>Trainer Management</h3>
        <p>Manage coach specializations, hourly rates, client assignments, and personal training session schedules.</p>
      </div>

      <div class="feature-card reveal">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <h3>Membership Management</h3>
        <p>Configure subscription plans, pricing tiers, duration periods, expiration alerts, and renewal tracking.</p>
      </div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     HOW IT WORKS SECTION
     ========================================================================== -->
<section class="steps-section">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-tag">WORKFLOW</span>
      <h2 class="section-title">HOW IT WORKS</h2>
    </div>

    <div class="steps-grid">
      <div class="step-card reveal">
        <div class="step-number">01</div>
        <h3 class="step-title">REGISTER</h3>
        <p class="step-desc">Create your member profile, input fitness goals, and select a membership plan suited to your schedule.</p>
      </div>

      <div class="step-card reveal">
        <div class="step-number">02</div>
        <h3 class="step-title">TRAIN</h3>
        <p class="step-desc">Follow assigned custom workout routines, check in at the facility, and work directly with certified trainers.</p>
      </div>

      <div class="step-card reveal">
        <div class="step-number">03</div>
        <h3 class="step-title">PROGRESS</h3>
        <p class="step-desc">Track check-in history, subscription renewals, body metric trends, and transformation milestones online.</p>
      </div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     DASHBOARD PREVIEW SECTION
     ========================================================================== -->
<section class="quotes-section" id="motivation">
  <div class="container">
    <div class="quotes-shell reveal">
      <div class="quotes-eyebrow">IRONCORE MINDSET</div>
      <div class="quotes-mark" aria-hidden="true">“</div>
      <blockquote class="quote-display" id="quote-display">Discipline is built one decision at a time.</blockquote>
      <div class="quote-author" id="quote-author">IRONCORE</div>
      <div class="quote-progress" aria-hidden="true"><span id="quote-progress-bar"></span></div>
    </div>
  </div>
</section>

<section class="pricing-section" id="membership">
  <div class="container">
    <div class="section-header reveal text-center" style="text-align:center;">
      <span class="section-tag">MEMBERSHIP</span>
      <h2 class="section-title">PLANS CREATED BY YOUR GYM</h2>
    </div>
    <?php if (empty($publicPlans)): ?>
      <div class="empty-public-data reveal">
        <strong>No membership plans published yet.</strong>
        <span>An administrator can create plans from the Memberships module. Published plans will appear here automatically.</span>
        <a href="/login.php" class="btn btn-secondary">ADMIN SIGN IN</a>
      </div>
    <?php else: ?>
      <div class="pricing-grid">
        <?php foreach ($publicPlans as $plan): ?>
          <article class="pricing-card reveal <?= !empty($plan['is_recommended']) ? 'recommended' : '' ?>">
            <?php if (!empty($plan['is_recommended'])): ?><div class="recommended-badge">RECOMMENDED</div><?php endif; ?>
            <h3 class="plan-title"><?= e(strtoupper($plan['title'])) ?></h3>
            <?php if (!empty($plan['tag'])): ?><p class="plan-tag"><?= e($plan['tag']) ?></p><?php endif; ?>
            <div class="plan-price-wrap">
              <span class="plan-price">₹<?= number_format((float)$plan['price'], 0) ?></span>
              <span class="plan-period">/ <?= e($plan['billing_cycle']) ?></span>
            </div>
            <?php if (!empty($plan['description'])): ?><p class="plan-tag"><?= e($plan['description']) ?></p><?php endif; ?>
            <a href="/register.php?plan=<?= (int)$plan['id'] ?>" class="btn btn-primary btn-block">CHOOSE PLAN</a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="cta-section">
  <div class="container">
    <div class="cta-box reveal">
      <h2 class="cta-title">YOUR NEXT REP<br><span class="text-accent">STARTS HERE.</span></h2>
      <p class="cta-desc">Take control of your gym. Take control of your progress with IRONCORE today.</p>
      <a href="/register.php" class="btn btn-primary" style="padding: 1.1rem 2.5rem; font-size: 1.05rem;">
        GET STARTED NOW
      </a>
    </div>
  </div>
</section>

<?php
// Include Footer Layout
require_once __DIR__ . '/../app/views/layouts/footer.php';
?>
