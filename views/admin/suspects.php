<div class="admin-page">
    <div class="admin-header">
        <h1>Suspect Management</h1>
        <p>View all suspects across cases</p>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Case</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Occupation</th>
                    <th>Risk Level</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suspects as $suspect): ?>
                <tr>
                    <td><code><?= e($suspect['case_code']) ?></code> <?= e($suspect['case_title']) ?></td>
                    <td><?= e($suspect['name']) ?></td>
                    <td><?= $suspect['age'] ?? 'N/A' ?></td>
                    <td><?= e($suspect['occupation'] ?? 'N/A') ?></td>
                    <td><span class="badge badge-<?= match($suspect['risk_level']) { 'critical' => 'error', 'high' => 'warning', 'medium' => 'info', default => 'gray' } ?>"><?= ucfirst($suspect['risk_level']) ?></span></td>
                    <td><?= date('M j, Y', strtotime($suspect['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>