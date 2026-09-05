/**
 * IRONCORE Core Application Script
 * Handles Navigation, Sticky Header, Mobile Drawer, Accessibility & live dashboards.
 */

document.addEventListener('DOMContentLoaded', () => {
  const navbar = document.querySelector('.navbar');
  const mobileToggle = document.querySelector('.mobile-toggle');
  const navMenu = document.querySelector('.nav-menu');

  window.addEventListener('scroll', () => {
    if (window.scrollY > 20) navbar?.classList.add('scrolled');
    else navbar?.classList.remove('scrolled');
  });

  if (mobileToggle && navMenu) {
    mobileToggle.addEventListener('click', () => {
      const isOpen = navMenu.classList.toggle('is-active');
      mobileToggle.classList.toggle('is-active');
      mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      document.body.style.overflow = isOpen ? 'hidden' : '';
    });
    navMenu.querySelectorAll('.nav-link').forEach(link => link.addEventListener('click', () => {
      navMenu.classList.remove('is-active');
      mobileToggle.classList.remove('is-active');
      mobileToggle.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    }));
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && navMenu.classList.contains('is-active')) {
        navMenu.classList.remove('is-active');
        mobileToggle.classList.remove('is-active');
        mobileToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      }
    });
  }

  document.querySelectorAll('a[href^="#"]').forEach(anchor => anchor.addEventListener('click', function(e) {
    const targetId = this.getAttribute('href');
    if (targetId === '#') return;
    const targetEl = document.querySelector(targetId);
    if (targetEl) {
      e.preventDefault();
      const offsetPosition = targetEl.getBoundingClientRect().top + window.pageYOffset - 80;
      window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
    }
  }));

  loadLiveDashboard();
});

/**
 * Replace dashboard placeholder values with live MySQL values without
 * replacing the existing dashboard design or page-specific JavaScript.
 */
async function loadLiveDashboard() {
  const isDashboard = document.querySelector('.kpi-grid, .dashboard-shell');
  if (!isDashboard) return;
  try {
    const response = await fetch('/dashboard-data.php', { credentials: 'same-origin', cache: 'no-store', headers: { 'Accept': 'application/json' } });
    if (!response.ok) return;
    const data = await response.json();
    if (!data.success) return;
    if (data.role === 'admin') updateAdminDashboard(data);
    if (data.role === 'trainer') updateTrainerDashboard(data);
    if (data.role === 'member') updateMemberDashboard(data);
    document.documentElement.dataset.liveDashboard = 'true';
  } catch (error) {
    console.warn('IRONCORE live dashboard unavailable:', error);
  }
}

function setKpiByTitle(title, value) {
  document.querySelectorAll('.kpi-card').forEach(card => {
    const label = card.querySelector('.kpi-title');
    if (!label || label.textContent.trim().toLowerCase() !== title.toLowerCase()) return;
    const valueEl = card.querySelector('.kpi-value');
    if (valueEl) valueEl.textContent = value;
  });
}

function initials(name) {
  return String(name || '').trim().split(/\s+/).slice(0, 2).map(x => x[0] || '').join('').toUpperCase();
}

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
}

function formatDate(value) {
  if (!value) return '—';
  const d = new Date(`${value}T00:00:00`);
  return Number.isNaN(d.getTime()) ? value : d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

function updateAdminDashboard(data) {
  const s = data.stats || {};
  setKpiByTitle('Total Members', Number(s.total_members || 0).toLocaleString('en-IN'));
  setKpiByTitle('Active Members', Number(s.active_members || 0).toLocaleString('en-IN'));
  setKpiByTitle("Today's Attendance", Number(s.today_attendance || 0).toLocaleString('en-IN'));
  setKpiByTitle('Monthly Revenue', `₹${Number(s.monthly_revenue || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 })}`);

  const tables = document.querySelectorAll('.panel-card .data-table tbody');
  const recentBody = tables[0];
  if (recentBody) {
    const rows = (data.recent_members || []).map(m => `<tr><td><div class="member-cell"><div class="member-avatar">${escapeHtml(initials(m.full_name))}</div><div><div class="member-info-name">${escapeHtml(m.full_name)}</div><div class="member-info-email">${escapeHtml(m.email)}</div></div></div></td><td><span style="font-weight:700;color:var(--color-accent)">${escapeHtml(String(m.plan_title || 'NO PLAN').toUpperCase())}</span></td><td>${escapeHtml(formatDate(m.join_date))}</td><td><span class="status-pill ${m.status === 'active' ? 'active' : m.status === 'suspended' ? 'danger' : 'pending'}"><span class="status-dot-sm"></span>${escapeHtml(String(m.status || 'unknown').replace(/^./, x => x.toUpperCase()))}</span></td></tr>`).join('');
    recentBody.innerHTML = rows || '<tr><td colspan="4" style="text-align:center">No members found.</td></tr>';
  }

  const expiryList = document.querySelector('.expiry-list');
  if (expiryList) {
    expiryList.innerHTML = (data.expiry || []).map(item => {
      const days = Number(item.days_left || 0);
      const label = days === 0 ? 'Today' : days === 1 ? 'Tomorrow' : `In ${days} Days`;
      const cls = days <= 1 ? 'danger' : 'warning';
      return `<li class="expiry-item"><div><div class="expiry-user-title">${escapeHtml(item.full_name)}</div><div class="expiry-user-sub">${escapeHtml(item.plan_title)} • Expires ${escapeHtml(formatDate(item.end_date))}</div></div><div style="text-align:right"><span class="status-pill ${cls}" style="margin-bottom:.35rem;display:inline-block">${label}</span><div><a href="/admin/memberships.php" style="font-size:.75rem;font-weight:700;color:var(--color-accent)">RENEW</a></div></div></li>`;
    }).join('') || '<li class="expiry-item">No memberships expire in the next 7 days.</li>';
  }
}

function updateTrainerDashboard(data) {
  const s = data.stats || {};
  setKpiByTitle('Assigned Clients', Number(s.assigned_clients || 0).toLocaleString('en-IN'));
  setKpiByTitle('Active Programs', Number(s.active_programs || 0).toLocaleString('en-IN'));
  setKpiByTitle('Sessions', Number(s.sessions || 0).toLocaleString('en-IN'));
  setKpiByTitle('Needs Attention', Number(s.attention || 0).toLocaleString('en-IN'));
  const table = document.querySelector('.data-table tbody');
  if (table && data.clients) {
    table.innerHTML = data.clients.map(c => `<tr><td><div class="member-cell"><div class="member-avatar">${escapeHtml(initials(c.full_name))}</div><div><div class="member-info-name">${escapeHtml(c.full_name)}</div><div class="member-info-email">${escapeHtml(c.email)}</div></div></div></td><td>${escapeHtml(c.plan_title || 'No Plan')}</td><td>${escapeHtml(c.last_attendance ? formatDate(c.last_attendance) : 'Never')}</td><td><span class="status-pill ${c.last_attendance ? 'active' : 'warning'}"><span class="status-dot-sm"></span>${c.last_attendance ? 'Active' : 'Needs attention'}</span></td></tr>`).join('');
  }
}

function updateMemberDashboard(data) {
  const s = data.summary || {};
  setKpiByTitle('Current Plan', String(s.current_plan || 'NO PLAN'));
  setKpiByTitle('Days Remaining', String(s.days_remaining || 0));
  setKpiByTitle('Workout Streak', `${Number(s.workout_streak || 0)} days`);
  setKpiByTitle('Attendance Rate', `${Number(s.attendance_rate || 0)}%`);

  const progress = data.progress;
  document.querySelectorAll('[data-progress-value]').forEach(el => {
    const key = el.dataset.progressValue;
    if (progress && progress[key] !== undefined && progress[key] !== null) el.textContent = progress[key];
  });
  document.querySelectorAll('[data-workout-list]').forEach(list => {
    list.innerHTML = (data.workouts || []).map(w => `<div class="workout-live-item"><strong>${escapeHtml(w.title)}</strong><span>${escapeHtml(w.goal || 'Training program')}${w.trainer_name ? ` • ${escapeHtml(w.trainer_name)}` : ''}</span></div>`).join('') || '<div class="workout-live-item">No workout plans assigned yet.</div>';
  });
}
