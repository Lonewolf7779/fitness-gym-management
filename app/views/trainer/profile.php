<?php
/**
 * IRONCORE Trainer - Coach Profile & Credentials Management
 * Live profile details, specialization attributes, and secure credentials management.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/TrainerMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

TrainerMiddleware::handle();

$userId = (int) ($_SESSION['user_id'] ?? 0);
$svc = new GymManagementService();
$user = $svc->getUserById($userId);
$trainer = $svc->getTrainerByUserId($userId);
$trainerData = $svc->trainerDashboard($userId);
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coach Profile & Settings | IRONCORE Trainer</title>
  
  <!-- System CSS Tokens & Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/admin-modules.css">
</head>
<body style="background-color: var(--color-bg);">

  <div class="dashboard-shell">
    <?php
    $currentSection = 'profile';
    $pageHeading    = 'COACH PROFILE & PREFERENCES';
    $pageSubtitle   = 'Manage your instructor credentials, coaching specialty, and security settings';
    require_once __DIR__ . '/../layouts/trainer_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. COACH IDENTITY HEADER CARD -->
        <section class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl); margin-bottom: var(--space-6);">
          <div style="display: flex; align-items: center; gap: var(--space-5); flex-wrap: wrap;">
            <div class="avatar-circle" style="width: 72px; height: 72px; font-size: 28px; font-weight: 800; background: rgba(229, 9, 20, 0.15); color: var(--color-primary); border: 2px solid rgba(229, 9, 20, 0.4);">
              <?= strtoupper(substr($user['full_name'] ?? 'Coach', 0, 1)) ?>
            </div>
            <div style="flex: 1; min-width: 240px;">
              <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap;">
                <h2 style="font-size: 22px; font-weight: 800; color: var(--color-text); margin: 0;"><?= htmlspecialchars($user['full_name'] ?? 'Coach') ?></h2>
                <span class="badge badge-primary">CERTIFIED TRAINER</span>
                <span class="badge badge-success">ACTIVE</span>
              </div>
              <p style="font-size: 14px; color: var(--color-text-muted); margin: 4px 0 0 0;"><?= htmlspecialchars($user['email'] ?? '') ?> • <?= htmlspecialchars($trainer['specialization'] ?? $trainer['specialty'] ?? 'Strength & Conditioning') ?></p>
            </div>
            <div style="display: flex; gap: var(--space-4); text-align: center;">
              <div style="padding: var(--space-3) var(--space-4); background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <div style="font-size: 20px; font-weight: 800; color: var(--color-primary);"><?= $trainerData['assigned_clients_count'] ?></div>
                <div style="font-size: 11px; color: var(--color-text-muted); text-transform: uppercase;">Athletes</div>
              </div>
              <div style="padding: var(--space-3) var(--space-4); background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <div style="font-size: 20px; font-weight: 800; color: #34D399;"><?= (int)($trainer['experience_years'] ?? 3) ?> Yrs</div>
                <div style="font-size: 11px; color: var(--color-text-muted); text-transform: uppercase;">Experience</div>
              </div>
            </div>
          </div>
        </section>

        <!-- 2. FORMS DUAL GRID: PROFILE DETAILS & CHANGE PASSWORD -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-6);">
          
          <!-- Profile Details Form -->
          <section class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
            <div style="margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-3);">
              <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text); margin: 0; text-transform: uppercase;">Instructor Details</h3>
              <p style="font-size: 13px; color: var(--color-text-muted); margin: 2px 0 0 0;">Update your public coaching bio and contact details</p>
            </div>

            <form id="profile-form" onsubmit="submitProfileForm(event)">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                <div>
                  <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Full Name *</label>
                  <input type="text" name="full_name" class="form-input" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required style="width: 100%;">
                </div>

                <div>
                  <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Email Address *</label>
                  <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required style="width: 100%;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                  <div>
                    <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Phone Number</label>
                    <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($trainer['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000" style="width: 100%;">
                  </div>
                  <div>
                    <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Experience (Years)</label>
                    <input type="number" name="experience_years" class="form-input" value="<?= (int)($trainer['experience_years'] ?? 3) ?>" min="0" max="50" style="width: 100%;">
                  </div>
                </div>

                <div>
                  <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Specialization / Specialty</label>
                  <input type="text" name="specialization" class="form-input" value="<?= htmlspecialchars($trainer['specialization'] ?? $trainer['specialty'] ?? '') ?>" placeholder="e.g., Hypertrophy, Strength, HIIT" style="width: 100%;">
                </div>

                <div>
                  <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Coaching Biography</label>
                  <textarea name="bio" class="form-input" rows="3" placeholder="Tell members about your coaching methodology and background..." style="width: 100%;"><?= htmlspecialchars($trainer['bio'] ?? '') ?></textarea>
                </div>

                <div id="profile-alert" style="display: none; padding: var(--space-3); border-radius: var(--radius-md); font-size: 13px;"></div>

                <button type="submit" class="btn btn-primary" id="save-profile-btn" style="align-self: flex-start;">Save Profile Changes</button>
              </div>
            </form>
          </section>

          <!-- Change Password Form -->
          <section class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
            <div style="margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-3);">
              <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text); margin: 0; text-transform: uppercase;">Security & Password</h3>
              <p style="font-size: 13px; color: var(--color-text-muted); margin: 2px 0 0 0;">Update your account password regularly for security</p>
            </div>

            <form id="password-form" onsubmit="submitPasswordForm(event)">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                <div>
                  <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Current Password *</label>
                  <input type="password" name="current_password" class="form-input" required placeholder="••••••••" style="width: 100%;">
                </div>

                <div>
                  <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">New Password (min 6 characters) *</label>
                  <input type="password" name="new_password" class="form-input" required minlength="6" placeholder="••••••••" style="width: 100%;">
                </div>

                <div>
                  <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Confirm New Password *</label>
                  <input type="password" name="confirm_password" class="form-input" required minlength="6" placeholder="••••••••" style="width: 100%;">
                </div>

                <div id="password-alert" style="display: none; padding: var(--space-3); border-radius: var(--radius-md); font-size: 13px;"></div>

                <button type="submit" class="btn btn-secondary" id="save-password-btn" style="align-self: flex-start;">Update Password</button>
              </div>
            </form>
          </section>

        </div>

      </main>
    </div>
  </div>

  <script>
    async function submitProfileForm(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('save-profile-btn');
      const alertBox = document.getElementById('profile-alert');
      
      btn.disabled = true;
      btn.innerText = 'Saving...';
      alertBox.style.display = 'none';

      const formData = new FormData(form);
      try {
        const res = await fetch('/api.php?action=update_profile', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();
        if (json.success) {
          alertBox.className = 'badge badge-success';
          alertBox.style.display = 'block';
          alertBox.innerText = 'Profile updated successfully!';
          btn.disabled = false;
          btn.innerText = 'Save Profile Changes';
          setTimeout(() => { window.location.reload(); }, 900);
        } else {
          alertBox.className = 'badge badge-warning';
          alertBox.style.display = 'block';
          alertBox.innerText = json.message || 'Error updating profile.';
          btn.disabled = false;
          btn.innerText = 'Save Profile Changes';
        }
      } catch (err) {
        alertBox.className = 'badge badge-warning';
        alertBox.style.display = 'block';
        alertBox.innerText = 'Network error. Please try again.';
        btn.disabled = false;
        btn.innerText = 'Save Profile Changes';
      }
    }

    async function submitPasswordForm(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('save-password-btn');
      const alertBox = document.getElementById('password-alert');
      
      const newPass = form.querySelector('[name="new_password"]').value;
      const confirmPass = form.querySelector('[name="confirm_password"]').value;

      if (newPass !== confirmPass) {
        alertBox.className = 'badge badge-warning';
        alertBox.style.display = 'block';
        alertBox.innerText = 'New passwords do not match.';
        return;
      }

      btn.disabled = true;
      btn.innerText = 'Updating...';
      alertBox.style.display = 'none';

      const formData = new FormData(form);
      try {
        const res = await fetch('/api.php?action=change_password', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();
        if (json.success) {
          alertBox.className = 'badge badge-success';
          alertBox.style.display = 'block';
          alertBox.innerText = 'Password updated successfully!';
          form.reset();
          btn.disabled = false;
          btn.innerText = 'Update Password';
        } else {
          alertBox.className = 'badge badge-warning';
          alertBox.style.display = 'block';
          alertBox.innerText = json.message || 'Error updating password.';
          btn.disabled = false;
          btn.innerText = 'Update Password';
        }
      } catch (err) {
        alertBox.className = 'badge badge-warning';
        alertBox.style.display = 'block';
        alertBox.innerText = 'Network error. Please try again.';
        btn.disabled = false;
        btn.innerText = 'Update Password';
      }
    }
  </script>

  <script src="/assets/js/dashboard.js"></script>
</body>
</html>
