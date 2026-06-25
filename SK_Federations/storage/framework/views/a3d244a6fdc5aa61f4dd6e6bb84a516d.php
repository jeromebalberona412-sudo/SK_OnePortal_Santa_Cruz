

<?php $__env->startSection('title', 'My Profile - SK OnePortal'); ?>

<?php $__env->startPush('body-attributes'); ?>
class="profile-page" data-heartbeat-interval-ms="<?php echo e((int) config('sk_fed_auth.single_session.heartbeat_interval_seconds', 30) * 1000); ?>"
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
    <?php echo $__env->make('layout::styles-profile', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="profile-container">
        <div class="profile-page-header">
            <div>
                <h1 class="profile-page-title">Profile</h1>
                <p class="profile-page-sub">Manage your profile information and account settings.</p>
            </div>
        </div>

        <div class="profile-tab-bar">
            <button type="button" class="profile-tab active" id="tabBtnInfo" aria-controls="tabInfo" aria-selected="true">
                <i class="fa-solid fa-user"></i> Profile Information
            </button>
            <button type="button" class="profile-tab" id="tabBtnSettings" aria-controls="tabSettings" aria-selected="false">
                <i class="fa-solid fa-gear"></i> Account Settings
            </button>
        </div>

        <div class="profile-tab-content active" id="tabInfo">
            <?php if(session('status')): ?>
                <div class="success-message show"><?php echo e(session('status')); ?></div>
            <?php endif; ?>

            <section class="sk-chairman-section">
                <div class="official-card">
                    <div class="official-details">
                        <div class="profile-field-group">
                            <div class="profile-field-group-label profile-field-group-label--bold">
                                <i class="fa-solid fa-user"></i> Personal Information
                            </div>
                            <div class="profile-field-row" style="margin-bottom: 14px;">
                                <div class="profile-field">
                                    <label>First Name</label>
                                    <p><?php echo e($profile['first_name']); ?></p>
                                </div>
                                <div class="profile-field">
                                    <label>Middle Name</label>
                                    <p><?php echo e($profile['middle_name']); ?></p>
                                </div>
                                <div class="profile-field">
                                    <label>Last Name</label>
                                    <p><?php echo e($profile['last_name']); ?></p>
                                </div>
                                <div class="profile-field">
                                    <label>Suffix</label>
                                    <p><?php echo e($profile['suffix']); ?></p>
                                </div>
                            </div>
                            <div class="profile-field-row">
                                <div class="profile-field">
                                    <label><i class="fa-solid fa-venus-mars"></i> Sex</label>
                                    <p><?php echo e($profile['sex']); ?></p>
                                </div>
                                <div class="profile-field">
                                    <label><i class="fa-solid fa-calendar-day"></i> Birthdate</label>
                                    <p><?php echo e($profile['birthdate']); ?></p>
                                </div>
                                <div class="profile-field">
                                    <label><i class="fa-solid fa-mobile-screen"></i> Contact Number</label>
                                    <p><?php echo e($profile['contact_number']); ?></p>
                                </div>
                                <div class="profile-field">
                                    <label><i class="fa-solid fa-briefcase"></i> Position</label>
                                    <p><?php echo e($profile['position']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="profile-field-group">
                            <div class="profile-field-group-label profile-field-group-label--bold">
                                <i class="fa-solid fa-location-dot"></i> Address
                            </div>
                            <div class="profile-field-row">
                                <div class="profile-field">
                                    <label>Region</label>
                                    <p><?php echo e($profile['region']); ?></p>
                                </div>
                                <div class="profile-field">
                                    <label>Province</label>
                                    <p><?php echo e($profile['province']); ?></p>
                                </div>
                                <div class="profile-field">
                                    <label>Municipality</label>
                                    <p><?php echo e($profile['municipality']); ?></p>
                                </div>
                                <div class="profile-field">
                                    <label>Barangay</label>
                                    <p><?php echo e($profile['barangay']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="profile-field-group">
                            <div class="profile-field-group-label profile-field-group-label--bold">
                                <i class="fa-solid fa-calendar-check"></i> Term Information
                            </div>
                            <div class="profile-field-row">
                                <div class="profile-field">
                                    <label><i class="fa-solid fa-calendar-check"></i> Term Start</label>
                                    <p><?php echo e($profile['term_start']); ?></p>
                                </div>
                                <div class="profile-field">
                                    <label><i class="fa-solid fa-calendar-xmark"></i> Term End</label>
                                    <p><?php echo e($profile['term_end']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="profile-field-group">
                            <div class="profile-field-group-label profile-field-group-label--bold">
                                <i class="fa-solid fa-circle-user"></i> Account
                            </div>
                            <div class="profile-field-row">
                                <div class="profile-field">
                                    <label><i class="fa-solid fa-envelope"></i> Email</label>
                                    <p><?php echo e($profile['email']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="profile-tab-content" id="tabSettings">
            <section class="sk-account-settings-section">
                <div class="account-settings-card">
                    <div class="account-settings-card-header">
                        <h2 class="account-settings-card-title">
                            <i class="fa-solid fa-gear"></i> Account Settings
                        </h2>
                    </div>

                    <div class="account-settings-row">
                        <div class="account-settings-info">
                            <div class="account-settings-icon">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <div class="account-settings-label">Email Address</div>
                                <div class="account-settings-desc"><?php echo e($profile['email']); ?></div>
                            </div>
                        </div>
                        <a href="<?php echo e(route('change-email')); ?>" class="account-settings-btn">
                            <i class="fa-solid fa-envelope"></i>
                            Change Email
                        </a>
                    </div>

                    <div class="account-settings-divider"></div>

                    <div class="account-settings-row">
                        <div class="account-settings-info">
                            <div class="account-settings-icon">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <div>
                                <div class="account-settings-label">Password</div>
                                <div class="account-settings-desc">Update your account password securely.</div>
                            </div>
                        </div>
                        <a href="<?php echo e(route('change-password')); ?>" class="account-settings-btn">
                            <i class="fa-solid fa-key"></i>
                            Change Password
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php
        $profileJsVersion = @filemtime(app_path('Modules/Profile/assets/js/profile.js')) ?: time();
    ?>
    <script>
        (() => {
            const heartbeatMs = Number(document.body.dataset.heartbeatIntervalMs || 30000);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            let id = null;

            async function beat() {
                try {
                    await fetch("<?php echo e(route('skfed.heartbeat')); ?>", {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        credentials: 'same-origin',
                        body: JSON.stringify({}),
                    });
                } catch (_) {}
            }

            beat();
            id = setInterval(beat, heartbeatMs);
            window.addEventListener('beforeunload', () => clearInterval(id));
        })();
    </script>
    <script src="<?php echo e(url('/modules/profile/js/profile.js')); ?>?v=<?php echo e($profileJsVersion); ?>" defer></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Profile\Providers/../Views/profile.blade.php ENDPATH**/ ?>