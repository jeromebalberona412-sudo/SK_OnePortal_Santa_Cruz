// Keep logout as POST + CSRF; prompt user before submit.
document.addEventListener('DOMContentLoaded', function () {
	const logoutForms = document.querySelectorAll('form[action*="logout"]');

	logoutForms.forEach(function (form) {
		if (form.hasAttribute('data-logout-confirm-bound')) {
			return;
		}

		form.setAttribute('data-logout-confirm-bound', 'true');
		form.addEventListener('submit', function (event) {
			const confirmed = window.confirm('Are you sure you want to logout?');
			if (!confirmed) {
				event.preventDefault();
			}
		});
	});
});
