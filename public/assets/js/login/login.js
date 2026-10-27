// Sign-in page: client-side validation, password reveal, Caps Lock hint and submit state
(function () {
    'use strict';

    const form = document.getElementById('loginForm');
    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const capsWarning = document.getElementById('capsWarning');
    const submit = document.getElementById('signInButton');

    // Bootstrap-style validation; show a busy button only when the form will actually submit
    form.addEventListener('submit', function (event) {
        if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            submit.classList.add('is-loading');
            submit.setAttribute('aria-busy', 'true');
            submit.querySelector('.ox-submit-label').textContent = 'Signing in…';
        }
        form.classList.add('was-validated');
    }, false);

    // Restore the button if the page is shown again from the back/forward cache
    window.addEventListener('pageshow', function () {
        submit.classList.remove('is-loading');
        submit.removeAttribute('aria-busy');
        submit.querySelector('.ox-submit-label').textContent = 'Sign in';
    });

    togglePassword.addEventListener('click', function () {
        const show = password.getAttribute('type') === 'password';
        password.setAttribute('type', show ? 'text' : 'password');
        togglePassword.setAttribute('aria-pressed', String(show));
        togglePassword.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        password.focus();
    });

    const updateCaps = function (event) {
        if (typeof event.getModifierState === 'function') {
            capsWarning.hidden = !event.getModifierState('CapsLock');
        }
    };
    password.addEventListener('keydown', updateCaps);
    password.addEventListener('keyup', updateCaps);
    password.addEventListener('blur', function () {
        capsWarning.hidden = true;
    });
})();
