<?php
require_once __DIR__ . '/bootstrap.php';

if (empty($_SESSION['collector_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../../route_helpers.php';
ensureRouteScheduleColumns($conn);

$currentCollectorId = $_SESSION['collector_id'];

// Pull a fresh row every request (not just what's in the session) so that
// route/truck reassignments made by an LGU/Barangay admin show up right away.
$currentCollector = null;
try {
    $stmt = $conn->prepare('
        SELECT gc.garbage_collector_id, gc.first_name, gc.last_name, gc.email,
               gc.route_id, gc.truck_id,
               r.path_coordinates, r.barangay_admin_id,
               r.zone_name, r.collection_days, r.start_time, r.end_time,
               t.plate_number, t.max_capacity_kg,
               ba.firstname AS barangay_admin_firstname, ba.lastname AS barangay_admin_lastname
        FROM garbage_collector gc
        LEFT JOIN route r ON gc.route_id = r.route_id
        LEFT JOIN truck t ON gc.truck_id = t.truck_id
        LEFT JOIN barangay_admin ba ON r.barangay_admin_id = ba.barangay_admin_id
        WHERE gc.garbage_collector_id = ?
        LIMIT 1
    ');
    $stmt->bind_param('i', $currentCollectorId);
    $stmt->execute();
    $currentCollector = $stmt->get_result()->fetch_assoc();
} catch (Exception $e) {
    $currentCollector = null;
}

// Account may have been deleted by an admin after the session was created.
if (!$currentCollector) {
    header('Location: logout.php');
    exit;
}

$currentCollectorName            = trim($currentCollector['first_name'] . ' ' . $currentCollector['last_name']);
$currentCollectorEmail           = $currentCollector['email'];
$currentCollectorRouteId         = $currentCollector['route_id'];
$currentCollectorTruckId         = $currentCollector['truck_id'];
$currentCollectorPathCoordinates = $currentCollector['path_coordinates'];
$currentCollectorZoneName        = $currentCollector['zone_name'];
$currentCollectorDays            = $currentCollector['collection_days'];
$currentCollectorStartTime       = $currentCollector['start_time'];
$currentCollectorEndTime         = $currentCollector['end_time'];
$currentCollectorBarangayAdminId = $currentCollector['barangay_admin_id'];
$currentCollectorPlateNumber     = $currentCollector['plate_number'];
$currentCollectorMaxCapacityKg   = $currentCollector['max_capacity_kg'];
$currentCollectorBarangayAdminName = trim(
    ($currentCollector['barangay_admin_firstname'] ?? '') . ' ' . ($currentCollector['barangay_admin_lastname'] ?? '')
);

// Keep the session's display name in sync in case first/last name changed.
$_SESSION['collector_name']  = $currentCollectorName;
$_SESSION['collector_email'] = $currentCollectorEmail;