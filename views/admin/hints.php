<div class="admin-page">
    <div class="admin-header">
        <h1>Hint Management</h1>
        <p>View all hints across challenges</p>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Case</th>
                    <th>Challenge</th>
                    <th>Level</th>
                    <th>Hint Text</th>
                    <th>XP Penalty</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hints as $hint): ?>
                <tr>
                    <td><code><?= e($hint['case_code']) ?></code></td>
                    <td><?= e($hint['challenge_title']) ?></td>
                    <td>Level <?= $hint['hint_level'] ?></td>
                    <td><?= e(substr($hint['hint_text'], 0, 100)) ?><?= strlen($hint['hint_text']) > 100 ? '...' : '' ?></td>
                    <td><?= $hint['xp_penalty'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>