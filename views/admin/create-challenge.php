<div class="admin-page">
    <div class="admin-header">
        <h1>Create New Challenge</h1>
        <p>Define a new challenge for an investigation case</p>
    </div>

    <form class="admin-form" action="<?= route('admin.challenges.store') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="case_id">Case *</label>
            <select id="case_id" name="case_id" required>
                <option value="">Select a case</option>
                <?php foreach ($cases as $c): ?>
                <option value="<?= $c['id'] ?>"><?= e($c['case_code']) ?> - <?= e($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" required maxlength="255" placeholder="Find transactions above 500,000">
        </div>
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" required rows="4" placeholder="Detailed challenge description"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="difficulty">Difficulty *</label>
                <select id="difficulty" name="difficulty" required>
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                </select>
            </div>
            <div class="form-group">
                <label for="challenge_type">Challenge Type</label>
                <select id="challenge_type" name="challenge_type">
                    <option value="query">SQL Query</option>
                    <option value="analysis">Data Analysis</option>
                    <option value="identification">Identification</option>
                    <option value="correlation">Correlation</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="xp_reward">XP Reward *</label>
                <input type="number" id="xp_reward" name="xp_reward" required min="1" value="50">
            </div>
            <div class="form-group">
                <label for="display_order">Display Order *</label>
                <input type="number" id="display_order" name="display_order" required min="0" value="0">
            </div>
        </div>
        <div class="form-group">
            <label for="expected_query_type">Expected Query Type</label>
            <input type="text" id="expected_query_type" name="expected_query_type" placeholder="SELECT with WHERE and JOIN">
        </div>
        <div class="form-group">
            <label for="validation_rules">Validation Rules (JSON)</label>
            <textarea id="validation_rules" name="validation_rules" rows="6" placeholder='{"min_rows": 1, "required_columns": ["employee_id", "amount"]}'></textarea>
            <small class="form-hint">JSON format for automated result validation. Leave empty for manual review.</small>
        </div>
        <div class="form-actions">
            <a href="<?= route('admin.challenges') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Challenge</button>
        </div>
    </form>
</div>