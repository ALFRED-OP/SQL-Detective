<div class="case-detail-page">
    <div class="case-header-detail">
        <div>
            <span class="case-code"><?= e($case['case_code']) ?></span>
            <h1><?= e($case['title']) ?></h1>
        </div>
        <a href="<?= route('detective.workspace', ['case' => $case['id']]) ?>" class="btn btn-primary">Investigate</a>
    </div>

    <div class="case-tabs">
        <nav class="tabs">
            <a href="<?= route('cases.show', ['case' => $case['id']]) ?>" class="tab">Overview</a>
            <a href="<?= route('cases.briefing', ['case' => $case['id']]) ?>" class="tab active">Briefing</a>
            <a href="<?= route('cases.evidence', ['case' => $case['id']]) ?>" class="tab">Evidence</a>
            <a href="<?= route('cases.suspects', ['case' => $case['id']]) ?>" class="tab">Suspects</a>
        </nav>
    </div>

    <div class="briefing-content">
        <section class="briefing-section">
            <h2>Case Briefing</h2>
            <div class="briefing-text">
                <?= nl2br(e($case['briefing'])) ?>
            </div>
        </section>

        <section class="briefing-section">
            <h2>Investigation Objective</h2>
            <div class="objective-box">
                <?= nl2br(e($case['objective'])) ?>
            </div>
        </section>

        <?php if ($case['expected_result_description']): ?>
        <section class="briefing-section">
            <h2>Expected Result</h2>
            <p><?= nl2br(e($case['expected_result_description'])) ?></p>
        </section>
        <?php endif; ?>

        <section class="briefing-section">
            <h2>Case Metadata</h2>
            <dl class="metadata-list">
                <dt>Difficulty</dt>
                <dd><span class="difficulty difficulty-<?= e($case['difficulty']) ?>"><?= ucfirst($case['difficulty']) ?></span></dd>
                <dt>Category</dt>
                <dd><?= e($case['category']) ?></dd>
                <dt>Estimated Time</dt>
                <dd><?= $case['estimated_minutes'] ?> minutes</dd>
                <dt>Base XP Reward</dt>
                <dd><?= $case['xp_reward'] ?> XP</dd>
            </dl>
        </section>

        <div class="briefing-actions">
            <a href="<?= route('detective.workspace', ['case' => $case['id']]) ?>" class="btn btn-primary btn-lg">Begin Investigation</a>
        </div>
    </div>
</div>