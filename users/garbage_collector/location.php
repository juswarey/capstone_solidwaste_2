<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle  = 'My Location';
$activePage = 'location';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $pingId = (int) ($_POST['location_history_id'] ?? 0);
        try {
            // Scoped to this collector so no one can delete another collector's pings.
            $stmt = $conn->prepare('DELETE FROM location_history WHERE location_history_id = ? AND garbage_collector_id = ?');
            $stmt->bind_param('ii', $pingId, $currentCollectorId);
            $stmt->execute();
            $success = 'Location entry deleted.';
        } catch (Exception $e) {
            $errors[] = 'Could not delete that entry.';
        }
    } elseif ($formAction === 'log') {
        $lat = $_POST['latitude'] ?? '';
        $lng = $_POST['longitude'] ?? '';

        if (!is_numeric($lat) || !is_numeric($lng)) {
            $errors[] = 'Could not read your location. Please allow location access and try again.';
        } else {
            try {
                $stmt = $conn->prepare('INSERT INTO location_history (garbage_collector_id, latitude, longitude) VALUES (?, ?, ?)');
                $stmt->bind_param('idd', $currentCollectorId, $lat, $lng);
                $stmt->execute();
                $success = 'Location logged.';
            } catch (Exception $e) {
                $errors[] = 'Could not save your location. Please try again.';
            }
        }
    }
}

// ---- Load this collector's location history ----
$pings = [];
try {
    $stmt = $conn->prepare('
        SELECT location_history_id, latitude, longitude, timestamp
        FROM location_history
        WHERE garbage_collector_id = ?
        ORDER BY timestamp DESC
        LIMIT 20
    ');
    $stmt->bind_param('i', $currentCollectorId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $pings[] = $row;
    }
} catch (Exception $e) {
    $errors[] = 'Could not load your location history.';
}

include __DIR__ . '/includes/admin_head.php';
?>

<div class="content-header">
    <div>
        <h2>My Location</h2>
        <p>Log your current position while you're out on your route.</p>
    </div>
</div>

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
        <h3>Log Current Location</h3>
    </div>
    <p id="locationStatus" style="margin-bottom:1rem;">Uses your device's GPS/browser location. You'll be asked to allow location access.</p>

    <form method="POST" action="location.php" id="logLocationForm">
        <input type="hidden" name="form_action" value="log">
        <input type="hidden" name="latitude" id="latitudeInput">
        <input type="hidden" name="longitude" id="longitudeInput">
        <button type="button" id="logLocationBtn" class="btn btn-primary">Log My Current Location</button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Logged At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pings)): ?>
                <tr><td colspan="4">No location history yet. Log your first position above.</td></tr>
            <?php else: ?>
                <?php foreach ($pings as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['latitude']) ?></td>
                        <td><?= htmlspecialchars($p['longitude']) ?></td>
                        <td><?= htmlspecialchars(date('M d, Y g:i:s A', strtotime($p['timestamp']))) ?></td>
                        <td>
                            <form method="POST" action="location.php" style="display:inline;" onsubmit="return confirm('Delete this location entry?');">
                                <input type="hidden" name="form_action" value="delete">
                                <input type="hidden" name="location_history_id" value="<?= (int) $p['location_history_id'] ?>">
                                <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('logLocationBtn');
    const status = document.getElementById('locationStatus');
    const form = document.getElementById('logLocationForm');
    const latInput = document.getElementById('latitudeInput');
    const lngInput = document.getElementById('longitudeInput');

    if (!btn) return;

    btn.addEventListener('click', () => {
        if (!navigator.geolocation) {
            status.textContent = 'Your browser does not support location access.';
            return;
        }
        btn.disabled = true;
        btn.textContent = 'Getting your location…';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                latInput.value = position.coords.latitude;
                lngInput.value = position.coords.longitude;
                form.submit();
            },
            () => {
                btn.disabled = false;
                btn.textContent = 'Log My Current Location';
                status.textContent = 'Could not get your location. Please allow location access in your browser and try again.';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });
});
</script>

<?php include __DIR__ . '/includes/admin_foot.php'; ?>
