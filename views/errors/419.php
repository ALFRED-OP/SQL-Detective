<div class="error-page">
    <div class="error-container">
        <div class="error-code">419</div>
        <h1>SESSION EXPIRED</h1>
        <p>Your session has expired or the security token is invalid. Please refresh the page and try again.</p>
        <div class="error-actions">
            <button class="btn btn-primary" onclick="window.location.reload()">Refresh Page</button>
            <a href="<?= route('login') ?>" class="btn btn-secondary">Sign In Again</a>
        </div>
    </div>
</div>