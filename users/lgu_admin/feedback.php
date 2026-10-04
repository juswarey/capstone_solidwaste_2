<?php
$pageTitle  = 'Feedback Reports';
$activePage = 'feedback';
include 'includes/auth.php';

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'delete') {
    $id = (int) ($_POST['feedback_report_id'] ?? 0);
    $stmt = $conn->prepare('DELETE FROM feedback_report WHERE feedback_report_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $success = 'Feedback report deleted.';
}

$list = $conn->query('
    SELECT fr.*, r.first_name, r.last_name, r.email
    FROM feedback_report fr
    LEFT JOIN resident r ON fr.resident_id = r.resident_id
    ORDER BY fr.feedback_report_id DESC
');

include 'includes/layout_top.php';
?>

<div class="content-header">
    <div>
        <h2>Feedback Reports</h2>
        <p>Reports submitted by residents, with their location. Read-only aside from removing resolved reports.</p>
    </div>
</div>

<?php if ($success): ?><div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div><?php endif; ?>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Resident</th><th>Report Type</th><th>Location</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($list->num_rows === 0): ?>
                <tr><td colspan="4">No feedback reports yet.</td></tr>
            <?php else: while ($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'Unknown') ?><br><span class="simple-list-meta"><?= htmlspecialchars($row['email'] ?? '') ?></span></td>
                    <td><?= htmlspecialchars($row['report_type']) ?></td>
                    <td><?= htmlspecialchars($row['latitude']) ?>, <?= htmlspecialchars($row['longitude']) ?></td>
                    <td>
                        <form method="POST" action="feedback.php" style="display:inline;" onsubmit="return confirm('Delete this report?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="feedback_report_id" value="<?= (int) $row['feedback_report_id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/layout_bottom.php'; ?>
