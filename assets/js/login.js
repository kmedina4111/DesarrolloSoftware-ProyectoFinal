// Interacciones de los formularios de acceso (login y recuperar contraseña)
(() => {
    const card = document.querySelector('main > section');
    const form = document.querySelector('form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('toggle-password');
    const capsHint = document.getElementById('caps-hint');
    const errorBox = document.getElementById('form-error');
    const errorText = document.getElementById('form-error-text');
    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    const submitLabel = submitBtn ? submitBtn.querySelector('.btn-label') : null;
    const submitText = submitLabel ? submitLabel.textContent : '';

    // 1. Brillo en el borde de la tarjeta reactivo al cursor
    if (card && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        card.addEventListener('pointermove', (e) => {
            const rect = card.getBoundingClientRect();
            card.style.setProperty('--mx', `${e.clientX - rect.left}px`);
            card.style.setProperty('--my', `${e.clientY - rect.top}px`);
            card.classList.add('is-glowing');
        }, { passive: true });

        card.addEventListener('pointerleave', () => {
            card.classList.remove('is-glowing');
        });
    }

    // 2. Mostrar / ocultar contraseña (aria-label fijo; el estado lo anuncia aria-pressed)
    if (toggleBtn && passwordInput) {
        const icon = toggleBtn.querySelector('i');
        toggleBtn.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            toggleBtn.setAttribute('aria-pressed', String(isHidden));
            if (icon) {
                icon.classList.toggle('fi-rr-eye', !isHidden);
                icon.classList.toggle('fi-rr-eye-crossed', isHidden);
            }
        });
    }

    // 3. Indicador de Caps Lock activo
    if (passwordInput && capsHint) {
        const updateCaps = (e) => {
            if (typeof e.getModifierState === 'function') {
                capsHint.classList.toggle('is-visible', e.getModifierState('CapsLock'));
            }
        };
        passwordInput.addEventListener('keydown', updateCaps);
        passwordInput.addEventListener('keyup', updateCaps);
        passwordInput.addEventListener('blur', () => capsHint.classList.remove('is-visible'));
    }

    // 4. Validación: el error se muestra en el banner y se asocia al campo que falló
    const fields = [emailInput, passwordInput].filter(Boolean);

    const showError = (msg, input) => {
        if (!errorBox || !errorText) return;
        errorText.textContent = msg;
        errorBox.classList.add('is-visible');
        input.setAttribute('aria-invalid', 'true');
        input.setAttribute('aria-describedby', 'form-error');
        input.focus();
    };

    const clearError = () => {
        if (errorBox) errorBox.classList.remove('is-visible');
        fields.forEach((i) => {
            i.removeAttribute('aria-invalid');
            i.removeAttribute('aria-describedby');
        });
    };

    fields.forEach((i) => i.addEventListener('input', clearError));

    // 5. Estado de carga en el botón
    const resetSubmit = () => {
        if (!submitBtn) return;
        submitBtn.disabled = false;
        submitBtn.classList.remove('is-loading');
        if (submitLabel) submitLabel.textContent = submitText;
    };

    if (form && submitBtn) {
        form.addEventListener('submit', (e) => {
            clearError();

            if (emailInput && !emailInput.checkValidity()) {
                e.preventDefault();
                showError('Ingresa un correo electrónico válido.', emailInput);
                return;
            }

            // En el login solo se exige que no esté vacía; la política de longitud va al crearla
            if (passwordInput && passwordInput.value === '') {
                e.preventDefault();
                showError('Ingresa tu contraseña.', passwordInput);
                return;
            }

            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            if (submitLabel) submitLabel.textContent = 'Enviando...';
        });
    }

    // Al volver con "atrás" el navegador restaura la página desde caché con el botón bloqueado
    window.addEventListener('pageshow', resetSubmit);
})();
