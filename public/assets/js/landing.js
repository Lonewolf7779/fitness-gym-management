/**
 * IRONCORE Landing Page Interactive Features
 * Animations, Counters, Interactive SVG Chart & Pricing Toggles
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Scroll Reveal Observer
  const revealElements = document.querySelectorAll('.reveal');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!prefersReducedMotion && 'IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -50px 0px' });

    revealElements.forEach(el => revealObserver.observe(el));
  } else {
    revealElements.forEach(el => el.classList.add('is-visible'));
  }

  // 2. Animated Counter Tickers
  const counterElements = document.querySelectorAll('[data-counter]');
  let countersTriggered = false;

  const animateCounters = () => {
    counterElements.forEach(el => {
      const target = parseFloat(el.getAttribute('data-target'));
      const prefix = el.getAttribute('data-prefix') || '';
      const suffix = el.getAttribute('data-suffix') || '';
      const decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
      const duration = 2000;
      const startTime = performance.now();

      const updateCount = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // Ease-out expo function
        const easeOutProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        const currentVal = target * easeOutProgress;

        el.textContent = `${prefix}${currentVal.toFixed(decimals)}${suffix}`;

        if (progress < 1) {
          requestAnimationFrame(updateCount);
        } else {
          el.textContent = `${prefix}${target.toFixed(decimals)}${suffix}`;
        }
      };

      requestAnimationFrame(updateCount);
    });
  };

  if ('IntersectionObserver' in window && counterElements.length > 0) {
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !countersTriggered) {
          countersTriggered = true;
          animateCounters();
        }
      });
    }, { threshold: 0.3 });

    const statsSection = document.querySelector('.metrics-row') || document.querySelector('.hero-stats-row');
    if (statsSection) counterObserver.observe(statsSection);
  }

  // 3. Interactive SVG Chart Switcher (Attendance vs Revenue)
  const chartContainer = document.getElementById('preview-chart-svg');
  const chartTabs = document.querySelectorAll('.chart-tab');

  const chartData = {
    attendance: {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      values: [140, 165, 183, 172, 195, 210, 155],
      max: 250,
      color: '#E8FF00',
      title: 'Weekly Member Attendance'
    },
    revenue: {
      labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
      values: [62, 74, 81, 67], // in thousands (k)
      max: 100,
      color: '#30D158',
      title: 'Monthly Revenue Stream (₹ In Thousands)'
    }
  };

  const renderSVGChart = (type = 'attendance') => {
    if (!chartContainer) return;
    const data = chartData[type];
    const width = 800;
    const height = 240;
    const padding = 40;
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
          <linearGradient id="chartGlow" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="${data.color}" stop-opacity="0.35"/>
            <stop offset="100%" stop-color="${data.color}" stop-opacity="0.0"/>
          </linearGradient>
        </defs>
        <!-- Grid Lines -->
        <line x1="${padding}" y1="${padding}" x2="${width - padding}" y2="${padding}" stroke="#292929" stroke-dasharray="4" />
        <line x1="${padding}" y1="${padding + chartH / 2}" x2="${width - padding}" y2="${padding + chartH / 2}" stroke="#292929" stroke-dasharray="4" />
        <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" stroke="#292929" />

        <!-- Area Fill -->
        <path d="${areaD}" fill="url(#chartGlow)" />

        <!-- Line -->
        <path d="${pathD}" fill="none" stroke="${data.color}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

        <!-- Data Points & Labels -->
        ${points.map(p => `
          <circle cx="${p.x}" cy="${p.y}" r="5" fill="${data.color}" stroke="#0B0B0B" stroke-width="2" />
          <text x="${p.x}" y="${height - 12}" fill="#9A9A9A" font-size="12" font-weight="600" text-anchor="middle">${p.label}</text>
          <text x="${p.x}" y="${p.y - 12}" fill="#FFFFFF" font-size="11" font-weight="700" text-anchor="middle">${p.val}</text>
        `).join('')}
      </svg>
    `;
  };

  if (chartTabs.length > 0) {
    chartTabs.forEach(tab => {
      tab.addEventListener('click', (e) => {
        chartTabs.forEach(t => t.classList.remove('active'));
        e.currentTarget.classList.add('active');
        const view = e.currentTarget.getAttribute('data-view');
        renderSVGChart(view);
      });
    });
    // Initial Render
    renderSVGChart('attendance');
  }
});
