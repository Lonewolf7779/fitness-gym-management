/**
 * IRONCORE Admin Dashboard Interactivity Script
 * Handles Mobile Sidebar, Notification Panels, Search Shortcuts, & SVG Charts
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
  const searchInput = document.getElementById('admin-search-input');
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      searchInput?.focus();
    }
    if (e.key === 'Escape') {
      closeSidebar();
      closeAllDropdowns();
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
        <!-- Y-Axis Grid Lines -->
        <line x1="${padding}" y1="${padding}" x2="${width - padding}" y2="${padding}" stroke="#292929" stroke-dasharray="4" />
        <line x1="${padding}" y1="${padding + chartH / 2}" x2="${width - padding}" y2="${padding + chartH / 2}" stroke="#292929" stroke-dasharray="4" />
        <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" stroke="#292929" />

        <!-- Fill Gradient -->
        <path d="${areaD}" fill="url(#adminChartGlow)" />

        <!-- Smooth Path Line -->
        <path d="${pathD}" fill="none" stroke="${data.color}" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />

        <!-- Data Circles & Tooltip Labels -->
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

  // Chart Switcher Buttons Listener
  switchBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      switchBtns.forEach(b => b.classList.remove('active'));
      e.currentTarget.classList.add('active');
      const view = e.currentTarget.getAttribute('data-view');
      renderAdminSVGChart(view);
    });
  });

  // Initial Chart Render
  if (chartContainer) {
    renderAdminSVGChart('revenue');
  }
});
