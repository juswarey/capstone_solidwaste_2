<?php
/**
 * Route / Zone schedule helpers
 * -------------------------------------------------------------
 * Each row in the `route` table represents one collection ZONE.
 * Besides the map path, a route now carries:
 *   zone_name       e.g. "Zone 3"
 *   collection_days e.g. "Tuesday,Thursday,Saturday"
 *   start_time      e.g. 07:00:00
 *   end_time        e.g. 08:00:00
 *
 * This file sits in the project root (next to config.php / db.php).
 */

const ROUTE_DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

/**
 * Adds the zone/schedule columns to `route` if they don't exist yet, so the
 * pages keep working even if the SQL migration hasn't been run.
 */
function ensureRouteScheduleColumns(mysqli $conn): void
{
    try {
        $res = $conn->query("SHOW COLUMNS FROM route LIKE 'zone_name'");
        if ($res && $res->num_rows === 0) {
            $conn->query(
                'ALTER TABLE route
                    ADD COLUMN zone_name VARCHAR(50) DEFAULT NULL,
                    ADD COLUMN collection_days VARCHAR(100) DEFAULT NULL,
                    ADD COLUMN start_time TIME DEFAULT NULL,
                    ADD COLUMN end_time TIME DEFAULT NULL'
            );
        }
    } catch (mysqli_sql_exception $e) {
        // Leave it to the page's own error handling.
    }
}

/**
 * Adds the per-collector assignment columns to `collection_schedule` if missing.
 * The Barangay Admin assigns a collector + route/zone + day + time on each row.
 */
function ensureScheduleAssignmentColumns(mysqli $conn): void
{
    try {
        $res = $conn->query("SHOW COLUMNS FROM collection_schedule LIKE 'garbage_collector_id'");
        if ($res && $res->num_rows === 0) {
            $conn->query(
                'ALTER TABLE collection_schedule
                    ADD COLUMN garbage_collector_id INT(11) DEFAULT NULL,
                    ADD COLUMN route_id INT(11) DEFAULT NULL,
                    ADD COLUMN start_time TIME DEFAULT NULL,
                    ADD COLUMN end_time TIME DEFAULT NULL'
            );
        }
    } catch (mysqli_sql_exception $e) {
        // Leave it to the page's own error handling.
    }
}

/**
 * Zone numbers that can be collected on a given day.
 *   Mon / Wed / Fri  -> Zones 1, 2, 5, 6
 *   Tue / Thu / Sat  -> Zones 3, 4, 7, 8, 9, 10
 *   Sunday           -> none (rest day)
 */
function zonesForDay(string $day): array
{
    switch ($day) {
        case 'Monday':
        case 'Wednesday':
        case 'Friday':
            return [1, 2, 5, 6];
        case 'Tuesday':
        case 'Thursday':
        case 'Saturday':
            return [3, 4, 7, 8, 9, 10];
        default:
            return [];
    }
}

/** "Zone 3" -> 3 (null if the name has no number). */
function zoneNumber(?string $zoneName): ?int
{
    return preg_match('/(\d+)/', (string) $zoneName, $m) ? (int) $m[1] : null;
}

/** Can this route/zone be collected on this day? */
function routeAllowedOnDay(array $route, string $day): bool
{
    $n = zoneNumber($route['zone_name'] ?? null);
    return $n !== null && in_array($n, zonesForDay($day), true);
}

/** Turns the posted checkbox array into an ordered "Monday,Wednesday" string. */
function routeDaysFromPost($posted): string
{
    if (!is_array($posted)) {
        return '';
    }
    return implode(',', array_values(array_intersect(ROUTE_DAYS, $posted)));
}

/** "Monday,Wednesday" -> ['Monday', 'Wednesday'] */
function routeDaysList(?string $csv): array
{
    if ($csv === null || trim($csv) === '') {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode(',', $csv))));
}

/** "Monday,Wednesday,Friday" -> "Mon / Wed / Fri" */
function routeDaysShort(?string $csv): string
{
    $short = array_map(fn($d) => substr($d, 0, 3), routeDaysList($csv));
    return implode(' / ', $short);
}

function routeRunsToday(?string $csv): bool
{
    return in_array(date('l'), routeDaysList($csv), true);
}

/** "HH:MM" (from <input type="time">) -> "HH:MM:00", or null if blank/invalid. */
function routeParseTime($value): ?string
{
    $value = trim((string) $value);
    if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?$/', $value, $m)) {
        return $m[1] . ':' . $m[2] . ':00';
    }
    return null;
}

/** "17:00:00" -> "5:00 PM" */
function routeFormatTime(?string $time): string
{
    if (!$time) {
        return '';
    }
    $ts = strtotime($time);
    return $ts ? date('g:i A', $ts) : '';
}

/** "5:00 PM - 6:00 PM" (empty string if no times are set) */
function routeTimeRange(?string $start, ?string $end): string
{
    if (!$start || !$end) {
        return '';
    }
    return routeFormatTime($start) . ' - ' . routeFormatTime($end);
}

/** Validates the posted schedule fields. Returns an error message or null. */
function routeScheduleError(?string $start, ?string $end): ?string
{
    if (($start === null) !== ($end === null)) {
        return 'Please enter both a start time and an end time, or leave both blank.';
    }
    if ($start !== null && $end <= $start) {
        return 'The end time must be later than the start time.';
    }
    return null;
}

/** "Zone 3" if set, otherwise "Route #12". */
function routeLabel(array $route): string
{
    $zone = trim((string) ($route['zone_name'] ?? ''));
    return $zone !== '' ? $zone : 'Route #' . (int) ($route['route_id'] ?? 0);
}

/** Full text for dropdowns: "Zone 3 (Route #4) - Tue / Thu / Sat, 7:00 AM - 8:00 AM" */
function routeOptionText(array $route): string
{
    $text = routeLabel($route);
    if (trim((string) ($route['zone_name'] ?? '')) !== '') {
        $text .= ' (Route #' . (int) $route['route_id'] . ')';
    }
    $days = routeDaysShort($route['collection_days'] ?? null);
    $time = routeTimeRange($route['start_time'] ?? null, $route['end_time'] ?? null);
    $extra = trim($days . ($days !== '' && $time !== '' ? ', ' : '') . $time);
    return $extra !== '' ? $text . ' - ' . $extra : $text;
}

/**
 * Prints the zone / days / time inputs used on both admin route forms.
 * $editing is the route row being edited, or null when adding.
 */
function renderRouteScheduleFields(?array $editing): void
{
    $zone     = $editing['zone_name'] ?? '';
    $selected = routeDaysList($editing['collection_days'] ?? '');
    $start    = !empty($editing['start_time']) ? substr($editing['start_time'], 0, 5) : '';
    $end      = !empty($editing['end_time']) ? substr($editing['end_time'], 0, 5) : '';
    ?>
    <div class="form-group">
        <label>Zone Name</label>
        <input type="text" name="zone_name" class="form-control" maxlength="50" placeholder="e.g. Zone 3" value="<?= htmlspecialchars($zone) ?>">
    </div>
    <div class="form-group">
        <label>Collection Days</label>
        <div style="display:flex;flex-wrap:wrap;gap:0.4rem 1.25rem;">
            <?php foreach (ROUTE_DAYS as $day): ?>
                <label style="display:inline-flex;align-items:center;gap:0.35rem;font-weight:normal;">
                    <input type="checkbox" name="collection_days[]" value="<?= $day ?>" <?= in_array($day, $selected, true) ? 'checked' : '' ?>>
                    <?= $day ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="form-group" style="display:flex;gap:1rem;flex-wrap:wrap;">
        <div style="flex:1;min-width:160px;">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control" value="<?= htmlspecialchars($start) ?>">
        </div>
        <div style="flex:1;min-width:160px;">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control" value="<?= htmlspecialchars($end) ?>">
        </div>
    </div>
    <?php
}

/** Panel shown to garbage collectors with their zone, days and time window. */
function renderZoneSchedulePanel(?int $routeId, ?string $zone, ?string $days, ?string $start, ?string $end): void
{
    if (!$routeId) {
        return;
    }
    $label   = routeLabel(['route_id' => $routeId, 'zone_name' => $zone]);
    $dayList = routeDaysList($days);
    $time    = routeTimeRange($start, $end);
    ?>
    <div class="panel" style="margin-bottom:1.5rem;">
        <div class="panel-header">
            <h3>My Zone Schedule &mdash; <?= htmlspecialchars($label) ?></h3>
            <?php if ($dayList && routeRunsToday($days)): ?>
                <span class="status-indicator active">Collecting today</span>
            <?php endif; ?>
        </div>
        <?php if (!$dayList && $time === ''): ?>
            <p style="margin-bottom:0;">No collection days or time have been set for this zone yet. Ask your Barangay or LGU Admin.</p>
        <?php else: ?>
            <ul class="simple-list">
                <li>
                    <span class="simple-list-title">Days</span>
                    <span class="simple-list-meta"><?= $dayList ? htmlspecialchars(implode(', ', $dayList)) : '&mdash;' ?></span>
                </li>
                <li>
                    <span class="simple-list-title">Time</span>
                    <span class="simple-list-meta"><?= $time !== '' ? htmlspecialchars($time) : '&mdash;' ?></span>
                </li>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}

/** "lat,lng; lat,lng" -> [['lat'=>..,'lng'=>..], ...] (invalid pairs skipped). */
function routePathPoints(?string $coords): array
{
    $points = [];
    foreach (array_filter(array_map('trim', explode(';', (string) $coords))) as $pair) {
        $p = array_map('trim', explode(',', $pair));
        if (count($p) === 2 && is_numeric($p[0]) && is_numeric($p[1])) {
            $points[] = ['lat' => (float) $p[0], 'lng' => (float) $p[1]];
        }
    }
    return $points;
}

/**
 * Google Map shown at the top of the route pages.
 * $routes: rows with route_id, zone_name, path_coordinates. Each path is drawn as a line with
 * numbered stop markers; the map zooms to fit them (or centres on Poblacion if none have a path).
 */
function renderRouteMap(array $routes, string $title = 'Route Map', string $subtitle = ''): void
{
    $data = [];
    foreach ($routes as $r) {
        $pts = routePathPoints($r['path_coordinates'] ?? '');
        if ($pts) {
            $data[] = ['label' => routeLabel($r), 'points' => $pts];
        }
    }
    ?>
    <style>
        .route-map-card{margin-bottom:1.5rem;overflow:hidden;padding:0;border:1px solid #e8ecf1;border-radius:17px;background:#fff;box-shadow:0 4px 18px rgba(20,30,50,.045);}
        .route-map-head{padding:18px 22px 12px;}
        .route-map-head h3{margin:0;font-size:16px;font-weight:700;color:#17202a;}
        .route-map-head p{margin:5px 0 0;font-size:12px;color:#929ba8;}
        #routeMap{width:100%;height:420px;}
    </style>
    <div class="panel route-map-card">
        <div class="route-map-head">
            <h3><?= htmlspecialchars($title) ?></h3>
            <?php if ($subtitle !== ''): ?><p><?= htmlspecialchars($subtitle) ?></p><?php endif; ?>
        </div>
        <div id="routeMap"></div>
    </div>
    <script>
        const ROUTE_MAP_DATA = <?= json_encode($data) ?>;
        async function initRouteMap() {
            const { Map, Polyline } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");
            const poblacion = { lat: 8.2291757, lng: 124.2379914 };
            const map = new Map(document.getElementById("routeMap"), {
                center: poblacion, zoom: 16, mapId: "DEMO_MAP_ID",
                streetViewControl: false, fullscreenControl: true, mapTypeControl: false
            });
            const colors = ['#2e7d32', '#1565c0', '#c62828', '#ef6c00', '#6a1b9a', '#00838f', '#827717', '#ad1457'];
            const bounds = new google.maps.LatLngBounds();
            ROUTE_MAP_DATA.forEach(function (route, i) {
                const color = colors[i % colors.length];
                new Polyline({ map: map, path: route.points, strokeColor: color, strokeOpacity: 0.9, strokeWeight: 4 });
                route.points.forEach(function (pt, n) {
                    bounds.extend(pt);
                    new AdvancedMarkerElement({
                        map: map, position: pt, title: route.label + ' - stop ' + (n + 1),
                        content: new PinElement({ glyph: String(n + 1), background: color, borderColor: color, glyphColor: '#fff', scale: 0.8 }).element
                    });
                });
            });
            if (ROUTE_MAP_DATA.length) {
                map.fitBounds(bounds, 48);
            } else {
                new AdvancedMarkerElement({ map: map, position: poblacion, title: "Barangay Poblacion, Iligan City" });
            }
        }
        window.initRouteMap = initRouteMap;
    </script>
    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDydw54pnA-pG9UE3dHyZ9dzpChfe3cIyI&loading=async&callback=initRouteMap"></script>
    <?php
}
