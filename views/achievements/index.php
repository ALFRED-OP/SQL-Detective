<div class="achievements-page">
    <div class="page-header">
        <h1>Achievements</h1>
        <p>Unlock achievements by completing investigations and reaching milestones</p>
    </div>

    <?php if ($this->user()): ?>
    <div class="achievement-progress">
        <div class="progress-summary">
            <span class="unlocked-count"><?= count(array_filter($achievements, fn($a) => in_array($a['id'], $userUnlocked))) ?> / <?= count($achievements) ?></span>
            <span class="progress-text">Achievements Unlocked</span>
        </div>
        <div class="progress-bar">
            <?php 
            $unlockedCount = count(array_filter($achievements, fn($a) => in_array($a['id'], $userUnlocked)));
            $totalCount = count($achievements);
            $percent = $totalCount > 0 ? round(($unlockedCount / $totalCount) * 100) : 0;
            ?>
            <div class="progress-fill" style="width: <?= $percent ?>%"></div>
        </div>
    </div>
    <?php endif; ?>

    <?php 
    $categories = [];
    foreach ($achievements as $achievement) {
        $categories[$achievement['requirement_type']][] = $achievement;
    }
    ?>
    <?php foreach ($categories as $type => $list): ?>
    <section class="achievement-category">
        <h2 class="category-title"><?= ucfirst(str_replace('_', ' ', $type)) ?></h2>
        <div class="achievements-grid">
            <?php foreach ($list as $achievement): ?>
            <div class="achievement-card <?= in_array($achievement['id'], $userUnlocked) ? 'unlocked' : 'locked' ?>">
                <div class="achievement-icon"><?= e($achievement['icon']) ?></div>
                <div class="achievement-info">
                    <h4><?= e($achievement['name']) ?></h4>
                    <p><?= e($achievement['description']) ?></p>
                </div>
                <?php if (in_array($achievement['id'], $userUnlocked)): ?>
                <div class="achievement-unlocked">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>Unlocked</span>
                </div>
                <?php else: ?>
                <div class="achievement-locked">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span>Locked</span>
                </div>
                <div class="achievement-requirement">
                    Requirement: <?= $achievement['requirement_value'] ?> <?= ucfirst(str_replace('_', ' ', $achievement['requirement_type'])) ?>
                </div>
                <?php endif; ?>
                <?php if ($achievement['xp_reward'] > 0): ?>
                <div class="achievement-reward">+<?= $achievement['xp_reward'] ?> XP</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>
</div>