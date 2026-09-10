<?php
/**
 * IRONCORE Member - Athlete Profile & Subscription Details
 * Live profile details, active membership term, and credentials management.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../services/GymManagementService.php';

AuthMiddleware::handle();

$userId = (int) ($_SESSION['user_id'] ?? 0);
$svc = new GymManagementService();
$user = $svc->getUserById($userId);
$member = $svc->getMemberByUserId($userId);
$memberData = $svc->memberDashboard($userId);
$sub = $memberData['subscription'];
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Athlete Profile & Membership | IRONCORE</title>
  
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
    $pageHeading    = 'ATHLETE PROFILE & MEMBERSHIP';
    $pageSubtitle   = 'Manage your personal athlete account, contact details, and subscription status';
    require_once __DIR__ . '/../layouts/member_nav.php';
    ?>

      <!-- MAIN CONTENT AREA -->
      <main class="dashboard-body">

        <!-- 1. ATHLETE IDENTITY & MEMBERSHIP STATUS -->
        <section class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl); margin-bottom: var(--space-6);">
          <div style="display: flex; align-items: center; justify-content: space-between; gap: var(--space-5); flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: var(--space-4);">
              <div class="avatar-circle" style="width: 72px; height: 72px; font-size: 28px; font-weight: 800; background: rgba(229, 9, 20, 0.15); color: var(--color-primary); border: 2px solid rgba(229, 9, 20, 0.4);">
                <?= strtoupper(substr($user['full_name'] ?? 'Athlete', 0, 1)) ?>
              </div>
              <div>
                <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap;">
                  <h2 style="font-size: 22px; font-weight: 800; color: var(--color-text); margin: 0;"><?= htmlspecialchars($user['full_name'] ?? 'Athlete') ?></h2>
                  <span class="badge badge-primary">ATHLETE</span>
                  <span class="badge <?= ($sub && $sub['status'] === 'active') ? 'badge-success' : 'badge-warning' ?>">
                    <?= $sub ? strtoupper($sub['status']) : 'NO MEMBERSHIP' ?>
                  </span>
                </div>
                <p style="font-size: 14px; color: var(--color-text-muted); margin: 4px 0 0 0;">
                  <?= htmlspecialchars($user['email'] ?? '') ?> • Joined <?= date('F Y', strtotime($member['join_date'] ?? 'now')) ?>
                </p>
              </div>
            </div>

            <!-- Active Membership Summary Card -->
            <?php if ($sub): ?>
            <div style="padding: var(--space-4); background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-lg); min-width: 260px;">
              <div style="font-size: 11px; text-transform: uppercase; color: var(--color-text-muted); letter-spacing: 0.5px;">Active Plan</div>
              <div style="font-size: 18px; font-weight: 800; color: var(--color-primary); margin-top: 2px;"><?= htmlspecialchars($sub['plan_title']) ?></div>
              <div style="font-size: 13px; color: var(--color-text-muted); margin-top: 4px;">
                Expires: <strong style="color: var(--color-text);"><?= htmlspecialchars(date('M d, Y', strtotime($sub['end_date']))) ?></strong> (<?= (int)$memberData['days_remaining'] ?> Days Left)
              </div>
            </div>
            <?php endif; ?>
          </div>
        </section>

        <!-- 2. DUAL GRID: EDIT PROFILE & UPDATE PASSWORD -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-6);">
          
          <!-- Edit Profile Form -->
          <section class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
            <div style="margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-3);">
              <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text); margin: 0; text-transform: uppercase;">Personal Information</h3>
              <p style="font-size: 13px; color: var(--color-text-muted); margin: 2px 0 0 0;">Update your emergency contact and profile details</p>
            </div>

            <form id="member-profile-form" onsubmit="submitMemberProfile(event)">
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

                <div>
                  <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Phone Number</label>
                  <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000" style="width: 100%;">
                </div>

                <div>
                  <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Emergency Contact</label>
                  <input type="text" name="emergency_contact" class="form-input" value="<?= htmlspecialchars($member['emergency_contact'] ?? '') ?>" placeholder="Name & Phone" style="width: 100%;">
                </div>

                <div>
                  <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; display: block;">Home Address</label>
                  <textarea name="address" class="form-input" rows="2" placeholder="Street Address, City..." style="width: 100%;"><?= htmlspecialchars($member['address'] ?? '') ?></textarea>
                </div>

                <div id="member-profile-alert" style="display: none; padding: var(--space-3); border-radius: var(--radius-md); font-size: 13px;"></div>

                <button type="submit" class="btn btn-primary" id="save-member-profile-btn" style="align-self: flex-start;">Save Profile</button>
              </div>
            </form>
          </section>

          <!-- Update Password Form -->
          <section class="modern-card" style="padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl);">
            <div style="margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-3);">
              <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text); margin: 0; text-transform: uppercase;">Security & Password</h3>
              <p style="font-size: 13px; color: var(--color-text-muted); margin: 2px 0 0 0;">Update your account password</p>
            </div>

            <form id="member-password-form" onsubmit="submitMemberPassword(event)">
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

                <div id="member-password-alert" style="display: none; padding: var(--space-3); border-radius: var(--radius-md); font-size: 13px;"></div>

                <button type="submit" class="btn btn-secondary" id="save-member-password-btn" style="align-self: flex-start;">Update Password</button>
              </div>
            </form>
          </section>

        </div>

      </main>
    </div>
  </div>

  <script>
    async function submitMemberProfile(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('save-member-profile-btn');
      const alertBox = document.getElementById('member-profile-alert');
      
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
          btn.innerText = 'Save Profile';
          setTimeout(() => { window.location.reload(); }, 800);
        } else {
          alertBox.className = 'badge badge-warning';
          alertBox.style.display = 'block';
          alertBox.innerText = json.message || 'Error updating profile.';
          btn.disabled = false;
          btn.innerText = 'Save Profile';
        }
      } catch (err) {
        alertBox.className = 'badge badge-warning';
        alertBox.style.display = 'block';
        alertBox.innerText = 'Network error. Please try again.';
        btn.disabled = false;
        btn.innerText = 'Save Profile';
      }
    }

    async function submitMemberPassword(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('save-member-password-btn');
      const alertBox = document.getElementById('member-password-alert');
      
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
