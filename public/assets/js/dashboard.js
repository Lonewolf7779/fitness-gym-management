/**
 * IRONCORE Admin & Member Interactivity Script
 * Handles Mobile Sidebar, Global Search (Modules + Members), Shortcuts, SVG Charts, & Member Table Filtering/Modals
 */

document.addEventListener('DOMContentLoaded', () => {
  // =========================================================================
  // 1. Mobile Sidebar Drawer Toggle
  // =========================================================================
  const sidebar = document.querySelector('.sidebar');
  const sidebarOverlay = document.querySelector('.sidebar-overlay');
  const mobileToggle = document.querySelector('.admin-mobile-toggle');

  const openSidebar = () => {
    sidebar?.classList.add('mobile-open');
    sidebarOverlay?.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  const closeSidebar = () => {
    sidebar?.classList.remove('mobile-open');
    sidebarOverlay?.classList.remove('active');
    document.body.style.overflow = '';
  };

  mobileToggle?.addEventListener('click', openSidebar);
  sidebarOverlay?.addEventListener('click', closeSidebar);

  // =========================================================================
  // 2. Keyboard Shortcuts (Ctrl+K for search, Escape to close overlays)
  // =========================================================================
  const adminSearchInput = document.getElementById('admin-search-input');
  const memberSearchInput = document.getElementById('member-search-input');
  const primarySearchInput = adminSearchInput || memberSearchInput;

  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      primarySearchInput?.focus();
      if (adminSearchInput && adminSearchInput.value.trim() !== '') {
        renderAdminSearchResults(adminSearchInput.value.trim());
      }
    }
    if (e.key === 'Escape') {
      closeSidebar();
      closeAllDropdowns();
      closeAdminSearchResults();
      closeAllModals();
    }
  });

  // =========================================================================
  // 3. Header Dropdown Menus (Notifications & User Profile)
  // =========================================================================
  const notifBtn = document.getElementById('notif-btn');
  const notifDropdown = document.getElementById('notif-dropdown');
  const profileBtn = document.getElementById('profile-btn');
  const profileDropdown = document.getElementById('profile-dropdown');

  const closeAllDropdowns = () => {
    notifDropdown?.classList.remove('show');
    profileDropdown?.classList.remove('show');
  };

  notifBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    const isShown = notifDropdown?.classList.contains('show');
    closeAllDropdowns();
    closeAdminSearchResults();
    if (!isShown) notifDropdown?.classList.add('show');
  });

  profileBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    const isShown = profileDropdown?.classList.contains('show');
    closeAllDropdowns();
    closeAdminSearchResults();
    if (!isShown) profileDropdown?.classList.add('show');
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown-menu-wrapper')) {
      closeAllDropdowns();
    }
  });

  // =========================================================================
  // 4. Global Admin Dashboard Search (Modules & Member Entities)
  // =========================================================================
  let adminSearchResults = document.getElementById('admin-search-results');
  if (!adminSearchResults && adminSearchInput) {
    adminSearchResults = document.createElement('div');
    adminSearchResults.id = 'admin-search-results';
    adminSearchResults.className = 'header-search-results';
    adminSearchInput.parentElement?.appendChild(adminSearchResults);
  }

  // Category 1: Admin Navigation / Modules
  const adminNavModules = [
    {
      title: 'Members',
      subtitle: 'Member Management & athletes list',
      url: '/admin/members.php',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
      keywords: ['member', 'members', 'athlete', 'athletes', 'user', 'users', 'directory', 'membership management']
    },
    {
      title: 'Add Member',
      subtitle: 'Register new athlete & membership',
      url: '/admin/members.php?action=add',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
      keywords: ['add member', 'new member', 'create member', 'register member', 'enroll']
    },
    {
      title: 'Dashboard',
      subtitle: 'Operational control center & metrics',
      url: '/admin/index.php',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
      keywords: ['dashboard', 'home', 'overview', 'metrics', 'kpi', 'revenue', 'control center']
    },
    {
      title: 'Trainers',
      subtitle: 'Trainer Management & staff roster',
      url: '/admin/trainers.php',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>',
      keywords: ['trainer', 'trainers', 'coach', 'coaches', 'staff', 'instructor', 'trainer management']
    },
    {
      title: 'Memberships',
      subtitle: 'Membership Management & pricing plans',
      url: '/admin/memberships.php',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
      keywords: ['membership', 'memberships', 'plan', 'plans', 'pricing', 'subscription', 'subscriptions']
    },
    {
      title: 'Attendance',
      subtitle: 'Attendance tracking & check-in logs',
      url: '/admin/attendance.php',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>',
      keywords: ['attendance', 'attendance tracking', 'checkin', 'check-in', 'check in', 'logs', 'qr', 'scanner', 'presence']
    },
    {
      title: 'Payments',
      subtitle: 'Payment Management & billing invoices',
      url: '/admin/payments.php',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
      keywords: ['payment', 'payments', 'payment management', 'billing', 'invoice', 'invoices', 'receipt', 'upi', 'cash', 'transaction']
    },
    {
      title: 'Workouts',
      subtitle: 'Workout Management & routine library',
      url: '/admin/workouts.php',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10h16M4 14h16M2 6v12M22 6v12"/></svg>',
      keywords: ['workout', 'workouts', 'workout management', 'routine', 'routines', 'exercise', 'exercises', 'programs']
    },
    {
      title: 'Reports',
      subtitle: 'Reports & business analytics',
      url: '/admin/reports.php',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
      keywords: ['report', 'reports', 'reports & analytics', 'analytics', 'statistics', 'charts', 'retention']
    },
    {
      title: 'Settings',
      subtitle: 'System settings & gym configuration',
      url: '/admin/settings.php',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
      keywords: ['setting', 'settings', 'config', 'configuration', 'system settings', 'profile']
    }
  ];

  // Category 2: Unified Existing Temporary Member Entities
  const adminMemberEntities = [
    { id: 1, name: 'Alex Rivera', email: 'alex@gmail.com', phone: '+91 9876543210', avatar: 'AR', plan: 'Pro Plan', status: 'Active' },
    { id: 2, name: 'Daniel Carter', email: 'daniel@gmail.com', phone: '+91 9876543211', avatar: 'DC', plan: 'Starter Plan', status: 'Active' },
    { id: 3, name: 'Sophia Miller', email: 'sophia@gmail.com', phone: '+91 9876543212', avatar: 'SM', plan: 'Elite Plan', status: 'Active' },
    { id: 4, name: 'Ryan Brooks', email: 'ryan@gmail.com', phone: '+91 9876543213', avatar: 'RB', plan: 'Pro Plan', status: 'Expired' },
    { id: 5, name: 'Elena Rostova', email: 'elena@ironcore.com', phone: '+91 9876543214', avatar: 'ER', plan: 'Elite Plan', status: 'Active' },
    { id: 6, name: 'Sarah Connor', email: 'sarah@gmail.com', phone: '+91 9876543215', avatar: 'SC', plan: 'Starter Plan', status: 'Inactive' },
    { id: 7, name: 'David Black', email: 'david@gmail.com', phone: '+91 9876543216', avatar: 'DB', plan: 'Starter Plan', status: 'Expired' },
    { id: 8, name: 'Michael Chang', email: 'michael@gmail.com', phone: '+91 9876543222', avatar: 'MC', plan: 'Pro Plan', status: 'Active' },
    { id: 9, name: 'Viktor Vance', email: 'viktor@gmail.com', phone: '+91 9876543217', avatar: 'VV', plan: 'Pro Plan', status: 'Expired' },
    { id: 10, name: 'Anita Sharma', email: 'anita@gmail.com', phone: '+91 9876543218', avatar: 'AS', plan: 'Elite Plan', status: 'Expired' },
    { id: 11, name: 'Rahul Kapoor', email: 'kapoor@gmail.com', phone: '+91 9876543219', avatar: 'RK', plan: 'Starter Plan', status: 'Active' },
    { id: 12, name: 'Priya Singh', email: 'priya@gmail.com', phone: '+91 9876543220', avatar: 'PS', plan: 'Pro Plan', status: 'Active' },
    { id: 13, name: 'Marcus Vance', email: 'marcus@ironcore.com', phone: '+91 9876543221', avatar: 'MV', plan: 'Elite Plan', status: 'Active' }
  ];

  const escapeHtml = (str) => {
    return (str || '').replace(/[&<>'"]/g, tag => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      "'": '&#39;',
      '"': '&quot;'
    }[tag] || tag));
  };

  const closeAdminSearchResults = () => {
    adminSearchResults?.classList.remove('show');
  };

  const renderAdminSearchResults = (query) => {
    if (!adminSearchResults) return;
    const cleanQuery = query.toLowerCase().trim();

    if (!cleanQuery) {
      closeAdminSearchResults();
      adminSearchResults.innerHTML = '';
      return;
    }

    // 1. Search Member Entities
    const cleanDigits = cleanQuery.replace(/[^0-9]/g, '');
    const matchedMembers = adminMemberEntities.filter(m => {
      const nameMatch = m.name.toLowerCase().includes(cleanQuery);
      const emailMatch = m.email.toLowerCase().includes(cleanQuery);
      const planMatch = m.plan.toLowerCase().includes(cleanQuery);
      const statusMatch = m.status.toLowerCase().includes(cleanQuery);
      const mCleanPhone = m.phone.replace(/[^0-9]/g, '');
      const phoneMatch = m.phone.includes(cleanQuery) || (cleanDigits.length >= 3 && mCleanPhone.includes(cleanDigits));
      return nameMatch || emailMatch || planMatch || statusMatch || phoneMatch;
    });

    // 2. Search Navigation Modules
    const matchedModules = adminNavModules.filter(item => {
      const titleMatch = item.title.toLowerCase().includes(cleanQuery);
      const subMatch = item.subtitle.toLowerCase().includes(cleanQuery);
      const keywordMatch = item.keywords.some(k => k.toLowerCase().includes(cleanQuery));
      return titleMatch || subMatch || keywordMatch;
    });

    const totalMatches = matchedMembers.length + matchedModules.length;

    if (totalMatches > 0) {
      let html = '';

      // Render Member Matches First if query matches member entities
      if (matchedMembers.length > 0) {
        html += `<div class="search-section-header">MEMBERS (${matchedMembers.length})</div>`;
        html += matchedMembers.map(m => `
          <a href="/admin/members.php?search=${encodeURIComponent(m.name)}" class="search-result-item">
            <div class="search-result-avatar">${escapeHtml(m.avatar)}</div>
            <div class="search-result-info">
              <div class="search-result-title">
                <span>${escapeHtml(m.name)}</span>
                <span class="search-result-tag member-tag">MEMBER</span>
              </div>
              <div class="search-result-desc">
                ${escapeHtml(m.email)} · ${escapeHtml(m.plan)} · <span class="status-pill-mini ${m.status.toLowerCase()}">${escapeHtml(m.status)}</span>
              </div>
            </div>
          </a>
        `).join('');
      }

      // Render Navigation Module Matches
      if (matchedModules.length > 0) {
        html += `<div class="search-section-header">MODULES (${matchedModules.length})</div>`;
        html += matchedModules.map(mod => `
          <a href="${mod.url}" class="search-result-item">
            <div class="search-result-icon">${mod.icon}</div>
            <div class="search-result-info">
              <div class="search-result-title">
                <span>${escapeHtml(mod.title)}</span>
                <span class="search-result-tag module-tag">MODULE</span>
              </div>
              <div class="search-result-desc">${escapeHtml(mod.subtitle)}</div>
            </div>
          </a>
        `).join('');
      }

      adminSearchResults.innerHTML = html;
      adminSearchResults.classList.add('show');
    } else {
      adminSearchResults.innerHTML = `
        <div class="search-no-results">
          No results found for "<strong>${escapeHtml(query)}</strong>".
        </div>
      `;
      adminSearchResults.classList.add('show');
    }
  };

  adminSearchInput?.addEventListener('input', (e) => {
    renderAdminSearchResults(e.target.value);
  });

  adminSearchInput?.addEventListener('focus', (e) => {
    if (e.target.value.trim() !== '') {
      renderAdminSearchResults(e.target.value);
    }
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.header-search')) {
      closeAdminSearchResults();
    }
  });

  // =========================================================================
  // 5. SVG Revenue & Attendance Chart (Dashboard Home)
  // =========================================================================
  const chartContainer = document.getElementById('admin-chart-svg');
  const switchBtns = document.querySelectorAll('.switch-btn');

  const adminChartData = {
    revenue: {
      title: 'Monthly Revenue Stream (₹ In Lakhs)',
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
      values: [1.8, 2.1, 2.3, 2.2, 2.5, 2.65, 2.72, 2.84],
      max: 3.5,
      color: '#E8FF00',
      prefix: '₹',
      suffix: 'L'
    },
    attendance: {
      title: 'Daily Member Check-ins (Last 7 Days)',
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      values: [320, 345, 387, 360, 410, 440, 290],
      max: 500,
      color: '#30D158',
      prefix: '',
      suffix: ''
    }
  };

  const renderAdminSVGChart = (viewType = 'revenue') => {
    if (!chartContainer) return;
    const data = adminChartData[viewType];
    const width = 850;
    const height = 240;
    const padding = 45;
    const chartW = width - padding * 2;
    const chartH = height - padding * 2;

    const step = chartW / (data.values.length - 1);
    const points = data.values.map((val, i) => {
      const x = padding + i * step;
      const y = height - padding - (val / data.max) * chartH;
      return { x, y, val, label: data.labels[i] };
    });

    let pathD = `M ${points[0].x} ${points[0].y}`;
    for (let i = 1; i < points.length; i++) {
      pathD += ` L ${points[i].x} ${points[i].y}`;
    }

    const areaD = `${pathD} L ${points[points.length - 1].x} ${height - padding} L ${points[0].x} ${height - padding} Z`;

    chartContainer.innerHTML = `
      <svg viewBox="0 0 ${width} ${height}" style="width: 100%; height: 100%; overflow: visible;">
        <defs>
          <linearGradient id="adminChartGlow" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="${data.color}" stop-opacity="0.25"/>
            <stop offset="100%" stop-color="${data.color}" stop-opacity="0.0"/>
          </linearGradient>
        </defs>
        <line x1="${padding}" y1="${padding}" x2="${width - padding}" y2="${padding}" stroke="#292929" stroke-dasharray="4" />
        <line x1="${padding}" y1="${padding + chartH / 2}" x2="${width - padding}" y2="${padding + chartH / 2}" stroke="#292929" stroke-dasharray="4" />
        <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" stroke="#292929" />

        <path d="${areaD}" fill="url(#adminChartGlow)" />
        <path d="${pathD}" fill="none" stroke="${data.color}" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />

        ${points.map(p => `
          <g class="chart-point-group">
            <circle cx="${p.x}" cy="${p.y}" r="5" fill="${data.color}" stroke="#0B0B0B" stroke-width="2.5" />
            <text x="${p.x}" y="${height - 14}" fill="#9A9A9A" font-size="12" font-weight="600" text-anchor="middle">${p.label}</text>
            <text x="${p.x}" y="${p.y - 12}" fill="#FFFFFF" font-size="11" font-weight="800" text-anchor="middle">${data.prefix}${p.val}${data.suffix}</text>
          </g>
        `).join('')}
      </svg>
    `;
  };

  switchBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      switchBtns.forEach(b => b.classList.remove('active'));
      e.currentTarget.classList.add('active');
      const view = e.currentTarget.getAttribute('data-view');
      renderAdminSVGChart(view);
    });
  });

  if (chartContainer) {
    renderAdminSVGChart('revenue');
  }

  // =========================================================================
  // 6. Toast Notification Helper
  // =========================================================================
  let toastTimer = null;
  const showToast = (message) => {
    const toast = document.getElementById('dashboard-toast');
    const toastMsg = document.getElementById('toast-message');
    if (!toast || !toastMsg) return;

    if (toastTimer) clearTimeout(toastTimer);

    toastMsg.textContent = message;
    toast.style.display = 'flex';
    toastTimer = setTimeout(() => {
      toast.style.display = 'none';
    }, 3500);
  };

  // Helper validation
  const validateEmailFormat = (email) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  };

  // =========================================================================
  // 7. MEMBER MANAGEMENT: SEARCH, FILTERING, MODALS & LIVE CRUD
  // =========================================================================
  const statusFilterSelect = document.getElementById('status-filter');
  const planFilterSelect = document.getElementById('plan-filter');
  const tableBody = document.getElementById('members-table-body');
  const countIndicator = document.getElementById('member-count-indicator');
  const noMembersRow = document.getElementById('no-members-row');

  // KPI Counter Elements
  const totalCountElem = document.getElementById('stat-total-count');
  const activeCountElem = document.getElementById('stat-active-count');

  // Dynamic KPI Counter Updater
  const updateKpiCounts = () => {
    if (!tableBody) return;
    const rows = tableBody.querySelectorAll('tr:not(#no-members-row)');
    const total = rows.length;
    let active = 0;

    rows.forEach(row => {
      const status = (row.getAttribute('data-status') || '').toLowerCase();
      if (status === 'active') {
        active++;
      }
    });

    if (totalCountElem) totalCountElem.textContent = total;
    if (activeCountElem) activeCountElem.textContent = active;
  };

  // Live Multi-Criteria Search & Filter Evaluator for Member Management
  const filterMembersTable = () => {
    if (!tableBody) return;
    const rows = tableBody.querySelectorAll('tr:not(#no-members-row)');
    const query = (memberSearchInput?.value || '').toLowerCase().trim();
    const selectedStatus = (statusFilterSelect?.value || 'all').toLowerCase();
    const selectedPlan = (planFilterSelect?.value || 'all').toLowerCase();

    let visibleCount = 0;
    const totalCount = rows.length;

    rows.forEach(row => {
      const name = (row.getAttribute('data-name') || '').toLowerCase();
      const email = (row.getAttribute('data-email') || '').toLowerCase();
      const phone = (row.getAttribute('data-phone') || '').toLowerCase();
      const status = (row.getAttribute('data-status') || '').toLowerCase();
      const plan = (row.getAttribute('data-plan') || '').toLowerCase();

      // Flexible digit matching for phone numbers
      const cleanPhone = phone.replace(/[^0-9]/g, '');
      const cleanQuery = query.replace(/[^0-9]/g, '');
      const phoneMatches = phone.includes(query) || (cleanQuery.length >= 3 && cleanPhone.includes(cleanQuery));

      const matchesQuery = !query || name.includes(query) || email.includes(query) || phoneMatches;
      const matchesStatus = selectedStatus === 'all' || status === selectedStatus;
      const matchesPlan = selectedPlan === 'all' || plan.includes(selectedPlan);

      if (matchesQuery && matchesStatus && matchesPlan) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (noMembersRow) {
      noMembersRow.style.display = (visibleCount === 0) ? '' : 'none';
    }

    if (countIndicator) {
      countIndicator.textContent = `Showing ${visibleCount} of ${totalCount} members`;
    }
  };

  // Attach search & filter event listeners for Member Management page
  memberSearchInput?.addEventListener('input', filterMembersTable);
  statusFilterSelect?.addEventListener('change', filterMembersTable);
  planFilterSelect?.addEventListener('change', filterMembersTable);

  // Modals DOM Elements
  const addModal = document.getElementById('add-member-modal');
  const viewModal = document.getElementById('member-view-modal');
  const editModal = document.getElementById('member-edit-modal');
  
  const openAddBtn = document.getElementById('open-add-modal-btn');
  const closeAddBtn = document.getElementById('close-add-modal-btn');
  const cancelAddBtn = document.getElementById('cancel-add-modal-btn');
  const addMemberForm = document.getElementById('add-member-form');
  const addFormError = document.getElementById('add-form-error');

  const closeViewBtn = document.getElementById('close-view-modal-btn');
  const closeViewFooterBtn = document.getElementById('close-view-footer-btn');

  const closeEditBtn = document.getElementById('close-edit-modal-btn');
  const cancelEditBtn = document.getElementById('cancel-edit-modal-btn');
  const editMemberForm = document.getElementById('edit-member-form');
  const editFormError = document.getElementById('edit-form-error');

  const closeAllModals = () => {
    addModal?.classList.remove('show');
    viewModal?.classList.remove('show');
    editModal?.classList.remove('show');
    if (addFormError) addFormError.style.display = 'none';
    if (editFormError) editFormError.style.display = 'none';
  };

  openAddBtn?.addEventListener('click', () => {
    addModal?.classList.add('show');
  });

  // Check URL parameters for ?search=... or ?action=add
  const urlParams = new URLSearchParams(window.location.search);
  const urlSearch = urlParams.get('search');
  if (urlSearch && memberSearchInput) {
    memberSearchInput.value = urlSearch;
  }
  if (urlParams.get('action') === 'add' && addModal) {
    addModal.classList.add('show');
  }

  // Modal Close & Cancel Click Handlers
  closeAddBtn?.addEventListener('click', closeAllModals);
  cancelAddBtn?.addEventListener('click', closeAllModals);
  closeViewBtn?.addEventListener('click', closeAllModals);
  closeViewFooterBtn?.addEventListener('click', closeAllModals);
  closeEditBtn?.addEventListener('click', closeAllModals);
  cancelEditBtn?.addEventListener('click', closeAllModals);

  // Close modals when clicking directly on overlay backdrop
  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
      closeAllModals();
    }
  });

  // =========================================================================
  // 8. Add Member Form Submission Handler
  // =========================================================================
  addMemberForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const firstName = document.getElementById('new-first-name')?.value.trim();
    const lastName = document.getElementById('new-last-name')?.value.trim();
    const email = document.getElementById('new-email')?.value.trim();
    const phone = document.getElementById('new-phone')?.value.trim();
    const plan = document.getElementById('new-plan')?.value || 'Pro Plan';
    const status = document.getElementById('new-status')?.value || 'active';
    const startDate = document.getElementById('new-start-date')?.value || new Date().toISOString().split('T')[0];

    // Validation
    if (!firstName || !lastName || !email || !phone) {
      if (addFormError) {
        addFormError.textContent = 'Please complete all required fields (Name, Email, Phone, Plan).';
        addFormError.style.display = 'block';
      }
      return;
    }

    if (!validateEmailFormat(email)) {
      if (addFormError) {
        addFormError.textContent = 'Please enter a valid email address format (e.g. member@example.com).';
        addFormError.style.display = 'block';
      }
      return;
    }

    // Calculate 30-day membership expiry date
    const startObj = new Date(startDate);
    const expiryObj = new Date(startObj);
    expiryObj.setDate(expiryObj.getDate() + 30);
    const expiryDateStr = expiryObj.toISOString().split('T')[0];

    const fullName = `${firstName} ${lastName}`;
    const initials = `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
    const newId = Date.now();

    if (tableBody) {
      const tr = document.createElement('tr');
      tr.id = `member-row-${newId}`;
      tr.setAttribute('data-id', newId);
      tr.setAttribute('data-name', fullName.toLowerCase());
      tr.setAttribute('data-email', email.toLowerCase());
      tr.setAttribute('data-phone', phone.toLowerCase());
      tr.setAttribute('data-status', status.toLowerCase());
      tr.setAttribute('data-plan', plan.toLowerCase());

      const statusPillClass = (status === 'expired' || status === 'inactive' || status === 'suspended') ? status : 'active';
      const planColor = plan.toLowerCase().includes('pro') ? 'var(--color-accent)' : (plan.toLowerCase().includes('elite') ? '#FFF' : 'var(--color-text-muted)');

      tr.innerHTML = `
        <td>
          <div class="member-cell">
            <div class="member-avatar" id="row-avatar-${newId}">${initials}</div>
            <div>
              <div class="member-info-name" id="row-name-${newId}">${fullName}</div>
              <div class="member-info-email" id="row-email-${newId}">${email}</div>
            </div>
          </div>
        </td>
        <td><span style="font-family: monospace; font-size: 0.825rem; color: var(--color-text-muted);" id="row-phone-${newId}">${phone}</span></td>
        <td><span id="row-plan-${newId}" style="font-weight: 700; color: ${planColor};">${plan.toUpperCase()}</span></td>
        <td><span style="font-size: 0.8rem; color: var(--color-text-muted);" id="row-joined-${newId}">${startDate}</span></td>
        <td><span style="font-size: 0.8rem; color: var(--color-text-muted);" id="row-expiry-${newId}">${expiryDateStr}</span></td>
        <td>
          <span class="status-pill ${statusPillClass}" id="row-status-pill-${newId}">
            <span class="status-dot-sm"></span> ${status.charAt(0).toUpperCase() + status.slice(1)}
          </span>
        </td>
        <td style="text-align: right;">
          <div class="table-actions" style="justify-content: flex-end;">
            <button type="button" class="btn-action-sm view-member-btn" data-id="${newId}" data-name="${fullName}" data-email="${email}" data-phone="${phone}" data-plan="${plan}" data-status="${status}" data-joined="${startDate}" data-expiry="${expiryDateStr}">View</button>
            <button type="button" class="btn-action-sm edit-member-btn" data-id="${newId}" data-name="${fullName}" data-email="${email}" data-phone="${phone}" data-plan="${plan}" data-status="${status}" data-joined="${startDate}" data-expiry="${expiryDateStr}">Edit</button>
          </div>
        </td>
      `;

      tableBody.insertBefore(tr, tableBody.firstChild);
      updateKpiCounts();
      filterMembersTable();
    }

    addMemberForm.reset();
    closeAllModals();
    showToast(`Member ${fullName} added successfully!`);
  });

  // =========================================================================
  // 9. Edit Member Form Submission Handler
  // =========================================================================
  editMemberForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const memberId = document.getElementById('edit-member-id')?.value;
    const fullName = document.getElementById('edit-full-name')?.value.trim();
    const email = document.getElementById('edit-email')?.value.trim();
    const phone = document.getElementById('edit-phone')?.value.trim();
    const plan = document.getElementById('edit-plan')?.value;
    const status = document.getElementById('edit-status')?.value;
    const joined = document.getElementById('edit-joined')?.value;
    const expiry = document.getElementById('edit-expiry')?.value;

    if (!fullName || !email || !phone) {
      if (editFormError) {
        editFormError.textContent = 'Please fill out all required fields (Name, Email, Phone).';
        editFormError.style.display = 'block';
      }
      return;
    }

    if (!validateEmailFormat(email)) {
      if (editFormError) {
        editFormError.textContent = 'Please enter a valid email address format.';
        editFormError.style.display = 'block';
      }
      return;
    }

    // Update target DOM row
    const targetRow = document.getElementById(`member-row-${memberId}`);
    if (targetRow) {
      targetRow.setAttribute('data-name', fullName.toLowerCase());
      targetRow.setAttribute('data-email', email.toLowerCase());
      targetRow.setAttribute('data-phone', phone.toLowerCase());
      targetRow.setAttribute('data-status', status.toLowerCase());
      targetRow.setAttribute('data-plan', plan.toLowerCase());

      const nameParts = fullName.split(' ').filter(p => p.length > 0);
      let initials = 'MB';
      if (nameParts.length === 1) {
        initials = nameParts[0].substring(0, 2).toUpperCase();
      } else if (nameParts.length >= 2) {
        initials = `${nameParts[0][0]}${nameParts[nameParts.length - 1][0]}`.toUpperCase();
      }

      const avatarElem = document.getElementById(`row-avatar-${memberId}`);
      if (avatarElem) avatarElem.textContent = initials;

      const nameElem = document.getElementById(`row-name-${memberId}`);
      if (nameElem) nameElem.textContent = fullName;

      const emailElem = document.getElementById(`row-email-${memberId}`);
      if (emailElem) emailElem.textContent = email;

      const phoneElem = document.getElementById(`row-phone-${memberId}`);
      if (phoneElem) phoneElem.textContent = phone;

      const planElem = document.getElementById(`row-plan-${memberId}`);
      if (planElem) {
        planElem.textContent = plan.toUpperCase();
        planElem.style.color = plan.toLowerCase().includes('pro') ? 'var(--color-accent)' : (plan.toLowerCase().includes('elite') ? '#FFF' : 'var(--color-text-muted)');
      }

      const joinedElem = document.getElementById(`row-joined-${memberId}`);
      if (joinedElem && joined) joinedElem.textContent = joined;

      const expiryElem = document.getElementById(`row-expiry-${memberId}`);
      if (expiryElem && expiry) expiryElem.textContent = expiry;

      const pillElem = document.getElementById(`row-status-pill-${memberId}`);
      if (pillElem) {
        const statusClass = (status === 'expired' || status === 'inactive' || status === 'suspended') ? status : 'active';
        pillElem.className = `status-pill ${statusClass}`;
        pillElem.innerHTML = `<span class="status-dot-sm"></span> ${status.charAt(0).toUpperCase() + status.slice(1)}`;
      }

      // Update button data attributes for view & edit
      const viewBtn = targetRow.querySelector('.view-member-btn');
      const editBtn = targetRow.querySelector('.edit-member-btn');
      [viewBtn, editBtn].forEach(btn => {
        if (btn) {
          btn.setAttribute('data-name', fullName);
          btn.setAttribute('data-email', email);
          btn.setAttribute('data-phone', phone);
          btn.setAttribute('data-plan', plan);
          btn.setAttribute('data-status', status);
          if (joined) btn.setAttribute('data-joined', joined);
          if (expiry) btn.setAttribute('data-expiry', expiry);
        }
      });
    }

    updateKpiCounts();
    filterMembersTable();
    closeAllModals();
    showToast(`Updated details for ${fullName}.`);
  });

  // =========================================================================
  // 10. Delegated Click Listener for View & Edit Actions
  // =========================================================================
  document.addEventListener('click', (e) => {
    const viewBtn = e.target.closest('.view-member-btn');
    const editBtn = e.target.closest('.edit-member-btn');
    
    // View Member Action
    if (viewBtn) {
      const name = viewBtn.getAttribute('data-name') || '';
      const email = viewBtn.getAttribute('data-email') || '';
      const phone = viewBtn.getAttribute('data-phone') || '';
      const plan = viewBtn.getAttribute('data-plan') || '';
      const status = (viewBtn.getAttribute('data-status') || 'active').toLowerCase();
      const joined = viewBtn.getAttribute('data-joined') || '';
      const expiry = viewBtn.getAttribute('data-expiry') || '';

      const nameParts = name.split(' ').filter(p => p.length > 0);
      let initials = 'MB';
      if (nameParts.length === 1) {
        initials = nameParts[0].substring(0, 2).toUpperCase();
      } else if (nameParts.length >= 2) {
        initials = `${nameParts[0][0]}${nameParts[nameParts.length - 1][0]}`.toUpperCase();
      }

      const viewAvatar = document.getElementById('view-avatar');
      const viewName = document.getElementById('view-name');
      const viewEmail = document.getElementById('view-email');
      const viewPhone = document.getElementById('view-phone');
      const viewPlan = document.getElementById('view-plan');
      const viewJoined = document.getElementById('view-joined');
      const viewExpiry = document.getElementById('view-expiry');
      const viewPill = document.getElementById('view-status-pill');

      if (viewAvatar) viewAvatar.textContent = initials;
      if (viewName) viewName.textContent = name;
      if (viewEmail) viewEmail.textContent = email;
      if (viewPhone) viewPhone.textContent = phone;
      if (viewPlan) viewPlan.textContent = plan;
      if (viewJoined) viewJoined.textContent = joined;
      if (viewExpiry) viewExpiry.textContent = expiry;

      if (viewPill) {
        const statusClass = (status === 'expired' || status === 'inactive' || status === 'suspended') ? status : 'active';
        viewPill.className = `status-pill ${statusClass}`;
        viewPill.innerHTML = `<span class="status-dot-sm"></span> ${status.charAt(0).toUpperCase() + status.slice(1)}`;
      }

      viewModal?.classList.add('show');
    }

    // Edit Member Action
    if (editBtn) {
      const id = editBtn.getAttribute('data-id') || '';
      const name = editBtn.getAttribute('data-name') || '';
      const email = editBtn.getAttribute('data-email') || '';
      const phone = editBtn.getAttribute('data-phone') || '';
      const plan = editBtn.getAttribute('data-plan') || 'Pro Plan';
      const status = (editBtn.getAttribute('data-status') || 'active').toLowerCase();
      const joined = editBtn.getAttribute('data-joined') || '';
      const expiry = editBtn.getAttribute('data-expiry') || '';

      const editIdElem = document.getElementById('edit-member-id');
      const editNameElem = document.getElementById('edit-full-name');
      const editEmailElem = document.getElementById('edit-email');
      const editPhoneElem = document.getElementById('edit-phone');
      const editPlanElem = document.getElementById('edit-plan');
      const editStatusElem = document.getElementById('edit-status');
      const editJoinedElem = document.getElementById('edit-joined');
      const editExpiryElem = document.getElementById('edit-expiry');

      if (editIdElem) editIdElem.value = id;
      if (editNameElem) editNameElem.value = name;
      if (editEmailElem) editEmailElem.value = email;
      if (editPhoneElem) editPhoneElem.value = phone;
      if (editPlanElem) editPlanElem.value = plan;
      if (editStatusElem) editStatusElem.value = status;
      if (editJoinedElem) editJoinedElem.value = joined;
      if (editExpiryElem) editExpiryElem.value = expiry;

      editModal?.classList.add('show');
    }
  });

  // Initial Count Sync & Query filtering
  updateKpiCounts();
  filterMembersTable();
});
