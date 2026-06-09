document.addEventListener('DOMContentLoaded', function () {
    const successContent = document.querySelector('.success-content');
    const redirectUrl = successContent?.dataset.redirectUrl || '';

    if (redirectUrl) {
        if (typeof window.showLoading === 'function') {
            window.showLoading('Redirecting', 'Taking you to the dashboard...');
        }

        setTimeout(() => {
            window.location.replace(redirectUrl);
        }, 1200);
    }

    document.querySelectorAll('a').forEach(link => {
        if (link.href && !link.target) {
            link.addEventListener('click', function (event) {
                event.preventDefault();

                if (typeof window.showLoading === 'function') {
                    window.showLoading('Redirecting', 'Please wait...');
                }

                setTimeout(() => {
                    window.location.href = this.href;
                }, 300);
            });
        }
    });
});
