<?php
require_once __DIR__ . '/includes/auth.php';
ensureScheduleAssignmentColumns($conn);

$pageTitle  = 'My Schedule';
$activePage = 'schedule';

$today = date('l');

$schedules = [];
$errors = [];
if (true) {
    try {
        $stmt = $conn->prepare('
            SELECT cs.collection_schedule_id, cs.day_of_week, cs.schedule_status, cs.start_time, cs.end_time,
                   wc.category_name, r.zone_name, cs.route_id
            FROM collection_schedule cs
            LEFT JOIN waste_category wc ON cs.waste_category_id = wc.waste_category_id
            LEFT JOIN route r ON cs.route_id = r.route_id
            WHERE cs.garbage_collector_id = ?
            ORDER BY FIELD(cs.day_of_week, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday")
        ');
        $stmt->bind_param('i', $currentCollectorId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $schedules[] = $row;
        }
    } catch (Exception $e) {
        $errors[] = 'Could not load the collection schedule.';
    }
}

include __DIR__ . '/includes/admin_head.php';
?>

<div class="content-header">
    <div>
        <h2>My Schedule</h2>
        <p>The schedule assigned to you by your Barangay Admin.</p>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (false): ?>
    <div class="empty-state-card">
        <div class="empty-state-icon">🗓️</div>
        <h3>No schedule to show yet</h3>
        <p>You'll see your barangay's collection schedule once you're assigned to a route.</p>
    </div>
<?php elseif (empty($schedules)): ?>
    <div class="empty-state-card">
        <div class="empty-state-icon">🗓️</div>
        <h3>No schedules set yet</h3>
        <p>Your Barangay Admin hasn't assigned you a schedule yet.</p>
    </div>
<?php else: ?>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Route / Zone</th>
                    <th>Waste Category</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $s): ?>
                    <tr<?= $s['day_of_week'] === $today ? ' style="background-color:var(--primary-light);"' : '' ?>>
                        <td><?= htmlspecialchars($s['route_id'] ? routeLabel($s) : '—') ?></td>
                        <td><?= htmlspecialchars($s['category_name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($s['day_of_week']) ?><?= $s['day_of_week'] === $today ? ' <strong>(Today)</strong>' : '' ?></td>
                        <td><?= htmlspecialchars(routeTimeRange($s['start_time'], $s['end_time']) ?: '—') ?></td>
                        <td>
                            <span class="status-indicator <?= strtolower($s['schedule_status']) === 'active' ? 'active' : 'inactive' ?>">
                                <?= htmlspecialchars($s['schedule_status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_foot.php'; ?>