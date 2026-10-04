<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../../route_helpers.php';
ensureRouteScheduleColumns($conn);
ensureScheduleAssignmentColumns($conn);

$pageTitle  = 'Collection Schedule';
$activePage = 'schedules';
$adminId    = $currentBarangayAdminId;

$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$statuses   = ['Active', 'Inactive'];

$errors  = [];
$success = '';

// ---- Load waste categories for the dropdown ----
$wasteCategories = [];
try {
    $res = $conn->query('SELECT waste_category_id, category_name FROM waste_category ORDER BY category_name');
    while ($res && $row = $res->fetch_assoc()) {
        $wasteCategories[] = $row;
    }
} catch (Exception $e) {
    $errors[] = 'Could not load waste categories.';
}

// ---- Routes/zones owned by this admin ----
$routes = [];
try {
    $stmt = $conn->prepare('SELECT route_id, zone_name FROM route WHERE barangay_admin_id = ? ORDER BY LENGTH(zone_name), zone_name, route_id');
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $routes[] = $row;
    }
} catch (Exception $e) {
    $errors[] = 'Could not load your routes.';
}
$routesById = [];
foreach ($routes as $r) { $routesById[(int) $r['route_id']] = $r; }

// ---- Collectors this admin can schedule: already on one of their routes, or not assigned to any route yet ----
$collectors = [];
try {
    $stmt = $conn->prepare('
        SELECT garbage_collector_id, first_name, last_name
        FROM garbage_collector
        WHERE route_id IS NULL OR route_id IN (SELECT route_id FROM route WHERE barangay_admin_id = ?)
        ORDER BY last_name, first_name
    ');
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $collectors[(int) $row['garbage_collector_id']] = $row;
    }
} catch (Exception $e) {
    $errors[] = 'Could not load garbage collectors.';
}

// ---- Handle form submissions (add / update / delete) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $scheduleId = (int) ($_POST['collection_schedule_id'] ?? 0);
        try {
            $stmt = $conn->prepare('DELETE FROM collection_schedule WHERE collection_schedule_id = ? AND barangay_admin_id = ?');
            $stmt->bind_param('ii', $scheduleId, $adminId);
            $stmt->execute();
            $success = 'Schedule deleted.';
        } catch (Exception $e) {
            $errors[] = 'Could not delete that schedule.';
        }
    } else {
        $wasteCategoryId = (int) ($_POST['waste_category_id'] ?? 0);
        $dayOfWeek       = $_POST['day_of_week'] ?? '';
        $scheduleStatus  = in_array($_POST['schedule_status'] ?? '', $statuses, true) ? $_POST['schedule_status'] : 'Active';
        $scheduleId      = (int) ($_POST['collection_schedule_id'] ?? 0);
        $collectorId     = (int) ($_POST['garbage_collector_id'] ?? 0);
        $routeId         = (int) ($_POST['route_id'] ?? 0);
        $start           = routeParseTime($_POST['start_time'] ?? '');
        $end             = routeParseTime($_POST['end_time'] ?? '');

        if ($wasteCategoryId <= 0 || !in_array($dayOfWeek, $daysOfWeek, true)) {
            $errors[] = 'Please choose a waste category and a valid day of the week.';
        } elseif ($dayOfWeek === 'Sunday') {
            $errors[] = 'Sunday is a rest day. No collections can be scheduled.';
        } elseif (!isset($collectors[$collectorId])) {
            $errors[] = 'Please choose a garbage collector.';
        } elseif (!isset($routesById[$routeId])) {
            $errors[] = 'Please choose a route / zone.';
        } elseif (!routeAllowedOnDay($routesById[$routeId], $dayOfWeek)) {
            $errors[] = 'That zone is not collected on ' . $dayOfWeek . '. Mon/Wed/Fri: Zones 1, 2, 5, 6. Tue/Thu/Sat: Zones 3, 4, 7, 8, 9, 10.';
        } elseif ($start === null || $end === null) {
            $errors[] = 'Please enter both a start time and an end time.';
        } elseif ($scheduleError = routeScheduleError($start, $end)) {
            $errors[] = $scheduleError;
        } else {
            try {
                if ($formAction === 'update' && $scheduleId > 0) {
                    $stmt = $conn->prepare('UPDATE collection_schedule SET waste_category_id = ?, day_of_week = ?, schedule_status = ?, garbage_collector_id = ?, route_id = ?, start_time = ?, end_time = ? WHERE collection_schedule_id = ? AND barangay_admin_id = ?');
                    $stmt->bind_param('issiissii', $wasteCategoryId, $dayOfWeek, $scheduleStatus, $collectorId, $routeId, $start, $end, $scheduleId, $adminId);
                    $stmt->execute();
                    $success = 'Schedule updated.';
                } else {
                    $stmt = $conn->prepare('INSERT INTO collection_schedule (waste_category_id, day_of_week, schedule_status, barangay_admin_id, garbage_collector_id, route_id, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmt->bind_param('issiiiss', $wasteCategoryId, $dayOfWeek, $scheduleStatus, $adminId, $collectorId, $routeId, $start, $end);
                    $stmt->execute();
                    $success = 'Schedule added.';
                }
                // The collector's assigned route (shown in their Route section) follows the schedule.
                $stmt = $conn->prepare('UPDATE garbage_collector SET route_id = ? WHERE garbage_collector_id = ?');
                $stmt->bind_param('ii', $routeId, $collectorId);
                $stmt->execute();
            } catch (Exception $e) {
                $errors[] = 'Could not save the schedule. Please try again.';
            }
        }
    }
}

// ---- Load this admin's schedules ----
$schedules = [];
try {
    $stmt = $conn->prepare('
        SELECT cs.collection_schedule_id, cs.waste_category_id, cs.day_of_week, cs.schedule_status,
               cs.garbage_collector_id, cs.route_id, cs.start_time, cs.end_time,
               wc.category_name, r.zone_name, gc.first_name, gc.last_name
        FROM collection_schedule cs
        LEFT JOIN waste_category wc ON cs.waste_category_id = wc.waste_category_id
        LEFT JOIN route r ON cs.route_id = r.route_id
        LEFT JOIN garbage_collector gc ON cs.garbage_collector_id = gc.garbage_collector_id
        WHERE cs.barangay_admin_id = ?
        ORDER BY FIELD(cs.day_of_week, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday")
    ');
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $schedules[] = $row;
    }
} catch (Exception $e) {
    $errors[] = 'Could not load your schedules.';
}

// ---- If editing, load that schedule's current data ----
$editingSchedule = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($schedules as $s) {
        if ((int) $s['collection_schedule_id'] === $editId) {
            $editingSchedule = $s;
            break;
        }
    }
}

include __DIR__ . '/includes/admin_head.php';
?>

<div class="content-header">
    <div>
        <h2>Collection Schedule</h2>
        <p>Assign each garbage collector a route/zone, collection day and time. Available zones depend on the day selected.</p>
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
        <?php if ($editingSchedule): ?><a href="schedules.php" class="panel-link">Cancel edit</a><?php endif; ?>
    </div>
            
    <?php if (empty($wasteCategories)): ?>
        <div class="empty-state-card empty-state-compact">
            <div class="empty-state-icon">♻️</div>
            <h3>No waste categories found</h3>
            <p>Ask the LGU Admin to add waste categories before creating a schedule.</p>
        </div>
    <?php else: ?>
        <form method="POST" action="schedules.php">
            <input type="hidden" name="form_action" value="<?= $editingSchedule ? 'update' : 'add' ?>">
            <?php if ($editingSchedule): ?>
                <input type="hidden" name="collection_schedule_id" value="<?= (int) $editingSchedule['collection_schedule_id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="garbage_collector_id">Garbage Collector</label>
                <select id="garbage_collector_id" name="garbage_collector_id" class="form-control" required>
                    <option value="">Select a collector&hellip;</option>
                    <?php foreach ($collectors as $c): ?>
                        <option value="<?= (int) $c['garbage_collector_id'] ?>" <?= (isset($editingSchedule) && (int) $editingSchedule['garbage_collector_id'] === (int) $c['garbage_collector_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(trim($c['first_name'] . ' ' . $c['last_name'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="waste_category_id">Waste Category</label>
                <select id="waste_category_id" name="waste_category_id" class="form-control" required>
                    <option value="">Select a category&hellip;</option>
                    <?php foreach ($wasteCategories as $wc): ?>
                        <option value="<?= (int) $wc['waste_category_id'] ?>" <?= (isset($editingSchedule) && (int) $editingSchedule['waste_category_id'] === (int) $wc['waste_category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($wc['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="day_of_week">Day of the Week</label>
                <select id="day_of_week" name="day_of_week" class="form-control" required>
                    <option value="">Select a day&hellip;</option>
                    <?php foreach ($daysOfWeek as $day): if ($day === 'Sunday') continue; // rest day ?>
                        <option value="<?= $day ?>" <?= (isset($editingSchedule) && $editingSchedule['day_of_week'] === $day) ? 'selected' : '' ?>><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="route_id">Route / Zone</label>
                <select id="route_id" name="route_id" class="form-control" required disabled>
                    <option value="">Select a day first&hellip;</option>
                </select>
                <small id="route_hint" style="opacity:.7;">Mon/Wed/Fri: Zones 1, 2, 5, 6 &bull; Tue/Thu/Sat: Zones 3, 4, 7, 8, 9, 10 &bull; Sunday is a rest day.</small>
            </div>

            <div class="form-group" style="display:flex;gap:1rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:160px;">
                    <label for="start_time">Start Time</label>
                    <input type="time" id="start_time" name="start_time" class="form-control" value="<?= htmlspecialchars(!empty($editingSchedule['start_time']) ? substr($editingSchedule['start_time'], 0, 5) : '') ?>" required>
                </div>
                <div style="flex:1;min-width:160px;">
                    <label for="end_time">End Time</label>
                    <input type="time" id="end_time" name="end_time" class="form-control" value="<?= htmlspecialchars(!empty($editingSchedule['end_time']) ? substr($editingSchedule['end_time'], 0, 5) : '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="schedule_status">Status</label>
                <select id="schedule_status" name="schedule_status" class="form-control" required>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= $status ?>" <?= (isset($editingSchedule) && $editingSchedule['schedule_status'] === $status) ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><?= $editingSchedule ? 'Update Schedule' : 'Add Schedule' ?></button>
        </form>
    <?php endif; ?>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Collector</th>
                <th>Route / Zone</th>
                <th>Waste Category</th>
                <th>Day</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($schedules)): ?>
                <tr><td colspan="7">No schedules yet. Add your first one above.</td></tr>
            <?php else: ?>
                <?php foreach ($schedules as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')) ?: '—') ?></td>
                        <td><?= htmlspecialchars($s['route_id'] ? routeLabel($s) : '—') ?></td>
                        <td><?= htmlspecialchars($s['category_name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($s['day_of_week']) ?></td>
                        <td><?= htmlspecialchars(routeTimeRange($s['start_time'], $s['end_time']) ?: '—') ?></td>
                        <td>
                            <span class="status-indicator <?= strtolower($s['schedule_status']) === 'active' ? 'active' : 'inactive' ?>">
                                <?= htmlspecialchars($s['schedule_status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="schedules.php?edit=<?= (int) $s['collection_schedule_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="schedules.php" style="display:inline;" onsubmit="return confirm('Delete this schedule? This cannot be undone.');">
                                <input type="hidden" name="form_action" value="delete">
                                <input type="hidden" name="collection_schedule_id" value="<?= (int) $s['collection_schedule_id'] ?>">
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
(function () {
    // Zones allowed per day (Sunday = rest day, no collection).
    var zonesByDay = {
        Monday: [1, 2, 5, 6], Wednesday: [1, 2, 5, 6], Friday: [1, 2, 5, 6],
        Tuesday: [3, 4, 7, 8, 9, 10], Thursday: [3, 4, 7, 8, 9, 10], Saturday: [3, 4, 7, 8, 9, 10],
        Sunday: []
    };
    var routes = <?= json_encode(array_map(fn($r) => ['id' => (int) $r['route_id'], 'label' => routeLabel($r), 'zone' => zoneNumber($r['zone_name'])], $routes)) ?>;
    var selectedRoute = <?= (int) ($editingSchedule['route_id'] ?? 0) ?>;
    var day = document.getElementById('day_of_week');
    var route = document.getElementById('route_id');
    if (!day || !route) return;

    function refresh() {
        var allowed = zonesByDay[day.value] || [];
        route.innerHTML = '';
        if (!day.value) {
            route.add(new Option('Select a day first\u2026', ''));
            route.disabled = true;
            return;
        }
        var matches = routes.filter(function (r) { return allowed.indexOf(r.zone) !== -1; });
        route.add(new Option(matches.length ? 'Select a zone\u2026' : 'No zones available for ' + day.value, ''));
        matches.forEach(function (r) {
            var o = new Option(r.label, r.id);
            if (r.id === selectedRoute) o.selected = true;
            route.add(o);
        });
        route.disabled = matches.length === 0;
    }
    day.addEventListener('change', function () { selectedRoute = 0; refresh(); });
    refresh();
})();
</script>

<?php include __DIR__ . '/includes/admin_foot.php'; ?>
