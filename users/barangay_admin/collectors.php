<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../../route_helpers.php';
ensureRouteScheduleColumns($conn);

$pageTitle  = 'Garbage Collectors';
$activePage = 'collectors';
$adminId    = $currentBarangayAdminId;

$collectors = [];
$errors = [];
try {
    $stmt = $conn->prepare('
        SELECT gc.garbage_collector_id, gc.first_name, gc.last_name, gc.email, gc.route_id, gc.truck_id,
               t.plate_number, r.zone_name
        FROM garbage_collector gc
        LEFT JOIN truck t ON gc.truck_id = t.truck_id
        LEFT JOIN route r ON gc.route_id = r.route_id
        WHERE gc.route_id IS NULL OR gc.route_id IN (SELECT route_id FROM route WHERE barangay_admin_id = ?)
        ORDER BY gc.last_name, gc.first_name
    ');
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $collectors[] = $row;
    }
} catch (Exception $e) {
    $errors[] = 'Could not load garbage collectors.';
}

include __DIR__ . '/includes/admin_head.php';
?>

<div class="content-header">
    <div>
        <h2>Garbage Collectors</h2>
        <p>Collectors on your routes, plus newly added collectors waiting for a route. Assign routes and schedules from the Collection Schedule page. This list is read-only &mdash; collector accounts are managed by the LGU Admin.</p>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Route</th>
                <th>Truck</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($collectors)): ?>
                <tr><td colspan="4">No collectors are currently assigned to your routes.</td></tr>
            <?php else: ?>
                <?php foreach ($collectors as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars(trim($c['first_name'] . ' ' . $c['last_name'])) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['route_id'] ? routeLabel($c) : 'Not assigned yet') ?></td>
                        <td><?= htmlspecialchars($c['plate_number'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/admin_foot.php'; ?>