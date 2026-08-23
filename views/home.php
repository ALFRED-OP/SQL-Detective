<div class="home-page">
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">SQL Detective</h1>
            <p class="hero-tagline">Investigate. Query. Discover the Truth.</p>
            <p class="hero-description">Solve fictional cases using real SQL skills. Learn database investigation through immersive storytelling.</p>
            <div class="hero-actions">
                <?php if (auth_check()): ?>
                    <a href="<?= route('cases') ?>" class="btn btn-primary btn-lg">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                        </svg>
                        Continue Investigation
                    </a>
                <?php else: ?>
                    <a href="<?= route('login') ?>" class="btn btn-primary btn-lg">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        Start Investigation
                    </a>
                    <a href="<?= route('register') ?>" class="btn btn-secondary btn-lg">Create Account</a>
                <?php endif; ?>
                <a href="<?= route('how-it-works') ?>" class="btn btn-ghost btn-lg">How It Works</a>
            </div>
        </div>
        <div class="hero-visual">
            <div class="code-preview">
                <div class="code-header">
                    <div class="code-dots">
                        <span></span><span></span><span></span>
                    </div>
                    <span class="code-filename">investigation.sql</span>
                </div>
                <pre class="code-content"><code>SELECT e.name, t.amount, t.timestamp
FROM employees e
JOIN transactions t ON t.employee_id = e.id
WHERE t.amount > 500000
  AND t.timestamp BETWEEN '2024-03-15 14:00' AND '2024-03-15 15:00'
ORDER BY t.timestamp;</code></pre>
                <div class="code-result">
                    <div class="result-header">Query Result (3 rows, 2.3ms)</div>
                    <table>
                        <thead><tr><th>name</th><th>amount</th><th>timestamp</th></tr></thead>
                        <tbody>
                            <tr><td>J. Morrison</td><td>₹10,00,000</td><td>14:32:15</td></tr>
                            <tr><td>S. Chen</td><td>₹7,50,000</td><td>14:45:22</td></tr>
                            <tr><td>R. Patel</td><td>₹6,00,000</td><td>14:51:08</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">01</div>
                    <h3>Read the Case</h3>
                    <p>Each investigation begins with a detailed briefing. Understand the crime, review the suspects, and examine the evidence.</p>
                </div>
                <div class="step">
                    <div class="step-number">02</div>
                    <h3>Inspect the Evidence</h3>
                    <p>Browse through documents, logs, records, and digital artifacts. Every piece of evidence contains clues for your queries.</p>
                </div>
                <div class="step">
                    <div class="step-number">03</div>
                    <h3>Explore the Database</h3>
                    <p>Navigate the fictional database schema. View tables, columns, relationships, and sample data to understand the structure.</p>
                </div>
                <div class="step">
                    <div class="step-number">04</div>
                    <h3>Write SQL</h3>
                    <p>Use the professional SQL editor to write queries. Execute, analyze results, and refine your investigation.</p>
                </div>
                <div class="step">
                    <div class="step-number">05</div>
                    <h3>Find the Truth</h3>
                    <p>Correlate data across tables, identify patterns, and solve the case. Earn XP, unlock achievements, and climb the ranks.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-cases">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Cases</h2>
                <a href="<?= route('cases') ?>" class="btn btn-ghost">View All Cases</a>
            </div>
            <div class="cases-grid">
                <?php foreach ($featuredCases as $case): ?>
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
                    </div>
                    <a href="<?= route('cases.show', ['case' => $case['id']]) ?>" class="btn btn-primary case-action">Investigate</a>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="sql-topics">
        <div class="container">
            <h2 class="section-title">SQL Topics Covered</h2>
            <div class="topics-grid">
                <?php foreach ($sqlTopics as $level => $topics): ?>
                <div class="topic-card">
                    <h3><?= e($level) ?></h3>
                    <div class="topic-tags">
                        <?php foreach ($topics as $topic): ?>
                        <span class="topic-tag"><?= e($topic) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="stats-bar">
        <div class="container">
            <div class="stat">
                <div class="stat-value"><?= number_format($stats['total_users'] ?? 0) ?></div>
                <div class="stat-label">Detectives</div>
            </div>
            <div class="stat">
                <div class="stat-value"><?= $stats['total_cases'] ?? 0 ?></div>
                <div class="stat-label">Cases</div>
            </div>
            <div class="stat">
                <div class="stat-value"><?= $stats['total_challenges'] ?? 0 ?></div>
                <div class="stat-label">Challenges</div>
            </div>
            <div class="stat">
                <div class="stat-value"><?= number_format($stats['total_xp'] ?? 0) ?></div>
                <div class="stat-label">Total XP Available</div>
            </div>
        </div>
    </section>
</div>