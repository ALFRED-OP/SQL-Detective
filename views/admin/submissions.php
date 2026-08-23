<div class="admin-page">
    <div class="admin-header">
        <h1>Challenge Submissions</h1>
        <p>View all challenge attempts</p>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Case</th>
                    <th>Challenge</th>
                    <th>Status</th>
                    <th>Time (ms)</th>
                    <th>Rows</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $sub): ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar small"><?= strtoupper($sub['user_name'][0]) ?></div>
                            <span><?= e($sub['user_name']) ?></span>
                        </div>
                    </td>
                    <td><code><?= e($sub['case_code']) ?></code></td>
                    <td><?= e($sub['challenge_title']) ?></td>
                    <td><span class="badge badge-<?= match($sub['result_status']) { 'success' => 'success', 'error' => 'error', 'timeout' => 'warning', default => 'gray' } ?>"><?= ucfirst(str_replace('_', ' ', $sub['result_status'])) ?></span></td>
                    <td><?= $sub['execution_time_ms'] ?? 'N/A' ?></td>
                    <td><?= $sub['rows_returned'] ?? 0 ?></td>
                    <td><?= date('M j, Y H:i', strtotime($sub['created_at'])) ?></td>
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