// Profile Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initProfileImageUpload();

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

function initProfileImageUpload() {
    const root = document.getElementById('profileImageUploadRoot');
    if (!root) return;

    const uploadUrl = root.dataset.uploadUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const profileAvatar = document.getElementById('profileAvatar');
    const previewImage = document.getElementById('profileImagePreview');
    const changePhotoBtn = document.getElementById('changePhotoBtn');
    const legacyFileInput = document.getElementById('photoUpload');
    const fileInput = document.getElementById('profileImageFileInput');
    const dropZone = document.getElementById('profileImageDropZone');
    const browseBtn = document.getElementById('profileImageBrowseBtn');
    const progressWrap = document.getElementById('profileImageProgress');
    const progressFill = document.getElementById('profileImageProgressFill');
    const progressText = document.getElementById('profileImageProgressText');
    const feedback = document.getElementById('profileImageFeedback');
    const lockNotice = document.getElementById('profileImageLockNotice');

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const maxBytes = 10 * 1024 * 1024;

    function setFeedback(message, type) {
        if (!feedback) return;
        feedback.textContent = message;
        feedback.classList.remove('is-hidden', 'is-error', 'is-success');
        feedback.classList.add(type === 'success' ? 'is-success' : 'is-error');
    }

    function clearFeedback() {
        if (!feedback) return;
        feedback.textContent = '';
        feedback.classList.add('is-hidden');
        feedback.classList.remove('is-error', 'is-success');
    }

    function setProgress(visible, percent, text) {
        if (!progressWrap || !progressFill || !progressText) return;
        progressWrap.classList.toggle('is-hidden', !visible);
        progressFill.style.width = `${percent}%`;
        progressText.textContent = text || 'Uploading profile picture...';
    }

    function updateAvatarImages(url) {
        [profileAvatar, previewImage].forEach((img) => {
            if (img) img.src = url;
        });

        document.querySelectorAll('.kabataan-header__avatar-btn img, .kabataan-header__dropdown-head img').forEach((img) => {
            img.src = url;
        });
    }

    function lockUpload(nextChangeDisplay) {
        root.dataset.canChange = '0';
        if (changePhotoBtn) {
            changePhotoBtn.disabled = true;
            changePhotoBtn.classList.add('is-disabled');
        }
        if (legacyFileInput) legacyFileInput.disabled = true;
        if (fileInput) fileInput.disabled = true;
        if (browseBtn) browseBtn.disabled = true;
        if (dropZone) dropZone.classList.add('is-disabled');

        if (lockNotice) {
            lockNotice.classList.remove('is-hidden');
            lockNotice.innerHTML = `Next profile picture update available on: <strong>${nextChangeDisplay}</strong>`;
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

    function isUploadAllowed() {
        return root.dataset.canChange === '1';
    }

    function uploadFile(file) {
        if (!isUploadAllowed()) {
            const nextChange = root.dataset.nextChange;
            setFeedback(
                nextChange
                    ? `You can change your profile picture again on ${nextChange}. Profile pictures can only be updated once every 30 days.`
                    : 'Profile picture updates are currently locked.',
                'error'
            );
            return;
        }

        const validationError = validateFile(file);
        if (validationError) {
            setFeedback(validationError, 'error');
            return;
        }

        clearFeedback();
        setProgress(true, 12, 'Validating image...');

        const formData = new FormData();
        formData.append('profile_picture', file);
        formData.append('_token', csrfToken);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.addEventListener('progress', (event) => {
            if (!event.lengthComputable) return;
            const percent = Math.max(15, Math.round((event.loaded / event.total) * 100));
            setProgress(true, percent, `Uploading profile picture... ${percent}%`);
        });

        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) return;

            setProgress(false, 0);

            let payload = null;
            try {
                payload = JSON.parse(xhr.responseText || '{}');
            } catch (error) {
                setFeedback('Unexpected server response. Please try again.', 'error');
                return;
            }

            if (xhr.status >= 200 && xhr.status < 300 && payload.success) {
                updateAvatarImages(payload.picture_url);
                setFeedback(payload.message || 'Profile picture uploaded successfully.', 'success');
                if (typeof window.showProfileToast === 'function') {
                    window.showProfileToast(payload.message || 'Profile picture uploaded successfully.', 'success');
                }
                if (payload.next_change_display) {
                    lockUpload(payload.next_change_display);
                }
                return;
            }

            const message = payload.message
                || (payload.errors && Object.values(payload.errors).flat()[0])
                || 'Failed to upload profile picture.';
            setFeedback(message, 'error');
            if (typeof window.showProfileToast === 'function') {
                window.showProfileToast(message, 'error');
            }
        };

        xhr.onerror = function() {
            setProgress(false, 0);
            setFeedback('Network error while uploading. Please try again.', 'error');
        };

        setProgress(true, 20, 'Uploading profile picture...');
        xhr.send(formData);
    }

    function openFilePicker() {
        if (!isUploadAllowed()) return;
        fileInput?.click();
    }

    browseBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        openFilePicker();
    });

    changePhotoBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        openFilePicker();
    });

    fileInput?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        if (file) uploadFile(file);
        event.target.value = '';
    });

    legacyFileInput?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        if (file) uploadFile(file);
        event.target.value = '';
    });

    if (dropZone) {
        dropZone.addEventListener('click', (event) => {
            if (event.target === browseBtn) return;
            openFilePicker();
        });

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                if (isUploadAllowed() || root.dataset.canChange === '1') {
                    dropZone.classList.add('is-dragover');
                }
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropZone.classList.remove('is-dragover');
            });
        });

        dropZone.addEventListener('drop', (event) => {
            const file = event.dataTransfer?.files?.[0];
            if (file) uploadFile(file);
        });
    }
}
