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
</head>
<body class="auth-page">

  <div class="auth-card">
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
