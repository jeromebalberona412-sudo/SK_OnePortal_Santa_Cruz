document.addEventListener('DOMContentLoaded', function () {
    const successContent = document.querySelector('.success-content');
    const redirectUrl = successContent?.dataset.redirectUrl || '';

    if (redirectUrl) {
        setTimeout(() => {
            window.location.replace(redirectUrl);
        }, 1200);
    }
});
