<?php
$pageTitle  = 'Routes';
$activePage = 'routes';
include 'includes/auth.php';
require_once __DIR__ . '/../../route_helpers.php';
ensureRouteScheduleColumns($conn);

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    try {
        if ($formAction === 'delete') {
            $id = (int) ($_POST['route_id'] ?? 0);
            $stmt = $conn->prepare('DELETE FROM route WHERE route_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $success = 'Route deleted.';
        } else {
            $path   = trim($_POST['path_coordinates'] ?? '');
            $bAdmin = (int) ($_POST['barangay_admin_id'] ?? 0);
            $id     = (int) ($_POST['route_id'] ?? 0);
            $zone   = trim($_POST['zone_name'] ?? '');
            $days   = routeDaysFromPost($_POST['collection_days'] ?? []);
            $start  = routeParseTime($_POST['start_time'] ?? '');
            $end    = routeParseTime($_POST['end_time'] ?? '');

            // Optional fields are stored as NULL when left blank.
            $pathDb = $path === '' ? null : $path;
            $zoneDb = $zone === '' ? null : $zone;
            $daysDb = $days === '' ? null : $days;

            if ($bAdmin <= 0) {
                $errors[] = 'Please choose a barangay admin.';
            } elseif ($scheduleError = routeScheduleError($start, $end)) {
                $errors[] = $scheduleError;
            } elseif ($formAction === 'update' && $id > 0) {
                $stmt = $conn->prepare('UPDATE route SET path_coordinates=?, barangay_admin_id=?, zone_name=?, collection_days=?, start_time=?, end_time=? WHERE route_id=?');
                $stmt->bind_param('sissssi', $pathDb, $bAdmin, $zoneDb, $daysDb, $start, $end, $id);
                $stmt->execute();
                $success = 'Route updated.';
            } else {
                $stmt = $conn->prepare('INSERT INTO route (path_coordinates, barangay_admin_id, zone_name, collection_days, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('sissss', $pathDb, $bAdmin, $zoneDb, $daysDb, $start, $end);
                $stmt->execute();
                $success = 'Route added.';
            }
        }
    } catch (mysqli_sql_exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}

$barangayAdmins = $conn->query('SELECT barangay_admin_id, firstname, lastname FROM barangay_admin ORDER BY firstname');

$list = $conn->query('
    SELECT r.*, ba.firstname, ba.lastname
    FROM route r
    LEFT JOIN barangay_admin ba ON r.barangay_admin_id = ba.barangay_admin_id
    ORDER BY r.zone_name IS NULL, LENGTH(r.zone_name), r.zone_name, r.route_id
');

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare('SELECT * FROM route WHERE route_id = ?');
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

include 'includes/layout_top.php';
?>

<div class="content-header">
    <div>
        <h2>Routes</h2>
        <p>Each route is a collection zone with its own days and time, assigned to a barangay admin.</p>
    </div>
</div>

<?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div><?php endif; ?>

<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-header">
        <h3><?= $editing ? 'Edit Route #' . (int) $editing['route_id'] : 'Add a New Route' ?></h3>
        <?php if ($editing): ?><a href="routes.php" class="panel-link">Cancel edit</a><?php endif; ?>
    </div>
    <form method="POST" action="routes.php">
        <input type="hidden" name="form_action" value="<?= $editing ? 'update' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="route_id" value="<?= (int) $editing['route_id'] ?>"><?php endif; ?>
        <div class="form-group">
            <label>Barangay Admin</label>
            <select name="barangay_admin_id" class="form-control" required>
                <option value="">Select a barangay admin&hellip;</option>
                <?php $barangayAdmins->data_seek(0); while ($ba = $barangayAdmins->fetch_assoc()): ?>
                    <option value="<?= (int) $ba['barangay_admin_id'] ?>" <?= (isset($editing) && (int) $editing['barangay_admin_id'] === (int) $ba['barangay_admin_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ba['firstname'] . ' ' . $ba['lastname']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <?php renderRouteScheduleFields($editing); ?>
        <div class="form-group">
            <label>Path Coordinates <span style="font-weight:normal;opacity:.7;">(optional)</span></label>
            <textarea name="path_coordinates" class="form-control" rows="3" placeholder="e.g. 8.2280,124.2452; 8.2295,124.2470"><?= htmlspecialchars($editing['path_coordinates'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Update Route' : 'Add Route' ?></button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Zone</th><th>Days</th><th>Time</th><th>Barangay Admin</th><th>Path Coordinates</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($list->num_rows === 0): ?>
                <tr><td colspan="6">No routes yet. Add one above.</td></tr>
            <?php else: while ($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars(routeLabel($row)) ?></strong><br><small>Route #<?= (int) $row['route_id'] ?></small></td>
                    <td><?= htmlspecialchars(routeDaysShort($row['collection_days']) ?: '—') ?></td>
                    <td><?= htmlspecialchars(routeTimeRange($row['start_time'], $row['end_time']) ?: '—') ?></td>
                    <td><?= htmlspecialchars(trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')) ?: '—') ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth((string) $row['path_coordinates'], 0, 40, '…') ?: '—') ?></td>
                    <td>
                        <a href="routes.php?edit=<?= (int) $row['route_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <form method="POST" action="routes.php" style="display:inline;" onsubmit="return confirm('Delete this route?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="route_id" value="<?= (int) $row['route_id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/layout_bottom.php'; ?>