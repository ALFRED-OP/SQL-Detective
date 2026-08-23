<div class="dashboard-page">
    <div class="dashboard-header">
        <div>
            <h1>Welcome back, <?= e($user['display_name']) ?></h1>
            <p class="detective-rank"><?= e($user['detective_rank']) ?> • Level <?= $user['level'] ?></p>
        </div>
        <div class="xp-bar-container">
            <div class="xp-bar-label">
                <span>XP Progress</span>
                <span><?= number_format($user['xp']) ?> / <?= number_format($xpForNextLevel) ?> XP</span>
            </div>
            <div class="xp-bar">
                <div class="xp-bar-fill" style="width: <?= $progressPercent ?>%"></div>
            </div>
        </div>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $caseStats['completed_cases'] ?? 0 ?></div>
                <div class="stat-label">Cases Solved</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $caseStats['remaining_cases'] ?? 0 ?></div>
                <div class="stat-label">Cases Remaining</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4"/><path d="M12 18v4"/><path d="M4.93 4.93l2.83 2.83"/><path d="M16.24 16.24l2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="M4.93 19.07l2.83-2.83"/><path d="M16.24 7.76l2.83-2.83"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $streak ?> day<?= $streak !== 1 ? 's' : '' ?></div>
                <div class="stat-label">Current Streak</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="18 11 12 17 6 11"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">#<?= $leaderboardPos ?></div>
                <div class="stat-label">Leaderboard Rank</div>
            </div>
        </div>
    </div>

    <div class="dashboard-sections">
        <section class="dashboard-section">
            <div class="section-header">
                <h2>Continue Investigation</h2>
                <?php if (!empty($recentCases)): ?>
                <a href="<?= route('cases') ?>" class="btn btn-ghost btn-sm">View All</a>
                <?php endif; ?>
            </div>
            <?php if (!empty($recentCases)): ?>
            <div class="cases-list">
                <?php foreach (array_slice($recentCases, 0, 3) as $case): ?>
                <article class="case-list-item">
                    <div class="case-list-info">
                        <span class="case-code"><?= e($case['case_code']) ?></span>
                        <h3><?= e($case['title']) ?></h3>
                        <div class="case-meta">
                            <span class="difficulty difficulty-<?= e($case['difficulty']) ?>"><?= ucfirst($case['difficulty']) ?></span>
                            <span><?= $case['category'] ?></span>
                        </div>
                    </div>
                    <div class="case-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $case['progress_percentage'] ?>%"></div>
                        </div>
                        <span class="progress-text"><?= round($case['progress_percentage']) ?>%</span>
                    </div>
                    <a href="<?= route('detective.workspace', ['case' => $case['id']]) ?>" class="btn btn-primary btn-sm">
                        <?= $case['completed'] ? 'Review' : 'Continue' ?>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
                <p>No cases started yet</p>
                <a href="<?= route('cases') ?>" class="btn btn-primary">Browse Cases</a>
            </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-section">
            <div class="section-header">
                <h2>Recent Achievements</h2>
                <a href="<?= route('profile.achievements') ?>" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <?php if (!empty($recentAchievements)): ?>
            <div class="achievements-list">
                <?php foreach (array_slice($recentAchievements, 0, 4) as $achievement): ?>
                <div class="achievement-item unlocked">
                    <div class="achievement-icon"><?= e($achievement['icon']) ?></div>
                    <div class="achievement-info">
                        <h4><?= e($achievement['name']) ?></h4>
                        <p><?= e($achievement['description']) ?></p>
                    </div>
                    <span class="achievement-date"><?= time_ago($achievement['unlocked_at']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                </svg>
                <p>No achievements unlocked yet</p>
                <a href="<?= route('cases') ?>" class="btn btn-primary">Start Investigating</a>
            </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-section">
            <div class="section-header">
                <h2>Recent Queries</h2>
                <a href="<?= route('dashboard.recent-queries') ?>" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <?php if (!empty($recentQueries)): ?>
            <div class="queries-list">
                <?php foreach (array_slice($recentQueries, 0, 5) as $query): ?>
                <div class="query-item">
                    <div class="query-status status-<?= $query['status'] ?>">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <?php if ($query['status'] === 'success'): ?>
                            <polyline points="20 6 9 17 4 12"/>
                            <?php else: ?>
                            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                            <?php endif; ?>
                        </svg>
                    </div>
                    <div class="query-info">
                        <code><?= e(substr($query['query'], 0, 80)) ?><?= strlen($query['query']) > 80 ? '...' : '' ?></code>
                        <span class="query-meta"><?= e($query['case_title']) ?> • <?= time_ago($query['created_at']) ?></span>
                    </div>
                    <span class="query-time"><?= $query['execution_time_ms'] ?>ms</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
                </svg>
                <p>No queries executed yet</p>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>