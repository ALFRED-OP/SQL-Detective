<div class="admin-page">
    <div class="admin-header">
        <h1>Create New Case</h1>
        <p>Define a new investigation case</p>
    </div>

    <form class="admin-form" action="<?= route('admin.cases.store') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="case_code">Case Code *</label>
                <input type="text" id="case_code" name="case_code" required maxlength="20" placeholder="CASE-001">
                <small class="form-hint">Unique identifier (e.g., CASE-001)</small>
            </div>
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required maxlength="255" placeholder="The Missing Million">
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" required rows="3" placeholder="Brief description for case listings"></textarea>
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
                <label for="category">Category *</label>
                <input type="text" id="category" name="category" required maxlength="100" placeholder="Financial Crime">
            </div>
        </div>
        <div class="form-group">
            <label for="briefing">Briefing *</label>
            <textarea id="briefing" name="briefing" required rows="6" placeholder="Detailed case briefing with background story, crime details, and context"></textarea>
        </div>
        <div class="form-group">
            <label for="objective">Objective *</label>
            <textarea id="objective" name="objective" required rows="4" placeholder="What the detective needs to accomplish"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="xp_reward">XP Reward *</label>
                <input type="number" id="xp_reward" name="xp_reward" required min="1" value="100">
            </div>
            <div class="form-group">
                <label for="estimated_minutes">Estimated Time (minutes) *</label>
                <input type="number" id="estimated_minutes" name="estimated_minutes" required min="1" value="30">
            </div>
        </div>
        <div class="form-actions">
            <a href="<?= route('admin.cases') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Case</button>
        </div>
    </form>
</div>