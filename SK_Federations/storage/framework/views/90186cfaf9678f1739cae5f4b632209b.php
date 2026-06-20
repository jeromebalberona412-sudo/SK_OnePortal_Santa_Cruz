<?php $__env->startSection('title', 'Change Password'); ?>

<?php $__env->startSection('card-class', 'ce-verify-card sk-fed-compact-card cp-change-card'); ?>

<?php
    $pwMin = (int) config('sk_fed_auth.password_reset.password.min_length', 12);
    $pwMax = (int) config('sk_fed_auth.password_reset.password.max_length', 64);
?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(url('/modules/profile/js/change-password.js')); ?>?v=<?php echo e(time()); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card-header">
        <h2 class="card-title">Change Password</h2>
        <p class="card-subtitle">Confirm your account email and set a new password. We will send a verification link before the change takes effect.</p>
    </div>

    <?php if($errors->any()): ?>
        <div class="sk-alert sk-alert-error">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('password.change.update')); ?>" method="POST" class="sk-login-form sk-fed-auth-form" id="change-password-form" data-password-min-length="<?php echo e($pwMin); ?>" data-password-max-length="<?php echo e($pwMax); ?>" novalidate>
        <?php echo csrf_field(); ?>

        <div class="sk-form-group">
            <label for="cpEmail" class="sk-label">Account Email</label>
            <div class="input-wrapper">
                <input type="email" id="cpEmail" name="email" class="sk-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Enter your account email" autocomplete="email" maxlength="255" value="<?php echo e(old('email', $user->email ?? '')); ?>" required>
            </div>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="sk-field-error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <div class="sk-field-error" id="cpEmailClientError" hidden></div>
        </div>

        <div class="sk-form-group">
            <label for="password" class="sk-label">New Password</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" class="sk-input password-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Enter new password" autocomplete="new-password" minlength="<?php echo e($pwMin); ?>" maxlength="<?php echo e($pwMax); ?>" required>
                <button type="button" class="pw-toggle-btn" data-target="password" aria-label="Show password">
                    <svg class="pw-eye pw-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="pw-eye pw-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M1 1l22 22"/></svg>
                </button>
            </div>
            <ul class="password-rules" id="passwordRules" aria-live="polite" hidden>
                <li id="rule-length">At least <?php echo e($pwMin); ?> characters</li>
                <li id="rule-lowercase">At least one lowercase letter</li>
                <li id="rule-uppercase">At least one uppercase letter</li>
                <li id="rule-number">At least one number</li>
                <li id="rule-special">At least one special character</li>
            </ul>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="sk-field-error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <div class="sk-field-error" id="password-client-error" hidden></div>
        </div>

        <div class="sk-form-group">
            <label for="password_confirmation" class="sk-label">Confirm New Password</label>
            <div class="password-wrapper">
                <input type="password" id="password_confirmation" name="password_confirmation" class="sk-input password-input" placeholder="Re-enter new password" autocomplete="new-password" minlength="<?php echo e($pwMin); ?>" maxlength="<?php echo e($pwMax); ?>" required>
                <button type="button" class="pw-toggle-btn" data-target="password_confirmation" aria-label="Show password">
                    <svg class="pw-eye pw-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="pw-eye pw-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M1 1l22 22"/></svg>
                </button>
            </div>
            <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="sk-field-error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <button type="submit" class="sk-submit-btn sk-fed-primary-btn" id="cpSubmitBtn">
            <span id="cpBtnText">Send Email Verification</span>
        </button>
    </form>

    <div class="youth-register-section ce-back-section">
        <p class="register-text">
            <a href="<?php echo e(route('profile')); ?>#settings" class="register-link">← Back to Profile</a>
        </p>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('profile::layouts.account-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Profile\Providers/../Views/change-password.blade.php ENDPATH**/ ?>