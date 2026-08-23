<!DOCTYPE html>
<html lang="en" data-theme="<?= $_COOKIE['theme'] ?? 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SQL Detective - Investigate. Query. Discover the Truth.">
    <title><?= e($title ?? 'SQL Detective') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/components.css') ?>">
</head>
<body>
    <div class="app-container">
        <?php if (auth_check()): ?>
        <header class="header">
            <div class="header-left">
                <a href="<?= route('dashboard') ?>" class="logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="12" x2="8" y2="12"/>
                        <line x1="16" y1="16" x2="8" y2="16"/>
                        <line x1="10" y1="19" x2="10" y2="19.01"/>
                    </svg>
                    <span class="logo-text">SQL Detective</span>
                </a>
            </div>
            <nav class="header-nav">
                <a href="<?= route('cases') ?>" class="nav-link">Cases</a>
                <a href="<?= route('leaderboard') ?>" class="nav-link">Leaderboard</a>
                <a href="<?= route('achievements') ?>" class="nav-link">Achievements</a>
            </nav>
            <div class="header-right">
                <button id="theme-toggle" class="btn btn-ghost" aria-label="Toggle theme" title="Toggle theme">
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>
                <div class="user-menu">
                    <button class="user-menu-toggle btn btn-ghost" aria-expanded="false" aria-haspopup="true">
                        <div class="user-avatar">
                            <?= strtoupper(auth_user()['display_name'][0] ?? '?') ?>
                        </div>
                        <span class="user-name"><?= e(auth_user()['display_name'] ?? 'Detective') ?></span>
                        <svg class="chevron-down" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="user-menu-dropdown" role="menu">
                        <div class="user-menu-header">
                            <div class="user-info">
                                <div class="user-avatar large"><?= strtoupper(auth_user()['display_name'][0] ?? '?') ?></div>
                                <div>
                                    <div class="user-display-name"><?= e(auth_user()['display_name'] ?? 'Detective') ?></div>
                                    <div class="user-rank"><?= e(auth_user()['detective_rank'] ?? 'SQL Rookie') ?></div>
                                </div>
                            </div>
                            <div class="user-xp">
                                <span>XP: <?= number_format(auth_user()['xp'] ?? 0) ?></span>
                                <span>Level <?= auth_user()['level'] ?? 1 ?></span>
                            </div>
                        </div>
                        <hr class="menu-divider">
                        <a href="<?= route('profile') ?>" class="dropdown-item" role="menuitem">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Profile
                        </a>
                        <a href="<?= route('profile.settings') ?>" class="dropdown-item" role="menuitem">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                            </svg>
                            Settings
                        </a>
                        <?php if (auth_user()['role'] === 'admin'): ?>
                        <hr class="menu-divider">
                        <a href="<?= route('admin.dashboard') ?>" class="dropdown-item" role="menuitem">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                            Admin Panel
                        </a>
                        <?php endif; ?>
                        <hr class="menu-divider">
                        <form action="<?= route('logout') ?>" method="POST" style="display: inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="dropdown-item dropdown-item-danger" role="menuitem">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        <?php endif; ?>

        <main class="main-content">
            <?php if (has_flash('message') || has_flash('error')): ?>
            <div class="flash-messages">
                <?php if (has_flash('message')): ?>
                <div class="flash flash-success">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <?= e(get_flash('message')) ?>
                </div>
                <?php endif; ?>
                <?php if (has_flash('error')): ?>
                <div class="flash flash-error">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <?= e(get_flash('error')) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?= $content ?>
        </main>

        <?php if (auth_check()): ?>
        <footer class="footer">
            <p>&copy; <?= date('Y') ?> SQL Detective. Built for NIELIT A-Level Major Project.</p>
        </footer>
        <?php endif; ?>
    </div>

    <script src="<?= asset('assets/js/app.js') ?>"></script>
    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
        <script src="<?= asset($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>