// Profile: live "is this username free?" check while typing, re-checked on save by the server
(function () {
    const form = document.getElementById('detailsForm');
    if (!form) return;

    const input = document.getElementById('profileUsername');
    const status = document.getElementById('usernameStatus');
    const serverError = document.getElementById('username_error');
    const current = input.dataset.current.toLowerCase();
    let controller = null;
    let timer = null;
    let available = true;

    function show(state, text) {
        status.className = `form-text ox-username-status is-${state}`;
        status.innerHTML = text;
        const bad = state === 'taken' || state === 'invalid';
        // Native validity drives Bootstrap's styling once the form was submitted
        input.setCustomValidity(bad ? status.textContent.trim() : '');
        input.classList.toggle('is-invalid', bad);
        input.classList.toggle('is-valid', state === 'free');
    }

    function check() {
        const value = input.value.trim();
        serverError.textContent = '';
        controller?.abort();
        if (value.toLowerCase() === current) {
            available = true;
            show('same', 'This is your current username.');
            return;
        }
        if (!/^[A-Za-z0-9._-]{3,50}$/.test(value)) {
            available = false;
            show('invalid', '<i class="icon-alert-circle" aria-hidden="true"></i> Use 3–50 letters, numbers, dots, dashes or underscores.');
            return;
        }
        show('checking', 'Checking…');
        controller = new AbortController();
        fetch(`${window.baseUrl}profile/checkUsername?username=${encodeURIComponent(value)}`, {
            signal: controller.signal,
            headers: { Accept: 'application/json' },
        })
            .then(r => r.json())
            .then(result => {
                available = result.available;
                show(result.available ? 'free' : 'taken', result.available
                    ? '<i class="icon-check-circle" aria-hidden="true"></i> This username is available.'
                    : '<i class="icon-x-circle" aria-hidden="true"></i> This username is already taken. Please choose another one.');
            })
            .catch(error => {
                // Offline or failed: let the server decide on save
                if (error.name !== 'AbortError') {
                    available = true;
                    show('same', 'We will check this username when you save.');
                }
            });
    }

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(check, 300);
    });

    form.addEventListener('submit', event => {
        form.classList.add('was-validated');
        if (!form.checkValidity() || !available) {
            event.preventDefault();
            (form.querySelector(':invalid') || input).focus();
            if (!available) Ox.toast('Choose a username that is not taken yet.', { type: 'error' });
        }
    });

    // Unsaved-change warning when leaving the page
    const initial = new URLSearchParams(new FormData(form)).toString();
    let submitting = false;
    form.addEventListener('submit', () => { submitting = true; });
    window.addEventListener('beforeunload', event => {
        if (!submitting && new URLSearchParams(new FormData(form)).toString() !== initial) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    if (location.hash === '#details' && serverError.textContent) input.focus();
})();
