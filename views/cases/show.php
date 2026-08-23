<div class="case-detail-page">
    <div class="case-header-detail">
        <div>
            <span class="case-code"><?= e($case['case_code']) ?></span>
            <h1><?= e($case['title']) ?></h1>
        </div>
        <div class="case-badges">
            <span class="difficulty difficulty-<?= e($case['difficulty']) ?>"><?= ucfirst($case['difficulty']) ?></span>
            <span class="badge badge-primary"><?= $case['challenge_count'] ?> Challenges</span>
            <span class="badge badge-info"><?= $case['category'] ?></span>
        </div>
    </div>

    <div class="case-tabs">
        <nav class="tabs">
            <a href="<?= route('cases.show', ['case' => $case['id']]) ?>" class="tab <?= !$activeTab || $activeTab === 'overview' ? 'active' : '' ?>">Overview</a>
            <a href="<?= route('cases.briefing', ['case' => $case['id']]) ?>" class="tab <?= $activeTab === 'briefing' ? 'active' : '' ?>">Briefing</a>
            <a href="<?= route('cases.evidence', ['case' => $case['id']]) ?>" class="tab <?= $activeTab === 'evidence' ? 'active' : '' ?>">Evidence</a>
            <a href="<?= route('cases.suspects', ['case' => $case['id']]) ?>" class="tab <?= $activeTab === 'suspects' ? 'active' : '' ?>">Suspects</a>
            <a href="<?= route('detective.workspace', ['case' => $case['id']]) ?>" class="tab tab-primary">Investigate</a>
        </nav>
    </div>

    <div class="case-content">
        <div class="case-main">
            <section class="case-section">
                <h2>Case Description</h2>
                <p><?= nl2br(e($case['description'])) ?></p>
            </section>

            <section class="case-section">
                <h2>Objective</h2>
                <p><?= nl2br(e($case['objective'])) ?></p>
            </section>

            <?php if ($case['expected_result_description']): ?>
            <section class="case-section">
                <h2>Expected Outcome</h2>
                <p><?= nl2br(e($case['expected_result_description'])) ?></p>
            </section>
            <?php endif; ?>

            <section class="case-section">
                <h2>Challenges</h2>
                <?php if (!empty($challenges)): ?>
                <div class="challenges-list">
                    <?php foreach ($challenges as $index => $challenge): ?>
                    <div class="challenge-item">
                        <div class="challenge-number"><?= $index + 1 ?></div>
                        <div class="challenge-info">
                            <h4><?= e($challenge['title']) ?></h4>
                            <p><?= nl2br(e($challenge['description'])) ?></p>
                            <div class="challenge-meta">
                                <span class="difficulty difficulty-<?= e($challenge['difficulty']) ?>"><?= ucfirst($challenge['difficulty']) ?></span>
                                <span><?= $challenge['xp_reward'] ?> XP</span>
                                <?php if ($challenge['expected_query_type']): ?>
                                <span class="query-type"><?= e($challenge['expected_query_type']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($progress && $progress['current_challenge_id'] == $challenge['id']): ?>
                        <span class="badge badge-primary">Current</span>
                        <?php elseif ($progress && $progress['completed']): ?>
                        <span class="badge badge-success">Solved</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">No challenges defined for this case yet.</p>
                <?php endif; ?>
            </section>

            <?php if ($progress && !$progress['completed']): ?>
            <section class="case-section">
                <h2>Your Progress</h2>
                <div class="progress-summary">
                    <div class="progress-bar-large">
                        <div class="progress-fill" style="width: <?= $progress['progress_percentage'] ?>%"></div>
                    </div>
                    <div class="progress-stats">
                        <span><?= round($progress['progress_percentage']) ?>% Complete</span>
                        <span><?= $progress['hints_used'] ?> Hints Used</span>
                        <span><?= $progress['xp_earned'] ?> XP Earned</span>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        </div>

        <aside class="case-sidebar">
            <div class="sidebar-card">
                <h3>Case Info</h3>
                <dl class="info-list">
                    <dt>Difficulty</dt>
                    <dd><span class="difficulty difficulty-<?= e($case['difficulty']) ?>"><?= ucfirst($case['difficulty']) ?></span></dd>
                    <dt>Category</dt>
                    <dd><?= e($case['category']) ?></dd>
                    <dt>Est. Time</dt>
                    <dd><?= $case['estimated_minutes'] ?> minutes</dd>
                    <dt>Total XP</dt>
                    <dd><?= $case['xp_reward'] ?> XP</dd>
                </dl>
            </div>

            <?php if (!empty($suspects)): ?>
            <div class="sidebar-card">
                <h3>Suspects (<?= count($suspects) ?>)</h3>
                <ul class="suspect-list">
                    <?php foreach ($suspects as $suspect): ?>
                    <li>
                        <strong><?= e($suspect['name']) ?></strong>
                        <span class="risk-level risk-<?= e($suspect['risk_level']) ?>"><?= ucfirst($suspect['risk_level']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= route('cases.suspects', ['case' => $case['id']]) ?>" class="btn btn-ghost btn-sm btn-block">View All</a>
            </div>
            <?php endif; ?>

            <?php if (!empty($evidence)): ?>
            <div class="sidebar-card">
                <h3>Evidence (<?= count($evidence) ?>)</h3>
                <ul class="evidence-list">
                    <?php foreach (array_slice($evidence, 0, 5) as $item): ?>
                    <li>
                        <span class="evidence-type"><?= ucfirst($item['evidence_type']) ?></span>
                        <strong><?= e($item['title']) ?></strong>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= route('cases.evidence', ['case' => $case['id']]) ?>" class="btn btn-ghost btn-sm btn-block">View All</a>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>