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
            <h1>Welcome Back, Detective</h1>
            <p>Sign in to continue your investigation</p>
        </div>

        <form id="login-form" class="auth-form" action="<?= route('login.post') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email" value="<?= e(old('email')) ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" value="1"> Remember me
                </label>
                <a href="<?= route('password.request') ?>" class="forgot-link">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="<?= route('register') ?>">Create one</a></p>
            <p class="demo-hint">Demo: demo@sqldetective.local / DemoPass123!</p>
        </div>
    </div>
</div>