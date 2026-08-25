<div class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <a href="<?= route('home') ?>" class="auth-logo">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
                    <polyline points="14,2 14,8 20,8"/>
                    <line x1="16" y1="12" x2="8" y2="12"/>
                    <line x1="16" y1="16" x2="8" y2="16"/>
                    <line x1="10" y1="19" x2="10" y2="19.01"/>
                </svg>
                <span>SQL Detective</span>
            </a>
            <h1>New Password</h1>
            <p>Choose a new password for your account</p>
        </div>

        <form id="reset-password-form" class="auth-form" action="<?= route('password.update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email" value="<?= e(old('email')) ?>">
            </div>
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Reset Password</button>
        </form>

        <div class="auth-footer">
            <p>Remember your password? <a href="<?= route('login') ?>">Sign in</a></p>
        </div>
    </div>
</div>
