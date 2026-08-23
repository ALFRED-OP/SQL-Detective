<div class="admin-page">
    <div class="admin-header">
        <h1>System Statistics</h1>
        <p>Overview of platform metrics</p>
    </div>

    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-icon primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['active_users'] ?></div>
                <div class="stat-label">Active Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['inactive_users'] ?></div>
                <div class="stat-label">Inactive Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['banned_users'] ?></div>
                <div class="stat-label">Banned Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['active_cases'] ?></div>
                <div class="stat-label">Active Cases</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['total_challenges'] ?></div>
                <div class="stat-label">Total Challenges</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['successful_attempts'] ?></div>
                <div class="stat-label">Successful Attempts</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['error_attempts'] ?></div>
                <div class="stat-label">Error Attempts</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['total_achievements_unlocked'] ?></div>
                <div class="stat-label">Achievements Unlocked</div>
            </div>
        </div>
    </div>

    <div class="admin-sections">
        <section class="admin-section">
            <h2>Cases by Difficulty</h2>
            <div class="chart-container">
                <?php foreach ($difficultyStats as $stat): ?>
                <div class="stat-bar">
                    <div class="stat-bar-label">
                        <span class="difficulty difficulty-<?= e($stat['difficulty']) ?>"><?= ucfirst($stat['difficulty']) ?></span>
                        <span><?= $stat['count'] ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $stats['active_cases'] > 0 ? round(($stat['count'] / $stats['active_cases']) * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-section">
            <h2>Cases by Category</h2>
            <div class="chart-container">
                <?php foreach ($categoryStats as $stat): ?>
                <div class="stat-bar">
                    <div class="stat-bar-label">
                        <span><?= e($stat['category']) ?></span>
                        <span><?= $stat['count'] ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $stats['active_cases'] > 0 ? round(($stat['count'] / $stats['active_cases']) * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-section">
            <h2>XP Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($stats['total_xp'] ?? 0) ?></div>
                        <div class="stat-label">Total XP (All Users)</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-value"><?= round($stats['avg_xp'] ?? 0) ?></div>
                        <div class="stat-label">Average XP per User</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>