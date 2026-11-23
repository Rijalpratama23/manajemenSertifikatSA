import './bootstrap';

document.querySelectorAll('[data-participant-nav-dropdown]').forEach((dropdown) => {
    if (!(dropdown instanceof HTMLElement)) {
        return;
    }

    const trigger = dropdown.querySelector('[data-participant-nav-trigger]');
    const menu = dropdown.querySelector('[data-participant-nav-menu]');

    if (!(trigger instanceof HTMLButtonElement) || !(menu instanceof HTMLElement)) {
        return;
    }

    let closeTimer;

    const closeMenu = (restoreFocus = false) => {
        window.clearTimeout(closeTimer);
        trigger.setAttribute('aria-expanded', 'false');
        menu.classList.remove('is-open');
        closeTimer = window.setTimeout(() => {
            menu.hidden = true;
        }, 160);

        if (restoreFocus) {
            trigger.focus();
        }
    };

    trigger.addEventListener('click', () => {
        const willOpen = trigger.getAttribute('aria-expanded') !== 'true';
        trigger.setAttribute('aria-expanded', String(willOpen));

        if (willOpen) {
            window.clearTimeout(closeTimer);
            menu.hidden = false;
            requestAnimationFrame(() => menu.classList.add('is-open'));
        } else {
            closeMenu();
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target instanceof Node && !dropdown.contains(event.target)) {
            closeMenu();
        }
    });

    dropdown.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu(true);
        }

        if (event.key === 'ArrowDown' && trigger.getAttribute('aria-expanded') !== 'true') {
            event.preventDefault();
            trigger.click();
        }
    });
});

document.querySelectorAll('[data-app-toast]').forEach((toast) => {
    if (!(toast instanceof HTMLElement)) {
        return;
    }

    let isDismissed = false;
    let dismissTimer;
    const dismissToast = () => {
        if (isDismissed) {
            return;
        }

        isDismissed = true;
        window.clearTimeout(dismissTimer);
        toast.classList.add('is-dismissed');
        window.setTimeout(() => toast.remove(), 250);
    };
    dismissTimer = window.setTimeout(dismissToast, 3000);

    const dismissButton = toast.querySelector('[data-dismiss-toast]');
    dismissButton?.addEventListener('click', () => {
        dismissToast();
    });
});

const alertDismissTimers = new WeakMap();
const alertRemovalTimers = new WeakMap();

function scheduleAlertDismissal(alert) {
    if (!(alert instanceof HTMLElement) || alert.hidden || alertDismissTimers.has(alert)) {
        return;
    }

    const dismissTimer = window.setTimeout(() => {
        alertDismissTimers.delete(alert);
        alert.classList.add('is-dismissing');

        const removalTimer = window.setTimeout(() => {
            alert.remove();
            alertRemovalTimers.delete(alert);
        }, 250);

        alertRemovalTimers.set(alert, removalTimer);
    }, 3000);

    alertDismissTimers.set(alert, dismissTimer);
}

document.querySelectorAll('.admin-alert').forEach(scheduleAlertDismissal);

new MutationObserver((mutations) => {
    mutations.forEach(({ target }) => {
        if (!(target instanceof HTMLElement) || !target.matches('.admin-alert')) {
            return;
        }

        if (target.hidden) {
            window.clearTimeout(alertDismissTimers.get(target));
            window.clearTimeout(alertRemovalTimers.get(target));
            alertDismissTimers.delete(target);
            alertRemovalTimers.delete(target);
            target.classList.remove('is-dismissing');
            return;
        }

        scheduleAlertDismissal(target);
    });
}).observe(document.body, {
    attributes: true,
    attributeFilter: ['hidden'],
    subtree: true,
});

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
    const periodLabel = periodPicker.querySelector('.dashboard-period');

    if (periodInput instanceof HTMLInputElement) {
        periodInput.addEventListener('change', () => {
            if (periodInput.value) {
                periodPicker.requestSubmit();
            }
        });

        if (periodLabel instanceof HTMLLabelElement) {
            periodLabel.addEventListener('click', (event) => {
                if (event.target === periodInput) {
                    return;
                }

                event.preventDefault();

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

const deleteConfirmation = document.querySelector('[data-delete-confirmation]');
let pendingDeleteForm = null;
let confirmedDeleteForm = null;
let confirmedTemplateSwitchForm = null;

function showDeleteConfirmation(form) {
    if (!(deleteConfirmation instanceof HTMLDialogElement)) {
        if (window.confirm(form.dataset.confirm ?? 'Yakin ingin melanjutkan?')) {
            form.submit();
        }

        return;
    }

    pendingDeleteForm = form;
    const confirmationTitle = deleteConfirmation.querySelector('#delete-confirmation-title');
    const confirmationMessage = deleteConfirmation.querySelector('#delete-confirmation-message');
    const confirmationStep = deleteConfirmation.querySelector('[data-confirmation-step]');
    const confirmationIcon = deleteConfirmation.querySelector('[data-confirmation-icon]');
    const confirmButton = deleteConfirmation.querySelector('[data-confirm-delete]');
    const isTemplateAlert = form.dataset.confirmStyle === 'template';

    deleteConfirmation.classList.toggle('is-template-alert', isTemplateAlert);
    deleteConfirmation.classList.toggle('is-warning', form.dataset.confirmTone === 'warning');
    deleteConfirmation.classList.toggle('is-danger', form.dataset.confirmTone === 'danger');
    deleteConfirmation.classList.toggle('is-switch', form.dataset.confirmTone === 'switch');

    if (confirmationTitle instanceof HTMLHeadingElement) {
        confirmationTitle.textContent = form.dataset.confirmTitle ?? 'Yakin Ingin Menghapus?';
    }

    if (confirmationMessage instanceof HTMLParagraphElement) {
        confirmationMessage.textContent = form.dataset.confirmMessage ?? 'Data akan dihapus secara permanen.';
    }

    if (confirmationStep instanceof HTMLElement) {
        confirmationStep.textContent = form.dataset.confirmStep ?? '';
        confirmationStep.hidden = !isTemplateAlert;
    }

    if (confirmationIcon instanceof HTMLElement) {
        confirmationIcon.dataset.icon = form.dataset.confirmIcon ?? 'warning';
    }

    if (confirmButton instanceof HTMLButtonElement) {
        confirmButton.textContent = form.dataset.confirmLabel ?? 'Ya';
    }

    const cancelButton = deleteConfirmation.querySelector('.delete-confirmation-no');
    if (cancelButton instanceof HTMLButtonElement) {
        cancelButton.textContent = isTemplateAlert ? 'Batal' : 'Tidak';
    }

    deleteConfirmation.showModal();
    confirmButton?.focus();
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    form.addEventListener('submit', (event) => {
        if (form === confirmedDeleteForm) {
            confirmedDeleteForm = null;
            return;
        }

        event.preventDefault();
        showDeleteConfirmation(form);
    });
});

if (deleteConfirmation instanceof HTMLDialogElement) {
    const cancelDeleteButtons = deleteConfirmation.querySelectorAll('[data-cancel-delete]');
    const confirmDeleteButton = deleteConfirmation.querySelector('[data-confirm-delete]');

    cancelDeleteButtons.forEach((button) => {
        button.addEventListener('click', () => {
            pendingDeleteForm = null;
            deleteConfirmation.close();
        });
    });

    confirmDeleteButton?.addEventListener('click', () => {
        if (!pendingDeleteForm) {
            deleteConfirmation.close();
            return;
        }

        confirmedDeleteForm = pendingDeleteForm.matches('form[data-confirm]') ? pendingDeleteForm : null;
        if (pendingDeleteForm.dataset.confirmSwitch === 'true') {
            confirmedTemplateSwitchForm = pendingDeleteForm;
        }
        pendingDeleteForm = null;
        deleteConfirmation.close();
        confirmedDeleteForm.requestSubmit();
    });

    deleteConfirmation.addEventListener('close', () => {
        deleteConfirmation.classList.remove('is-template-alert', 'is-warning', 'is-danger', 'is-switch');
    });

    deleteConfirmation.addEventListener('click', (event) => {
        if (event.target === deleteConfirmation) {
            pendingDeleteForm = null;
            deleteConfirmation.close();
        }
    });

    deleteConfirmation.addEventListener('cancel', () => {
        pendingDeleteForm = null;
    });
}

const participantModal = document.querySelector('[data-participant-modal]');
const openParticipantModalButton = document.querySelector('[data-open-participant-modal]');
const editParticipantModal = document.querySelector('[data-edit-participant-modal]');

if (participantModal instanceof HTMLDialogElement) {
    if (openParticipantModalButton instanceof HTMLButtonElement) {
        openParticipantModalButton.addEventListener('click', () => {
            participantModal.showModal();
            participantModal.querySelector('input:not([type="hidden"])')?.focus();
        });
    }

    if (participantModal.hasAttribute('data-open-on-load')) {
        participantModal.showModal();
        participantModal.querySelector('input:not([type="hidden"])')?.focus();
    }
}

if (editParticipantModal instanceof HTMLDialogElement) {
    const editForm = editParticipantModal.querySelector('form');

    document.querySelectorAll('[data-open-edit-participant]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement) || !(editForm instanceof HTMLFormElement)) {
            return;
        }

        button.addEventListener('click', () => {
            const nameInput = editForm.querySelector('[name="nama"]');
            const emailInput = editForm.querySelector('[name="email"]');
            const institutionInput = editForm.querySelector('[name="asal_instansi"]');
            const editUrl = button.dataset.editUrl;

            if (
                !(nameInput instanceof HTMLInputElement)
                || !(emailInput instanceof HTMLInputElement)
                || !(institutionInput instanceof HTMLInputElement)
                || !editUrl
            ) {
                return;
            }

            nameInput.value = button.dataset.participantName ?? '';
            emailInput.value = button.dataset.participantEmail ?? '';
            institutionInput.value = button.dataset.participantInstitution ?? '';
            editForm.action = editUrl;
            editParticipantModal.showModal();
            nameInput.focus();
        });
    });

    if (editParticipantModal.hasAttribute('data-open-on-load')) {
        editParticipantModal.showModal();
        editParticipantModal.querySelector('input:not([type="hidden"])')?.focus();
    }
}

const certificateTypeModal = document.querySelector('[data-certificate-type-modal]');
const certificateTypeFilter = document.querySelector('.certificate-type-filter');

if (certificateTypeFilter instanceof HTMLFormElement) {
    const filterTrigger = certificateTypeFilter.querySelector('[data-certificate-type-filter-trigger]');
    const filterMenu = certificateTypeFilter.querySelector('[data-certificate-type-filter-menu]');

    if (filterTrigger instanceof HTMLButtonElement && filterMenu instanceof HTMLElement) {
        const closeFilterMenu = () => {
            filterMenu.hidden = true;
            filterTrigger.setAttribute('aria-expanded', 'false');
        };

        filterTrigger.addEventListener('click', () => {
            const isExpanded = filterTrigger.getAttribute('aria-expanded') === 'true';
            filterMenu.hidden = isExpanded;
            filterTrigger.setAttribute('aria-expanded', String(!isExpanded));
        });

        filterMenu.addEventListener('click', (event) => {
            if (event.target instanceof HTMLButtonElement) {
                closeFilterMenu();
            }
        });

        document.addEventListener('click', (event) => {
            if (event.target instanceof Node && !certificateTypeFilter.contains(event.target)) {
                closeFilterMenu();
            }
        });

        certificateTypeFilter.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeFilterMenu();
                filterTrigger.focus();
            }
        });
    }
}

if (certificateTypeModal instanceof HTMLDialogElement) {
    const certificateTypeForm = certificateTypeModal.querySelector('form');
    const codeInput = certificateTypeForm?.querySelector('[name="kode"]');
    const nameInput = certificateTypeForm?.querySelector('[name="nama_jenis"]');
    const activeStatusInput = certificateTypeForm?.querySelector('[name="aktif"]');
    const statusPicker = certificateTypeForm?.querySelector('[data-certificate-type-status-select]');
    const statusTrigger = statusPicker?.querySelector('[data-status-trigger]');
    const statusMenu = statusPicker?.querySelector('[data-status-menu]');
    const statusValue = statusPicker?.querySelector('[data-status-value]');
    const nameCharacterCount = certificateTypeForm?.querySelector('[data-name-character-count]');
    const codeHint = codeInput?.closest('.form-field')?.querySelector('.field-hint');
    const modalTitle = certificateTypeModal.querySelector('h2');
    const modalDescription = certificateTypeModal.querySelector('.participant-modal-header p');
    const submitButton = certificateTypeForm?.querySelector('button[type="submit"]');

    document.querySelector('[data-open-certificate-type-create]')?.addEventListener('click', () => {
        if (
            !(certificateTypeForm instanceof HTMLFormElement)
            || !(codeInput instanceof HTMLInputElement)
            || !(nameInput instanceof HTMLInputElement)
            || !(activeStatusInput instanceof HTMLInputElement)
            || !(statusTrigger instanceof HTMLButtonElement)
            || !(statusMenu instanceof HTMLElement)
            || !(statusValue instanceof HTMLElement)
            || !(modalTitle instanceof HTMLHeadingElement)
            || !(modalDescription instanceof HTMLParagraphElement)
            || !(submitButton instanceof HTMLButtonElement)
        ) {
            return;
        }

        certificateTypeModal.dataset.modalKind = 'create';
        certificateTypeForm.action = certificateTypeModal.dataset.storeUrl ?? '';
        certificateTypeForm.querySelector('input[name="_method"]')?.remove();
        codeInput.value = '';
        codeInput.readOnly = false;
        nameInput.value = '';
        activeStatusInput.value = '1';
        statusValue.textContent = 'Aktif';
        statusMenu.querySelectorAll('[data-status-option]').forEach((option) => {
            option.setAttribute('aria-selected', String(option instanceof HTMLButtonElement && option.dataset.statusOption === '1'));
        });
        if (nameCharacterCount instanceof HTMLElement) {
            nameCharacterCount.textContent = '0';
        }
        if (codeHint instanceof HTMLElement) {
            codeHint.textContent = '2 sampai 10 huruf atau angka. Kode dipakai pada nomor sertifikat dan tidak dapat diubah setelah jenis dipakai.';
        }
        modalTitle.textContent = 'Tambah Jenis Sertifikat';
        modalDescription.textContent = 'Isi informasi jenis sertifikat baru.';
        submitButton.textContent = 'Simpan';
        certificateTypeModal.showModal();
        codeInput.focus();
    });

    document.querySelectorAll('[data-open-certificate-type-edit]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.addEventListener('click', () => {
            if (
                !(certificateTypeForm instanceof HTMLFormElement)
                || !(codeInput instanceof HTMLInputElement)
                || !(nameInput instanceof HTMLInputElement)
                || !(activeStatusInput instanceof HTMLInputElement)
                || !(statusTrigger instanceof HTMLButtonElement)
                || !(statusMenu instanceof HTMLElement)
                || !(statusValue instanceof HTMLElement)
                || !(modalTitle instanceof HTMLHeadingElement)
                || !(modalDescription instanceof HTMLParagraphElement)
                || !(submitButton instanceof HTMLButtonElement)
            ) {
                return;
            }

            const editUrl = button.dataset.editUrl;
            if (!editUrl) {
                return;
            }

            certificateTypeModal.dataset.modalKind = 'edit';
            certificateTypeForm.action = editUrl;
            let methodInput = certificateTypeForm.querySelector('input[name="_method"]');
            if (!(methodInput instanceof HTMLInputElement)) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                certificateTypeForm.append(methodInput);
            }
            methodInput.value = 'PUT';
            codeInput.value = button.dataset.typeCode ?? '';
            const codeLocked = button.dataset.codeLocked === 'true';
            codeInput.readOnly = codeLocked;
            codeInput.dataset.codeLocked = String(codeLocked);
            if (codeHint instanceof HTMLElement) {
                codeHint.textContent = codeLocked
                    ? 'Kode tidak dapat diubah karena jenis sertifikat ini sudah digunakan.'
                    : '2 sampai 10 huruf atau angka. Kode dipakai pada nomor sertifikat dan tidak dapat diubah setelah jenis dipakai.';
            }
            nameInput.value = button.dataset.typeName ?? '';
            const activeValue = button.dataset.typeActive === '1' ? '1' : '0';
            activeStatusInput.value = activeValue;
            statusValue.textContent = activeValue === '1' ? 'Aktif' : 'Nonaktif';
            statusMenu.querySelectorAll('[data-status-option]').forEach((option) => {
                option.setAttribute('aria-selected', String(option instanceof HTMLButtonElement && option.dataset.statusOption === activeValue));
            });
            if (nameCharacterCount instanceof HTMLElement) {
                nameCharacterCount.textContent = String(nameInput.value.length);
            }
            modalTitle.textContent = 'Ubah Jenis Sertifikat';
            modalDescription.textContent = 'Perbarui informasi jenis sertifikat.';
            submitButton.textContent = 'Simpan Perubahan';
            certificateTypeModal.showModal();
            codeInput.focus();
        });
    });

    if (certificateTypeModal.hasAttribute('data-open-on-load')) {
        certificateTypeModal.showModal();
        if (nameInput instanceof HTMLInputElement && nameCharacterCount instanceof HTMLElement) {
            nameCharacterCount.textContent = String(nameInput.value.length);
        }
        if (codeInput instanceof HTMLInputElement && codeHint instanceof HTMLElement && codeInput.readOnly) {
            codeHint.textContent = 'Kode tidak dapat diubah karena jenis sertifikat ini sudah digunakan.';
        }
        certificateTypeModal.querySelector('input:not([type="hidden"]):not([type="radio"])')?.focus();
    }

    if (nameInput instanceof HTMLInputElement && nameCharacterCount instanceof HTMLElement) {
        const updateNameCharacterCount = () => {
            nameCharacterCount.textContent = String(nameInput.value.length);
        };
        nameInput.addEventListener('input', updateNameCharacterCount);
        updateNameCharacterCount();
    }

    if (
        activeStatusInput instanceof HTMLInputElement
        && statusPicker instanceof HTMLElement
        && statusTrigger instanceof HTMLButtonElement
        && statusMenu instanceof HTMLElement
        && statusValue instanceof HTMLElement
    ) {
        statusTrigger.addEventListener('click', () => {
            const isExpanded = statusTrigger.getAttribute('aria-expanded') === 'true';
            statusMenu.hidden = isExpanded;
            statusTrigger.setAttribute('aria-expanded', String(!isExpanded));
        });

        statusMenu.querySelectorAll('[data-status-option]').forEach((option) => {
            if (!(option instanceof HTMLButtonElement)) {
                return;
            }

            option.addEventListener('click', () => {
                const value = option.dataset.statusOption;
                if (value !== '1' && value !== '0') {
                    return;
                }

                activeStatusInput.value = value;
                statusValue.textContent = value === '1' ? 'Aktif' : 'Nonaktif';
                statusMenu.querySelectorAll('[data-status-option]').forEach((item) => {
                    item.setAttribute('aria-selected', String(item === option));
                });
                statusMenu.hidden = true;
                statusTrigger.setAttribute('aria-expanded', 'false');
                statusTrigger.focus();
            });
        });

        document.addEventListener('click', (event) => {
            if (event.target instanceof Node && !statusPicker.contains(event.target)) {
                statusMenu.hidden = true;
                statusTrigger.setAttribute('aria-expanded', 'false');
            }
        });

        statusPicker.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                statusMenu.hidden = true;
                statusTrigger.setAttribute('aria-expanded', 'false');
                statusTrigger.focus();
            }
        });
    }
}

const certificateTemplatePreview = document.querySelector('[data-template-preview]');
const certificateTemplateForm = document.querySelector('[data-certificate-template-form]');

document.querySelectorAll('[data-template-filter]').forEach((filter) => {
    if (filter instanceof HTMLSelectElement) {
        filter.addEventListener('change', () => {
            if (filter.form instanceof HTMLFormElement) {
                filter.form.requestSubmit();
            }
        });
    }
});

if (certificateTemplateForm instanceof HTMLFormElement) {
    const typeInput = certificateTemplateForm.querySelector('[data-template-type]');
    const nameInput = certificateTemplateForm.querySelector('[data-template-name]');
    const activeInput = certificateTemplateForm.querySelector('[data-template-active]');
    const fileInput = certificateTemplateForm.querySelector('[data-template-file-input]');
    const fileName = certificateTemplateForm.querySelector('[data-template-file-name]');
    const previewImage = certificateTemplateForm.querySelector('[data-template-preview-image]');
    const previewPlaceholder = certificateTemplateForm.querySelector('[data-template-preview-placeholder]');
    const previewButton = certificateTemplateForm.querySelector('[data-open-template-form-preview]');
    const clientError = certificateTemplateForm.querySelector('[data-template-client-error]');
    const nameCount = certificateTemplateForm.querySelector('[data-template-name-count]');
    const previewDialog = document.querySelector('[data-template-form-preview]');
    const previewCanvas = previewDialog?.querySelector('[data-template-preview-canvas]');
    let previewObjectUrl = null;
    let fileSelectionId = 0;
    const positionCanvas = certificateTemplateForm.querySelector('[data-template-position-canvas]');
    const positionBackground = certificateTemplateForm.querySelector('[data-template-position-background]');
    const resetPositionButton = certificateTemplateForm.querySelector('[data-reset-template-position]');

    const positionDefaults = {
        judul: { x: 50, y: 17, size: 30 },
        diberikan: { x: 50, y: 31, size: 13 },
        nama: { x: 50, y: 40, size: 25 },
        nomor: { x: 22, y: 86, size: 11 },
        deskripsi: { x: 50, y: 58, size: 15 },
        tanggal: { x: 50, y: 76, size: 13 },
        qr: { x: 82, y: 86, size: 12 },
    };

    const updatePositionItem = (key) => {
        const item = certificateTemplateForm.querySelector(`[data-position-item="${key}"]`);
        const xInput = certificateTemplateForm.querySelector(`[data-position-x="${key}"]`);
        const yInput = certificateTemplateForm.querySelector(`[data-position-y="${key}"]`);
        const sizeInput = certificateTemplateForm.querySelector(`[data-position-size="${key}"]`);

        if (!(item instanceof HTMLElement) || !(xInput instanceof HTMLInputElement) || !(yInput instanceof HTMLInputElement) || !(sizeInput instanceof HTMLInputElement)) {
            return;
        }

        item.style.left = `${Math.min(100, Math.max(0, Number(xInput.value) || 0))}%`;
        item.style.top = `${Math.min(100, Math.max(0, Number(yInput.value) || 0))}%`;

        if (key === 'qr') {
            item.style.width = `${Math.min(40, Math.max(5, Number(sizeInput.value) || 12))}%`;
            item.style.height = 'auto';
            item.style.aspectRatio = '1';
            item.style.fontSize = '10px';
        } else {
            item.style.fontSize = `${Math.min(key === 'nama' || key === 'judul' ? 60 : 40, Math.max(6, Number(sizeInput.value) || 12))}px`;
        }
    };

    certificateTemplateForm.querySelectorAll('[data-position-item]').forEach((item) => {
        if (!(item instanceof HTMLElement)) {
            return;
        }

        const key = item.dataset.positionKey;
        if (!key) {
            return;
        }
        updatePositionItem(key);

        item.addEventListener('pointerdown', (event) => {
            if (!(positionCanvas instanceof HTMLElement)) {
                return;
            }

            event.preventDefault();
            item.setPointerCapture(event.pointerId);

            const moveItem = (pointerEvent) => {
                const rect = positionCanvas.getBoundingClientRect();
                const x = ((pointerEvent.clientX - rect.left) / rect.width) * 100;
                const y = ((pointerEvent.clientY - rect.top) / rect.height) * 100;
                const xInput = certificateTemplateForm.querySelector(`[data-position-x="${key}"]`);
                const yInput = certificateTemplateForm.querySelector(`[data-position-y="${key}"]`);

                if (xInput instanceof HTMLInputElement) {
                    xInput.value = Math.round(Math.min(100, Math.max(0, x)) * 10) / 10;
                }
                if (yInput instanceof HTMLInputElement) {
                    yInput.value = Math.round(Math.min(100, Math.max(0, y)) * 10) / 10;
                }
                updatePositionItem(key);
            };

            const stopMoving = () => {
                item.removeEventListener('pointermove', moveItem);
                item.removeEventListener('pointerup', stopMoving);
                item.removeEventListener('pointercancel', stopMoving);
            };

            item.addEventListener('pointermove', moveItem);
            item.addEventListener('pointerup', stopMoving);
            item.addEventListener('pointercancel', stopMoving);
        });
    });

    certificateTemplateForm.querySelectorAll('[data-position-x], [data-position-y], [data-position-size]').forEach((input) => {
        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        input.addEventListener('input', () => {
            const key = input.dataset.positionX
                ?? input.dataset.positionY
                ?? input.dataset.positionSize;
            if (key) {
                updatePositionItem(key);
            }
        });
    });

    resetPositionButton?.addEventListener('click', () => {
        Object.entries(positionDefaults).forEach(([key, position]) => {
            ['x', 'y', 'size'].forEach((property) => {
                const input = certificateTemplateForm.querySelector(`[data-position-${property}="${key}"]`);
                if (input instanceof HTMLInputElement) {
                    input.value = String(position[property]);
                }
            });
            updatePositionItem(key);
        });
    });
    const initialFileName = fileName instanceof HTMLElement ? fileName.textContent.trim() : '';
    const initialPreviewSrc = previewImage instanceof HTMLImageElement
        ? previewImage.getAttribute('src') ?? ''
        : '';
    const hasInitialPreview = previewImage instanceof HTMLImageElement
        && !previewImage.hidden
        && initialPreviewSrc !== '';

    if (nameInput instanceof HTMLInputElement && nameCount instanceof HTMLElement) {
        const updateNameCount = () => {
            nameCount.textContent = String(nameInput.value.length);
        };
        nameInput.addEventListener('input', updateNameCount);
        updateNameCount();
    }

    if (
        fileInput instanceof HTMLInputElement
        && fileName instanceof HTMLElement
        && previewImage instanceof HTMLImageElement
        && previewPlaceholder instanceof HTMLElement
        && previewButton instanceof HTMLButtonElement
        && clientError instanceof HTMLElement
    ) {
        const clearPreviewObjectUrl = () => {
            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = null;
            }
        };
        const restoreInitialPreview = () => {
            fileName.textContent = initialFileName;
            if (initialPreviewSrc) {
                previewImage.src = initialPreviewSrc;
                if (positionBackground instanceof HTMLImageElement) {
                    positionBackground.src = initialPreviewSrc;
                    positionBackground.hidden = false;
                }
            } else {
                previewImage.removeAttribute('src');
                if (positionBackground instanceof HTMLImageElement) {
                    positionBackground.removeAttribute('src');
                    positionBackground.hidden = true;
                }
            }
            previewImage.hidden = !hasInitialPreview;
            previewPlaceholder.hidden = hasInitialPreview;
            previewButton.disabled = !hasInitialPreview;
        };
        const rejectFile = (message) => {
            clearPreviewObjectUrl();
            fileInput.value = '';
            restoreInitialPreview();
            clientError.textContent = message;
            clientError.hidden = false;
        };

        fileInput.addEventListener('change', () => {
            const selectionId = ++fileSelectionId;
            const file = fileInput.files?.[0];
            clientError.hidden = true;
            clientError.textContent = '';
            clearPreviewObjectUrl();

            if (!file) {
                restoreInitialPreview();
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                rejectFile('Ukuran gambar melebihi batas 5 MB. Pilih file yang lebih kecil.');
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            previewObjectUrl = objectUrl;
            const image = new Image();
            image.addEventListener('load', () => {
                if (selectionId !== fileSelectionId || fileInput.files?.[0] !== file) {
                    return;
                }

                if (image.naturalWidth < 1403 || image.naturalHeight < 992 || image.naturalWidth <= image.naturalHeight) {
                    rejectFile('Ukuran atau orientasi gambar tidak sesuai. Gunakan gambar lanskap minimal 1403 × 992 piksel.');
                    return;
                }

                previewImage.src = objectUrl;
                if (positionBackground instanceof HTMLImageElement) {
                    positionBackground.src = objectUrl;
                    positionBackground.hidden = false;
                }
                previewImage.hidden = false;
                previewPlaceholder.hidden = true;
                previewButton.disabled = false;
                fileName.textContent = file.name;
            });
            image.addEventListener('error', () => {
                if (selectionId !== fileSelectionId || fileInput.files?.[0] !== file) {
                    return;
                }

                rejectFile('Gambar tidak dapat dibaca. Pilih file PNG atau JPG yang valid.');
            });
            image.src = objectUrl;
        });
    }

    if (
        certificateTemplateForm instanceof HTMLFormElement
        && typeInput instanceof HTMLSelectElement
        && nameInput instanceof HTMLInputElement
        && activeInput instanceof HTMLSelectElement
    ) {
        certificateTemplateForm.addEventListener('submit', (event) => {
            if (clientError instanceof HTMLElement && !clientError.hidden) {
                event.preventDefault();
                fileInput?.focus();
                return;
            }

            if (confirmedTemplateSwitchForm === certificateTemplateForm) {
                confirmedTemplateSwitchForm = null;
                delete certificateTemplateForm.dataset.confirmSwitch;
                return;
            }

            if (certificateTemplateForm.dataset.templateActiveInitial === 'true' && activeInput.value === '0') {
                event.preventDefault();
                certificateTemplateForm.dataset.confirm = 'Nonaktifkan template?';
                certificateTemplateForm.dataset.confirmStyle = 'template';
                certificateTemplateForm.dataset.confirmSwitch = 'true';
                certificateTemplateForm.dataset.confirmStep = '1. Nonaktifkan';
                certificateTemplateForm.dataset.confirmIcon = 'pause';
                certificateTemplateForm.dataset.confirmTitle = `Nonaktifkan "${nameInput.value}"?`;
                certificateTemplateForm.dataset.confirmMessage = 'Jenis sertifikat ini tidak dapat menerbitkan sertifikat baru sampai memiliki template aktif. Sertifikat yang sudah terbit tidak berubah.';
                certificateTemplateForm.dataset.confirmLabel = 'Nonaktifkan';
                certificateTemplateForm.dataset.confirmTone = 'warning';
                showDeleteConfirmation(certificateTemplateForm);
                return;
            }

            if (activeInput.value !== '1') {
                return;
            }

            const selectedType = typeInput.selectedOptions[0];
            const typeId = typeInput.value;
            if (!selectedType || !typeId) {
                return;
            }

            const currentTemplateId = certificateTemplateForm.dataset.templateId ?? '';
            const activeTemplate = document.querySelector(
                `[data-active-template][data-template-type-id="${CSS.escape(typeId)}"]:not([data-template-id="${CSS.escape(currentTemplateId)}"])`,
            );
            if (!(activeTemplate instanceof HTMLElement)) {
                return;
            }

            event.preventDefault();
            const typeName = selectedType.textContent?.trim().replace(/\s+\(Nonaktif\)$/, '') ?? 'jenis sertifikat';
            certificateTemplateForm.dataset.confirm = 'Ganti template aktif?';
            certificateTemplateForm.dataset.confirmStyle = 'template';
            certificateTemplateForm.dataset.confirmSwitch = 'true';
            certificateTemplateForm.dataset.confirmStep = '3. Ganti template aktif';
            certificateTemplateForm.dataset.confirmIcon = 'switch';
            certificateTemplateForm.dataset.confirmTitle = `Ganti template aktif untuk ${typeName}?`;
            certificateTemplateForm.dataset.confirmMessage = `Menonaktifkan ${activeTemplate.dataset.templateName ?? 'template sebelumnya'}, aktifkan ${nameInput.value || 'template baru'}. Sertifikat yang sudah terbit tidak berubah.`;
            certificateTemplateForm.dataset.confirmLabel = 'Ganti Template';
            certificateTemplateForm.dataset.confirmTone = 'switch';
            showDeleteConfirmation(certificateTemplateForm);
        });
    }

    if (
        previewButton instanceof HTMLButtonElement
        && previewDialog instanceof HTMLDialogElement
        && previewCanvas instanceof HTMLElement
        && previewImage instanceof HTMLImageElement
    ) {
        previewButton.addEventListener('click', () => {
            previewCanvas.style.setProperty('--template-preview-background', `url("${previewImage.src}")`);

            const previewMap = {
                judul: previewCanvas.querySelector('.preview-sample-title'),
                diberikan: previewCanvas.querySelector('.preview-sample-given'),
                nama: previewCanvas.querySelector('.preview-sample-recipient'),
                nomor: previewCanvas.querySelector('.preview-sample-number'),
                deskripsi: previewCanvas.querySelector('.preview-sample-description'),
                tanggal: previewCanvas.querySelector('.preview-sample-signature'),
                qr: previewCanvas.querySelector('.preview-sample-qr'),
            };

            previewCanvas.querySelector('.preview-sample-given')?.removeAttribute('hidden');
            Object.entries(previewMap).forEach(([key, element]) => {
                if (!(element instanceof HTMLElement)) {
                    return;
                }

                const xInput = certificateTemplateForm.querySelector(`[data-position-x="${key}"]`);
                const yInput = certificateTemplateForm.querySelector(`[data-position-y="${key}"]`);
                const sizeInput = certificateTemplateForm.querySelector(`[data-position-size="${key}"]`);
                const x = xInput instanceof HTMLInputElement ? Number(xInput.value) : 50;
                const y = yInput instanceof HTMLInputElement ? Number(yInput.value) : 50;
                const size = sizeInput instanceof HTMLInputElement ? Number(sizeInput.value) : 12;

                element.style.left = `${x}%`;
                element.style.top = `${y}%`;

                if (key === 'qr') {
                    element.style.width = `${size}%`;
                    element.style.height = 'auto';
                    element.style.aspectRatio = '1';
                } else {
                    element.style.fontSize = `${size}px`;
                }
            });
            previewDialog.showModal();
        });

        previewDialog.querySelector('[data-close-template-form-preview]')?.addEventListener('click', () => {
            previewDialog.close();
        });
    }
}

document.querySelectorAll('[data-template-toggle]').forEach((form) => {
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    form.addEventListener('submit', (event) => {
        if (form.dataset.templateActive !== 'false') {
            return;
        }

        if (confirmedTemplateSwitchForm === form) {
            confirmedTemplateSwitchForm = null;
            delete form.dataset.confirmSwitch;
            return;
        }

        const typeId = form.dataset.templateTypeId;
        if (!typeId) {
            return;
        }

        const activeTemplate = document.querySelector(
            `[data-active-template][data-template-type-id="${CSS.escape(typeId)}"]:not([data-template-id="${CSS.escape(form.dataset.templateId ?? '')}"])`,
        );
        if (!(activeTemplate instanceof HTMLElement)) {
            return;
        }

        event.preventDefault();
        form.dataset.confirm = 'Ganti template aktif?';
        form.dataset.confirmStyle = 'template';
        form.dataset.confirmSwitch = 'true';
        form.dataset.confirmStep = '3. Ganti template aktif';
        form.dataset.confirmIcon = 'switch';
        form.dataset.confirmTitle = `Ganti template aktif untuk ${form.dataset.templateTypeName ?? 'jenis sertifikat'}?`;
        form.dataset.confirmMessage = `Menonaktifkan ${activeTemplate.dataset.templateName ?? 'template sebelumnya'}, aktifkan ${form.dataset.templateName ?? 'template ini'}. Sertifikat yang sudah terbit tidak berubah.`;
        form.dataset.confirmLabel = 'Ganti Template';
        form.dataset.confirmTone = 'switch';
        showDeleteConfirmation(form);
    });
});

if (certificateTemplatePreview instanceof HTMLDialogElement) {
    const previewTitle = certificateTemplatePreview.querySelector('[data-preview-title]');
    const previewImage = certificateTemplatePreview.querySelector('[data-preview-image]');
    const previewPdf = certificateTemplatePreview.querySelector('[data-preview-pdf]');

    document.querySelectorAll('[data-open-template-preview]').forEach((button) => {
        button.addEventListener('click', () => {
            if (
                !(button instanceof HTMLButtonElement)
                || !(previewTitle instanceof HTMLHeadingElement)
                || !(previewImage instanceof HTMLImageElement)
                || !(previewPdf instanceof HTMLIFrameElement)
            ) {
                return;
            }

            const url = button.dataset.previewUrl;
            if (!url) {
                return;
            }

            previewTitle.textContent = button.dataset.previewName ?? 'Pratinjau Template';
            const isPdf = button.dataset.previewType === 'pdf';
            previewImage.hidden = isPdf;
            previewPdf.hidden = !isPdf;
            if (isPdf) {
                previewPdf.src = url;
                previewImage.removeAttribute('src');
            } else {
                previewImage.src = url;
                previewImage.alt = `Desain ${previewTitle.textContent}`;
                previewPdf.removeAttribute('src');
            }
            certificateTemplatePreview.showModal();
        });
    });

    certificateTemplatePreview.querySelector('[data-close-template-preview]')?.addEventListener('click', () => {
        certificateTemplatePreview.close();
    });
}

const certificateIssuanceForm = document.querySelector('[data-certificate-issuance-form]');

document.querySelectorAll('[data-copy-certificate-number]').forEach((button) => {
    if (!(button instanceof HTMLButtonElement)) {
        return;
    }

    button.addEventListener('click', () => {
        const number = button.dataset.copyCertificateNumber;
        if (!number || !navigator.clipboard?.writeText) {
            window.alert('Penyalinan otomatis tidak tersedia. Silakan salin nomor sertifikat secara manual.');
            return;
        }

        navigator.clipboard.writeText(number)
            .then(() => {
                button.textContent = 'Tersalin';
                window.setTimeout(() => {
                    button.textContent = 'Salin nomor';
                }, 1800);
            })
            .catch(() => {
                window.alert('Nomor sertifikat gagal disalin. Silakan salin nomor secara manual.');
            });
    });
});

document.querySelectorAll('[data-certificate-archive-filter]').forEach((filter) => {
    if (filter instanceof HTMLSelectElement) {
        filter.addEventListener('change', () => {
            if (filter.form instanceof HTMLFormElement) {
                filter.form.requestSubmit();
            }
        });
    }
});

if (certificateIssuanceForm instanceof HTMLFormElement) {
    const issuanceConfirmation = document.querySelector('[data-certificate-issuance-confirmation]');
    const confirmParticipant = issuanceConfirmation?.querySelector('[data-issuance-confirm-participant]');
    const confirmType = issuanceConfirmation?.querySelector('[data-issuance-confirm-type]');
    const confirmPeriod = issuanceConfirmation?.querySelector('[data-issuance-confirm-period]');
    const confirmTemplate = issuanceConfirmation?.querySelector('[data-issuance-confirm-template]');
    const confirmCancel = issuanceConfirmation?.querySelector('[data-issuance-confirm-cancel]');
    const confirmSubmit = issuanceConfirmation?.querySelector('[data-issuance-confirm-submit]');
    let confirmedIssuance = false;

    if (
        issuanceConfirmation instanceof HTMLDialogElement
        && confirmParticipant instanceof HTMLElement
        && confirmType instanceof HTMLElement
        && confirmPeriod instanceof HTMLElement
        && confirmTemplate instanceof HTMLElement
        && confirmCancel instanceof HTMLButtonElement
        && confirmSubmit instanceof HTMLButtonElement
    ) {
        const getTemplateName = () => {
            const option = certificateIssuanceForm.querySelector('[data-issuance-type]')?.selectedOptions[0];
            return option instanceof HTMLOptionElement ? option.dataset.templateName ?? '' : '';
        };

        certificateIssuanceForm.addEventListener('submit', (event) => {
            if (confirmedIssuance) {
                confirmedIssuance = false;
                return;
            }

            event.preventDefault();
            const startDate = certificateIssuanceForm.querySelector('[data-issuance-start]');
            const endDate = certificateIssuanceForm.querySelector('[data-issuance-end]');
            const period = startDate instanceof HTMLInputElement && endDate instanceof HTMLInputElement
                ? [startDate.value, endDate.value].filter(Boolean).map(formatIssuanceDate).join(' - ')
                : '';
            const selectedParticipants = certificateIssuanceForm.querySelectorAll('[data-issuance-participant-row] input:checked');
            const participantNames = Array.from(selectedParticipants, (checkbox) => checkbox.dataset.participantName);

            confirmParticipant.textContent = participantNames.length
                ? `${participantNames.length} peserta: ${participantNames.join(', ')}`
                : '-';
            const selectedType = certificateIssuanceForm.querySelector('[data-issuance-type]');
            confirmType.textContent = selectedType instanceof HTMLSelectElement
                ? selectedType.selectedOptions[0]?.textContent?.trim() ?? '-'
                : '-';
            confirmPeriod.textContent = period || '-';
            confirmTemplate.textContent = getTemplateName() || '-';
            issuanceConfirmation.showModal();
            confirmCancel.focus();
        });

        confirmCancel.addEventListener('click', () => issuanceConfirmation.close());
        confirmSubmit.addEventListener('click', () => {
            if (!certificateIssuanceForm.reportValidity()) {
                issuanceConfirmation.close();
                return;
            }

            confirmedIssuance = true;
            issuanceConfirmation.close();
            certificateIssuanceForm.requestSubmit();
        });
        issuanceConfirmation.addEventListener('click', (event) => {
            if (event.target === issuanceConfirmation) {
                issuanceConfirmation.close();
            }
        });
        issuanceConfirmation.addEventListener('cancel', () => {
            confirmedIssuance = false;
        });
    }

    const participantsList = certificateIssuanceForm.querySelector('[data-issuance-participants]');
    const participantHint = certificateIssuanceForm.querySelector('[data-issuance-participant-hint]');
    const participantsEmpty = certificateIssuanceForm.querySelector('[data-issuance-participants-empty]');
    const selectAllParticipants = certificateIssuanceForm.querySelector('[data-issuance-select-all]');
    const selectAllParticipantsRow = certificateIssuanceForm.querySelector('[data-issuance-select-all-row]');
    const typeSelect = certificateIssuanceForm.querySelector('[data-issuance-type]');
    const templateId = certificateIssuanceForm.querySelector('[data-issuance-template-id]');
    const templateUsed = certificateIssuanceForm.querySelector('[data-issuance-template-used]');
    const templateBadge = certificateIssuanceForm.querySelector('[data-issuance-template-badge]');
    const certificatePreview = certificateIssuanceForm.querySelector('[data-issuance-certificate]');
    const namePreview = certificateIssuanceForm.querySelector('[data-preview-participant]');
    const typePreview = certificateIssuanceForm.querySelector('[data-preview-type]');
    const periodPreview = certificateIssuanceForm.querySelector('[data-preview-period]');
    const durationPreview = certificateIssuanceForm.querySelector('[data-preview-duration]');
    const scorePreview = certificateIssuanceForm.querySelector('[data-preview-score]');
    const dateStart = certificateIssuanceForm.querySelector('[data-issuance-start]');
    const dateEnd = certificateIssuanceForm.querySelector('[data-issuance-end]');
    const descriptionInput = certificateIssuanceForm.querySelector('[data-issuance-description]');
    const descriptionPreview = certificateIssuanceForm.querySelector('[data-preview-description]');
    const supportPreview = certificateIssuanceForm.querySelector('[data-preview-support]');
    const formatIssuanceDate = (value) => {
        if (!value) {
            return '';
        }

        const date = new Date(`${value}T00:00:00`);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        }).format(date);
    };

    const updateParticipants = () => {
        if (!(participantsList instanceof HTMLFieldSetElement) || !(typeSelect instanceof HTMLSelectElement)) {
            return;
        }

        const typeId = typeSelect.value;
        participantsList.disabled = !typeId;
        if (participantHint instanceof HTMLElement) {
            participantHint.hidden = Boolean(typeId);
        }

        let visibleCount = 0;
        const rows = Array.from(participantsList.querySelectorAll('[data-issuance-participant-row]'));
        rows.forEach((row) => {
            if (!(row instanceof HTMLElement)) {
                return;
            }

            const checkbox = row.querySelector('input[type="checkbox"]');
            const issuedTypes = (row.dataset.issuedTypes ?? '').split(',').filter(Boolean);
            const eligible = Boolean(typeId) && !issuedTypes.includes(typeId);
            row.hidden = !eligible;
            if (!eligible && checkbox instanceof HTMLInputElement) {
                checkbox.checked = false;
                checkbox.required = false;
            }
            if (eligible) {
                visibleCount += 1;
            }
        });

        const firstEligibleCheckbox = rows
            .filter((row) => row instanceof HTMLElement && !row.hidden)
            .map((row) => row.querySelector('input[type="checkbox"]'))
            .find((checkbox) => checkbox instanceof HTMLInputElement);
        syncParticipantRequired(firstEligibleCheckbox);
        if (participantsEmpty instanceof HTMLElement) {
            participantsEmpty.hidden = !typeId || visibleCount > 0;
        }
        if (selectAllParticipantsRow instanceof HTMLElement) {
            selectAllParticipantsRow.hidden = !typeId || visibleCount === 0;
        }
        updateParticipantPreview();
    };

    const updateParticipantPreview = () => {
        if (!(namePreview instanceof HTMLElement) || !(participantsList instanceof HTMLFieldSetElement)) {
            return;
        }

        const checked = Array.from(
            participantsList.querySelectorAll('input[type="checkbox"]:checked'),
            (checkbox) => checkbox.dataset.participantName,
        );
        const firstEligibleCheckbox = Array.from(
            participantsList.querySelectorAll('[data-issuance-participant-row]:not([hidden]) input[type="checkbox"]'),
        ).find((checkbox) => checkbox instanceof HTMLInputElement);
        syncParticipantRequired(firstEligibleCheckbox);
        syncSelectAllParticipants();
        namePreview.textContent = checked.length > 1
            ? `${checked.length} peserta dipilih`
            : checked[0] ?? 'Nama Peserta';
    };

    const syncParticipantRequired = (firstEligibleCheckbox) => {
        if (!(participantsList instanceof HTMLFieldSetElement)) {
            return;
        }

        const hasSelection = Boolean(participantsList.querySelector('input[type="checkbox"]:checked'));
        participantsList.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
            if (checkbox instanceof HTMLInputElement) {
                checkbox.required = !hasSelection && checkbox === firstEligibleCheckbox;
            }
        });
    };

    const syncSelectAllParticipants = () => {
        if (
            !(participantsList instanceof HTMLFieldSetElement)
            || !(selectAllParticipants instanceof HTMLInputElement)
        ) {
            return;
        }

        const eligibleCheckboxes = Array.from(
            participantsList.querySelectorAll('[data-issuance-participant-row]:not([hidden]) input[type="checkbox"]'),
        ).filter((checkbox) => checkbox instanceof HTMLInputElement);
        const selectedCount = eligibleCheckboxes.filter((checkbox) => checkbox.checked).length;

        selectAllParticipants.disabled = eligibleCheckboxes.length === 0;
        selectAllParticipants.checked = eligibleCheckboxes.length > 0
            && selectedCount === eligibleCheckboxes.length;
        selectAllParticipants.indeterminate = selectedCount > 0
            && selectedCount < eligibleCheckboxes.length;
    };

    const updateTypeFields = () => {
        if (!(typeSelect instanceof HTMLSelectElement)) {
            return;
        }

        const typeCode = typeSelect.selectedOptions[0]?.dataset.typeCode ?? '';
        certificateIssuanceForm.querySelectorAll('[data-issuance-type-fields]').forEach((fieldset) => {
            if (!(fieldset instanceof HTMLFieldSetElement)) {
                return;
            }

            const active = Boolean(typeCode) && fieldset.dataset.issuanceTypeFields === typeCode;
            fieldset.hidden = !active;
            fieldset.disabled = !active;
        });
    };

    const activeSupportFields = () => {
        const typeCode = typeSelect instanceof HTMLSelectElement
            ? typeSelect.selectedOptions[0]?.dataset.typeCode
            : '';

        return Array.from(certificateIssuanceForm.querySelectorAll('[data-issuance-type-fields]'))
            .find((fieldset) => fieldset instanceof HTMLFieldSetElement
                && fieldset.dataset.issuanceTypeFields === typeCode);
    };

    const updatePreview = () => {
        if (
            !(typeSelect instanceof HTMLSelectElement)
            || !(templateId instanceof HTMLInputElement)
            || !(templateUsed instanceof HTMLElement)
            || !(templateBadge instanceof HTMLElement)
            || !(certificatePreview instanceof HTMLElement)
            || !(typePreview instanceof HTMLElement)
        ) {
            return;
        }

        const option = typeSelect.selectedOptions[0];
        const selectedTemplateId = option?.dataset.templateId ?? '';
        const selectedTemplateName = option?.dataset.templateName ?? '';
        const selectedTypeName = option?.dataset.typeName ?? 'Jenis Sertifikat';
        const templateUrl = option?.dataset.templateUrl ?? '';
        templateId.value = selectedTemplateId;
        typePreview.textContent = selectedTypeName;
        templateBadge.textContent = selectedTemplateName || 'Pilih jenis';
        templateUsed.textContent = selectedTemplateName
            ? `Template yang dipakai: ${selectedTemplateName}`
            : 'Pilih jenis sertifikat untuk menentukan template aktif.';
        templateUsed.hidden = !selectedTemplateName;
        certificatePreview.style.backgroundImage = templateUrl
            ? `linear-gradient(rgb(255 255 255 / 27%), rgb(255 255 255 / 27%)), url("${templateUrl}")`
            : 'none';

        if (periodPreview instanceof HTMLElement && dateStart instanceof HTMLInputElement && dateEnd instanceof HTMLInputElement) {
            const start = formatIssuanceDate(dateStart.value);
            const end = formatIssuanceDate(dateEnd.value);
            periodPreview.textContent = start && end ? `periode ${start} – ${end}` : '';
        }

        const activeFields = activeSupportFields();
        const durationInput = activeFields?.querySelector('[data-support-column="durasi"]');
        const scoreInput = activeFields?.querySelector('[data-support-column="nilai"]');
        const predicateInput = activeFields?.querySelector('[data-support-column="predikat"]');
        if (durationPreview instanceof HTMLElement && durationInput instanceof HTMLInputElement) {
            durationPreview.textContent = durationInput.value.trim()
                ? `(${durationInput.value.trim()})`
                : '';
        }

        if (scorePreview instanceof HTMLElement) {
            const score = scoreInput instanceof HTMLInputElement ? scoreInput.value.trim() : '';
            const predicate = predicateInput instanceof HTMLInputElement ? predicateInput.value.trim() : '';
            scorePreview.textContent = [score ? `Nilai ${score}` : '', predicate]
                .filter(Boolean)
                .join(' · ');
        }
        if (supportPreview instanceof HTMLElement) {
            const extraValues = Array.from(activeFields?.querySelectorAll('[data-support-input]') ?? [])
                .filter((input) => input instanceof HTMLInputElement
                    && input.value.trim()
                    && !input.dataset.supportColumn)
                .map((input) => `${input.dataset.supportLabel}: ${input.value.trim()}`);
            supportPreview.textContent = extraValues.join(' · ');
        }
        if (descriptionPreview instanceof HTMLElement && descriptionInput instanceof HTMLTextAreaElement) {
            descriptionPreview.textContent = descriptionInput.value.trim() || 'Deskripsi sertifikat';
        }
    };

    if (participantsList instanceof HTMLFieldSetElement) {
        participantsList.addEventListener('change', updateParticipantPreview);
        updateParticipants();
    }
    if (
        participantsList instanceof HTMLFieldSetElement
        && selectAllParticipants instanceof HTMLInputElement
    ) {
        selectAllParticipants.addEventListener('change', () => {
            participantsList.querySelectorAll(
                '[data-issuance-participant-row]:not([hidden]) input[type="checkbox"]',
            ).forEach((checkbox) => {
                if (checkbox instanceof HTMLInputElement) {
                    checkbox.checked = selectAllParticipants.checked;
                }
            });
            updateParticipantPreview();
        });
    }

    if (typeSelect instanceof HTMLSelectElement) {
        typeSelect.addEventListener('change', () => {
            updateParticipants();
            updateTypeFields();
            updatePreview();
        });
        updateParticipants();
        updateTypeFields();
        updatePreview();
    }

    [
        dateStart,
        dateEnd,
        descriptionInput,
    ].forEach((input) => {
        input?.addEventListener('input', updatePreview);
        input?.addEventListener('change', updatePreview);
    });
    certificateIssuanceForm.querySelectorAll('[data-support-input]').forEach((input) => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });

    if (dateStart instanceof HTMLInputElement && dateEnd instanceof HTMLInputElement) {
        dateStart.addEventListener('change', () => {
            dateEnd.min = dateStart.value;
            if (dateEnd.value && dateEnd.value < dateStart.value) {
                dateEnd.value = '';
            }
            updatePreview();
        });
        dateEnd.min = dateStart.value;
    }
}

document.querySelectorAll('[data-participant-modal], [data-edit-participant-modal], [data-certificate-type-modal]').forEach((modal) => {
    if (!(modal instanceof HTMLDialogElement)) {
        return;
    }

    modal.querySelectorAll('[data-close-participant-modal]').forEach((button) => {
        button.addEventListener('click', () => modal.close());
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.close();
        }
    });
});
