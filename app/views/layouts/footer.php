<?php
/**
 * IRONCORE Layout Footer
 */
?>
  <!-- Global Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="/index.php" class="brand-logo" aria-label="IRONCORE Home">
            <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/>
            </svg>
            <span>IRONCORE</span>
          </a>
          <p>Fitness & Gym Management System. Empowering gym owners, trainers, and fitness athletes with a unified performance platform.</p>
        </div>

        <div class="footer-col">
          <h4>Platform</h4>
          <ul class="footer-links">
            <li><a href="/index.php#features">Member Management</a></li>
            <li><a href="/index.php#features">Attendance Tracking</a></li>
            <li><a href="/index.php#features">Workout Programming</a></li>
            <li><a href="/index.php#features">Progress Analytics</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Quick Links</h4>
          <ul class="footer-links">
            <li><a href="/index.php#home">Home</a></li>
            <li><a href="/index.php#membership">Membership Plans</a></li>
            <li><a href="/login.php">Member Portal Login</a></li>
            <li><a href="/register.php">Create Account</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>System Tech</h4>
          <ul class="footer-links">
            <li><span class="system-status"><span class="status-dot"></span> System Operational</span></li>
            <li><span style="color: var(--color-text-dim);">PHP 8.2 & Native MySQL</span></li>
            <li><span style="color: var(--color-text-dim);">Vanilla JS & CSS3</span></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Ironcore. All rights reserved.</p>
        <p>Built with MVC PHP & Vanilla Web Tech</p>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/landing.js"></script>
  <script src="/assets/js/auth.js"></script>
</body>
</html>
