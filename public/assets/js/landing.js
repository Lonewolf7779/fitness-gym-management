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

  // 3. Rotating Motivation Quotes
  const quoteDisplay = document.getElementById('quote-display');
  const quoteAuthor = document.getElementById('quote-author');
  const quoteProgress = document.getElementById('quote-progress-bar');

  const quotes = [
    { text: 'Discipline is built one decision at a time.', author: 'IRONCORE' },
    { text: 'You do not need a perfect day. You need another honest rep.', author: 'IRONCORE' },
    { text: 'Strength grows where consistency refuses to leave.', author: 'IRONCORE' },
    { text: 'Train with purpose. Recover with intention. Return stronger.', author: 'IRONCORE' },
    { text: 'The work nobody sees is the work that changes you.', author: 'IRONCORE' },
    { text: 'Progress is quiet. Keep showing up.', author: 'IRONCORE' }
  ];

  let quoteIndex = 0;
  const quoteInterval = 5200;

  const showNextQuote = () => {
    if (!quoteDisplay || quotes.length < 2) return;
    quoteDisplay.classList.add('is-changing');
    window.setTimeout(() => {
      quoteIndex = (quoteIndex + 1) % quotes.length;
      quoteDisplay.textContent = quotes[quoteIndex].text;
      if (quoteAuthor) quoteAuthor.textContent = quotes[quoteIndex].author;
      quoteDisplay.classList.remove('is-changing');
      if (quoteProgress) {
        quoteProgress.style.transition = 'none';
        quoteProgress.style.width = '0%';
        requestAnimationFrame(() => {
          quoteProgress.style.transition = `width ${quoteInterval}ms linear`;
          quoteProgress.style.width = '100%';
        });
      }
    }, 180);
  };

  if (quoteDisplay) {
    if (quoteProgress) {
      quoteProgress.style.transition = `width ${quoteInterval}ms linear`;
      requestAnimationFrame(() => { quoteProgress.style.width = '100%'; });
    }
    if (!prefersReducedMotion) {
      window.setInterval(showNextQuote, quoteInterval);
    }
  }
});
