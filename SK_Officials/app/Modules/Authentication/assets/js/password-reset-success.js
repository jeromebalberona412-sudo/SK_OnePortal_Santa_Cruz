document.addEventListener('DOMContentLoaded', function () {
    const loginRoute = document.querySelector('.success-btn')?.getAttribute('href') || '/login';

    // Auto-redirect after 3 seconds
    setTimeout(function () {
        LoadingScreen.show('Redirecting', 'Taking you to login...');
        setTimeout(() => {
            window.location.href = loginRoute;
        }, 300);
    }, 3000);

    document.querySelector('.success-btn')?.addEventListener('click', function (e) {
        e.preventDefault();
        LoadingScreen.show('Redirecting', 'Taking you to login...');
        setTimeout(() => {
            window.location.href = this.href;
        }, 300);
    });
});
