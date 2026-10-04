<?php
require_once __DIR__ . '/includes/auth.php';
ensureScheduleAssignmentColumns($conn);

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

/** Safely run a scalar COUNT query with bound params. Returns 0 on any failure. */
function safeScalarCount(mysqli $conn, string $sql, string $types = '', array $params = []): int {
    try {
        $stmt = $conn->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int) ($row['c'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

$barangayAdminId = $currentCollectorBarangayAdminId;

$mySchedulesCount   = safeScalarCount($conn, 'SELECT COUNT(*) AS c FROM collection_schedule WHERE garbage_collector_id = ?', 'i', [$currentCollectorId]);
$wasteCategoryCount = safeScalarCount($conn, 'SELECT COUNT(*) AS c FROM waste_category', '', []);
$myPingsCount       = safeScalarCount($conn, 'SELECT COUNT(*) AS c FROM location_history WHERE garbage_collector_id = ?', 'i', [$currentCollectorId]);

$today = date('l');
$todayCollections = safeScalarCount(
    $conn,
    "SELECT COUNT(*) AS c FROM collection_schedule WHERE garbage_collector_id = ? AND day_of_week = ? AND schedule_status = 'Active'",
    'is',
    [$currentCollectorId, $today]
);

// Today's collection schedule for this collector's barangay
$todaySchedules = [];
if (true) {
    try {
        $stmt = $conn->prepare('
            SELECT cs.collection_schedule_id, cs.day_of_week, cs.schedule_status, cs.start_time, cs.end_time, cs.route_id, wc.category_name, r.zone_name
            FROM collection_schedule cs
            LEFT JOIN waste_category wc ON cs.waste_category_id = wc.waste_category_id
            LEFT JOIN route r ON cs.route_id = r.route_id
            WHERE cs.garbage_collector_id = ? AND cs.day_of_week = ?
            ORDER BY cs.collection_schedule_id DESC
        ');
        $stmt->bind_param('is', $currentCollectorId, $today);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $todaySchedules[] = $row;
        }
    } catch (Exception $e) { /* table may be empty */ }
}

// Most recent location pings for this collector
$recentPings = [];
try {
    $stmt = $conn->prepare('
        SELECT location_history_id, latitude, longitude, timestamp
        FROM location_history
        WHERE garbage_collector_id = ?
        ORDER BY timestamp DESC
        LIMIT 5
    ');
    $stmt->bind_param('i', $currentCollectorId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $recentPings[] = $row;
    }
} catch (Exception $e) { /* table may be empty */ }

include __DIR__ . '/includes/admin_head.php';
?>

<div class="content-header">
    <div>
        <h2>Welcome back<?= $currentCollectorName ? ', ' . htmlspecialchars($currentCollectorName) : '' ?> </h2>
        <p>Here's an overview of your assigned route, truck, and today's collection schedule.</p>
    </div>
</div>

<?php if (!$currentCollectorRouteId || !$currentCollectorTruckId): ?>
    <div class="alert alert-error">
        <p>You haven't been fully assigned a route and/or truck yet. Contact your Barangay or LGU Admin to get set up.</p>
    </div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-green">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M9 3 5 6.99h3V14h2V6.99h3L9 3zm7 14.01V10h-2v7.01h-3L15 21l4-3.99h-3z"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= $currentCollectorRouteId ? htmlspecialchars(routeLabel(['route_id' => $currentCollectorRouteId, 'zone_name' => $currentCollectorZoneName])) : '—' ?></div>
            <div class="stat-label">My Route</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-blue">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M3 6h11v10H3V6zm11 3h3l4 4v3h-7V9z"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= $currentCollectorPlateNumber ? htmlspecialchars($currentCollectorPlateNumber) : '—' ?></div>
            <div class="stat-label">My Truck</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-gray">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 8H5v10h14V10z"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= number_format($mySchedulesCount) ?></div>
            <div class="stat-label">Collection Schedules (My Barangay)</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-amber">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M6 7h12l-1 13H7L6 7zm3-4h6l1 2H8l1-2z"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= number_format($wasteCategoryCount) ?></div>
            <div class="stat-label">Waste Categories (Reference)</div>
        </div>
    </div>

    <div class="stat-card stat-card-highlight">
        <div class="stat-icon stat-icon-green">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= number_format($todayCollections) ?></div>
            <div class="stat-label">Active Collections Today (<?= htmlspecialchars($today) ?>)</div>
        </div>
    </div>
</div>

<div class="dashboard-panels">
    <div class="panel">
        <div class="panel-header">
            <h3>Today's Collection Schedule</h3>
            <a href="schedule.php" class="panel-link">View all &rarr;</a>
        </div>
        <?php if (!$currentCollectorRouteId && empty($todaySchedules)): ?>
            <div class="empty-state-card empty-state-compact">
                <div class="empty-state-icon"></div>
                <h3>No route assigned</h3>
                <p>Once you're assigned to a route, your barangay's schedule will show up here.</p>
            </div>
        <?php elseif (empty($todaySchedules)): ?>
            <div class="empty-state-card empty-state-compact">
                <div class="empty-state-icon"></div>
                <h3>Nothing scheduled today</h3>
                <p>No collections are assigned to you for <?= htmlspecialchars($today) ?>.</p>
            </div>
        <?php else: ?>
            <ul class="simple-list">
                <?php foreach ($todaySchedules as $s): ?>
                    <li>
                        <span class="simple-list-title"><?= htmlspecialchars(($s['route_id'] ? routeLabel($s) . ' — ' : '') . ($s['category_name'] ?? 'Waste')) ?><?php $t = routeTimeRange($s['start_time'], $s['end_time']); if ($t !== ''): ?> <small>(<?= htmlspecialchars($t) ?>)</small><?php endif; ?></span>
                        <span class="status-indicator <?= strtolower($s['schedule_status']) === 'active' ? 'active' : 'inactive' ?>">
                            <?= htmlspecialchars($s['schedule_status'] ?? '') ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h3>Recent Location Pings</h3>
            <a href="location.php" class="panel-link">View all &rarr;</a>
        </div>
        <?php if (empty($recentPings)): ?>
            <div class="empty-state-card empty-state-compact">
                <div class="empty-state-icon"></div>
                <h3>No location logged yet</h3>
                <p>Use "My Location" to log your position while on your route.</p>
            </div>
        <?php else: ?>
            <ul class="simple-list">
                <?php foreach ($recentPings as $p): ?>
                    <li>
                        <span class="simple-list-title"><?= number_format((float) $p['latitude'], 5) ?>, <?= number_format((float) $p['longitude'], 5) ?></span>
                        <span class="simple-list-meta"><?= htmlspecialchars(date('M d, g:i A', strtotime($p['timestamp']))) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_foot.php'; ?>
