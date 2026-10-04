<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../../route_helpers.php';
ensureRouteScheduleColumns($conn);

$pageTitle  = 'My Routes';
$activePage = 'routes';
$adminId    = $currentBarangayAdminId;

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $routeId = (int) ($_POST['route_id'] ?? 0);
        try {
            $stmt = $conn->prepare('DELETE FROM route WHERE route_id = ? AND barangay_admin_id = ?');
            $stmt->bind_param('ii', $routeId, $adminId);
            $stmt->execute();
            $success = 'Route deleted.';
        } catch (Exception $e) {
            $errors[] = 'Could not delete that route. It may still be referenced by a garbage collector.';
        }
    } else {
        $pathCoordinates = trim($_POST['path_coordinates'] ?? '');
        $routeId         = (int) ($_POST['route_id'] ?? 0);
        $zone            = trim($_POST['zone_name'] ?? '');
        $days            = routeDaysFromPost($_POST['collection_days'] ?? []);
        $start           = routeParseTime($_POST['start_time'] ?? '');
        $end             = routeParseTime($_POST['end_time'] ?? '');

        // Optional fields are stored as NULL when left blank.
        $pathDb = $pathCoordinates === '' ? null : $pathCoordinates;
        $zoneDb = $zone === '' ? null : $zone;
        $daysDb = $days === '' ? null : $days;

        if ($scheduleError = routeScheduleError($start, $end)) {
            $errors[] = $scheduleError;
        } else {
            try {
                if ($formAction === 'update' && $routeId > 0) {
                    $stmt = $conn->prepare('UPDATE route SET path_coordinates = ?, zone_name = ?, collection_days = ?, start_time = ?, end_time = ? WHERE route_id = ? AND barangay_admin_id = ?');
                    $stmt->bind_param('sssssii', $pathDb, $zoneDb, $daysDb, $start, $end, $routeId, $adminId);
                    $stmt->execute();
                    $success = 'Route updated.';
                } else {
                    $stmt = $conn->prepare('INSERT INTO route (path_coordinates, barangay_admin_id, zone_name, collection_days, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->bind_param('sissss', $pathDb, $adminId, $zoneDb, $daysDb, $start, $end);
                    $stmt->execute();
                    $success = 'Route added.';
                }
            } catch (Exception $e) {
                $errors[] = 'Could not save the route. Please try again.';
            }
        }
    }
}

// ---- Load this admin's routes ----
$routes = [];
try {
    $stmt = $conn->prepare('SELECT route_id, path_coordinates, zone_name, collection_days, start_time, end_time FROM route WHERE barangay_admin_id = ? ORDER BY zone_name IS NULL, LENGTH(zone_name), zone_name, route_id');
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $routes[] = $row;
    }
} catch (Exception $e) {
    $errors[] = 'Could not load your routes.';
}

// ---- If editing, load that route's current data ----
$editingRoute = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($routes as $r) {
        if ((int) $r['route_id'] === $editId) {
            $editingRoute = $r;
            break;
        }
    }
}

include __DIR__ . '/includes/admin_head.php';
?>

<div class="content-header">
    <div>
        <h2>My Routes</h2>
        <p>Collection zones for your barangay, each with its own days and time. Garbage collectors are linked to these routes.</p>
    </div>
</div>

<?php renderRouteMap($routes, 'Route Map', 'Your barangay\'s collection routes. Each route with path coordinates is drawn as a line with numbered stops.'); ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div>
<?php endif; ?>

<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-header">
        <h3><?= $editingRoute ? 'Edit Route #' . (int) $editingRoute['route_id'] : 'Add a New Route' ?></h3>
        <?php if ($editingRoute): ?><a href="routes.php" class="panel-link">Cancel edit</a><?php endif; ?>
    </div>

    <form method="POST" action="routes.php">
        <input type="hidden" name="form_action" value="<?= $editingRoute ? 'update' : 'add' ?>">
        <?php if ($editingRoute): ?>
            <input type="hidden" name="route_id" value="<?= (int) $editingRoute['route_id'] ?>">
        <?php endif; ?>

        <?php renderRouteScheduleFields($editingRoute); ?>

        <div class="form-group">
            <label for="path_coordinates">Path Coordinates <span style="font-weight:normal;opacity:.7;">(optional)</span></label>
            <textarea id="path_coordinates" name="path_coordinates" class="form-control" rows="3" placeholder="e.g. 8.2280,124.2452; 8.2295,124.2470; 8.2310,124.2481"><?= htmlspecialchars($editingRoute['path_coordinates'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><?= $editingRoute ? 'Update Route' : 'Add Route' ?></button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Zone</th>
                <th>Days</th>
                <th>Time</th>
                <th>Path Coordinates</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($routes)): ?>
                <tr><td colspan="5">No routes yet. Add your first one above.</td></tr>
            <?php else: ?>
                <?php foreach ($routes as $r): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars(routeLabel($r)) ?></strong><br><small>Route #<?= (int) $r['route_id'] ?></small></td>
                        <td><?= htmlspecialchars(routeDaysShort($r['collection_days']) ?: '—') ?></td>
                        <td><?= htmlspecialchars(routeTimeRange($r['start_time'], $r['end_time']) ?: '—') ?></td>
                        <td><?= htmlspecialchars(mb_strimwidth((string) $r['path_coordinates'], 0, 40, '…') ?: '—') ?></td>
                        <td>
                            <a href="routes.php?edit=<?= (int) $r['route_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="routes.php" style="display:inline;" onsubmit="return confirm('Delete this route? This cannot be undone.');">
                                <input type="hidden" name="form_action" value="delete">
                                <input type="hidden" name="route_id" value="<?= (int) $r['route_id'] ?>">
                                <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/admin_foot.php'; ?>