(function (global) {
    const DEFAULT_TOAST_ID = 'scholarshipToast';
    const DEFAULT_MSG_ID = 'scholarshipToastMsg';
    let hideTimer = null;

    function showScholarshipToast(message, toastId = DEFAULT_TOAST_ID) {
        const toast = document.getElementById(toastId);
        if (!toast) return;

        const msgEl = toast.querySelector('#scholarshipToastMsg')
            || toast.querySelector('[data-scholarship-toast-msg]');

        if (msgEl) {
            msgEl.textContent = message;
        } else {
            toast.textContent = message;
        }

        toast.style.display = 'flex';
        toast.style.background = '#22c55e';
        toast.classList.add('is-visible');

        clearTimeout(hideTimer);
        hideTimer = setTimeout(() => {
            toast.classList.remove('is-visible');
            setTimeout(() => {
                toast.style.display = 'none';
            }, 300);
        }, 3200);
    }

    global.showScholarshipToast = showScholarshipToast;
})(window);
