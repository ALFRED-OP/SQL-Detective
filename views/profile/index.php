<div class="profile-page">
    <div class="profile-header">
        <div class="profile-avatar">
            <?= strtoupper($user['display_name'][0]) ?>
        </div>
        <div class="profile-info">
            <h1><?= e($user['display_name']) ?></h1>
            <div class="profile-rank">
                <span class="rank-badge"><?= e($user['detective_rank']) ?></span>
                <span class="level-badge">Level <?= $user['level'] ?></span>
            </div>
            <div class="profile-xp">
                <div class="xp-bar">
                    <div class="xp-bar-fill" style="width: <?= $progressPercent ?>%"></div>
                </div>
                <span><?= number_format($user['xp']) ?> / <?= number_format($xpForNextLevel) ?> XP</span>
            </div>
        </div>
    </div>

    <nav class="profile-tabs" role="tablist">
        <a href="<?= route('profile') ?>" role="tab" class="profile-tab active" aria-selected="true">Overview</a>
        <a href="<?= route('profile.achievements') ?>" role="tab" class="profile-tab" aria-selected="false">Achievements</a>
        <a href="<?= route('profile.settings') ?>" role="tab" class="profile-tab" aria-selected="false">Settings</a>
    </nav>

    <div class="profile-content">
        <section class="profile-section">
            <div class="section-header">
                <h2>Statistics</h2>
            </div>
            <div class="stats-grid">
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
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $streak ?> days</div>
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
                        <div class="stat-value"><?= number_format($caseStats['total_xp_earned'] ?? 0) ?></div>
                        <div class="stat-label">Total XP Earned</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $challengeStats['challenges_solved'] ?? 0 ?></div>
                        <div class="stat-label">Challenges Solved</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $unlockedCount ?? 0 ?> / <?= $totalCount ?? 0 ?></div>
                        <div class="stat-label">Achievements</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="profile-section">
            <div class="section-header">
                <h2>Recent Investigations</h2>
                <a href="<?= route('cases') ?>" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <?php if (!empty($recentCases)): ?>
            <div class="cases-list">
                <?php foreach (array_slice($recentCases, 0, 5) as $case): ?>
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
                    <span class="xp-earned">+<?= $case['xp_earned'] ?> XP</span>
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

        <section class="profile-section">
            <div class="section-header">
                <h2>Recent Achievements</h2>
                <a href="<?= route('profile.achievements') ?>" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <?php if (!empty($achievements)): ?>
            <div class="achievements-grid">
                <?php foreach (array_slice($achievements, 0, 6) as $achievement): ?>
                <?php if (!empty($achievement['unlocked_at'])): ?>
                <div class="achievement-card unlocked">
                    <div class="achievement-icon"><?= e($achievement['icon']) ?></div>
                    <div class="achievement-info">
                        <h4><?= e($achievement['name']) ?></div>
                        <p><?= e($achievement['description']) ?></div>
                    </div>
                    <span class="achievement-date"><?= time_ago($achievement['unlocked_at']) ?></span>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                </svg>
                <p>No achievements unlocked yet</p>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>