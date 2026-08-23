<div class="admin-page">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <p>System administration and monitoring</p>
    </div>

    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-icon primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['total_cases'] ?></div>
                <div class="stat-label">Active Cases</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
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
            <div class="stat-icon info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['successful_attempts'] ?></div>
                <div class="stat-label">Successful Attempts</div>
            </div>
        </div>
    </div>

    <div class="admin-sections">
        <section class="admin-section">
            <div class="section-header">
                <h2>Recent Users</h2>
                <a href="<?= route('admin.users') ?>" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Level</th>
                            <th>XP</th>
                            <th>Cases</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar small"><?= strtoupper($user['display_name'][0]) ?></div>
                                    <span><?= e($user['display_name']) ?></span>
                                </div>
                            </td>
                            <td><?= e($user['email']) ?></td>
                            <td><span class="badge badge-<?= $user['role'] === 'admin' ? 'primary' : 'gray' ?>"><?= ucfirst($user['role']) ?></span></td>
                            <td>Level <?= $user['level'] ?></td>
                            <td><?= number_format($user['xp']) ?></td>
                            <td><?= $user['cases_completed'] ?></td>
                            <td><span class="status-badge status-<?= $user['status'] ?>"><?= ucfirst($user['status']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-section">
            <div class="section-header">
                <h2>Recent Cases</h2>
                <a href="<?= route('admin.cases') ?>" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Difficulty</th>
                            <th>Category</th>
                            <th>Challenges</th>
                            <th>XP</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentCases as $case): ?>
                        <tr>
                            <td><code><?= e($case['case_code']) ?></code></td>
                            <td><?= e($case['title']) ?></td>
                            <td><span class="difficulty difficulty-<?= e($case['difficulty']) ?>"><?= ucfirst($case['difficulty']) ?></span></td>
                            <td><?= e($case['category']) ?></td>
                            <td><?= $case['challenge_count'] ?></td>
                            <td><?= $case['xp_reward'] ?></td>
                            <td><span class="status-badge status-<?= $case['status'] ?>"><?= ucfirst($case['status']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($case['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>