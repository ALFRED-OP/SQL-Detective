<div class="admin-page">
    <div class="admin-header">
        <h1>Evidence Management</h1>
        <p>View all evidence across cases</p>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Case</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Importance</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evidence as $item): ?>
                <tr>
                    <td><code><?= e($item['case_code']) ?></code> <?= e($item['case_title']) ?></td>
                    <td><?= e($item['title']) ?></td>
                    <td><span class="badge badge-info"><?= ucfirst($item['evidence_type']) ?></span></td>
                    <td><span class="badge badge-<?= match($item['importance']) { 'critical' => 'error', 'high' => 'warning', 'medium' => 'info', default => 'gray' } ?>"><?= ucfirst($item['importance']) ?></span></td>
                    <td><?= date('M j, Y', strtotime($item['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>