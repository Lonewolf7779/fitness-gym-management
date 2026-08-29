<?php
/**
 * IRONCORE Layout Header
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';

$pageTitle = $pageTitle ?? 'IRONCORE | Fitness & Gym Management System';
$metaDesc  = $metaDesc ?? 'Manage your gym. Manage your members. Track fitness. Grow your business with IRONCORE Fitness & Gym Management System.';
$isLoggedIn = !empty($_SESSION['user_id']);
$userRole   = $_SESSION['role'] ?? 'member';

$portalUrl = '/member/index.php';
if ($userRole === 'admin') $portalUrl = '/admin/index.php';
if ($userRole === 'trainer') $portalUrl = '/trainer/index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($metaDesc) ?>">
  <title><?= e($pageTitle) ?></title>
  
  <!-- CSS Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/landing.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body>

  <!-- Sticky Glassmorphic Navbar -->
  <header class="navbar" id="main-header">
    <div class="container nav-container">
      <a href="/index.php" class="brand-logo" aria-label="IRONCORE Home">
        <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/>
        </svg>
        <span>IRONCORE</span>
      </a>

      <nav aria-label="Main Navigation">
        <ul class="nav-menu">
          <li><a href="/index.php#home" class="nav-link">Home</a></li>
          <li><a href="/index.php#features" class="nav-link">Features</a></li>
          <li><a href="/index.php#membership" class="nav-link">Membership</a></li>
          <li><a href="/index.php#about" class="nav-link">About</a></li>
        </ul>
      </nav>

      <div class="nav-actions">
        <?php if ($isLoggedIn): ?>
          <a href="<?= $portalUrl ?>" class="btn btn-secondary">Dashboard</a>
          <a href="/logout.php" class="btn btn-primary">Logout</a>
        <?php else: ?>
          <a href="/login.php" class="btn btn-secondary">Login</a>
          <a href="/register.php" class="btn btn-primary">Join Gym</a>
        <?php endif; ?>
      </div>

      <button class="mobile-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </header>
