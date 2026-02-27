document.addEventListener('DOMContentLoaded', function () {
    const accountTypeFilter = document.getElementById('accountTypeFilter');
    const federationForm = document.getElementById('addSkFedForm');
    const officialsForm = document.getElementById('addSkOfficialsForm');
    const federationEditForm = document.getElementById('editAccountForm');
    const officialsEditForm = document.getElementById('editSkOfficialsForm');
    const editButtons = document.querySelectorAll('.btn-edit-account');

    if (accountTypeFilter) {
        accountTypeFilter.addEventListener('change', function () {
            const target = this.value === 'sk_officials' ? '/accounts/officials' : '/accounts/federation';
            window.location.href = target;
        });
    }

    const attachSubmitHandler = function (form) {
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);

            const formData = new FormData(form);
            const payload = {};

            for (const [key, value] of formData.entries()) {
                if (key !== '_token') {
                    payload[key] = value;
                }
            }

            const positionMap = {
                sk_chairman: 'Chairman',
                sk_councilor: 'Councilor',
                sk_kagawad: 'Kagawad',
                sk_treasurer: 'Treasurer',
                sk_secretary: 'Secretary',
                sk_auditor: 'Auditor',
                sk_pio: 'PIO'
            };

            const normalizedStatus = String(payload.status || '')
                .trim()
                .toUpperCase()
                .replace(/\s+/g, '_');

            if (normalizedStatus) {
                payload.status = normalizedStatus;
            }

            if (payload.position && positionMap[payload.position]) {
                payload.position = positionMap[payload.position];
            }

            if (!payload.barangay_id && payload.barangay) {
                payload.barangay_id = payload.barangay;
            }

            delete payload.barangay;

            if (!payload.role) {
                payload.role = form.id === 'addSkOfficialsForm' ? 'sk_official' : 'sk_fed';
            }

            payload.term_status = payload.term_status || (payload.status === 'INACTIVE' ? 'INACTIVE' : 'ACTIVE');

            showLoadingOverlay();

            fetch('/accounts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(async (response) => {
                    const contentType = response.headers.get('content-type') || '';
                    const data = contentType.includes('application/json') ? await response.json() : {};
                    return { ok: response.ok, data };
                })
                .then(({ ok, data }) => {
                    hideLoadingOverlay();

                    if (!ok || !data.success) {
                        if (data.errors) {
                            Object.keys(data.errors).forEach((field) => {
                                showFieldError(form, field, data.errors[field][0]);
                            });
                        } else {
                            alert('Failed to create account. Please try again.');
                        }
                        return;
                    }

                    closeAddAccountModal();
                    showAddSuccessModal();
                })
                .catch(() => {
                    hideLoadingOverlay();
                    alert('An unexpected error occurred. Please try again.');
                });
        });
    };

    attachSubmitHandler(federationForm);
    attachSubmitHandler(officialsForm);

    const attachEditSubmitHandler = function (form) {
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);

            const accountId = form.dataset.accountId;
            if (!accountId) {
                alert('Unable to update account. Missing account ID.');
                return;
            }

            const formData = new FormData(form);
            const payload = {};

            for (const [key, value] of formData.entries()) {
                if (key !== '_token' && key !== '_method') {
                    payload[key] = value;
                }
            }

            payload.term_status = payload.term_status || 'ACTIVE';

            showLoadingOverlay();

            fetch(`/accounts/${accountId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(async (response) => {
                    const contentType = response.headers.get('content-type') || '';
                    const data = contentType.includes('application/json') ? await response.json() : {};
                    return { ok: response.ok, data };
                })
                .then(({ ok, data }) => {
                    hideLoadingOverlay();

                    if (!ok || !data.success) {
                        if (data.errors) {
                            Object.keys(data.errors).forEach((field) => {
                                showFieldError(form, field, data.errors[field][0]);
                            });
                        } else {
                            alert('Failed to update account. Please try again.');
                        }
                        return;
                    }

                    closeEditModalByType();
                    showEditSuccessModalByType();
                })
                .catch(() => {
                    hideLoadingOverlay();
                    alert('An unexpected error occurred. Please try again.');
                });
        });
    };

    const populateEditForm = function (form, data) {
        if (!form) {
            return;
        }

        form.dataset.accountId = data.accountId || '';

        setFormFieldValue(form, 'first_name', data.firstName || '');
        setFormFieldValue(form, 'last_name', data.lastName || '');
        setFormFieldValue(form, 'middle_name', data.middleName || '');
        setFormFieldValue(form, 'suffix', data.suffix || '');
        setFormFieldValue(form, 'email', data.email || '');
        setFormFieldValue(form, 'position', data.position || '');
        setFormFieldValue(form, 'barangay_id', data.barangayId || '');
        setFormFieldValue(form, 'status', data.status || 'ACTIVE');
        setFormFieldValue(form, 'term_start', data.termStart || '');
        setFormFieldValue(form, 'term_end', data.termEnd || '');
        setFormFieldValue(form, 'term_status', data.termStatus || 'ACTIVE');

        clearAllErrors(form);
    };

    const openEditModalWithData = function (button) {
        const data = button.dataset;
        const isOfficials = getCurrentAccountType() === 'sk_officials';

        if (isOfficials) {
            populateEditForm(officialsEditForm, data);
            openEditSkOfficialsModal();
            return;
        }

        populateEditForm(federationEditForm, data);
        openEditModal();
    };

    editButtons.forEach((button) => {
        button.addEventListener('click', function () {
            openEditModalWithData(button);
        });
    });

    attachEditSubmitHandler(federationEditForm);
    attachEditSubmitHandler(officialsEditForm);
});

function setFormFieldValue(form, name, value) {
    const field = form.querySelector(`[name="${name}"]`);
    if (!field) {
        return;
    }

    field.value = value;
}

function getCurrentAccountType() {
    const accountTypeFilter = document.getElementById('accountTypeFilter');

    if (accountTypeFilter && accountTypeFilter.value) {
        return accountTypeFilter.value;
    }

    return window.location.pathname.includes('/accounts/officials') ? 'sk_officials' : 'sk_federation';
}

function getModalIdsByType(type) {
    if (type === 'sk_officials') {
        return {
            addModalId: 'addSkOfficialsModal',
            successModalId: 'skOfficialsSuccessModal'
        };
    }

    return {
        addModalId: 'addAccountModal',
        successModalId: 'addSuccessModal'
    };
}

function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    modal.style.display = show ? 'flex' : 'none';
    document.body.style.overflow = show ? 'hidden' : '';
}

window.openAddAccountModal = function () {
    const ids = getModalIdsByType(getCurrentAccountType());
    toggleModal(ids.addModalId, true);
};

window.closeAddAccountModal = function () {
    const ids = getModalIdsByType(getCurrentAccountType());
    toggleModal(ids.addModalId, false);
};

window.showAddSuccessModal = function () {
    const ids = getModalIdsByType(getCurrentAccountType());
    toggleModal(ids.successModalId, true);
};

window.closeAddSuccessModal = function () {
    const ids = getModalIdsByType(getCurrentAccountType());
    toggleModal(ids.successModalId, false);
    window.location.reload();
};

window.openAddSkOfficialsModal = function () {
    toggleModal('addSkOfficialsModal', true);
};

window.closeAddSkOfficialsModal = function () {
    toggleModal('addSkOfficialsModal', false);
};

window.showSkOfficialsSuccessModal = function () {
    toggleModal('skOfficialsSuccessModal', true);
};

window.closeSkOfficialsSuccessModal = function () {
    toggleModal('skOfficialsSuccessModal', false);
    window.location.reload();
};

window.openEditModal = function () {
    toggleModal('editAccountModal', true);
};

window.closeEditModal = function () {
    toggleModal('editAccountModal', false);
};

window.showEditSuccessModal = function () {
    toggleModal('editSuccessModal', true);
};

window.closeEditSuccessModal = function () {
    toggleModal('editSuccessModal', false);
    window.location.reload();
};

window.openEditSkOfficialsModal = function () {
    toggleModal('editSkOfficialsModal', true);
};

window.closeEditSkOfficialsModal = function () {
    toggleModal('editSkOfficialsModal', false);
};

window.showEditSkOfficialsSuccessModal = function () {
    toggleModal('editSkOfficialsSuccessModal', true);
};

window.closeEditSkOfficialsSuccessModal = function () {
    toggleModal('editSkOfficialsSuccessModal', false);
    window.location.reload();
};

function closeEditModalByType() {
    if (getCurrentAccountType() === 'sk_officials') {
        closeEditSkOfficialsModal();
        return;
    }

    closeEditModal();
}

function showEditSuccessModalByType() {
    if (getCurrentAccountType() === 'sk_officials') {
        showEditSkOfficialsSuccessModal();
        return;
    }

    showEditSuccessModal();
}

function showFieldError(form, fieldName, message) {
    const field = form.querySelector(`[name="${fieldName}"]`);
    if (!field) return;

    field.classList.add('error');
    const errorElement = field.parentElement.querySelector('.form-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }
}

function clearAllErrors(form) {
    form.querySelectorAll('.form-input-modern').forEach((field) => {
        field.classList.remove('error');
    });

    form.querySelectorAll('.form-error').forEach((errorElement) => {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    });
}

function showLoadingOverlay() {
    let overlay = document.getElementById('loadingOverlay');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Processing...</p>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (!overlay) return;

    overlay.style.display = 'none';
    document.body.style.overflow = '';
}
