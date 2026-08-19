document.addEventListener('DOMContentLoaded', function () {
    const loginRoute = document.querySelector('.success-btn')?.getAttribute('href') || '/login';

    setTimeout(function () {
        window.location.href = loginRoute;
    }, 3000);

    document.querySelector('.success-btn')?.addEventListener('click', function () {
        window.location.href = this.href;
    });
});
