<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle  = 'Waste Categories';
$activePage = 'waste';

$categories = [];
$errors = [];
try {
    $res = $conn->query('SELECT waste_category_id, category_name, disposal_guidelines FROM waste_category ORDER BY category_name');
    while ($res && $row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
} catch (Exception $e) {
    $errors[] = 'Could not load waste categories.';
}

include __DIR__ . '/includes/admin_head.php';
?>

<div class="content-header">
    <div>
        <h2>Waste Categories</h2>
        <p>Reference list of waste categories and disposal guidelines. Managed by the LGU Admin.</p>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (empty($categories)): ?>
    <div class="empty-state-card">
        <div class="empty-state-icon">♻️</div>
        <h3>No waste categories yet</h3>
        <p>Ask the LGU Admin to add waste categories.</p>
    </div>
<?php else: ?>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Disposal Guidelines</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['category_name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($cat['disposal_guidelines'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_foot.php'; ?>
