document.addEventListener('DOMContentLoaded', function () {
    const accountTypeFilter = document.getElementById('accountTypeFilter');
    const addForm = document.getElementById('addSkFedForm');

    if (accountTypeFilter) {
        accountTypeFilter.addEventListener('change', function () {
            const target = this.value === 'sk_officials' ? '/accounts/officials' : '/accounts/federation';
            window.location.href = target;
        });
    }

    if (addForm) {
        addForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(addForm);

            const formData = new FormData(addForm);
            const payload = {};

            for (const [key, value] of formData.entries()) {
                if (key !== '_token') {
                    payload[key] = value;
                }
            }

            showLoadingOverlay();

            fetch('/manage-account', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(async (response) => {
                    const data = await response.json();
                    return { ok: response.ok, data };
                })
                .then(({ ok, data }) => {
                    hideLoadingOverlay();

                    if (!ok || !data.success) {
                        if (data.errors) {
                            Object.keys(data.errors).forEach((field) => {
                                showFieldError(addForm, field, data.errors[field][0]);
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
    }
});

window.openAddAccountModal = function () {
    const modal = document.getElementById('addAccountModal');
    if (!modal) return;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeAddAccountModal = function () {
    const modal = document.getElementById('addAccountModal');
    if (!modal) return;

    modal.style.display = 'none';
    document.body.style.overflow = '';
};

window.showAddSuccessModal = function () {
    const modal = document.getElementById('addSuccessModal');
    if (!modal) return;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeAddSuccessModal = function () {
    const modal = document.getElementById('addSuccessModal');
    if (!modal) return;

    modal.style.display = 'none';
    document.body.style.overflow = '';
    window.location.reload();
};

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
