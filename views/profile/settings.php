<div class="profile-page">
    <div class="profile-header">
        <div class="profile-avatar">
            <?= strtoupper(auth_user()['display_name'][0]) ?>
        </div>
        <div class="profile-info">
            <h1><?= e(auth_user()['display_name']) ?></h1>
        </div>
    </div>

    <nav class="profile-tabs" role="tablist">
        <a href="<?= route('profile') ?>" role="tab" class="profile-tab">Overview</a>
        <a href="<?= route('profile.achievements') ?>" role="tab" class="profile-tab">Achievements</a>
        <a href="<?= route('profile.settings') ?>" role="tab" class="profile-tab active" aria-selected="true">Settings</a>
    </nav>

    <div class="settings-page">
        <section class="settings-section">
            <h2>Profile Settings</h2>
            <form id="profile-form" class="settings-form" action="<?= route('profile.update') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PATCH">
                <div class="form-group">
                    <label for="display_name">Display Name</label>
                    <input type="text" id="display_name" name="display_name" required value="<?= e(auth_user()['display_name']) ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?= e(auth_user()['email']) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </section>

        <section class="settings-section">
            <h2>Change Password</h2>
            <form id="password-form" class="settings-form" action="<?= route('profile.password') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PATCH">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                    <small class="form-hint">Min 8 chars, uppercase, lowercase, number</small>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </section>

        <section class="settings-section">
            <h2>Preferences</h2>
            <div class="settings-form">
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Dark Mode</h4>
                        <p>Automatically follows system preference</p>
                    </div>
                    <button type="button" id="theme-toggle-settings" class="btn btn-ghost">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        Toggle Theme
                    </button>
                </div>
            </div>
        </section>

        <section class="settings-section danger-zone">
            <h2>Danger Zone</h2>
            <div class="setting-item">
                <div class="setting-info">
                    <h4>Delete Account</h4>
                    <p>Permanently delete your account and all investigation data. This action cannot be undone.</p>
                </div>
                <button type="button" class="btn btn-danger" data-confirm="Are you sure you want to delete your account? This cannot be undone.">Delete Account</button>
            </div>
        </section>
    </div>
</div>