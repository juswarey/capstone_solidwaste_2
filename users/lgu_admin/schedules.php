<?php
$pageTitle  = 'Collection Schedules';
$activePage = 'schedules';
include 'includes/auth.php';

$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$statuses   = ['Active', 'Inactive'];

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $id = (int) ($_POST['collection_schedule_id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM collection_schedule WHERE collection_schedule_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $success = 'Schedule deleted.';
    } else {
        $wasteCategoryId = (int) ($_POST['waste_category_id'] ?? 0);
        $bAdmin          = (int) ($_POST['barangay_admin_id'] ?? 0);
        $day             = $_POST['day_of_week'] ?? '';
        $status          = $_POST['schedule_status'] ?? 'Active';
        $id              = (int) ($_POST['collection_schedule_id'] ?? 0);

        if ($wasteCategoryId <= 0 || $bAdmin <= 0 || !in_array($day, $daysOfWeek, true)) {
            $errors[] = 'Waste category, barangay admin, and day of week are required.';
        } else {
            if ($formAction === 'update' && $id > 0) {
                $stmt = $conn->prepare('UPDATE collection_schedule SET waste_category_id=?, barangay_admin_id=?, day_of_week=?, schedule_status=? WHERE collection_schedule_id=?');
                $stmt->bind_param('iissi', $wasteCategoryId, $bAdmin, $day, $status, $id);
                $stmt->execute();
                $success = 'Schedule updated.';
            } else {
                $stmt = $conn->prepare('INSERT INTO collection_schedule (waste_category_id, barangay_admin_id, day_of_week, schedule_status) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('iiss', $wasteCategoryId, $bAdmin, $day, $status);
                $stmt->execute();
                $success = 'Schedule added.';
            }
        }
    }
}

$wasteCategories = $conn->query('SELECT waste_category_id, category_name FROM waste_category ORDER BY category_name');
$barangayAdmins  = $conn->query('SELECT barangay_admin_id, firstname, lastname FROM barangay_admin ORDER BY firstname');

$list = $conn->query('
    SELECT cs.*, wc.category_name, ba.firstname, ba.lastname
    FROM collection_schedule cs
    LEFT JOIN waste_category wc ON cs.waste_category_id = wc.waste_category_id
    LEFT JOIN barangay_admin ba ON cs.barangay_admin_id = ba.barangay_admin_id
    ORDER BY FIELD(cs.day_of_week, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday")
');

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare('SELECT * FROM collection_schedule WHERE collection_schedule_id = ?');
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

include 'includes/layout_top.php';
?>

<div class="content-header">
    <div>
        <h2>Collection Schedules</h2>
        <p>All collection schedules across every barangay.</p>
    </div>
</div>

<?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div><?php endif; ?>

<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-header">
        <h3><?= $editing ? 'Edit Schedule #' . (int) $editing['collection_schedule_id'] : 'Add a New Schedule' ?></h3>
        <?php if ($editing): ?><a href="schedules.php" class="panel-link">Cancel edit</a><?php endif; ?>
    </div>
    <form method="POST" action="schedules.php">
        <input type="hidden" name="form_action" value="<?= $editing ? 'update' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="collection_schedule_id" value="<?= (int) $editing['collection_schedule_id'] ?>"><?php endif; ?>
        <div class="form-group">
            <label>Barangay Admin</label>
            <select name="barangay_admin_id" class="form-control" required>
                <option value="">Select&hellip;</option>
                <?php $barangayAdmins->data_seek(0); while ($ba = $barangayAdmins->fetch_assoc()): ?>
                    <option value="<?= (int) $ba['barangay_admin_id'] ?>" <?= (isset($editing) && (int) $editing['barangay_admin_id'] === (int) $ba['barangay_admin_id']) ? 'selected' : '' ?>><?= htmlspecialchars($ba['firstname'] . ' ' . $ba['lastname']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Waste Category</label>
            <select name="waste_category_id" class="form-control" required>
                <option value="">Select&hellip;</option>
                <?php $wasteCategories->data_seek(0); while ($wc = $wasteCategories->fetch_assoc()): ?>
                    <option value="<?= (int) $wc['waste_category_id'] ?>" <?= (isset($editing) && (int) $editing['waste_category_id'] === (int) $wc['waste_category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($wc['category_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Day of the Week</label>
            <select name="day_of_week" class="form-control" required>
                <option value="">Select&hellip;</option>
                <?php foreach ($daysOfWeek as $day): ?>
                    <option value="<?= $day ?>" <?= (isset($editing) && $editing['day_of_week'] === $day) ? 'selected' : '' ?>><?= $day ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="schedule_status" class="form-control" required>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= $status ?>" <?= (isset($editing) && $editing['schedule_status'] === $status) ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Update Schedule' : 'Add Schedule' ?></button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Barangay Admin</th><th>Waste Category</th><th>Day</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($list->num_rows === 0): ?>
                <tr><td colspan="5">No schedules yet. Add one above.</td></tr>
            <?php else: while ($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars(trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')) ?: '—') ?></td>
                    <td><?= htmlspecialchars($row['category_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($row['day_of_week']) ?></td>
                    <td><span class="status-indicator <?= strtolower($row['schedule_status']) === 'active' ? 'active' : 'inactive' ?>"><?= htmlspecialchars($row['schedule_status']) ?></span></td>
                    <td>
                        <a href="schedules.php?edit=<?= (int) $row['collection_schedule_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <form method="POST" action="schedules.php" style="display:inline;" onsubmit="return confirm('Delete this schedule?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="collection_schedule_id" value="<?= (int) $row['collection_schedule_id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/layout_bottom.php'; ?>
