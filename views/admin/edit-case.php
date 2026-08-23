<div class="admin-page">
    <div class="admin-header">
        <h1>Edit Case</h1>
        <p>Modify case: <?= e($case['case_code']) ?> - <?= e($case['title']) ?></p>
    </div>

    <form class="admin-form" action="<?= route('admin.cases.update', ['case' => $case['id']]) ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PATCH">
        <div class="form-row">
            <div class="form-group">
                <label for="case_code">Case Code *</label>
                <input type="text" id="case_code" name="case_code" required maxlength="20" value="<?= e($case['case_code']) ?>">
            </div>
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required maxlength="255" value="<?= e($case['title']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" required rows="3"><?= e($case['description']) ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="difficulty">Difficulty *</label>
                <select id="difficulty" name="difficulty" required>
                    <option value="beginner" <?= $case['difficulty'] === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                    <option value="intermediate" <?= $case['difficulty'] === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="advanced" <?= $case['difficulty'] === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>
            <div class="form-group">
                <label for="category">Category *</label>
                <input type="text" id="category" name="category" required maxlength="100" value="<?= e($case['category']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="briefing">Briefing *</label>
            <textarea id="briefing" name="briefing" required rows="6"><?= e($case['briefing']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="objective">Objective *</label>
            <textarea id="objective" name="objective" required rows="4"><?= e($case['objective']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="expected_result_description">Expected Result Description</label>
            <textarea id="expected_result_description" name="expected_result_description" rows="4"><?= e($case['expected_result_description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="xp_reward">XP Reward *</label>
                <input type="number" id="xp_reward" name="xp_reward" required min="1" value="<?= $case['xp_reward'] ?>">
            </div>
            <div class="form-group">
                <label for="estimated_minutes">Estimated Time (minutes) *</label>
                <input type="number" id="estimated_minutes" name="estimated_minutes" required min="1" value="<?= $case['estimated_minutes'] ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="status">Status *</label>
            <select id="status" name="status" required>
                <option value="active" <?= $case['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $case['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="archived" <?= $case['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
        <div class="form-actions">
            <a href="<?= route('admin.cases') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Case</button>
        </div>
    </form>
</div>