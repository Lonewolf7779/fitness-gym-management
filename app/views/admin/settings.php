<?php
/**
 * IRONCORE System Settings View
 * Live configuration persistence in MySQL system_settings table.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../services/SettingsService.php';

AdminMiddleware::handle();

$settings = new SettingsService();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Security token expired. Please refresh and try again.';
    } else {
        try {
            $settings->save([
                'gym_name'      => $_POST['gym_name'] ?? 'IRONCORE Fitness',
                'contact_email' => $_POST['contact_email'] ?? '',
                'phone'         => $_POST['phone'] ?? '',
                'address'       => $_POST['address'] ?? '',
                'currency'      => $_POST['currency'] ?? 'INR',
                'timezone'      => $_POST['timezone'] ?? 'Asia/Kolkata'
            ]);
            $message = 'Gym settings saved successfully.';
        } catch (Throwable $e) {
            $error = APP_DEBUG ? $e->getMessage() : 'Unable to save settings.';
        }
    }
}

$s = array_merge([
    'gym_name'      => 'IRONCORE Fitness',
    'contact_email' => 'contact@ironcore.com',
    'phone'         => '+91 98765 43210',
    'address'       => 'Plot 42, Cyber City, High-Tech Zone, Hyderabad, 500081',
    'currency'      => 'INR',
    'timezone'      => 'Asia/Kolkata'
], $settings->all());

$adminName = $_SESSION['full_name'] ?? 'Admin';
$adminEmail = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Settings | IRONCORE</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body style="background-color: var(--color-bg);">
  <div class="dashboard-shell">
    <?php
    $currentSection = 'settings';
    $pageHeading    = 'SYSTEM SETTINGS';
    $pageSubtitle   = 'Configure gym profile, operational defaults, and regional formatting';
    require_once __DIR__ . '/../layouts/admin_nav.php';
    ?>

      <main class="dashboard-body">
        <section class="panel-card" style="max-width: 900px; margin: 0 auto; padding: 2.25rem;">
          <h2 style="margin-bottom: 0.5rem;">Facility & Operational Configuration</h2>
          <p style="color: var(--color-text-muted); margin-bottom: 1.5rem;">
            These settings are stored securely in MySQL and govern operational defaults across the entire system.
          </p>

          <?php if ($message): ?>
            <div class="status-pill active" style="margin-bottom: 1.5rem; display: inline-flex; padding: 0.5rem 1rem;">
              <span class="status-dot-sm"></span> <?= e($message) ?>
            </div>
          <?php endif; ?>

          <?php if ($error): ?>
            <div class="status-pill danger" style="margin-bottom: 1.5rem; display: inline-flex; padding: 0.5rem 1rem;">
              <?= e($error) ?>
            </div>
          <?php endif; ?>

          <form method="post" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
            <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">

            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Gym Facility Name *</label>
              <input type="text" name="gym_name" class="form-control" value="<?= e($s['gym_name']) ?>" required style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
            </div>

            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Contact Email Address *</label>
              <input type="email" name="contact_email" class="form-control" value="<?= e($s['contact_email']) ?>" required style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
            </div>

            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Official Phone Number</label>
              <input type="tel" name="phone" class="form-control" value="<?= e($s['phone']) ?>" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
            </div>

            <div>
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Base Currency</label>
              <select name="currency" class="form-control" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
                <option value="INR" <?= $s['currency'] === 'INR' ? 'selected' : '' ?>>INR (₹)</option>
                <option value="USD" <?= $s['currency'] === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                <option value="EUR" <?= $s['currency'] === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                <option value="GBP" <?= $s['currency'] === 'GBP' ? 'selected' : '' ?>>GBP (£)</option>
              </select>
            </div>

            <div style="grid-column: 1 / -1;">
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Timezone</label>
              <select name="timezone" class="form-control" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);">
                <option value="Asia/Kolkata" <?= $s['timezone'] === 'Asia/Kolkata' ? 'selected' : '' ?>>Asia/Kolkata (IST +5:30)</option>
                <option value="UTC" <?= $s['timezone'] === 'UTC' ? 'selected' : '' ?>>UTC</option>
                <option value="Asia/Dubai" <?= $s['timezone'] === 'Asia/Dubai' ? 'selected' : '' ?>>Asia/Dubai (GST +4:00)</option>
                <option value="America/New_York" <?= $s['timezone'] === 'America/New_York' ? 'selected' : '' ?>>America/New_York (EST -5:00)</option>
                <option value="Europe/London" <?= $s['timezone'] === 'Europe/London' ? 'selected' : '' ?>>Europe/London (GMT +0:00)</option>
              </select>
            </div>

            <div style="grid-column: 1 / -1;">
              <label class="form-label" style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Facility Physical Address</label>
              <textarea name="address" rows="3" class="form-control" style="width: 100%; padding: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); color: #FFF; border-radius: var(--radius-sm);"><?= e($s['address']) ?></textarea>
            </div>

            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
              <a class="btn btn-secondary" href="/admin/index.php">Cancel</a>
              <button class="btn btn-primary" type="submit">Save System Settings</button>
            </div>
          </form>
        </section>
      </main>
    </div>
  </div>

  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
