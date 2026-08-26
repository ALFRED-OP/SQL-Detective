<div class="detective-workspace">
    <div class="workspace-header">
        <div class="case-info">
            <span class="case-code"><?= e($case['case_code']) ?></span>
            <h1><?= e($case['title']) ?></h1>
            <?php if ($currentChallenge): ?>
            <div class="current-challenge-badge">
                <span class="badge badge-primary">Current Challenge</span>
                <span><?= e($currentChallenge['title']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="workspace-actions">
            <a href="<?= route('cases.show', ['case' => $case['id']]) ?>" class="btn btn-ghost">Case Details</a>
            <a href="<?= route('dashboard') ?>" class="btn btn-ghost">Dashboard</a>
        </div>
    </div>

    <div class="workspace-layout">
        <aside class="sidebar-left" id="sidebar-left">
            <div class="sidebar-tabs" role="tablist">
                <button role="tab" class="sidebar-tab active" data-tab="database" aria-selected="true">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <ellipse cx="12" cy="5" rx="9" ry="3"/>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                    </svg>
                    Database
                </button>
                <button role="tab" class="sidebar-tab" data-tab="evidence" aria-selected="false">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    Evidence
                </button>
                <button role="tab" class="sidebar-tab" data-tab="challenges" aria-selected="false">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    Challenges
                </button>
            </div>

            <div class="sidebar-panels">
                <div role="tabpanel" class="sidebar-panel active" id="panel-database">
                    <div class="database-explorer">
                        <?php foreach ($databases as $database): ?>
                        <div class="database-tree">
                            <div class="tree-node database-node">
                                <span class="tree-toggle" data-toggle="true">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"/>
                                    </svg>
                                </span>
                                <span class="tree-icon">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <ellipse cx="12" cy="5" rx="9" ry="3"/>
                                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                                    </svg>
                                </span>
                                <span class="tree-label"><?= e($database['database_name']) ?></span>
                            </div>
                            <div class="tree-children">
                                <div class="tree-node folder-node">
                                    <span class="tree-toggle" data-toggle="true">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"/>
                                        </svg>
                                    </span>
                                    <span class="tree-icon">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                                        </svg>
                                    </span>
                                    <span class="tree-label">Tables</span>
                                </div>
                                <div class="tree-children tables-list">
                                    <?php foreach ($tables as $table): ?>
                                    <div class="tree-node table-node" data-table-id="<?= $table['id'] ?>">
                                        <span class="tree-icon">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 3h18v18H3z"/>
                                            </svg>
                                        </span>
                                        <span class="tree-label"><?= e($table['table_name']) ?></span>
                                        <span class="tree-badge"><?= count($table['columns']) ?> cols</span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div role="tabpanel" class="sidebar-panel" id="panel-evidence">
                    <div class="evidence-sidebar">
                        <?php
                        $evidenceDb = db();
                        $evidenceStmt = $evidenceDb->prepare("SELECT * FROM evidence WHERE case_id = ? ORDER BY importance DESC, id");
                        $evidenceStmt->execute([$case['id']]);
                        $evidence = $evidenceStmt->fetchAll();
                        ?>
                        <?php foreach ($evidence as $item): ?>
                        <div class="evidence-item" data-evidence-id="<?= $item['id'] ?>">
                            <div class="evidence-item-header">
                                <span class="evidence-type-badge type-<?= e($item['evidence_type']) ?>">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <?php if ($item['evidence_type'] === 'document'): ?>
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <?php elseif ($item['evidence_type'] === 'log'): ?>
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <?php else: ?>
                                        <circle cx="12" cy="12" r="10"/>
                                        <?php endif; ?>
                                    </svg>
                                    <?= ucfirst($item['evidence_type']) ?>
                                </span>
                                <span class="evidence-importance importance-<?= e($item['importance']) ?>"><?= ucfirst($item['importance']) ?></span>
                            </div>
                            <h4><?= e($item['title']) ?></h4>
                            <p><?= e(substr($item['description'], 0, 120)) ?><?= strlen($item['description']) > 120 ? '...' : '' ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div role="tabpanel" class="sidebar-panel" id="panel-challenges">
                    <div class="challenges-sidebar">
                        <?php foreach ($challenges as $index => $challenge): ?>
                        <div class="challenge-item-mini" data-challenge-id="<?= $challenge['id'] ?>">
                            <div class="challenge-mini-header">
                                <span class="challenge-mini-number"><?= $index + 1 ?></span>
                                <span class="challenge-mini-difficulty difficulty-<?= e($challenge['difficulty']) ?>"></span>
                            </div>
                            <h4><?= e($challenge['title']) ?></h4>
                            <p><?= e(substr($challenge['description'], 0, 100)) ?><?= strlen($challenge['description']) > 100 ? '...' : '' ?></p>
                            <div class="challenge-mini-meta">
                                <span><?= $challenge['xp_reward'] ?> XP</span>
                                <?php if ($progress && $progress['current_challenge_id'] == $challenge['id']): ?>
                                <span class="badge badge-primary">Current</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </aside>

        <main class="workspace-main">
            <div class="editor-toolbar">
                <div class="toolbar-left">
                    <button class="btn btn-primary" id="run-query" title="Run Query (Ctrl+Enter)">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                        Run Query
                    </button>
                    <button class="btn btn-ghost" id="clear-query" title="Clear">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Clear
                    </button>
                    <button class="btn btn-ghost" id="format-query" title="Format SQL">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <path d="M12 6v12M6 12h12"/>
                        </svg>
                        Format
                    </button>
                </div>
                <div class="toolbar-right">
                    <span class="editor-status" id="editor-status">Ready</span>
                </div>
            </div>

            <div class="editor-container">
                <textarea id="sql-editor" class="sql-editor" spellcheck="false" placeholder="Write your SQL query here..."><?= $currentChallenge ? "-- Challenge: {$currentChallenge['title']}\n-- {$currentChallenge['description']}\n\n" : '' ?></textarea>
                <div class="line-numbers" id="line-numbers"></div>
            </div>

            <div class="result-panel" id="result-panel">
                <div class="result-header">
                    <h3>Query Result</h3>
                    <div class="result-actions">
                        <button class="btn btn-ghost btn-sm" id="copy-result" title="Copy Result">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                        </button>
                        <button class="btn btn-ghost btn-sm" id="export-csv" title="Export CSV">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="result-content" id="result-content">
                    <div class="result-placeholder">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        <p>Execute a query to see results</p>
                    </div>
                </div>
            </div>
        </main>

        <aside class="sidebar-right" id="sidebar-right">
            <div class="sidebar-tabs" role="tablist">
                <button role="tab" class="sidebar-tab active" data-tab="schema" aria-selected="true">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h18v18H3z"/>
                    </svg>
                    Schema
                </button>
                <button role="tab" class="sidebar-tab" data-tab="relationships" aria-selected="false">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <circle cx="19" cy="5" r="3"/>
                        <circle cx="5" cy="19" r="3"/>
                        <line x1="13.6" y1="10.4" x2="17.4" y2="6.6"/>
                        <line x1="8.4" y1="20.6" x2="10.6" y2="18.4"/>
                    </svg>
                    Relations
                </button>
                <button role="tab" class="sidebar-tab" data-tab="history" aria-selected="false">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    History
                </button>
            </div>

            <div class="sidebar-panels">
                <div role="tabpanel" class="sidebar-panel active" id="panel-schema">
                    <div class="schema-viewer" id="schema-viewer">
                        <p class="text-muted">Select a table from the Database panel to view its schema</p>
                    </div>
                </div>

                <div role="tabpanel" class="sidebar-panel" id="panel-relationships">
                    <div class="relationships-viewer" id="relationships-viewer">
                        <?php if (!empty($relationships)): ?>
                        <div class="relationships-list">
                            <?php foreach ($relationships as $rel): ?>
                            <div class="relationship-item">
                                <span class="rel-from"><?= e($rel['from_table']) ?>.<?= e($rel['from_column']) ?></span>
                                <svg class="icon rel-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                    <polyline points="12 5 19 12 12 19"/>
                                </svg>
                                <span class="rel-to"><?= e($rel['to_table']) ?>.<?= e($rel['to_column']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">No relationships defined for this case</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div role="tabpanel" class="sidebar-panel" id="panel-history">
                    <div class="history-list" id="history-list">
                        <?php if (!empty($queryHistory)): ?>
                        <?php foreach ($queryHistory as $item): ?>
                        <div class="history-item" data-query="<?= e($item['query']) ?>">
                            <div class="history-status status-<?= $item['status'] ?>"></div>
                            <div class="history-query"><?= e(substr($item['query'], 0, 80)) ?><?= strlen($item['query']) > 80 ? '...' : '' ?></div>
                            <div class="history-meta">
                                <span><?= $item['execution_time_ms'] ?>ms</span>
                                <span><?= $item['rows_returned'] ?> rows</span>
                                <span><?= time_ago($item['created_at']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p class="text-muted">No query history yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>