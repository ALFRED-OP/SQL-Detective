<div class="admin-page">
    <div class="admin-header">
        <div>
            <h1>Case Management</h1>
            <p>Create and manage investigation cases</p>
        </div>
        <a href="<?= route('admin.cases.create') ?>" class="btn btn-primary">Create Case</a>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Title</th>
                    <th>Difficulty</th>
                    <th>Category</th>
                    <th>Challenges</th>
                    <th>XP</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cases as $case): ?>
                <tr>
                    <td><code><?= e($case['case_code']) ?></code></td>
                    <td><?= e($case['title']) ?></td>
                    <td><span class="difficulty difficulty-<?= e($case['difficulty']) ?>"><?= ucfirst($case['difficulty']) ?></span></td>
                    <td><?= e($case['category']) ?></td>
                    <td><?= $case['challenge_count'] ?></td>
                    <td><?= $case['xp_reward'] ?></td>
                    <td><?= $case['estimated_minutes'] ?> min</td>
                    <td><span class="status-badge status-<?= $case['status'] ?>"><?= ucfirst($case['status']) ?></span></td>
                    <td><?= date('M j, Y', strtotime($case['created_at'])) ?></td>
                    <td>
                        <a href="<?= route('admin.cases.edit', ['case' => $case['id']]) ?>" class="btn btn-ghost btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm delete-case" data-case-id="<?= $case['id'] ?>" data-case-code="<?= e($case['case_code']) ?>">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php if ($pagination['current_page'] > 1): ?>
        <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="btn btn-ghost btn-sm">Previous</a>
        <?php endif; ?>
        <span class="page-info">Page <?= $pagination['current_page'] ?> of <?= $pagination['last_page'] ?></span>
        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
        <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="btn btn-ghost btn-sm">Next</a>
        <?php endif; ?>
    </div>
</div>