<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/response.php';

$selectedPlan = $_GET['plan'] ?? 'pro';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Registration | IRONCORE Fitness</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body class="auth-page">

  <div class="auth-card" style="max-width: 480px;">
    <div class="auth-header">
      <a href="/index.php" class="brand-logo">
        <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>
        <span>IRONCORE</span>
      </a>
      <h2>Join IRONCORE</h2>
      <p>Create your member profile & start training</p>
    </div>

    <?php if ($flashError = getFlash('error')): ?>
      <div class="alert alert-danger"><?= e($flashError) ?></div>
    <?php endif; ?>

    <form action="/register.php" method="POST" class="auth-form">
      <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

      <div class="form-group">
        <label for="full_name" class="form-label">Full Name</label>
        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Alex Rivera" required autofocus>
      </div>

      <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="alex@gmail.com" required>
      </div>

      <div class="form-group">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="tel" id="phone" name="phone" class="form-control" placeholder="+91 9876543210">
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">CREATE ACCOUNT</button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="/login.php">Log In Here</a>
    </div>
  </div>

  <script src="/assets/js/auth.js"></script>
</body>
</html>
