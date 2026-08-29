<?php
/**
 * IRONCORE Fitness & Gym Management System
 * Public Landing Page Entry Point
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/security.php';

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
          <span class="hero-stat-value" data-counter data-target="642">0</span>
          <span class="hero-stat-label">Active Members</span>
        </div>
        <div class="hero-stat-item">
          <span class="hero-stat-value" data-counter data-target="38">0</span>
          <span class="hero-stat-label">Pro Trainers</span>
        </div>
        <div class="hero-stat-item">
          <span class="hero-stat-value" data-counter data-target="99.4" data-suffix="%">0%</span>
          <span class="hero-stat-label">Check-in Accuracy</span>
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
<section class="dashboard-preview-section">
  <div class="container">
    <div class="section-header reveal text-center" style="margin-bottom: 2.5rem;">
      <span class="section-tag">LIVE PLATFORM PREVIEW</span>
      <h2 class="section-title">CONTROL AT YOUR FINGERTIPS</h2>
    </div>

    <div class="preview-container reveal">
      <div class="preview-top-bar">
        <div class="preview-title-wrap">
          <h3>IRONCORE CENTRAL COMMAND</h3>
          <p>Real-Time Operations & Metric Monitoring</p>
        </div>
        <div class="chart-tab-group">
          <button class="chart-tab active" data-view="attendance">ATTENDANCE</button>
          <button class="chart-tab" data-view="revenue">REVENUE</button>
        </div>
      </div>

      <div class="metrics-row">
        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-label">Active Members</span>
            <svg class="metric-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div class="metric-value" data-counter data-target="642">0</div>
          <div class="metric-trend">+14% vs last month</div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-label">Trainers</span>
            <svg class="metric-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/></svg>
          </div>
          <div class="metric-value" data-counter data-target="38">0</div>
          <div class="metric-trend" style="color: var(--color-accent);">100% Certified</div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-label">Monthly Revenue</span>
            <svg class="metric-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div class="metric-value">₹<span data-counter data-target="2.84" data-decimals="2">0.00</span>L</div>
          <div class="metric-trend">+18.5% growth</div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-label">Today's Check-ins</span>
            <svg class="metric-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          </div>
          <div class="metric-value" data-counter data-target="183">0</div>
          <div class="metric-trend">Peak hours: 6 PM - 9 PM</div>
        </div>
      </div>

      <!-- Interactive Dynamic SVG Chart Render Container -->
      <div class="preview-chart-box" id="preview-chart-svg">
        <!-- SVG Injected dynamically via landing.js -->
      </div>

      <div class="preview-caption">"A COMPLETE VIEW OF YOUR GYM OPERATIONAL HEALTH"</div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     MEMBERSHIP / PRICING SECTION
     ========================================================================== -->
<section class="pricing-section" id="membership">
  <div class="container">
    <div class="section-header reveal text-center" style="text-align: center;">
      <span class="section-tag">PRICING TIERS</span>
      <h2 class="section-title">CHOOSE YOUR MEMBERSHIP PLAN</h2>
    </div>

    <div class="pricing-grid">
      <!-- Starter Plan -->
      <div class="pricing-card reveal">
        <h3 class="plan-title">STARTER</h3>
        <p class="plan-tag">Essential Facility Access</p>
        <div class="plan-price-wrap">
          <span class="plan-price">₹999</span>
          <span class="plan-period">/ month</span>
        </div>
        <ul class="plan-features">
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Full Gym Floor Access</li>
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Digital Attendance Tracking</li>
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Member Dashboard</li>
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Standard Locker Access</li>
        </ul>
        <a href="/register.php?plan=starter" class="btn btn-secondary btn-block">CHOOSE PLAN</a>
      </div>

      <!-- Pro Plan (Recommended) -->
      <div class="pricing-card recommended reveal">
        <div class="recommended-badge">RECOMMENDED</div>
        <h3 class="plan-title">PRO</h3>
        <p class="plan-tag">For Dedicated Athletes</p>
        <div class="plan-price-wrap">
          <span class="plan-price" style="color: var(--color-accent);">₹1,999</span>
          <span class="plan-period">/ month</span>
        </div>
        <ul class="plan-features">
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Everything in Starter</li>
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Personalized Workout Plans</li>
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Trainer Assistance & Support</li>
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Body Progress & Metrics Tracking</li>
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Group Fitness Sessions</li>
        </ul>
        <a href="/register.php?plan=pro" class="btn btn-primary btn-block">CHOOSE PLAN</a>
      </div>

      <!-- Elite Plan -->
      <div class="pricing-card reveal">
        <h3 class="plan-title">ELITE</h3>
        <p class="plan-tag">All-Inclusive VIP Experience</p>
        <div class="plan-price-wrap">
          <span class="plan-price">₹2,999</span>
          <span class="plan-period">/ month</span>
        </div>
        <ul class="plan-features">
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Everything in Pro</li>
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Dedicated 1-on-1 Personal Trainer</li>
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Custom Nutrition & Meal Specs</li>
          <li class="included"><svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Priority Locker & Recovery Lounge</li>
        </ul>
        <a href="/register.php?plan=elite" class="btn btn-secondary btn-block">CHOOSE PLAN</a>
      </div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     FINAL CTA SECTION
     ========================================================================== -->
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
