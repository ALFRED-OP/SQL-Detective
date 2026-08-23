<div class="leaderboard-page">
    <div class="page-header">
        <h1>Leaderboard</h1>
        <p>Top detectives ranked by XP</p>
    </div>

    <?php if ($this->user() && $userRank): ?>
    <div class="user-rank-banner">
        <span class="rank-label">Your Rank</span>
        <span class="rank-value">#<?= $userRank ?></span>
    </div>
    <?php endif; ?>

    <div class="leaderboard-table-container">
        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Detective</th>
                    <th>Rank</th>
                    <th>Level</th>
                    <th>XP</th>
                    <th>Cases</th>
                    <th>Achievements</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaders as $index => $leader): ?>
                <tr class="<?= $this->user() && $leader['id'] == $this->user() ? 'current-user' : '' ?>">
                    <td class="rank-cell">
                        <?php if ($index < 3): ?>
                        <span class="medal medal-<?= ['gold', 'silver', 'bronze'][$index] ?>">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                            </svg>
                        </span>
                        <?php else: ?>
                        #<?= $index + 1 ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="leader-info">
                            <div class="leader-avatar">
                                <?= strtoupper($leader['display_name'][0]) ?>
                            </div>
                            <div>
                                <div class="leader-name"><?= e($leader['display_name']) ?></div>
                                <div class="leader-username">@<?= e($leader['username']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="rank-badge"><?= e($leader['detective_rank']) ?></span></td>
                    <td>Level <?= $leader['level'] ?></td>
                    <td class="xp-cell"><?= number_format($leader['xp']) ?></td>
                    <td><?= $leader['cases_completed'] ?></td>
                    <td><?= $leader['achievements_unlocked'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="leaderboard-pagination">
        <button class="btn btn-ghost" disabled>Previous</button>
        <span class="page-info">Page 1</span>
        <button class="btn btn-ghost" disabled>Next</button>
    </div>
</div>