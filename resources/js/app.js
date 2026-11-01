import './bootstrap';

const passwordToggle = document.querySelector('[data-password-toggle]');
const passwordInput = document.querySelector('#password');

if (
    passwordToggle instanceof HTMLButtonElement
    && passwordInput instanceof HTMLInputElement
) {
    passwordToggle.addEventListener('click', () => {
        const passwordIsVisible = passwordInput.type === 'text';

        passwordInput.type = passwordIsVisible ? 'password' : 'text';
        passwordToggle.setAttribute('aria-pressed', String(!passwordIsVisible));
        passwordToggle.setAttribute(
            'aria-label',
            passwordIsVisible ? 'Tampilkan password' : 'Sembunyikan password',
        );
    });
}

const forgotPasswordButton = document.querySelector('[data-forgot-password]');
const recoveryMessage = document.querySelector('[data-recovery-message]');

if (forgotPasswordButton instanceof HTMLButtonElement && recoveryMessage instanceof HTMLElement) {
    forgotPasswordButton.addEventListener('click', () => {
        recoveryMessage.hidden = !recoveryMessage.hidden;
    });
}
