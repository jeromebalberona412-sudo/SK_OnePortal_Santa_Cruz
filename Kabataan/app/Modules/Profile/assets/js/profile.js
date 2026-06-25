// Profile Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initProfileAvatarChange();
    initProfileSupportingDocuments();

    // ── Toast Notification ──────────────────────────────────────────────────
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `profile-toast profile-toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
            color: white;
            padding: 16px 24px;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    window.showProfileToast = showToast;

    // Add animation styles
    if (!document.getElementById('profileToastStyles')) {
        const style = document.createElement('style');
        style.id = 'profileToastStyles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }

    // Elements
    const filterTabs = document.querySelectorAll('.tab-btn');
    const programItems = document.querySelectorAll('.program-item');

    // Filter Programs
    if (filterTabs.length > 0) {
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');

                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                filterPrograms(filter);
            });
        });
    }

    function filterPrograms(filter) {
        programItems.forEach(item => {
            const status = item.getAttribute('data-status');

            if (filter === 'all') {
                item.style.display = 'flex';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 10);
            } else if (status === filter) {
                item.style.display = 'flex';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 10);
            } else {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    item.style.display = 'none';
                }, 300);
            }
        });

        setTimeout(() => {
            const visiblePrograms = Array.from(programItems).filter(item =>
                item.style.display !== 'none'
            );

            const programsList = document.querySelector('.programs-list');
            const existingEmpty = programsList?.querySelector('.empty-state');

            if (programsList && visiblePrograms.length === 0 && !existingEmpty) {
                programsList.appendChild(createEmptyState(filter));
            } else if (existingEmpty && visiblePrograms.length > 0) {
                existingEmpty.remove();
            }
        }, 350);
    }

    function createEmptyState(filter) {
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'empty-state';

        let message = 'No programs found';
        if (filter === 'pending') message = 'No pending programs';
        if (filter === 'approved') message = 'No approved programs';
        if (filter === 'evaluation') message = 'No programs in evaluation';
        if (filter === 'completed') message = 'No completed programs';

        emptyDiv.innerHTML = `
            <div class="empty-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="6" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M3 10 L12 6 L21 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="8" y1="13" x2="16" y2="13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="10" y1="16" x2="14" y2="16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h3>${message}</h3>
            <p>Check back later or explore new programs in the dashboard!</p>
        `;

        return emptyDiv;
    }

    const programsList = document.querySelector('.programs-list');
    if (programsList) {
        programsList.style.scrollBehavior = 'smooth';
    }

    programItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';

        setTimeout(() => {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 50);
    });
});

function initProfileAvatarChange() {
    const wrapper = document.getElementById('profileAvatarWrapper');
    if (!wrapper) return;

    const uploadUrl = wrapper.dataset.uploadUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const profileAvatar = document.getElementById('profileAvatar');
    const fileInput = document.getElementById('photoUpload');
    const lockModal = document.getElementById('profilePictureLockModal');
    const uploadInstructionsModal = document.getElementById('profilePictureUploadModal');
    const uploadContinueBtn = document.getElementById('profilePictureUploadContinueBtn');
    const lockDateEl = document.getElementById('profilePictureLockDate');
    const confirmModal = document.getElementById('profilePictureConfirmModal');
    const confirmPreview = document.getElementById('profilePictureConfirmPreview');
    const confirmCancelBtn = document.getElementById('profilePictureConfirmCancelBtn');
    const confirmSubmitBtn = document.getElementById('profilePictureConfirmSubmitBtn');
    let pendingProfileFile = null;
    let pendingPreviewUrl = null;

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const maxBytes = 10 * 1024 * 1024;

    if (profileAvatar) {
        profileAvatar.addEventListener('error', () => {
            const fallback = profileAvatar.dataset.fallback || wrapper.dataset.fallbackAvatar;
            if (fallback && profileAvatar.src !== fallback) {
                profileAvatar.src = fallback;
            }
        });
    }

    function isUploadAllowed() {
        return wrapper.dataset.canChange === '1';
    }

    function normalizeAvatarUrl(url) {
        if (!url) return url;
        const storageMatch = url.match(/\/storage\/.+$/);
        if (storageMatch) return storageMatch[0];
        return url;
    }

    function updateAvatarImages(url) {
        const resolved = normalizeAvatarUrl(url);
        if (profileAvatar) {
            profileAvatar.onerror = () => {
                profileAvatar.onerror = null;
                profileAvatar.src = profileAvatar.dataset.fallback || wrapper.dataset.fallbackAvatar || '';
            };
            profileAvatar.src = resolved;
        }

        document.querySelectorAll('.kabataan-header__avatar-btn img, .kabataan-header__dropdown-head img').forEach((img) => {
            img.src = resolved;
        });
    }

    function lockUpload(nextChangeDisplay) {
        wrapper.dataset.canChange = '0';
        wrapper.dataset.nextChange = nextChangeDisplay || '';
        wrapper.setAttribute('aria-label', 'Profile picture update locked');
        wrapper.setAttribute('title', 'Profile picture update locked');
        if (fileInput) fileInput.disabled = true;
        if (lockDateEl && nextChangeDisplay) {
            lockDateEl.textContent = nextChangeDisplay;
        }
    }

    function showLockModal() {
        const nextChange = wrapper.dataset.nextChange;
        if (lockDateEl && nextChange) {
            lockDateEl.textContent = nextChange;
        }
        if (lockModal) {
            lockModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    window.closeProfilePictureLockModal = function() {
        if (lockModal) {
            lockModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };

    window.closeProfilePictureUploadModal = function() {
        if (uploadInstructionsModal) {
            uploadInstructionsModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };

    function openPhotoPicker() {
        if (!fileInput || fileInput.disabled) {
            return;
        }

        // Must run synchronously inside the user click handler (no modal close before this).
        fileInput.click();
    }

    function closeModalsForUpload() {
        if (uploadInstructionsModal) {
            uploadInstructionsModal.style.display = 'none';
        }
        if (confirmModal) {
            confirmModal.style.display = 'none';
        }
        document.body.style.overflow = 'auto';
    }

    function clearPendingProfileFile() {
        if (pendingPreviewUrl) {
            URL.revokeObjectURL(pendingPreviewUrl);
            pendingPreviewUrl = null;
        }
        pendingProfileFile = null;
        if (confirmPreview) {
            confirmPreview.removeAttribute('src');
        }
    }

    window.closeProfilePictureConfirmModal = function() {
        clearPendingProfileFile();
        if (confirmModal) {
            confirmModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };

    function showConfirmModal(file) {
        clearPendingProfileFile();
        pendingProfileFile = file;
        pendingPreviewUrl = URL.createObjectURL(file);

        if (confirmPreview) {
            confirmPreview.src = pendingPreviewUrl;
        }
        if (confirmModal) {
            confirmModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function showUploadInstructionsModal() {
        if (uploadInstructionsModal) {
            uploadInstructionsModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function validateFile(file) {
        if (!file) {
            return 'Please select an image file.';
        }

        if (!allowedTypes.includes(file.type)) {
            return 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
        }

        if (file.size > maxBytes) {
            return 'Profile image must be 10MB or smaller.';
        }

        return null;
    }

    function uploadFile(file) {
        if (!isUploadAllowed()) {
            showLockModal();
            return;
        }

        const validationError = validateFile(file);
        if (validationError) {
            if (typeof window.showProfileToast === 'function') {
                window.showProfileToast(validationError, 'error');
            }
            return;
        }

        closeModalsForUpload();
        clearPendingProfileFile();

        const formData = new FormData();
        formData.append('profile_picture', file);
        formData.append('_token', csrfToken);

        if (typeof showLoading === 'function') {
            showLoading('Uploading profile picture');
        }

        if (confirmSubmitBtn) {
            confirmSubmitBtn.disabled = true;
            confirmSubmitBtn.textContent = 'Uploading...';
        }

        fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
            credentials: 'same-origin',
        })
            .then(async (response) => {
                let payload = {};
                try {
                    payload = await response.json();
                } catch (error) {
                    throw new Error('Unexpected server response. Please try again.');
                }

                if (response.ok && payload.success) {
                    updateAvatarImages(payload.picture_url);
                    const message = payload.message || 'Profile picture uploaded successfully.';
                    if (typeof window.showProfileToast === 'function') {
                        window.showProfileToast(message, 'success');
                    }
                    if (payload.next_change_display) {
                        lockUpload(payload.next_change_display);
                    }
                    return;
                }

                const message = payload.message
                    || (payload.errors && Object.values(payload.errors).flat()[0])
                    || 'Failed to upload profile picture.';
                throw new Error(message);
            })
            .catch((error) => {
                if (typeof window.showProfileToast === 'function') {
                    window.showProfileToast(error.message || 'Network error while uploading. Please try again.', 'error');
                }
            })
            .finally(() => {
                if (typeof hideLoading === 'function') {
                    hideLoading();
                }
                if (confirmSubmitBtn) {
                    confirmSubmitBtn.disabled = false;
                    confirmSubmitBtn.textContent = 'Confirm & Save';
                }
            });
    }

    function handleAvatarClick(event) {
        event.preventDefault();
        if (!isUploadAllowed()) {
            showLockModal();
            return;
        }
        showUploadInstructionsModal();
    }

    uploadContinueBtn?.addEventListener('click', () => {
        window.closeProfilePictureUploadModal();
        openPhotoPicker();
    });

    uploadInstructionsModal?.addEventListener('click', (event) => {
        if (event.target === uploadInstructionsModal) {
            window.closeProfilePictureUploadModal();
        }
    });

    wrapper.addEventListener('click', handleAvatarClick);
    wrapper.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            handleAvatarClick(event);
        }
    });

    fileInput?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        event.target.value = '';

        if (!file) {
            return;
        }

        const validationError = validateFile(file);
        if (validationError) {
            if (typeof window.showProfileToast === 'function') {
                window.showProfileToast(validationError, 'error');
            }
            return;
        }

        closeModalsForUpload();
        showConfirmModal(file);
    });

    confirmCancelBtn?.addEventListener('click', () => {
        window.closeProfilePictureConfirmModal();
        openPhotoPicker();
    });

    confirmSubmitBtn?.addEventListener('click', () => {
        if (!pendingProfileFile) {
            window.closeProfilePictureConfirmModal();
            return;
        }
        const file = pendingProfileFile;
        uploadFile(file);
    });

    confirmModal?.addEventListener('click', (event) => {
        if (event.target === confirmModal) {
            window.closeProfilePictureConfirmModal();
        }
    });

    fileInput?.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    lockModal?.addEventListener('click', (event) => {
        if (event.target === lockModal) {
            window.closeProfilePictureLockModal();
        }
    });
}

function initProfileSupportingDocuments() {
    const section = document.getElementById('profileSupportingDocsSection');
    const uploadModal = document.getElementById('supportingDocsModal');
    if (!section || !uploadModal) {
        return;
    }

    const uploadUrl = section.dataset.uploadUrl || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const submitBtn = document.getElementById('profileSupportingDocSubmitBtn');
    const errorEl = document.getElementById('profileSupportingDocUploadError');
    const schoolIdPanel = document.getElementById('profileSchoolIdUpload');
    const clearancePanel = document.getElementById('profileBarangayClearanceUpload');
    const schoolIdInput = document.getElementById('profileSchoolIdFile');
    const clearanceInput = document.getElementById('profileBarangayClearanceFile');
    const docTypeRadios = uploadModal.querySelectorAll('input[name="profile_document_type"]');
    const maxBytes = 10 * 1024 * 1024;
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

    const previewConfig = {
        school_id: {
            input: schoolIdInput,
            preview: document.getElementById('profileSchoolIdPreview'),
            previewImg: document.getElementById('profileSchoolIdPreviewImg'),
            fileName: document.getElementById('profileSchoolIdFileName'),
            dropzone: schoolIdPanel?.querySelector('.kkp-wizard-dropzone'),
        },
        barangay_clearance: {
            input: clearanceInput,
            preview: document.getElementById('profileBarangayClearancePreview'),
            previewImg: document.getElementById('profileBarangayClearancePreviewImg'),
            fileName: document.getElementById('profileBarangayClearanceFileName'),
            dropzone: clearancePanel?.querySelector('.kkp-wizard-dropzone'),
        },
    };

    let selectedType = '';
    let pendingFile = null;

    function showError(message) {
        if (!errorEl) {
            return;
        }
        if (!message) {
            errorEl.hidden = true;
            errorEl.textContent = '';
            return;
        }
        errorEl.hidden = false;
        errorEl.textContent = message;
    }

    function selectedDocumentType() {
        const checked = uploadModal.querySelector('input[name="profile_document_type"]:checked');
        return checked ? checked.value : '';
    }

    function updatePanels() {
        const type = selectedDocumentType();
        selectedType = type;
        if (schoolIdPanel) schoolIdPanel.hidden = type !== 'school_id';
        if (clearancePanel) clearancePanel.hidden = type !== 'barangay_clearance';
        clearPendingFile();
        updateSubmitState();
    }

    function clearFilePreview(type) {
        const config = previewConfig[type];
        if (!config?.input) return;
        config.input.value = '';
        if (config.preview) config.preview.hidden = true;
        if (config.dropzone) config.dropzone.hidden = false;
        if (config.previewImg) config.previewImg.removeAttribute('src');
        if (config.fileName) config.fileName.textContent = '';
    }

    function clearPendingFile() {
        pendingFile = null;
        clearFilePreview('school_id');
        clearFilePreview('barangay_clearance');
        showError('');
    }

    function updateSubmitState() {
        if (!submitBtn) return;
        submitBtn.disabled = !(selectedDocumentType() && pendingFile);
    }

    function validateFile(file) {
        if (!file) {
            return 'Please select an image file.';
        }
        if (!allowedTypes.includes(file.type)) {
            return 'Only JPG and PNG images are allowed.';
        }
        if (file.size > maxBytes) {
            return 'Supporting document must be 10MB or smaller.';
        }
        return null;
    }

    function setPendingFile(type, file) {
        const validationError = validateFile(file);
        if (validationError) {
            showError(validationError);
            return;
        }

        const config = previewConfig[type];
        if (!config) return;

        pendingFile = file;
        showError('');

        if (type === 'school_id') {
            clearFilePreview('barangay_clearance');
        } else {
            clearFilePreview('school_id');
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            if (config.previewImg && event.target?.result) {
                config.previewImg.src = event.target.result;
            }
            if (config.fileName) {
                config.fileName.textContent = file.name;
            }
            if (config.preview) config.preview.hidden = false;
            if (config.dropzone) config.dropzone.hidden = true;
        };
        reader.readAsDataURL(file);
        updateSubmitState();
    }

    function resetUploadModal() {
        docTypeRadios.forEach((radio) => {
            radio.checked = false;
        });
        selectedType = '';
        if (schoolIdPanel) schoolIdPanel.hidden = true;
        if (clearancePanel) clearancePanel.hidden = true;
        clearPendingFile();
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Upload Document';
        }
    }

    function uploadDocument() {
        const documentType = selectedDocumentType();
        if (!documentType || !pendingFile || !uploadUrl) {
            showError('Please select a document type and file.');
            return;
        }

        const validationError = validateFile(pendingFile);
        if (validationError) {
            showError(validationError);
            return;
        }

        const formData = new FormData();
        formData.append('document_type', documentType);
        formData.append('_token', csrfToken);
        formData.append(documentType, pendingFile);

        if (typeof showLoading === 'function') {
            showLoading('Uploading supporting document');
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
        }

        fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
            credentials: 'same-origin',
        })
            .then(async (response) => {
                let payload = {};
                try {
                    payload = await response.json();
                } catch (error) {
                    throw new Error('Unexpected server response. Please try again.');
                }

                if (response.ok && payload.success) {
                    if (typeof window.showProfileToast === 'function') {
                        window.showProfileToast(payload.message || 'Supporting document uploaded successfully.', 'success');
                    }
                    window.closeSupportingDocsModal?.();
                    window.location.reload();
                    return;
                }

                const message = payload.message
                    || (payload.errors && Object.values(payload.errors).flat()[0])
                    || 'Failed to upload supporting document.';
                throw new Error(message);
            })
            .catch((error) => {
                showError(error.message || 'Network error while uploading. Please try again.');
                if (typeof window.showProfileToast === 'function') {
                    window.showProfileToast(error.message || 'Failed to upload supporting document.', 'error');
                }
            })
            .finally(() => {
                if (typeof hideLoading === 'function') {
                    hideLoading();
                }
                if (submitBtn) {
                    submitBtn.disabled = !(selectedDocumentType() && pendingFile);
                    submitBtn.textContent = 'Upload Document';
                }
            });
    }

    window.resetProfileSupportingDocUpload = resetUploadModal;

    docTypeRadios.forEach((radio) => {
        radio.addEventListener('change', updatePanels);
    });

    schoolIdInput?.addEventListener('change', function () {
        if (this.files?.[0]) {
            setPendingFile('school_id', this.files[0]);
        }
    });

    clearanceInput?.addEventListener('change', function () {
        if (this.files?.[0]) {
            setPendingFile('barangay_clearance', this.files[0]);
        }
    });

    uploadModal.querySelectorAll('[data-clear-profile-doc]').forEach((button) => {
        button.addEventListener('click', function () {
            const inputId = button.getAttribute('data-clear-profile-doc');
            if (inputId === 'profileSchoolIdFile') {
                clearFilePreview('school_id');
            } else if (inputId === 'profileBarangayClearanceFile') {
                clearFilePreview('barangay_clearance');
            }
            pendingFile = null;
            updateSubmitState();
        });
    });

    submitBtn?.addEventListener('click', uploadDocument);
}
