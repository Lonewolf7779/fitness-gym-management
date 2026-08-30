/**
 * IRONCORE Admin & Member Interactivity Script
 * Handles Mobile Sidebar, Search Shortcuts, SVG Charts, & Client-side Member Filtering/Modals
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Sidebar Drawer Toggle
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

  // 2. Keyboard Shortcut (Ctrl + K or Cmd + K) for Quick Search Focus
  const searchInput = document.getElementById('admin-search-input') || document.getElementById('member-search-input');
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      searchInput?.focus();
    }
    if (e.key === 'Escape') {
      closeSidebar();
      closeAllDropdowns();
      closeAllModals();
    }
  });

  // 3. Header Dropdown Menus (Notifications & User Profile)
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
    if (!isShown) notifDropdown?.classList.add('show');
  });

  profileBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    const isShown = profileDropdown?.classList.contains('show');
    closeAllDropdowns();
    if (!isShown) profileDropdown?.classList.add('show');
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown-menu-wrapper')) {
      closeAllDropdowns();
    }
  });

  // 4. Interactive SVG Revenue & Attendance Chart Visualization
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

  // Toast Notification Helper
  const showToast = (message) => {
    const toast = document.getElementById('dashboard-toast');
    const toastMsg = document.getElementById('toast-message');
    if (!toast || !toastMsg) return;

    toastMsg.textContent = message;
    toast.style.display = 'flex';
    setTimeout(() => {
      toast.style.display = 'none';
    }, 3500);
  };

  // Helper validation
  const validateEmailFormat = (email) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  };

  // =========================================================================
  // 5. MEMBER MANAGEMENT SEARCH, FILTERING, & MODALS
  // =========================================================================
  const memberSearchInput = document.getElementById('member-search-input');
  const statusFilterSelect = document.getElementById('status-filter');
  const planFilterSelect = document.getElementById('plan-filter');
  const tableBody = document.getElementById('members-table-body');
  const countIndicator = document.getElementById('member-count-indicator');
  const noMembersRow = document.getElementById('no-members-row');

  const filterMembersTable = () => {
    if (!tableBody) return;
    const rows = tableBody.querySelectorAll('tr:not(#no-members-row)');
    const query = (memberSearchInput?.value || '').toLowerCase().trim();
    const selectedStatus = (statusFilterSelect?.value || 'all').toLowerCase();
    const selectedPlan = (planFilterSelect?.value || 'all').toLowerCase();

    let visibleCount = 0;
    const totalCount = rows.length;

    rows.forEach(row => {
      const name = row.getAttribute('data-name') || '';
      const email = row.getAttribute('data-email') || '';
      const phone = row.getAttribute('data-phone') || '';
      const status = row.getAttribute('data-status') || '';
      const plan = row.getAttribute('data-plan') || '';

      const matchesQuery = !query || name.includes(query) || email.includes(query) || phone.includes(query);
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

  memberSearchInput?.addEventListener('input', filterMembersTable);
  statusFilterSelect?.addEventListener('change', filterMembersTable);
  planFilterSelect?.addEventListener('change', filterMembersTable);

  // Modals Elements
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

  // Check URL parameters for ?action=add
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('action') === 'add' && addModal) {
    addModal.classList.add('show');
  }

  closeAddBtn?.addEventListener('click', closeAllModals);
  cancelAddBtn?.addEventListener('click', closeAllModals);
  closeViewBtn?.addEventListener('click', closeAllModals);
  closeViewFooterBtn?.addEventListener('click', closeAllModals);
  closeEditBtn?.addEventListener('click', closeAllModals);
  cancelEditBtn?.addEventListener('click', closeAllModals);

  // Add Member Form Submission Handler
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
        addFormError.textContent = 'Please complete all required fields.';
        addFormError.style.display = 'block';
      }
      return;
    }

    if (!validateEmailFormat(email)) {
      if (addFormError) {
        addFormError.textContent = 'Please enter a valid email address format.';
        addFormError.style.display = 'block';
      }
      return;
    }

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
        <td><span id="row-plan-${newId}" style="font-weight: 700; color: var(--color-accent);">${plan.toUpperCase()}</span></td>
        <td><span style="font-size: 0.8rem; color: var(--color-text-muted);" id="row-joined-${newId}">${startDate}</span></td>
        <td><span style="font-size: 0.8rem; color: var(--color-text-muted);" id="row-expiry-${newId}">30 Days</span></td>
        <td>
          <span class="status-pill ${status}" id="row-status-pill-${newId}">
            <span class="status-dot-sm"></span> ${status.charAt(0).toUpperCase() + status.slice(1)}
          </span>
        </td>
        <td style="text-align: right;">
          <div class="table-actions" style="justify-content: flex-end;">
            <button type="button" class="btn-action-sm view-member-btn" data-id="${newId}" data-name="${fullName}" data-email="${email}" data-phone="${phone}" data-plan="${plan}" data-status="${status}" data-joined="${startDate}" data-expiry="30 Days">View</button>
            <button type="button" class="btn-action-sm edit-member-btn" data-id="${newId}" data-name="${fullName}" data-email="${email}" data-phone="${phone}" data-plan="${plan}" data-status="${status}" data-joined="${startDate}" data-expiry="30 Days">Edit</button>
          </div>
        </td>
      `;
      tableBody.insertBefore(tr, tableBody.firstChild);
      filterMembersTable();

      // Update KPI Statistics Counters
      const totalCountElem = document.getElementById('stat-total-count');
      if (totalCountElem) {
        totalCountElem.textContent = parseInt(totalCountElem.textContent || '248') + 1;
      }
      if (status === 'active') {
        const activeCountElem = document.getElementById('stat-active-count');
        if (activeCountElem) {
          activeCountElem.textContent = parseInt(activeCountElem.textContent || '213') + 1;
        }
      }
    }

    addMemberForm.reset();
    closeAllModals();
    showToast(`Member ${fullName} added successfully!`);
  });

  // Edit Member Form Submission Handler
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
        editFormError.textContent = 'Please fill out all required fields.';
        editFormError.style.display = 'block';
      }
      return;
    }

    if (!validateEmailFormat(email)) {
      if (editFormError) {
        editFormError.textContent = 'Please enter a valid email address.';
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

      const initials = fullName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

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
        pillElem.className = `status-pill ${status}`;
        pillElem.innerHTML = `<span class="status-dot-sm"></span> ${status.charAt(0).toUpperCase() + status.slice(1)}`;
      }

      // Update button data attributes
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

    filterMembersTable();
    closeAllModals();
    showToast(`Updated details for ${fullName}.`);
  });

  // View / Edit Modal Listener Delegation
  document.addEventListener('click', (e) => {
    const viewBtn = e.target.closest('.view-member-btn');
    const editBtn = e.target.closest('.edit-member-btn');
    
    if (viewBtn) {
      const name = viewBtn.getAttribute('data-name') || '';
      const email = viewBtn.getAttribute('data-email') || '';
      const phone = viewBtn.getAttribute('data-phone') || '';
      const plan = viewBtn.getAttribute('data-plan') || '';
      const status = viewBtn.getAttribute('data-status') || '';
      const joined = viewBtn.getAttribute('data-joined') || '';
      const expiry = viewBtn.getAttribute('data-expiry') || '';
      const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

      document.getElementById('view-avatar').textContent = initials;
      document.getElementById('view-name').textContent = name;
      document.getElementById('view-email').textContent = email;
      document.getElementById('view-phone').textContent = phone;
      document.getElementById('view-plan').textContent = plan;
      document.getElementById('view-joined').textContent = joined;
      document.getElementById('view-expiry').textContent = expiry;

      const pill = document.getElementById('view-status-pill');
      if (pill) {
        pill.className = `status-pill ${status}`;
        pill.innerHTML = `<span class="status-dot-sm"></span> ${status.charAt(0).toUpperCase() + status.slice(1)}`;
      }

      viewModal?.classList.add('show');
    }

    if (editBtn) {
      const id = editBtn.getAttribute('data-id') || '';
      const name = editBtn.getAttribute('data-name') || '';
      const email = editBtn.getAttribute('data-email') || '';
      const phone = editBtn.getAttribute('data-phone') || '';
      const plan = editBtn.getAttribute('data-plan') || '';
      const status = editBtn.getAttribute('data-status') || '';
      const joined = editBtn.getAttribute('data-joined') || '';
      const expiry = editBtn.getAttribute('data-expiry') || '';

      document.getElementById('edit-member-id').value = id;
      document.getElementById('edit-full-name').value = name;
      document.getElementById('edit-email').value = email;
      document.getElementById('edit-phone').value = phone;
      document.getElementById('edit-plan').value = plan;
      document.getElementById('edit-status').value = status.toLowerCase();
      if (joined) document.getElementById('edit-joined').value = joined;
      if (expiry) document.getElementById('edit-expiry').value = expiry;

      editModal?.classList.add('show');
    }
  });
});
