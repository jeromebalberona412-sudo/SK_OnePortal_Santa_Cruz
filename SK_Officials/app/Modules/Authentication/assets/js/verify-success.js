document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('a').forEach(link => {
        if (link.href && !link.target) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                LoadingScreen.show('Redirecting', 'Please wait...');
                setTimeout(() => {
                    window.location.href = this.href;
                }, 300);
            });
        }
    });
});
