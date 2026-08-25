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
            <h1>Verify Your Email</h1>
            <p>A verification link has been sent to your email address.</p>
        </div>

        <div class="auth-form">
            <div class="info-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <div>
                    <p><strong>Check your inbox</strong></p>
                    <p>We sent a verification link to <strong><?= e($_SESSION['user']['email'] ?? 'your email') ?></strong>. Click the link to activate your detective account.</p>
                </div>
            </div>

            <form action="<?= route('verification.verify') ?>" method="POST" id="verify-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-block">Resend Verification Email</button>
            </form>

            <div class="auth-footer">
                <p><a href="<?= route('dashboard') ?>">Skip for now</a></p>
            </div>
        </div>
    </div>
</div>
