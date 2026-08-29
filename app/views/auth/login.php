<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/response.php';

$isDevAuth = (defined('APP_ENV') && APP_ENV === 'local' && defined('AUTH_MODE') && AUTH_MODE === 'dev');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | IRONCORE Fitness</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body class="auth-page">

  <div class="auth-card" style="<?= $isDevAuth ? 'max-width: 480px;' : '' ?>">
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

    <?php if ($isDevAuth): ?>
      <!-- Development-only helper banner. Must never be shown in production. -->
      <div style="background: rgba(232, 255, 0, 0.08); border: 1px solid rgba(232, 255, 0, 0.3); border-radius: var(--radius-sm); padding: 0.85rem; margin-bottom: 1.5rem; font-size: 0.775rem; color: var(--color-text-muted);">
        <div style="font-weight: 700; color: var(--color-accent); margin-bottom: 0.35rem; text-transform: uppercase;">⚡ DEV AUTH MODE ACTIVE</div>
        <div>Admin: <code style="color:#FFF;">admin@ironcore.com</code> / <code style="color:#FFF;">Admin@123</code></div>
        <div>Trainer: <code style="color:#FFF;">marcus@ironcore.com</code> / <code style="color:#FFF;">Trainer@123</code></div>
        <div>Member: <code style="color:#FFF;">alex@gmail.com</code> / <code style="color:#FFF;">Member@123</code></div>
        <div>Suspended: <code style="color:#FFF;">suspended@gmail.com</code> / <code style="color:#FFF;">Member@123</code></div>
      </div>
    <?php endif; ?>

    <form action="/login.php" method="POST" class="auth-form">
      <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

      <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" required autofocus>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
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
