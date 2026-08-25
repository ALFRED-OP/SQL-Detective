/**
 * SQL Detective — Detective Workspace
 * Handles SQL editor, query execution, schema viewer, and investigation interface
 */
document.addEventListener('DOMContentLoaded', function() {
    initSQLWorkspace();
});

function initSQLWorkspace() {
    const editor = document.getElementById('sql-editor');
    if (!editor) return;

    initCodeEditor(editor);
    initSchemaViewer();
    initQueryHistory();
    initHints();
    initChallenges();
    initWorkspaceLayout();
}

/* ── Code Editor ─────────────────────────────────────────── */
function initCodeEditor(editor) {
    editor.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            e.preventDefault();
            const start = this.selectionStart;
            const end = this.selectionEnd;
            this.value = this.value.substring(0, start) + '  ' + this.value.substring(end);
            this.selectionStart = this.selectionEnd = start + 2;
        }
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            executeQuery();
        }
    });

    const runBtn = document.getElementById('run-query-btn');
    if (runBtn) {
        runBtn.addEventListener('click', executeQuery);
    }
}

/* ── Query Execution ─────────────────────────────────────── */
function executeQuery() {
    const editor = document.getElementById('sql-editor');
    const query = editor.value.trim();

    if (!query) {
        showQueryError('Please enter a SQL query.');
        return;
    }

    if (query.length > 10000) {
        showQueryError('Query too long. Maximum 10,000 characters allowed.');
        return;
    }

    const caseId = document.getElementById('case-id')?.value;
    const challengeId = document.getElementById('challenge-id')?.value;

    const runBtn = document.getElementById('run-query-btn');
    if (runBtn) {
        runBtn.disabled = true;
        runBtn.innerHTML = '<span class="spinner"></span> Executing...';
    }

    showQueryLoading();

    fetch('/api/query', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
        body: JSON.stringify({
            query: query,
            case_id: caseId,
            challenge_id: challengeId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (runBtn) {
            runBtn.disabled = false;
            runBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5,3 19,12 5,21"/></svg> Run Query';
        }

        if (data.error) {
            showQueryError(data.error);
        } else {
            showQueryResults(data);
            if (data.xp_earned !== undefined) {
                showXPEarned(data.xp_earned);
            }
            if (data.achievement) {
                showAchievementPopup(data.achievement);
            }
        }
    })
    .catch(err => {
        if (runBtn) {
            runBtn.disabled = false;
            runBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5,3 19,12 5,21"/></svg> Run Query';
        }
        showQueryError('Network error. Please check your connection and try again.');
    });
}

function showQueryLoading() {
    const resultsArea = document.getElementById('query-results');
    if (!resultsArea) return;
    resultsArea.innerHTML = `
        <div class="query-loading">
            <div class="loading-spinner"></div>
            <p>Executing query...</p>
        </div>
    `;
    resultsArea.style.display = 'block';
}

function showQueryError(message) {
    const resultsArea = document.getElementById('query-results');
    if (!resultsArea) return;
    resultsArea.innerHTML = `
        <div class="query-error">
            <div class="error-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <div class="error-message">${escapeHtml(message)}</div>
        </div>
    `;
    resultsArea.style.display = 'block';
}

function showQueryResults(data) {
    const resultsArea = document.getElementById('query-results');
    if (!resultsArea) return;

    if (!data.rows || data.rows.length === 0) {
        resultsArea.innerHTML = `
            <div class="query-success">
                <div class="success-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
                <div>Query executed successfully. ${data.row_count || 0} rows returned.</div>
                <div class="query-time">${data.execution_time || '< 1ms'}</div>
            </div>
        `;
        resultsArea.style.display = 'block';
        return;
    }

    const columns = data.columns || Object.keys(data.rows[0]);
    let tableHtml = `
        <div class="query-results-header">
            <span class="row-count">${data.row_count || data.rows.length} rows returned</span>
            <span class="query-time">${data.execution_time || '< 1ms'}</span>
        </div>
        <div class="results-table-wrapper">
            <table class="results-table">
                <thead><tr>
    `;
    columns.forEach(col => {
        tableHtml += `<th>${escapeHtml(col)}</th>`;
    });
    tableHtml += '</tr></thead><tbody>';

    const maxRows = Math.min(data.rows.length, 100);
    for (let i = 0; i < maxRows; i++) {
        tableHtml += '<tr>';
        columns.forEach(col => {
            const val = data.rows[i][col];
            const display = val === null ? '<span class="null-value">NULL</span>' : escapeHtml(String(val));
            tableHtml += `<td>${display}</td>`;
        });
        tableHtml += '</tr>';
    }

    if (data.rows.length > 100) {
        tableHtml += `<tr><td colspan="${columns.length}" class="truncated-row">... and ${data.rows.length - 100} more rows (showing first 100)</td></tr>`;
    }

    tableHtml += '</tbody></table></div>';
    resultsArea.innerHTML = tableHtml;
    resultsArea.style.display = 'block';
}

/* ── Schema Viewer ───────────────────────────────────────── */
function initSchemaViewer() {
    const toggle = document.getElementById('schema-toggle');
    const panel = document.getElementById('schema-panel');
    if (!toggle || !panel) return;

    toggle.addEventListener('click', function() {
        panel.classList.toggle('open');
        this.classList.toggle('active');
    });

    document.querySelectorAll('.schema-table-name').forEach(function(el) {
        el.addEventListener('click', function() {
            const tableName = this.dataset.table;
            loadTableSchema(tableName);
        });
    });
}

function loadTableSchema(tableName) {
    const detailArea = document.getElementById('schema-detail');
    if (!detailArea) return;

    const caseId = document.getElementById('case-id')?.value;
    detailArea.innerHTML = '<div class="loading-spinner small"></div>';

    fetch(`/api/schema?case_id=${caseId}&table=${encodeURIComponent(tableName)}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                detailArea.innerHTML = `<p class="error-text">${escapeHtml(data.error)}</p>`;
                return;
            }
            let html = `<h4 class="schema-table-title">${escapeHtml(tableName)}</h4>`;
            if (data.description) {
                html += `<p class="schema-table-desc">${escapeHtml(data.description)}</p>`;
            }
            html += '<table class="schema-columns-table"><thead><tr><th>Column</th><th>Type</th><th>Nullable</th><th>Key</th></tr></thead><tbody>';
            (data.columns || []).forEach(function(col) {
                html += `<tr><td><code>${escapeHtml(col.name)}</code></td><td>${escapeHtml(col.type)}</td><td>${col.nullable === 'YES' ? 'Yes' : 'No'}</td><td>${col.key || ''}</td></tr>`;
            });
            html += '</tbody></table>';
            if (data.sample_data && data.sample_data.length > 0) {
                html += '<p class="sample-label">Sample Data:</p><div class="sample-data-scroll"><table class="sample-data-table"><thead><tr>';
                Object.keys(data.sample_data[0]).forEach(function(k) { html += `<th>${escapeHtml(k)}</th>`; });
                html += '</tr></thead><tbody>';
                data.sample_data.forEach(function(row) {
                    html += '<tr>';
                    Object.values(row).forEach(function(v) {
                        html += `<td>${v === null ? '<span class="null-value">NULL</span>' : escapeHtml(String(v))}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</tbody></table></div>';
            }
            detailArea.innerHTML = html;
        })
        .catch(function() {
            detailArea.innerHTML = '<p class="error-text">Failed to load schema.</p>';
        });
}

/* ── Query History ───────────────────────────────────────── */
function initQueryHistory() {
    const toggle = document.getElementById('history-toggle');
    const panel = document.getElementById('query-history');
    if (!toggle || !panel) return;

    toggle.addEventListener('click', function() {
        panel.classList.toggle('open');
    });
}

function loadQueryIntoEditor(query) {
    const editor = document.getElementById('sql-editor');
    if (editor) {
        editor.value = query;
        editor.focus();
    }
}

/* ── Hints System ────────────────────────────────────────── */
function initHints() {
    document.querySelectorAll('.hint-request-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const hintId = this.dataset.hintId;
            const caseId = document.getElementById('case-id')?.value;
            requestHint(hintId, caseId);
        });
    });
}

function requestHint(hintId, caseId) {
    fetch('/api/hint', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
        body: JSON.stringify({ hint_id: hintId, case_id: caseId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            showToast(data.error, 'error');
            return;
        }
        const hintEl = document.getElementById(`hint-${hintId}`);
        if (hintEl) {
            hintEl.innerHTML = `<div class="hint-content revealed">${escapeHtml(data.hint_text)}</div>`;
            if (data.xp_cost !== undefined) {
                showToast(`Hint revealed! (-${data.xp_cost} XP)`, 'info');
            }
        }
    })
    .catch(function() {
        showToast('Failed to load hint.', 'error');
    });
}

/* ── Challenges ──────────────────────────────────────────── */
function initChallenges() {
    document.querySelectorAll('.challenge-submit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const challengeId = this.dataset.challengeId;
            submitChallenge(challengeId);
        });
    });
}

function submitChallenge(challengeId) {
    const query = document.getElementById('sql-editor')?.value?.trim();
    if (!query) {
        showToast('Write a SQL query first!', 'error');
        return;
    }

    const btn = document.querySelector(`[data-challenge-id="${challengeId}"]`);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner small"></span> Checking...';
    }

    fetch('/api/challenge/submit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
        body: JSON.stringify({ challenge_id: challengeId, query: query })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'Submit Answer';
        }

        if (data.correct) {
            showChallengeSuccess(data);
        } else {
            showChallengeFeedback(data);
        }
    })
    .catch(function() {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'Submit Answer';
        }
        showToast('Submission failed. Please try again.', 'error');
    });
}

function showChallengeSuccess(data) {
    const modal = document.getElementById('challenge-modal');
    if (modal) {
        modal.innerHTML = `
            <div class="challenge-success">
                <div class="success-icon large">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
                <h3>Case Solved!</h3>
                <p>Excellent detective work!</p>
                <div class="xp-reward">+${data.xp_earned} XP</div>
                ${data.level_up ? `<div class="level-up">Level Up! You are now Level ${data.new_level}</div>` : ''}
                ${data.achievement ? `<div class="achievement-unlocked">Achievement: ${escapeHtml(data.achievement.name)}</div>` : ''}
            </div>
        `;
        modal.classList.add('show');
    }
}

function showChallengeFeedback(data) {
    showToast(data.feedback || 'Not quite right. Try again!', 'error');
    if (data.hint) {
        const hintArea = document.getElementById('challenge-hint');
        if (hintArea) {
            hintArea.innerHTML = `<p class="hint-text">${escapeHtml(data.hint)}</p>`;
        }
    }
}

/* ── Workspace Layout ────────────────────────────────────── */
function initWorkspaceLayout() {
    const panels = document.querySelectorAll('.resizable-panel');
    let isResizing = false;
    let currentPanel = null;
    let startX, startY, startWidth, startHeight;

    panels.forEach(function(panel) {
        const handle = panel.querySelector('.resize-handle');
        if (!handle) return;

        handle.addEventListener('mousedown', function(e) {
            isResizing = true;
            currentPanel = panel;
            startX = e.clientX;
            startY = e.clientY;
            startWidth = panel.offsetWidth;
            startHeight = panel.offsetHeight;
            document.body.classList.add('resizing');
        });
    });

    document.addEventListener('mousemove', function(e) {
        if (!isResizing || !currentPanel) return;
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        if (currentPanel.classList.contains('resize-horizontal')) {
            currentPanel.style.width = Math.max(200, startWidth + dx) + 'px';
        } else {
            currentPanel.style.height = Math.max(100, startHeight + dy) + 'px';
        }
    });

    document.addEventListener('mouseup', function() {
        isResizing = false;
        currentPanel = null;
        document.body.classList.remove('resizing');
    });
}

/* ── Helpers ─────────────────────────────────────────────── */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type || 'info'}`;
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:12px 24px;border-radius:8px;color:#fff;z-index:10000;animation:slideIn 0.3s ease;font-size:14px;';
    const colors = { success: '#16a34a', error: '#dc2626', info: '#2563eb', warning: '#f59e0b' };
    toast.style.background = colors[type] || colors.info;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 4000);
}

function showXPEarned(xp) {
    showToast(`+${xp} XP earned!`, 'success');
}

function showAchievementPopup(achievement) {
    const popup = document.createElement('div');
    popup.className = 'achievement-popup';
    popup.innerHTML = `
        <div class="achievement-popup-content">
            <div class="achievement-icon">${achievement.icon || '🏆'}</div>
            <h4>Achievement Unlocked!</h4>
            <p>${escapeHtml(achievement.name)}</p>
        </div>
    `;
    popup.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:var(--color-surface);border:2px solid var(--color-warning);border-radius:16px;padding:2rem;z-index:10001;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);';
    document.body.appendChild(popup);
    setTimeout(function() { popup.remove(); }, 5000);
}

/* ── Editor toolbar (undo/redo/clear) ───────────────────── */
function clearEditor() {
    const editor = document.getElementById('sql-editor');
    if (editor) editor.value = '';
}

function formatSQL() {
    const editor = document.getElementById('sql-editor');
    if (!editor) return;
    let q = editor.value;
    q = q.replace(/\bSELECT\b/gi, 'SELECT\n  ')
         .replace(/\bFROM\b/gi, '\nFROM\n  ')
         .replace(/\bWHERE\b/gi, '\nWHERE\n  ')
         .replace(/\bAND\b/gi, '\n  AND')
         .replace(/\bOR\b/gi, '\n  OR')
         .replace(/\bJOIN\b/gi, '\nJOIN')
         .replace(/\bLEFT JOIN\b/gi, '\nLEFT JOIN')
         .replace(/\bRIGHT JOIN\b/gi, '\nRIGHT JOIN')
         .replace(/\bINNER JOIN\b/gi, '\nINNER JOIN')
         .replace(/\bGROUP BY\b/gi, '\nGROUP BY\n  ')
         .replace(/\bORDER BY\b/gi, '\nORDER BY\n  ')
         .replace(/\bHAVING\b/gi, '\nHAVING\n  ')
         .replace(/\bLIMIT\b/gi, '\nLIMIT')
         .replace(/\bINSERT INTO\b/gi, 'INSERT INTO')
         .replace(/\bVALUES\b/gi, '\nVALUES')
         .replace(/\bUPDATE\b/gi, 'UPDATE')
         .replace(/\bSET\b/gi, '\nSET\n  ')
         .replace(/\bDELETE FROM\b/gi, 'DELETE FROM')
         .replace(/;\s*$/gm, ';\n')
         .trim();
    editor.value = q;
}

function insertTemplate(template) {
    const editor = document.getElementById('sql-editor');
    if (editor) {
        editor.value = template;
        editor.focus();
    }
}