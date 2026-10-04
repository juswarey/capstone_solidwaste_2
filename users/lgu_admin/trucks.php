<?php
$pageTitle  = 'Trucks';
$activePage = 'trucks';
include 'includes/auth.php';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $id = (int) ($_POST['truck_id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM truck WHERE truck_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $success = 'Truck deleted.';
    } else {
        $plate    = trim($_POST['plate_number'] ?? '');
        $capacity = $_POST['max_capacity_kg'] ?? '';
        $id       = (int) ($_POST['truck_id'] ?? 0);

        if ($plate === '' || $capacity === '') {
            $errors[] = 'Plate number and max capacity are required.';
        } else {
            if ($formAction === 'update' && $id > 0) {
                $stmt = $conn->prepare('UPDATE truck SET plate_number=?, max_capacity_kg=? WHERE truck_id=?');
                $stmt->bind_param('sdi', $plate, $capacity, $id);
                $stmt->execute();
                $success = 'Truck updated.';
            } else {
                $stmt = $conn->prepare('INSERT INTO truck (plate_number, max_capacity_kg) VALUES (?, ?)');
                $stmt->bind_param('sd', $plate, $capacity);
                $stmt->execute();
                $success = 'Truck added.';
            }
        }
    }
}

$list = $conn->query('SELECT * FROM truck ORDER BY plate_number');

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare('SELECT * FROM truck WHERE truck_id = ?');
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

include 'includes/layout_top.php';
?>

<div class="content-header">
    <div>
        <h2>Trucks</h2>
        <p>Manage the fleet trucks used across all barangays.</p>
    </div>
</div>

<?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div><?php endif; ?>

<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-header">
        <h3><?= $editing ? 'Edit Truck' : 'Add a New Truck' ?></h3>
        <?php if ($editing): ?><a href="trucks.php" class="panel-link">Cancel edit</a><?php endif; ?>
    </div>
    <form method="POST" action="trucks.php">
        <input type="hidden" name="form_action" value="<?= $editing ? 'update' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="truck_id" value="<?= (int) $editing['truck_id'] ?>"><?php endif; ?>
        <div class="form-group"><label>Plate Number</label><input type="text" name="plate_number" class="form-control" value="<?= htmlspecialchars($editing['plate_number'] ?? '') ?>" required></div>
        <div class="form-group"><label>Max Capacity (kg)</label><input type="number" step="0.01" name="max_capacity_kg" class="form-control" value="<?= htmlspecialchars($editing['max_capacity_kg'] ?? '') ?>" required></div>
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Update Truck' : 'Add Truck' ?></button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Plate Number</th><th>Max Capacity (kg)</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($list->num_rows === 0): ?>
                <tr><td colspan="3">No trucks yet. Add one above.</td></tr>
            <?php else: while ($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['plate_number']) ?></td>
                    <td><?= htmlspecialchars($row['max_capacity_kg']) ?></td>
                    <td>
                        <a href="trucks.php?edit=<?= (int) $row['truck_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <form method="POST" action="trucks.php" style="display:inline;" onsubmit="return confirm('Delete this truck?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="truck_id" value="<?= (int) $row['truck_id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/layout_bottom.php'; ?>
