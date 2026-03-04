<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content logout-modal-content">
        <button type="button" class="modal-close-icon" onclick="closeLogoutModal()">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-body" style="padding: 48px 40px;">
            <div style="text-align: center;">
                <div class="logout-icon-wrapper">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <h2 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 24px 0 12px 0;">
                    Logout Confirmation
                </h2>
                <p style="font-size: 16px; color: #64748b; line-height: 1.6; margin: 0 0 32px 0;">
                    Are you sure you want to logout?
                </p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button type="button" class="btn-cancel-modern" onclick="closeLogoutModal()">
                        Cancel
                    </button>
                    <button type="button" class="btn-logout-modern" onclick="confirmLogout()">
                        Yes, Logout
                        <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Logout Modal Styles */
    .logout-modal-content {
        max-width: 480px;
        border-radius: 16px;
        position: relative;
    }

    .logout-icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        box-shadow: 0 8px 24px rgba(239, 68, 68, 0.25);
        animation: scaleIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .logout-icon-wrapper i {
        font-size: 36px;
        color: #dc2626;
    }

    .btn-logout-modern {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        min-width: 160px;
        justify-content: center;
    }

    .btn-logout-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }

    .btn-logout-modern i {
        transition: transform 0.3s ease;
    }

    .btn-logout-modern:hover i {
        transform: translateX(3px);
    }

    @media (max-width: 640px) {
        .logout-modal-content {
            max-width: 95%;
        }

        .logout-modal-content .modal-body {
            padding: 40px 24px !important;
        }

        .logout-icon-wrapper {
            width: 70px;
            height: 70px;
        }

        .logout-icon-wrapper i {
            font-size: 32px;
        }

        .btn-logout-modern {
            padding: 12px 24px;
            font-size: 14px;
            min-width: 140px;
        }
    }
</style>

<script>
    // Show logout confirmation modal
    function showLogoutModal() {
        document.getElementById('logoutModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    // Close logout modal
    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Confirm logout and submit form
    function confirmLogout() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Use fetch to logout, then replace location to prevent back navigation
        fetch("{{ route('logout') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        }).then(() => {
            // Use replace to prevent back navigation
            window.location.replace("{{ route('login') }}");
        }).catch(() => {
            // Fallback: use form submission
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('logout') }}";
            
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Close modal when clicking outside
    (function() {
        window.addEventListener('click', function(event) {
            const logoutModal = document.getElementById('logoutModal');
            if (event.target === logoutModal) {
                closeLogoutModal();
            }
        });
    })();
</script>
