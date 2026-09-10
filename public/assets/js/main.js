/**
 * IRONCORE Landing & Common UI Interactivity
 */

document.addEventListener('DOMContentLoaded', () => {
  const navbar = document.querySelector('.navbar');
  const mobileToggle = document.querySelector('.mobile-toggle');
  const navMenu = document.querySelector('.nav-menu');
  const navBackdrop = document.getElementById('nav-backdrop') || document.querySelector('.nav-backdrop');

  // Sticky Glassmorphic Navbar on Scroll
  window.addEventListener('scroll', () => {
    if (window.scrollY > 20) {
      navbar?.classList.add('scrolled');
    } else {
      navbar?.classList.remove('scrolled');
    }
  });

  // Mobile Landing Menu Drawer Toggle
  if (mobileToggle && navMenu) {
    const closeMobileNav = () => {
      navMenu.classList.remove('is-active');
      mobileToggle.classList.remove('is-active');
      mobileToggle.setAttribute('aria-expanded', 'false');
      navBackdrop?.classList.remove('is-active');
      document.body.style.overflow = '';
    };

    const toggleMobileNav = () => {
      const isOpen = navMenu.classList.toggle('is-active');
      mobileToggle.classList.toggle('is-active');
      mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      if (navBackdrop) {
        if (isOpen) navBackdrop.classList.add('is-active');
        else navBackdrop.classList.remove('is-active');
      }
      document.body.style.overflow = isOpen ? 'hidden' : '';
    };

    mobileToggle.addEventListener('click', toggleMobileNav);
    navBackdrop?.addEventListener('click', closeMobileNav);

    navMenu.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', closeMobileNav);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && navMenu.classList.contains('is-active')) {
        closeMobileNav();
      }
    });
  }

  // Smooth Scroll for Hash Links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;
      const targetEl = document.querySelector(targetId);
      if (targetEl) {
        e.preventDefault();
        const headerOffset = 80;
        const elementPosition = targetEl.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });
      }
    });
  });
});
