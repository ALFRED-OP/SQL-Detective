<div class="admin-page">
    <div class="admin-header">
        <h1>Audit Logs</h1>
        <p>System audit trail</p>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>IP Hash</th>
                    <th>Metadata</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= $log['user_name'] ? e($log['user_name']) : '<span class="text-muted">System</span>' ?></td>
                    <td><code><?= e($log['action']) ?></code></td>
                    <td><code><?= substr($log['ip_hash'], 0, 16) ?>...</code></td>
                    <td>
                        <?php if ($log['metadata']): ?>
                        <pre class="log-metadata"><?= e(json_encode(json_decode($log['metadata'], true), JSON_PRETTY_PRINT)) ?></pre>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M j, Y H:i:s', strtotime($log['created_at'])) ?></td>
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