<?php
$pageTitle  = 'Residents';
$activePage = 'residents';
include 'includes/auth.php';

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'delete') {
    $id = (int) ($_POST['resident_id'] ?? 0);
    $stmt = $conn->prepare('DELETE FROM resident WHERE resident_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $success = 'Resident account deleted.';
}

$list = $conn->query('SELECT * FROM resident ORDER BY last_name, first_name');

include 'includes/layout_top.php';
?>

<div class="content-header">
    <div>
        <h2>Residents</h2>
        <p>Registered resident accounts. Read-only aside from removing an account.</p>
    </div>
</div>

<?php if ($success): ?><div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div><?php endif; ?>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Contact</th><th>Home Address</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($list->num_rows === 0): ?>
                <tr><td colspan="5">No residents registered yet.</td></tr>
            <?php else: while ($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['contact_number'] ?? '—') ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth($row['home_address'] ?? '', 0, 60, '…')) ?></td>
                    <td>
                        <form method="POST" action="residents.php" style="display:inline;" onsubmit="return confirm('Delete this resident account?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="resident_id" value="<?= (int) $row['resident_id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/layout_bottom.php'; ?>
