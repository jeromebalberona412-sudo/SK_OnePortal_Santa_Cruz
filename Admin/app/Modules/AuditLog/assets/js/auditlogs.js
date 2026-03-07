document.addEventListener('DOMContentLoaded', function () {
    const auditlogsBtn = document.querySelector('.nav-link.auditlogs-btn');
    if (auditlogsBtn) {
        auditlogsBtn.classList.add('active');
    }

    const eventFilter = document.getElementById('eventFilter');
    if (eventFilter && eventFilter.form) {
        eventFilter.addEventListener('change', function () {
            eventFilter.form.submit();
        });
    }
});
