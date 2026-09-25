<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/response.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | IRONCORE Fitness</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body class="auth-page">

  <div class="auth-card">
    <div style="margin-bottom: 1rem;">
      <a href="/index.php" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--color-text-muted); text-decoration: none; font-weight: 700; transition: color var(--transition-fast);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span>Back to Home</span>
      </a>
    </div>

    <div class="auth-header">
      <a href="/index.php" class="brand-logo">
        <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
        <span>IRONCORE</span>
      </a>
      <h2>Welcome Back</h2>
      <p>Log in to access your portal</p>
    </div>

    <?php if ($flashError = getFlash('error')): ?>
      <div class="alert alert-danger"><?= e($flashError) ?></div>
    <?php endif; ?>

    <?php if ($flashSuccess = getFlash('success')): ?>
      <div class="alert alert-success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>

    <form action="/login.php" method="POST" class="auth-form">
      <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

      <div class="form-group">
        <label for="identity" class="form-label">Email Address or Username</label>
        <input type="text" id="identity" name="identity" class="form-control" placeholder="Username or email address" required autofocus>
      </div>

      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xs);">
          <label for="password" class="form-label" style="margin-bottom: 0;">Password</label>
        </div>
        <div style="position: relative;">
          <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required style="padding-right: 2.75rem;">
          <button type="button" class="pwd-toggle-btn" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--color-text-muted); cursor: pointer; display: flex; align-items: center; padding: 0.25rem;" aria-label="Toggle password visibility">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">LOG IN</button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="/register.php">Register Here</a>
    </div>
  </div>

  <script src="/assets/js/auth.js"></script>
</body>
</html>
