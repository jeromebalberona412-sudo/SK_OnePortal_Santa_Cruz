<?php $__env->startSection('title', 'Change Email'); ?>

<?php $__env->startSection('card-class', 'sk-fed-compact-card'); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(url('/modules/profile/js/change-email.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div id="ceStep1">
        <div class="card-header">
            <h2 class="card-title">Change Email</h2>
            <p class="card-subtitle">Enter your current email, new email address, and current password to request a change.</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="sk-alert sk-alert-error">
                <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <form class="sk-login-form sk-fed-auth-form" id="ceForm" action="<?php echo e(route('change-email.request')); ?>" method="POST" novalidate>
            <?php echo csrf_field(); ?>

            <div class="sk-form-group">
                <label for="ceCurrentEmail" class="sk-label">Current Email</label>
                <div class="input-wrapper">
                    <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <input type="email" id="ceCurrentEmail" name="current_email" class="sk-input" placeholder="Enter your current email" autocomplete="email" maxlength="100" value="<?php echo e(old('current_email', $user->email ?? '')); ?>" autofocus required>
                </div>
                <div class="sk-field-error" id="ceCurrentEmailError" hidden></div>
            </div>

            <div class="sk-form-group">
                <label for="ceNewEmail" class="sk-label">New Email Address</label>
                <div class="input-wrapper">
                    <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <input type="email" id="ceNewEmail" name="new_email" class="sk-input" placeholder="Enter your new email address" autocomplete="off" maxlength="100" value="<?php echo e(old('new_email')); ?>" required>
                </div>
                <div class="sk-field-error" id="ceNewEmailError" hidden></div>
            </div>

            <div class="sk-form-group">
                <label for="cePassword" class="sk-label">Current Password</label>
                <div class="password-wrapper ce-password-wrapper">
                    <input type="password" id="cePassword" name="password" class="sk-input password-input" placeholder="Enter your current password" autocomplete="current-password" maxlength="64" required>
                    <button type="button" class="toggle-password" data-target="cePassword" aria-label="Toggle password visibility" tabindex="-1">
                        <svg class="eye-icon eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="eye-icon eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
                <div class="sk-field-error" id="cePasswordError" hidden></div>
            </div>

            <button type="submit" class="sk-submit-btn sk-fed-primary-btn" id="ceSubmitBtn">
                <span id="ceBtnText">Send Verification Link</span>
                <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                </svg>
            </button>
        </form>

        <div class="youth-register-section ce-back-section">
            <p class="register-text">
                <a href="<?php echo e(route('profile')); ?>#settings" class="register-link">← Back to Profile</a>
            </p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('profile::layouts.account-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Profile\Providers/../Views/change-email.blade.php ENDPATH**/ ?>