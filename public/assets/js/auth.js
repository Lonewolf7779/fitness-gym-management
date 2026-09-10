/**
 * IRONCORE Client-side Auth Form Helper & Password Toggle
 */

document.addEventListener('DOMContentLoaded', () => {
  // Password Visibility Toggle
  document.querySelectorAll('.pwd-toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const container = btn.closest('.form-group') || btn.parentElement;
      const input = container.querySelector('input[name="password"], input[type="password"], input[type="text"]');
      if (!input) return;

      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      
      // Update SVG Icon
      btn.innerHTML = isPassword 
        ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
        : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    });
  });

  // Client-side form validations
  const authForms = document.querySelectorAll('.auth-form');
  authForms.forEach(form => {
    form.addEventListener('submit', (e) => {
      const emailInput = form.querySelector('input[type="email"]');
      const passwordInput = form.querySelector('input[name="password"]');

      if (emailInput && !emailInput.value.trim()) {
        e.preventDefault();
        alert('Please enter a valid email address.');
        emailInput.focus();
        return;
      }

      if (passwordInput && passwordInput.value.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long.');
        passwordInput.focus();
        return;
      }
    });
  });
});
