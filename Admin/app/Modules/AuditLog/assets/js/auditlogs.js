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

    const outcomeFilter = document.getElementById('outcomeFilter');
    if (outcomeFilter && outcomeFilter.form) {
        outcomeFilter.addEventListener('change', function () {
            outcomeFilter.form.submit();
        });
    }

    const dismissButtons = document.querySelectorAll('[data-dismiss-alert]');
    dismissButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const alertItem = button.closest('.alert-item');
            if (!alertItem) {
                return;
            }

            alertItem.classList.add('is-dismissing');
            window.setTimeout(function () {
                alertItem.remove();
            }, 200);
        });
    });
});
