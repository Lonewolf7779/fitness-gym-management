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

  // Category 2: Unified Member Entities (Dynamically synced from live database)
  let adminMemberEntities = [];

  // Asynchronously sync live members from database
  fetch('/api.php?action=members')
    .then(r => r.json())
    .then(res => {
      if (res && res.success && Array.isArray(res.data) && res.data.length > 0) {
        adminMemberEntities = res.data.map(m => {
          const name = m.full_name || '';
          const parts = name.split(' ').filter(Boolean);
          const av = parts.map(p => p[0]).join('').substring(0, 2).toUpperCase() || 'MB';
          return {
            id: m.id,
            name: name,
            email: m.email || '',
            phone: m.phone || '',
            avatar: av,
            plan: m.plan_title || 'No Plan',
            status: m.status ? (m.status.charAt(0).toUpperCase() + m.status.slice(1)) : 'Active'
          };
        });
      }
    })
    .catch(() => {});

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
  // 5. GYM PERFORMANCE TRENDS (Operational Multi-Series SVG Telemetry)
  // =========================================================================
  const trendsStage = document.getElementById('admin-trends-stage');
  const legendChips = document.querySelectorAll('.legend-chip');
  const rangeBtns = document.querySelectorAll('.trends-range-selector .range-btn');

  let currentTrendsData = null;
  let currentRange = 12;
  const activeSeries = {
    revenue: true,
    checkins: true,
    members: true
  };

  const renderGymPerformanceTrends = (data) => {
    if (!trendsStage) return;

    if (!data || !data.has_any_data) {
      trendsStage.innerHTML = `
        <div class="trends-empty-state">
          <div class="trends-empty-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="20" x2="18" y2="10"/>
              <line x1="12" y1="20" x2="12" y2="4"/>
              <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
          </div>
          <h4>NO PERFORMANCE DATA YET</h4>
          <p>Revenue, attendance and membership trends will appear here as gym activity is recorded.</p>
        </div>
      `;
      return;
    }

    const width = 960;
    const height = 240;
    const padding = { top: 25, right: 35, bottom: 35, left: 45 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;

    const numPoints = (data.labels && data.labels.length) ? data.labels.length : 1;
    const step = numPoints > 1 ? chartW / (numPoints - 1) : 0;

    // Helper for smooth cubic Bezier paths
    const getSmoothPath = (pts) => {
      if (!pts || !pts.length) return '';
      if (pts.length === 1) return `M ${pts[0].x} ${pts[0].y}`;
      let d = `M ${pts[0].x} ${pts[0].y}`;
      for (let i = 0; i < pts.length - 1; i++) {
        const p0 = pts[i === 0 ? 0 : i - 1];
        const p1 = pts[i];
        const p2 = pts[i + 1];
        const p3 = pts[i + 2] || p2;
        const cp1x = p1.x + (p2.x - p0.x) / 6;
        const cp1y = p1.y + (p2.y - p0.y) / 6;
        const cp2x = p2.x - (p3.x - p1.x) / 6;
        const cp2y = p2.y - (p3.y - p1.y) / 6;
        d += ` C ${cp1x.toFixed(1)} ${cp1y.toFixed(1)}, ${cp2x.toFixed(1)} ${cp2y.toFixed(1)}, ${p2.x.toFixed(1)} ${p2.y.toFixed(1)}`;
      }
      return d;
    };

    // Calculate normalized point coordinates for each active series
    const seriesRenderData = {};
    const seriesKeys = ['revenue', 'checkins', 'members'];

    seriesKeys.forEach(key => {
      if (!activeSeries[key] || !data.series[key]) return;
      const s = data.series[key];
      const maxVal = s.max > 0 ? s.max : 1;
      const pts = s.values.map((v, i) => {
        const x = padding.left + i * step;
        const pct = s.max > 0 ? (Number(v) / maxVal) : 0;
        const y = padding.top + (1 - pct) * chartH;
        return { x, y, val: v };
      });
      const pathD = getSmoothPath(pts);
      const areaD = pts.length > 0 
        ? `${pathD} L ${pts[pts.length - 1].x} ${padding.top + chartH} L ${pts[0].x} ${padding.top + chartH} Z`
        : '';

      seriesRenderData[key] = { s, pts, pathD, areaD };
    });

    // Generate grid lines
    const gridYLevels = [0, 0.25, 0.5, 0.75, 1];

    let svgHtml = `
      <div class="trends-svg-container" style="position: relative; width: 100%; height: 100%;">
        <svg viewBox="0 0 ${width} ${height}" style="width: 100%; height: 100%; overflow: visible;" preserveAspectRatio="xMidYMid meet">
          <defs>
            <linearGradient id="glow-revenue" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#E8FF00" stop-opacity="0.16"/>
              <stop offset="100%" stop-color="#E8FF00" stop-opacity="0.0"/>
            </linearGradient>
            <linearGradient id="glow-checkins" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.16"/>
              <stop offset="100%" stop-color="#38BDF8" stop-opacity="0.0"/>
            </linearGradient>
            <linearGradient id="glow-members" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#34D399" stop-opacity="0.16"/>
              <stop offset="100%" stop-color="#34D399" stop-opacity="0.0"/>
            </linearGradient>
          </defs>

          <!-- Horizontal Reference Grid Lines -->
          ${gridYLevels.map((lvl, idx) => {
            const y = padding.top + lvl * chartH;
            const isBottom = idx === gridYLevels.length - 1;
            const strokeColor = isBottom ? 'rgba(255,255,255,0.12)' : 'rgba(255,255,255,0.04)';
            const dash = isBottom ? '' : 'stroke-dasharray="4 4"';
            const labelText = idx === 0 ? '100%' : (idx === 2 ? '50%' : (isBottom ? '0%' : ''));
            return `
              <line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" stroke="${strokeColor}" ${dash} />
              ${labelText ? `<text x="${padding.left - 10}" y="${y + 4}" fill="#6B7280" font-size="10" font-weight="700" text-anchor="end">${labelText}</text>` : ''}
            `;
          }).join('')}

          <!-- Dynamic Vertical Guideline for Hover -->
          <line id="trend-guide-line" x1="0" y1="${padding.top}" x2="0" y2="${padding.top + chartH}" stroke="rgba(255,255,255,0.25)" stroke-dasharray="3 3" style="display: none;" />

          <!-- Render Area Glows -->
          ${seriesKeys.map(key => {
            if (!seriesRenderData[key]) return '';
            return `<path d="${seriesRenderData[key].areaD}" fill="url(#glow-${key})" />`;
          }).join('')}

          <!-- Render Lines -->
          ${seriesKeys.map(key => {
            if (!seriesRenderData[key]) return '';
            const item = seriesRenderData[key];
            return `
              <path d="${item.pathD}" fill="none" stroke="${item.s.color}" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round" />
            `;
          }).join('')}

          <!-- Render Point Circles -->
          ${seriesKeys.map(key => {
            if (!seriesRenderData[key]) return '';
            const item = seriesRenderData[key];
            return item.pts.map((p, i) => `
              <circle class="trend-pt trend-pt-${key}" data-series="${key}" data-idx="${i}" cx="${p.x}" cy="${p.y}" r="3.5" fill="${item.s.color}" stroke="#121217" stroke-width="2" style="transition: r 0.15s ease;" />
            `).join('');
          }).join('')}

          <!-- X-Axis Month Labels -->
          ${data.labels.map((lbl, i) => {
            const x = padding.left + i * step;
            return `
              <text x="${x}" y="${height - 12}" fill="#9CA3AF" font-size="11" font-weight="700" text-anchor="middle">${lbl}</text>
            `;
          }).join('')}

          <!-- Invisible Hover Column Hitboxes -->
          ${data.labels.map((_, i) => {
            const x = padding.left + i * step;
            const colW = numPoints > 1 ? step : chartW;
            const startX = numPoints > 1 ? x - step / 2 : padding.left;
            return `
              <rect class="trend-hitbox" data-idx="${i}" data-x="${x}" x="${startX}" y="${padding.top}" width="${colW}" height="${chartH}" fill="transparent" style="cursor: crosshair;" />
            `;
          }).join('')}
        </svg>

        <!-- Floating Interactive Tooltip -->
        <div class="trends-tooltip" id="trends-tooltip" role="tooltip" aria-hidden="true"></div>
      </div>
    `;

    trendsStage.innerHTML = svgHtml;

    // Attach hover listeners for interactive tooltip and guide line
    const hitboxes = trendsStage.querySelectorAll('.trend-hitbox');
    const guideLine = trendsStage.querySelector('#trend-guide-line');
    const tooltip = trendsStage.querySelector('#trends-tooltip');
    const pts = trendsStage.querySelectorAll('.trend-pt');

    hitboxes.forEach(hb => {
      hb.addEventListener('mouseenter', (e) => {
        const idx = Number(e.currentTarget.getAttribute('data-idx'));
        const guideX = Number(e.currentTarget.getAttribute('data-x'));

        if (guideLine) {
          guideLine.setAttribute('x1', guideX);
          guideLine.setAttribute('x2', guideX);
          guideLine.style.display = 'block';
        }

        pts.forEach(p => {
          const pIdx = Number(p.getAttribute('data-idx'));
          p.setAttribute('r', pIdx === idx ? '6' : '3.5');
        });

        if (tooltip) {
          const monthTitle = data.full_labels[idx] || data.labels[idx];
          let rowsHtml = `<div class="tooltip-title">${monthTitle}</div>`;

          if (activeSeries.revenue && data.series.revenue) {
            const rVal = Number(data.series.revenue.values[idx] || 0);
            const rFormatted = rVal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            rowsHtml += `
              <div class="tooltip-row">
                <span class="tooltip-label"><span class="legend-dot dot-revenue"></span> Revenue</span>
                <span class="tooltip-val val-revenue">₹${rFormatted}</span>
              </div>
            `;
          }

          if (activeSeries.checkins && data.series.checkins) {
            const cVal = Number(data.series.checkins.values[idx] || 0);
            rowsHtml += `
              <div class="tooltip-row">
                <span class="tooltip-label"><span class="legend-dot dot-checkins"></span> Check-ins</span>
                <span class="tooltip-val val-checkins">${cVal}</span>
              </div>
            `;
          }

          if (activeSeries.members && data.series.members) {
            const mVal = Number(data.series.members.values[idx] || 0);
            rowsHtml += `
              <div class="tooltip-row">
                <span class="tooltip-label"><span class="legend-dot dot-members"></span> Active Members</span>
                <span class="tooltip-val val-members">${mVal}</span>
              </div>
            `;
          }

          tooltip.innerHTML = rowsHtml;
          const pctLeft = (guideX / width) * 100;
          tooltip.style.left = `${pctLeft}%`;
          tooltip.style.top = `${padding.top + 32}px`;
          tooltip.classList.add('visible');
          tooltip.setAttribute('aria-hidden', 'false');
        }
      });
    });

    const svgContainer = trendsStage.querySelector('.trends-svg-container');
    svgContainer?.addEventListener('mouseleave', () => {
      if (guideLine) guideLine.style.display = 'none';
      if (tooltip) {
        tooltip.classList.remove('visible');
        tooltip.setAttribute('aria-hidden', 'true');
      }
      pts.forEach(p => p.setAttribute('r', '3.5'));
    });
  };

  const fetchTrendsData = async (range = 12) => {
    try {
      currentRange = range;
      const res = await fetch(`/api.php?action=chart_trends&range=${range}`).then(r => r.json());
      if (res && res.success && res.data) {
        currentTrendsData = res.data;
        renderGymPerformanceTrends(currentTrendsData);
      }
    } catch (e) {}
  };

  // Legend Toggles
  legendChips.forEach(chip => {
    chip.addEventListener('click', (e) => {
      const sKey = e.currentTarget.getAttribute('data-series');
      if (!sKey) return;

      activeSeries[sKey] = !activeSeries[sKey];

      // Keep at least one active
      if (!activeSeries.revenue && !activeSeries.checkins && !activeSeries.members) {
        activeSeries[sKey] = true;
        return;
      }

      e.currentTarget.classList.toggle('active', activeSeries[sKey]);
      e.currentTarget.classList.toggle('inactive', !activeSeries[sKey]);
      e.currentTarget.setAttribute('aria-pressed', activeSeries[sKey] ? 'true' : 'false');

      if (currentTrendsData) {
        renderGymPerformanceTrends(currentTrendsData);
      }
    });
  });

  // Range Selector Buttons (12M, 6M, 3M)
  rangeBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      const range = Number(e.currentTarget.getAttribute('data-range')) || 12;
      rangeBtns.forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-pressed', 'false');
      });
      e.currentTarget.classList.add('active');
      e.currentTarget.setAttribute('aria-pressed', 'true');
      fetchTrendsData(range);
    });
  });

  if (trendsStage) {
    fetchTrendsData(12);
  }

  // Backward compatibility for any remaining legacy chart container
  const chartContainer = document.getElementById('admin-chart-svg');
  if (chartContainer) {
    fetch('/api.php?action=chart_revenue')
      .then(r => r.json())
      .then(res => {
        if (res && res.success && res.data && chartContainer) {
          chartContainer.innerHTML = `<div style="padding:1rem;color:#888;">Updated to Gym Performance Trends.</div>`;
        }
      })
      .catch(() => {});
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

  const resetPasswordModal = document.getElementById('reset-password-modal');
  const closeResetBtn = document.getElementById('close-reset-modal-btn');
  const cancelResetBtn = document.getElementById('cancel-reset-modal-btn');
  const resetPasswordForm = document.getElementById('reset-password-form');
  const resetFormError = document.getElementById('reset-form-error');

  const closeAllModals = () => {
    addModal?.classList.remove('show');
    viewModal?.classList.remove('show');
    editModal?.classList.remove('show');
    resetPasswordModal?.classList.remove('show');
    if (addFormError) addFormError.style.display = 'none';
    if (editFormError) editFormError.style.display = 'none';
    if (resetFormError) resetFormError.style.display = 'none';
  };

  document.addEventListener('click', (e) => {
    if (e.target.closest('#open-add-modal-btn')) {
      e.preventDefault();
      addModal?.classList.add('show');
    }
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
  closeResetBtn?.addEventListener('click', closeAllModals);
  cancelResetBtn?.addEventListener('click', closeAllModals);

  // Close modals when clicking directly on overlay backdrop
  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
      closeAllModals();
    }
  });

  // =========================================================================
  // 8. Add Member Form Submission Handler (Live API Integration)
  // =========================================================================
  addMemberForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const firstName = document.getElementById('new-first-name')?.value.trim();
    const lastName = document.getElementById('new-last-name')?.value.trim();
    const email = document.getElementById('new-email')?.value.trim();
    const phone = document.getElementById('new-phone')?.value.trim();
    const planId = document.getElementById('new-plan')?.value || '';
    const status = document.getElementById('new-status')?.value || 'active';
    const startDate = document.getElementById('new-start-date')?.value || new Date().toISOString().split('T')[0];
    const password = document.getElementById('new-password')?.value || '';
    const csrfToken = addMemberForm.querySelector('input[name="csrf_token"]')?.value || '';

    // Validation
    if (!firstName || !lastName || !email || !phone) {
      if (addFormError) {
        addFormError.textContent = 'Please complete all required fields (Name, Email, Phone).';
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

    const fullName = `${firstName} ${lastName}`;
    const submitBtn = addMemberForm.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    try {
      const formData = new FormData();
      formData.append('csrf_token', csrfToken);
      formData.append('name', fullName);
      formData.append('email', email);
      formData.append('phone', phone);
      formData.append('plan_id', planId);
      formData.append('status', status);
      formData.append('start_date', startDate);
      formData.append('join_date', startDate);
      if (password) formData.append('password', password);

      const res = await fetch('/api.php?action=create_member', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (!data.success) {
        if (addFormError) {
          addFormError.textContent = data.message || 'Error adding member.';
          addFormError.style.display = 'block';
        }
        if (submitBtn) submitBtn.disabled = false;
        return;
      }

      showToast(`Member ${fullName} added successfully!`);
      closeAllModals();
      setTimeout(() => {
        window.location.reload();
      }, 500);
    } catch (err) {
      if (addFormError) {
        addFormError.textContent = 'Server connection error occurred.';
        addFormError.style.display = 'block';
      }
      if (submitBtn) submitBtn.disabled = false;
    }
  });

  // =========================================================================
  // 9. Edit Member Form Submission Handler (Live API Integration)
  // =========================================================================
  editMemberForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const memberId = document.getElementById('edit-member-id')?.value;
    const fullName = document.getElementById('edit-full-name')?.value.trim();
    const email = document.getElementById('edit-email')?.value.trim();
    const phone = document.getElementById('edit-phone')?.value.trim();
    const planId = document.getElementById('edit-plan')?.value || '';
    const status = document.getElementById('edit-status')?.value || 'active';
    const emergency = document.getElementById('edit-emergency')?.value.trim();
    const csrfToken = editMemberForm.querySelector('input[name="csrf_token"]')?.value || '';

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

    const submitBtn = editMemberForm.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    try {
      const formData = new FormData();
      formData.append('csrf_token', csrfToken);
      formData.append('id', memberId);
      formData.append('name', fullName);
      formData.append('email', email);
      formData.append('phone', phone);
      formData.append('plan_id', planId);
      formData.append('status', status);
      formData.append('emergency_contact', emergency);

      const res = await fetch('/api.php?action=update_member', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (!data.success) {
        if (editFormError) {
          editFormError.textContent = data.message || 'Error updating member.';
          editFormError.style.display = 'block';
        }
        if (submitBtn) submitBtn.disabled = false;
        return;
      }

      showToast(`Updated details for ${fullName}.`);
      closeAllModals();
      setTimeout(() => {
        window.location.reload();
      }, 500);
    } catch (err) {
      if (editFormError) {
        editFormError.textContent = 'Server connection error occurred.';
        editFormError.style.display = 'block';
      }
      if (submitBtn) submitBtn.disabled = false;
    }
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
      const emergency = viewBtn.getAttribute('data-emergency') || '—';

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
      const viewEmergency = document.getElementById('view-emergency');
      const viewPill = document.getElementById('view-status-pill');

      if (viewAvatar) viewAvatar.textContent = initials;
      if (viewName) viewName.textContent = name;
      if (viewEmail) viewEmail.textContent = email;
      if (viewPhone) viewPhone.textContent = phone;
      if (viewPlan) viewPlan.textContent = plan.toUpperCase();
      if (viewJoined) viewJoined.textContent = joined;
      if (viewExpiry) viewExpiry.textContent = expiry;
      if (viewEmergency) viewEmergency.textContent = emergency || '—';

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
      const plan = editBtn.getAttribute('data-plan') || '';
      const status = (editBtn.getAttribute('data-status') || 'active').toLowerCase();
      const emergency = editBtn.getAttribute('data-emergency') || '';

      const editIdElem = document.getElementById('edit-member-id');
      const editNameElem = document.getElementById('edit-full-name');
      const editEmailElem = document.getElementById('edit-email');
      const editPhoneElem = document.getElementById('edit-phone');
      const editPlanElem = document.getElementById('edit-plan');
      const editStatusElem = document.getElementById('edit-status');
      const editEmergencyElem = document.getElementById('edit-emergency');

      if (editIdElem) editIdElem.value = id;
      if (editNameElem) editNameElem.value = name;
      if (editEmailElem) editEmailElem.value = email;
      if (editPhoneElem) editPhoneElem.value = phone;
      if (editPlanElem) editPlanElem.value = plan;
      if (editStatusElem) editStatusElem.value = status;
      if (editEmergencyElem) editEmergencyElem.value = emergency;

      editModal?.classList.add('show');
    }

    // Reset Password Action
    const resetBtn = e.target.closest('.reset-pwd-btn');
    if (resetBtn) {
      const userId = resetBtn.getAttribute('data-user-id') || '';
      const name = resetBtn.getAttribute('data-name') || '';
      const email = resetBtn.getAttribute('data-email') || '';

      const resetUserIdElem = document.getElementById('reset-user-id');
      const resetUserNameElem = document.getElementById('reset-user-name');
      const resetUserEmailElem = document.getElementById('reset-user-email');
      const resetNewPwdElem = document.getElementById('reset-new-password');

      if (resetUserIdElem) resetUserIdElem.value = userId;
      if (resetUserNameElem) resetUserNameElem.textContent = name;
      if (resetUserEmailElem) resetUserEmailElem.textContent = email;
      if (resetNewPwdElem) resetNewPwdElem.value = '';

      resetPasswordModal?.classList.add('show');
    }

    // Delete Member Action
    const deleteBtn = e.target.closest('.delete-member-btn');
    if (deleteBtn) {
      const id = deleteBtn.getAttribute('data-id') || '';
      const name = deleteBtn.getAttribute('data-name') || '';
      if (!confirm(`Are you sure you want to permanently remove athlete "${name}"?`)) return;

      const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
      const formData = new FormData();
      formData.append('csrf_token', csrfToken);
      formData.append('id', id);

      fetch('/api.php?action=delete_member', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        alert(data.message || 'Operation complete.');
        if (data.success) window.location.reload();
      })
      .catch(err => alert('Failed to delete member: ' + err.message));
    }
  });

  // Reset Password Form Submit
  resetPasswordForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const userId = document.getElementById('reset-user-id')?.value;
    const newPassword = document.getElementById('reset-new-password')?.value;
    const csrfToken = resetPasswordForm.querySelector('input[name="csrf_token"]')?.value || '';

    if (!newPassword || newPassword.length < 6) {
      if (resetFormError) {
        resetFormError.textContent = 'Password must be at least 6 characters long.';
        resetFormError.style.display = 'block';
      }
      return;
    }

    try {
      const formData = new FormData();
      formData.append('csrf_token', csrfToken);
      formData.append('user_id', userId);
      formData.append('new_password', newPassword);

      const res = await fetch('/api.php?action=reset_password', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (!data.success) {
        if (resetFormError) {
          resetFormError.textContent = data.message || 'Error updating password.';
          resetFormError.style.display = 'block';
        }
        return;
      }

      showToast(`Password updated successfully.`);
      closeAllModals();
    } catch (err) {
      if (resetFormError) {
        resetFormError.textContent = 'Server connection error occurred.';
        resetFormError.style.display = 'block';
      }
    }
  });

  // Initial Count Sync & Query filtering
  updateKpiCounts();
  filterMembersTable();
});
