<div class="error-page">
    <div class="error-container">
        <div class="error-code">401</div>
        <h1>UNAUTHORIZED</h1>
        <p>Authentication required to access this investigation.</p>
        <div class="error-actions">
            <a href="<?= route('login') ?>" class="btn btn-primary">Sign In</a>
            <a href="<?= route('register') ?>" class="btn btn-secondary">Create Account</a>
        </div>
    </div>
</div>