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
            <a href="<?= route('cases.evidence', ['case' => $case['id']]) ?>" class="tab active">Evidence</a>
            <a href="<?= route('cases.suspects', ['case' => $case['id']]) ?>" class="tab">Suspects</a>
        </nav>
    </div>

    <div class="evidence-list-page">
        <?php if (!empty($evidence)): ?>
        <?php foreach ($evidence as $item): ?>
        <article class="evidence-card">
            <div class="evidence-header">
                <span class="evidence-type-badge type-<?= e($item['evidence_type']) ?>">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <?php if ($item['evidence_type'] === 'document'): ?>
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                        <?php elseif ($item['evidence_type'] === 'log'): ?>
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="12" y1="18" x2="12" y2="12"/>
                        <line x1="9" y1="15" x2="15" y2="15"/>
                        <?php else: ?>
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                        <?php endif; ?>
                    </svg>
                    <?= ucfirst($item['evidence_type']) ?>
                </span>
                <span class="evidence-importance importance-<?= e($item['importance']) ?>"><?= ucfirst($item['importance']) ?></span>
            </div>
            <h3><?= e($item['title']) ?></h3>
            <p><?= nl2br(e($item['description'])) ?></p>
            <?php if ($item['evidence_data']): ?>
            <div class="evidence-data">
                <pre><code><?= e(json_encode(json_decode($item['evidence_data'], true), JSON_PRETTY_PRINT)) ?></code></pre>
            </div>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <p>No evidence available for this case</p>
        </div>
        <?php endif; ?>
    </div>
</div>