<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle  = 'My Route';
$activePage = 'route';

// Parse "lat,long; lat,long; ..." into a list of stops for a friendlier table.
$stops = [];
if ($currentCollectorPathCoordinates) {
    $pairs = array_filter(array_map('trim', explode(';', $currentCollectorPathCoordinates)));
    foreach ($pairs as $i => $pair) {
        $parts = array_map('trim', explode(',', $pair));
        if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
            $stops[] = ['seq' => $i + 1, 'lat' => $parts[0], 'lng' => $parts[1]];
        }
    }
}

include __DIR__ . '/includes/admin_head.php';
?>

<div class="content-header">
    <div>
        <h2>My Route</h2>
        <p>The collection route currently assigned to you. Only your Barangay or LGU Admin can change this.</p>
    </div>
</div>

<?php renderRouteMap(
    $currentCollectorRouteId ? [['route_id' => $currentCollectorRouteId, 'zone_name' => $currentCollectorZoneName, 'path_coordinates' => $currentCollectorPathCoordinates]] : [],
    'Route Map',
    'Your assigned route, drawn with numbered stops.'
); ?>

<?php if (!$currentCollectorRouteId): ?>
    <div class="empty-state-card">
        <div class="empty-state-icon">🚛</div>
        <h3>No route assigned yet</h3>
        <p>Ask your Barangay or LGU Admin to assign you to a route.</p>
    </div>
<?php else: ?>
    <div class="panel" style="margin-bottom:1.5rem;">
        <div class="panel-header">
            <h3><?= htmlspecialchars(routeLabel(['route_id' => $currentCollectorRouteId, 'zone_name' => $currentCollectorZoneName])) ?> <small style="font-weight:normal;opacity:.7;">(Route #<?= (int) $currentCollectorRouteId ?>)</small></h3>
            <span class="status-indicator active">Assigned</span>
        </div>
        <p style="margin-bottom:0;">
            Managed by:
            <strong><?= $currentCollectorBarangayAdminName ? htmlspecialchars($currentCollectorBarangayAdminName) : 'Barangay Admin' ?></strong>
        </p>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Stop #</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stops)): ?>
                    <tr><td colspan="3"><?= htmlspecialchars($currentCollectorPathCoordinates ?: 'No coordinates recorded for this route yet.') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($stops as $stop): ?>
                        <tr>
                            <td><?= (int) $stop['seq'] ?></td>
                            <td><?= htmlspecialchars($stop['lat']) ?></td>
                            <td><?= htmlspecialchars($stop['lng']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_foot.php'; ?>