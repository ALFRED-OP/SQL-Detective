<div class="admin-page">
    <div class="admin-header">
        <h1>Achievement Management</h1>
        <p>View all achievements</p>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Icon</th>
                    <th>Requirement</th>
                    <th>Value</th>
                    <th>XP Reward</th>
                    <th>Secret</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($achievements as $achievement): ?>
                <tr>
                    <td><?= e($achievement['name']) ?></td>
                    <td><?= e($achievement['description']) ?></td>
                    <td><?= e($achievement['icon']) ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $achievement['requirement_type'])) ?></td>
                    <td><?= $achievement['requirement_value'] ?></td>
                    <td><?= $achievement['xp_reward'] ?></td>
                    <td><?= $achievement['is_secret'] ? 'Yes' : 'No' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>