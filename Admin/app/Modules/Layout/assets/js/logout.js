function getLogoutModal() {
	return document.getElementById('logoutModal');
}

function setModalVisibility(visible) {
	const modal = getLogoutModal();
	if (!modal) {
		return;
	}

	modal.hidden = !visible;
	modal.classList.toggle('show', visible);
	document.body.classList.toggle('modal-open', visible);

	if (visible) {
		const cancelButton = modal.querySelector('.btn-cancel-modern');
		if (cancelButton) {
			cancelButton.focus();
		}
	}
}

window.showLogoutModal = function () {
	setModalVisibility(true);
};

window.closeLogoutModal = function () {
	setModalVisibility(false);
};

window.confirmLogout = function () {
	const modal = getLogoutModal();
	if (!modal) {
		return;
	}

	const logoutUrl = modal.dataset.logoutUrl || '/logout';
	const loginUrl = modal.dataset.loginUrl || '/login';
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
	const confirmButton = modal.querySelector('.btn-logout-modern');

	if (confirmButton) {
		confirmButton.disabled = true;
	}

	fetch(logoutUrl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-CSRF-TOKEN': csrfToken,
			'Accept': 'application/json',
		},
		credentials: 'same-origin',
	})
		.then(function () {
			window.location.replace(loginUrl);
		})
		.catch(function () {
			const form = document.createElement('form');
			form.method = 'POST';
			form.action = logoutUrl;

			const csrfInput = document.createElement('input');
			csrfInput.type = 'hidden';
			csrfInput.name = '_token';
			csrfInput.value = csrfToken;
			form.appendChild(csrfInput);

			document.body.appendChild(form);
			form.submit();
		})
		.finally(function () {
			if (confirmButton) {
				confirmButton.disabled = false;
			}
		});
};

document.addEventListener('DOMContentLoaded', function () {
	const modal = getLogoutModal();
	if (!modal) {
		return;
	}

	modal.hidden = true;
	modal.classList.remove('show');

	modal.addEventListener('click', function (event) {
		if (event.target === modal) {
			window.closeLogoutModal();
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && modal.classList.contains('show')) {
			window.closeLogoutModal();
		}
	});
});
