<?php
$pageTitle  = 'Reports';
$activePage = 'reports';
include 'includes/auth.php';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $id = (int) ($_POST['report_id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM reports WHERE report_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $success = 'Report deleted.';
    } else {
        $reportType = trim($_POST['report_type'] ?? '');
        if ($reportType === '') {
            $errors[] = 'Report type is required.';
        } else {
            $stmt = $conn->prepare('INSERT INTO reports (lgu_admin_id, report_type, generated_timestamp) VALUES (?, ?, NOW())');
            $stmt->bind_param('is', $adminId, $reportType);
            $stmt->execute();
            $success = 'Report generated.';
        }
    }
}

$list = $conn->query('SELECT * FROM reports ORDER BY generated_timestamp DESC');

include 'includes/layout_top.php';
?>

<div class="content-header">
    <div>
        <h2>Reports</h2>
        <p>Generate and keep a log of system reports (collection summaries, fleet usage, feedback summaries, etc.).</p>
    </div>
</div>

<?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div><?php endif; ?>

<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-header"><h3>Generate a Report</h3></div>
    <form method="POST" action="reports.php">
        <input type="hidden" name="form_action" value="add">
        <div class="form-group">
            <label>Report Type</label>
            <input type="text" name="report_type" class="form-control" placeholder="e.g. Weekly Collection Summary" required>
        </div>
        <button type="submit" class="btn btn-primary">Generate Report</button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Report Type</th><th>Generated</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($list->num_rows === 0): ?>
                <tr><td colspan="3">No reports generated yet.</td></tr>
            <?php else: while ($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['report_type']) ?></td>
                    <td><?= $row['generated_timestamp'] ? date('M d, Y g:i A', strtotime($row['generated_timestamp'])) : '—' ?></td>
                    <td>
                        <form method="POST" action="reports.php" style="display:inline;" onsubmit="return confirm('Delete this report?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="report_id" value="<?= (int) $row['report_id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/layout_bottom.php'; ?>
