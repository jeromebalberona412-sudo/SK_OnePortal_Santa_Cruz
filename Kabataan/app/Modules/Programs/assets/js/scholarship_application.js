/**
 * Scholarship Application Form JavaScript
 * Form handling, age calculation, picture upload, e-signature, and validation
 */

(function () {
    'use strict';

    const modal = document.getElementById('scholarshipApplicationModal');
    const form = document.getElementById('scholarshipForm');
    const closeBtn = document.getElementById('scholarshipModalClose');
    const cancelBtn = document.getElementById('scholarshipModalCancel');
    const submitBtn = document.getElementById('scholarshipModalSubmit');
    const toast = document.getElementById('scholarshipToast');
    const toastMsg = document.getElementById('scholarshipToastMsg');

    // ── Modal Controls ──
    function closeModal() {
        modal.classList.remove('active');
    }

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // ── Age Calculation ──
    const dobInput = document.getElementById('scholDOB');
    const ageInput = document.getElementById('scholAge');
    if (dobInput && ageInput) {
        dobInput.addEventListener('change', function () {
            const bday = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - bday.getFullYear();
            const m = today.getMonth() - bday.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--;
            ageInput.value = age >= 0 ? age : '';
        });
    }

    // ── Picture Upload ──
    const pictureBox = document.getElementById('pictureBox');
    const pictureUpload = document.getElementById('pictureUpload');
    const picturePreview = document.getElementById('picturePreview');
    const pictureText = document.getElementById('pictureText');

    if (pictureBox && pictureUpload) {
        pictureBox.addEventListener('click', () => pictureUpload.click());
        pictureUpload.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    picturePreview.src = event.target.result;
                    picturePreview.style.display = 'block';
                    pictureText.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ── Set Today's Date ──
    const dateInput = document.getElementById('scholApplicationDate');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;
    }

    // ── Others Checkbox Toggle ──
    const othersCheck = document.getElementById('scholOthersCheck');
    const othersInput = document.getElementById('scholOthersInput');
    if (othersCheck && othersInput) {
        othersCheck.addEventListener('change', function () {
            othersInput.disabled = !this.checked;
            if (!this.checked) othersInput.value = '';
        });
    }

    // ── E-Signature Canvas ──
    const canvas = document.getElementById('scholSignaturePad');
    const clearBtn = document.getElementById('scholClearSignature');
    const signatureData = document.getElementById('scholSignatureData');

    if (canvas) {
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0, lastY = 0;

        // Set canvas size
        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.lineWidth = 2;
            ctx.strokeStyle = '#111827';
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        // Drawing functions
        function startDrawing(e) {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            lastX = (e.clientX || e.touches[0].clientX) - rect.left;
            lastY = (e.clientY || e.touches[0].clientY) - rect.top;
        }

        function draw(e) {
            if (!isDrawing) return;
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(x, y);
            ctx.stroke();
            lastX = x;
            lastY = y;
        }

        function stopDrawing() {
            isDrawing = false;
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                signatureData.value = '';
            });
        }
    }

    // ── Form Validation ──
    function validateForm() {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderBottomColor = '#ef4444';
                isValid = false;
            } else {
                field.style.borderBottomColor = '#374151';
            }
        });

        // Check if signature exists
        if (canvas) {
            const emptyCanvas = document.createElement('canvas');
            emptyCanvas.width = canvas.width;
            emptyCanvas.height = canvas.height;
            if (canvas.toDataURL() === emptyCanvas.toDataURL()) {
                showToast('Please provide your signature', 'error');
                return false;
            }
        }

        return isValid;
    }

    // ── Form Submission ──
    submitBtn.addEventListener('click', function () {
        if (!validateForm()) {
            showToast('Please fill in all required fields', 'error');
            return;
        }

        // Capture signature
        if (canvas && signatureData) {
            signatureData.value = canvas.toDataURL('image/png');
        }

        // Collect form data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        console.log('Form Data:', data);
        showToast('Application submitted successfully!', 'success');
        
        // Reset form after 1.5s
        setTimeout(() => {
            form.reset();
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            if (picturePreview) {
                picturePreview.style.display = 'none';
                pictureText.style.display = 'block';
            }
            closeModal();
        }, 1500);
    });

    // ── Toast Notification ──
    function showToast(message, type = 'success') {
        toastMsg.textContent = message;
        toast.className = 'schol-toast schol-toast-show';
        if (type === 'error') toast.classList.add('schol-toast-error');
        
        setTimeout(() => {
            toast.classList.remove('schol-toast-show', 'schol-toast-error');
        }, 3000);
    }

    window.showToast = showToast;
})();
