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
        fileInput.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            clientError.hidden = true;
            clientError.textContent = '';
            if (!file) {
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                fileInput.value = '';
                clientError.textContent = 'Ukuran desain tidak boleh melebihi 5 MB.';
                clientError.hidden = false;
                return;
            }

            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
            }
            previewObjectUrl = URL.createObjectURL(file);
            const image = new Image();
            image.addEventListener('load', () => {
                if (image.naturalWidth < 1754 || image.naturalHeight < 1240 || image.naturalWidth <= image.naturalHeight) {
                    URL.revokeObjectURL(previewObjectUrl);
                    previewObjectUrl = null;
                    fileInput.value = '';
                    clientError.textContent = 'Desain harus berorientasi lanskap dengan ukuran minimal 1754 × 1240 piksel.';
                    clientError.hidden = false;
                    return;
                }

                previewImage.src = image.src;
                previewImage.hidden = false;
                previewPlaceholder.hidden = true;
                previewButton.disabled = false;
                fileName.textContent = file.name;
            });
            image.addEventListener('error', () => {
                fileInput.value = '';
                clientError.textContent = 'Berkas desain tidak dapat dibaca sebagai gambar PNG atau JPG.';
                clientError.hidden = false;
            });
            image.src = previewObjectUrl;
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
