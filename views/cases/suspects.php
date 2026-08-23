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
            <a href="<?= route('cases.briefing', ['case' => $case['id']]) ?>" class="tab">Briefing</a>
            <a href="<?= route('cases.evidence', ['case' => $case['id']]) ?>" class="tab">Evidence</a>
            <a href="<?= route('cases.suspects', ['case' => $case['id']]) ?>" class="tab active">Suspects</a>
        </nav>
    </div>

    <div class="suspects-list-page">
        <?php if (!empty($suspects)): ?>
        <?php foreach ($suspects as $suspect): ?>
        <article class="suspect-card">
            <div class="suspect-header">
                <div class="suspect-avatar">
                    <?= strtoupper($suspect['name'][0]) ?>
                </div>
                <div class="suspect-basic">
                    <h3><?= e($suspect['name']) ?></h3>
                    <div class="suspect-meta">
                        <?php if ($suspect['age']): ?>
                        <span>Age: <?= $suspect['age'] ?></span>
                        <?php endif; ?>
                        <?php if ($suspect['occupation']): ?>
                        <span><?= e($suspect['occupation']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="risk-level risk-<?= e($suspect['risk_level']) ?>"><?= ucfirst($suspect['risk_level']) ?> Risk</span>
            </div>
            <div class="suspect-details">
                <?php if ($suspect['description']): ?>
                <div class="suspect-field">
                    <strong>Profile:</strong>
                    <p><?= nl2br(e($suspect['description'])) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($suspect['alibi']): ?>
                <div class="suspect-field">
                    <strong>Alibi:</strong>
                    <p><?= nl2br(e($suspect['alibi'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <p>No suspects identified for this case</p>
        </div>
        <?php endif; ?>
    </div>
</div>