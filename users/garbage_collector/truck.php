<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle  = 'My Truck';
$activePage = 'truck';

// Other collectors sharing the same truck (e.g. different shifts), if any.
$coDrivers = [];
if ($currentCollectorTruckId) {
    try {
        $stmt = $conn->prepare('
            SELECT garbage_collector_id, first_name, last_name, route_id
            FROM garbage_collector
            WHERE truck_id = ? AND garbage_collector_id != ?
        ');
        $stmt->bind_param('ii', $currentCollectorTruckId, $currentCollectorId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $coDrivers[] = $row;
        }
    } catch (Exception $e) { /* ignore */ }
}

include __DIR__ . '/includes/admin_head.php';
?>

<div class="content-header">
    <div>
        <h2>My Truck</h2>
        <p>The collection truck currently assigned to you. Only the LGU Admin can change this.</p>
    </div>
</div>

<?php if (!$currentCollectorTruckId): ?>
    <div class="empty-state-card">
        <div class="empty-state-icon">🚚</div>
        <h3>No truck assigned yet</h3>
        <p>Ask your LGU Admin to assign you to a truck.</p>
    </div>
<?php else: ?>
    <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M3 6h11v10H3V6zm11 3h3l4 4v3h-7V9z"/></svg>
            </div>
            <div>
                <div class="stat-value"><?= htmlspecialchars($currentCollectorPlateNumber) ?></div>
                <div class="stat-label">Plate Number</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-amber">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M6 7h12l-1 13H7L6 7zm3-4h6l1 2H8l1-2z"/></svg>
            </div>
            <div>
                <div class="stat-value"><?= $currentCollectorMaxCapacityKg !== null ? number_format((float) $currentCollectorMaxCapacityKg, 2) . ' kg' : '—' ?></div>
                <div class="stat-label">Max Capacity</div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h3>Other Collectors on This Truck</h3>
        </div>
        <?php if (empty($coDrivers)): ?>
            <div class="empty-state-card empty-state-compact">
                <div class="empty-state-icon"></div>
                <h3>You're the only one assigned</h3>
                <p>No other collectors are currently linked to this truck.</p>
            </div>
        <?php else: ?>
            <ul class="simple-list">
                <?php foreach ($coDrivers as $c): ?>
                    <li>
                        <span class="simple-list-title"><?= htmlspecialchars(trim($c['first_name'] . ' ' . $c['last_name'])) ?></span>
                        <span class="simple-list-meta"><?= $c['route_id'] ? 'Route #' . (int) $c['route_id'] : 'No route' ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_foot.php'; ?>
