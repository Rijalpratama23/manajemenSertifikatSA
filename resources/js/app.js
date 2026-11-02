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

const periodPicker = document.querySelector('[data-period-picker]');

if (periodPicker instanceof HTMLFormElement) {
    const periodInput = periodPicker.querySelector('input[type="month"]');
    const openMonthPickerButton = periodPicker.querySelector('[data-open-month-picker]');

    if (periodInput instanceof HTMLInputElement) {
        periodInput.addEventListener('change', () => {
            if (periodInput.value) {
                periodPicker.requestSubmit();
            }
        });

        if (openMonthPickerButton instanceof HTMLButtonElement) {
            openMonthPickerButton.addEventListener('click', () => {
                if ('showPicker' in periodInput) {
                    periodInput.showPicker();
                    return;
                }

                periodInput.focus();
                periodInput.click();
            });
        }
    }
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    if (form instanceof HTMLFormElement) {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm ?? 'Lanjutkan tindakan ini?')) {
                event.preventDefault();
            }
        });
    }
});

const participantModal = document.querySelector('[data-participant-modal]');
const openParticipantModalButton = document.querySelector('[data-open-participant-modal]');

if (participantModal instanceof HTMLDialogElement) {
    if (openParticipantModalButton instanceof HTMLButtonElement) {
        openParticipantModalButton.addEventListener('click', () => {
            participantModal.showModal();
            participantModal.querySelector('input:not([type="hidden"])')?.focus();
        });
    }

    participantModal.querySelectorAll('[data-close-participant-modal]').forEach((button) => {
        button.addEventListener('click', () => participantModal.close());
    });

    participantModal.addEventListener('click', (event) => {
        if (event.target === participantModal) {
            participantModal.close();
        }
    });

    if (participantModal.hasAttribute('data-open-on-load')) {
        participantModal.showModal();
        participantModal.querySelector('input:not([type="hidden"])')?.focus();
    }
}
