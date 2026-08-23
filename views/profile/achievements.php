<div class="profile-page">
    <div class="profile-header">
        <div class="profile-avatar">
            <?= strtoupper(auth_user()['display_name'][0]) ?>
        </div>
        <div class="profile-info">
            <h1><?= e(auth_user()['display_name']) ?></h1>
            <div class="profile-achievement-summary">
                <span><?= $unlockedCount ?> / <?= $totalCount ?> Unlocked</span>
            </div>
        </div>
    </div>

    <nav class="profile-tabs" role="tablist">
        <a href="<?= route('profile') ?>" role="tab" class="profile-tab">Overview</a>
        <a href="<?= route('profile.achievements') ?>" role="tab" class="profile-tab active" aria-selected="true">Achievements</a>
        <a href="<?= route('profile.settings') ?>" role="tab" class="profile-tab">Settings</a>
    </nav>

    <div class="achievements-page">
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
                <div class="achievement-card <?= $achievement['unlocked'] ? 'unlocked' : 'locked' ?>">
                    <div class="achievement-icon"><?= e($achievement['icon']) ?></div>
                    <div class="achievement-info">
                        <h4><?= e($achievement['name']) ?></h4>
                        <p><?= e($achievement['description']) ?></p>
                    </div>
                    <?php if ($achievement['unlocked']): ?>
                    <div class="achievement-unlocked">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>Unlocked</span>
                    </div>
                    <div class="achievement-date"><?= time_ago($achievement['unlocked_at']) ?></div>
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
</div>