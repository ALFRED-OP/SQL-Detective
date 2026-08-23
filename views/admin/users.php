<div class="admin-page">
    <div class="admin-header">
        <div>
            <h1>User Management</h1>
            <p>Manage user accounts and permissions</p>
        </div>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Level</th>
                    <th>XP</th>
                    <th>Cases</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar small"><?= strtoupper($user['display_name'][0]) ?></div>
                            <span><?= e($user['display_name']) ?></span>
                            <small>@<?= e($user['username']) ?></small>
                        </div>
                    </td>
                    <td><?= e($user['email']) ?></td>
                    <td><span class="badge badge-<?= $user['role'] === 'admin' ? 'primary' : 'gray' ?>"><?= ucfirst($user['role']) ?></span></td>
                    <td>Level <?= $user['level'] ?></td>
                    <td><?= number_format($user['xp']) ?></td>
                    <td><?= $user['cases_completed'] ?></td>
                    <td><span class="status-badge status-<?= $user['status'] ?>"><?= ucfirst($user['status']) ?></span></td>
                    <td><?= $user['last_login_at'] ? date('M j, Y H:i', strtotime($user['last_login_at'])) : 'Never' ?></td>
                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <?php if ($user['id'] != $this->user()): ?>
                        <button class="btn btn-ghost btn-sm toggle-user" data-user-id="<?= $user['id'] ?>" data-status="<?= $user['status'] ?>">
                            <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                        <?php else: ?>
                        <span class="text-muted">Current User</span>
                        <?php endif; ?>
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