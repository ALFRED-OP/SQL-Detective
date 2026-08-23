<div class="cases-page">
    <div class="page-header">
        <h1>Case Files</h1>
        <p>Browse and investigate active cases</p>
    </div>

    <div class="cases-filters">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <select name="difficulty" class="filter-select">
                    <option value="">All Difficulties</option>
                    <option value="beginner" <?= $filters['difficulty'] === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                    <option value="intermediate" <?= $filters['difficulty'] === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="advanced" <?= $filters['difficulty'] === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>
            <div class="filter-group">
                <select name="category" class="filter-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <select name="status" class="filter-select">
                    <option value="">All Cases</option>
                    <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="available" <?= $filters['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                </select>
            </div>
            <div class="filter-group">
                <input type="text" name="search" class="filter-search" placeholder="Search cases..." value="<?= e($filters['search']) ?>">
            </div>
        </form>
    </div>

    <?php if (!empty($cases)): ?>
    <div class="cases-grid">
        <?php foreach ($cases as $case): ?>
        <article class="case-card">
            <div class="case-header">
                <span class="case-code"><?= e($case['case_code']) ?></span>
                <span class="difficulty difficulty-<?= e($case['difficulty']) ?>"><?= ucfirst($case['difficulty']) ?></span>
            </div>
            <h3 class="case-title"><?= e($case['title']) ?></h3>
            <p class="case-description"><?= e($case['description']) ?></p>
            <div class="case-meta">
                <span class="case-category"><?= e($case['category']) ?></span>
                <span class="case-xp"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 11 12 17 6 11"/></span> <?= $case['xp_reward'] ?> XP</span>
                <span class="case-time"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></span> <?= $case['estimated_minutes'] ?> min</span>
                <span class="case-challenges"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></span> <?= $case['challenge_count'] ?> challenges</span>
            </div>
            <div class="case-progress">
                <?php if ($case['completed']): ?>
                <span class="badge badge-success">Completed</span>
                <span class="progress-text">+<?= $case['xp_earned'] ?> XP earned</span>
                <?php elseif ($case['progress_percentage'] > 0): ?>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $case['progress_percentage'] ?>%"></div>
                </div>
                <span class="progress-text"><?= round($case['progress_percentage']) ?>% complete</span>
                <?php else: ?>
                <span class="badge badge-gray">Not Started</span>
                <?php endif; ?>
            </div>
            <a href="<?= route('cases.show', ['case' => $case['id']]) ?>" class="btn btn-primary case-action">
                <?= $case['completed'] ? 'Review Case' : ($case['progress_percentage'] > 0 ? 'Continue' : 'Investigate') ?>
            </a>
        </article>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
        </svg>
        <p>No cases found matching your criteria</p>
        <button type="button" class="btn btn-secondary" onclick="document.querySelector('.filters-form').reset(); window.location.href='<?= route('cases') ?>'">Clear Filters</button>
    </div>
    <?php endif; ?>
</div>