/**
 * IRONCORE Client-side Auth Form Helper
 */

document.addEventListener('DOMContentLoaded', () => {
  const authForms = document.querySelectorAll('.auth-form');

  authForms.forEach(form => {
    form.addEventListener('submit', (e) => {
      const emailInput = form.querySelector('input[type="email"]');
      const passwordInput = form.querySelector('input[type="password"]');

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
