<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

$adminId = $currentBarangayAdminId;


/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/

function safeScalarCount(
    mysqli $conn,
    string $sql,
    string $types = '',
    array $params = []
): int {
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


/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$myRoutesCount = safeScalarCount(
    $conn,
    'SELECT COUNT(*) AS c
     FROM route
     WHERE barangay_admin_id = ?',
    'i',
    [$adminId]
);


$mySchedulesCount = safeScalarCount(
    $conn,
    'SELECT COUNT(*) AS c
     FROM collection_schedule
     WHERE barangay_admin_id = ?',
    'i',
    [$adminId]
);


$myCollectorsCount = safeScalarCount(
    $conn,
    'SELECT COUNT(*) AS c
     FROM garbage_collector
     WHERE route_id IN (
         SELECT route_id
         FROM route
         WHERE barangay_admin_id = ?
     )',
    'i',
    [$adminId]
);


$wasteCategoryCount = safeScalarCount(
    $conn,
    'SELECT COUNT(*) AS c
     FROM waste_category',
    '',
    []
);


/*
|--------------------------------------------------------------------------
| TODAY
|--------------------------------------------------------------------------
*/

$today = date('l');

$todayCollections = safeScalarCount(
    $conn,
    'SELECT COUNT(*) AS c
     FROM collection_schedule
     WHERE barangay_admin_id = ?
     AND day_of_week = ?',
    'is',
    [$adminId, $today]
);


/*
|--------------------------------------------------------------------------
| CHART 1
| COLLECTION SCHEDULE STATUS
|--------------------------------------------------------------------------
*/

$scheduleStatusLabels = [];
$scheduleStatusValues = [];

try {

    $stmt = $conn->prepare('
        SELECT
            COALESCE(schedule_status, "Unknown") AS status,
            COUNT(*) AS total
        FROM collection_schedule
        WHERE barangay_admin_id = ?
        GROUP BY schedule_status
        ORDER BY total DESC
    ');

    $stmt->bind_param('i', $adminId);
    $stmt->execute();

    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {

        $scheduleStatusLabels[] = $row['status'];
        $scheduleStatusValues[] = (int) $row['total'];

    }

} catch (Exception $e) {

    $scheduleStatusLabels = [];
    $scheduleStatusValues = [];

}


/*
|--------------------------------------------------------------------------
| CHART 2
| COLLECTION SCHEDULES BY DAY
|--------------------------------------------------------------------------
*/

$dayLabels = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday'
];

$dayValues = array_fill(0, 7, 0);

try {

    $stmt = $conn->prepare('
        SELECT
            day_of_week,
            COUNT(*) AS total
        FROM collection_schedule
        WHERE barangay_admin_id = ?
        GROUP BY day_of_week
    ');

    $stmt->bind_param('i', $adminId);
    $stmt->execute();

    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {

        $day = trim($row['day_of_week']);

        $index = array_search($day, $dayLabels);

        if ($index !== false) {
            $dayValues[$index] = (int) $row['total'];
        }

    }

} catch (Exception $e) {

    $dayValues = array_fill(0, 7, 0);

}


/*
|--------------------------------------------------------------------------
| CHART 3
| SCHEDULES BY WASTE CATEGORY
|--------------------------------------------------------------------------
*/

$categoryLabels = [];
$categoryValues = [];

try {

    $stmt = $conn->prepare('
        SELECT
            COALESCE(wc.category_name, "Unknown") AS category_name,
            COUNT(cs.collection_schedule_id) AS total
        FROM collection_schedule cs
        LEFT JOIN waste_category wc
            ON cs.waste_category_id = wc.waste_category_id
        WHERE cs.barangay_admin_id = ?
        GROUP BY wc.waste_category_id, wc.category_name
        ORDER BY total DESC
    ');

    $stmt->bind_param('i', $adminId);
    $stmt->execute();

    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {

        $categoryLabels[] = $row['category_name'];
        $categoryValues[] = (int) $row['total'];

    }

} catch (Exception $e) {

    $categoryLabels = [];
    $categoryValues = [];

}


/*
|--------------------------------------------------------------------------
| CHART 4
| GARBAGE COLLECTORS BY ROUTE
|--------------------------------------------------------------------------
*/

$routeLabels = [];
$routeValues = [];

try {

    $stmt = $conn->prepare('
        SELECT
            r.route_id,
            COUNT(gc.garbage_collector_id) AS total
        FROM route r
        LEFT JOIN garbage_collector gc
            ON r.route_id = gc.route_id
        WHERE r.barangay_admin_id = ?
        GROUP BY r.route_id
        ORDER BY r.route_id ASC
    ');

    $stmt->bind_param('i', $adminId);
    $stmt->execute();

    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {

        $routeLabels[] = 'Route #' . $row['route_id'];
        $routeValues[] = (int) $row['total'];

    }

} catch (Exception $e) {

    $routeLabels = [];
    $routeValues = [];

}


/*
|--------------------------------------------------------------------------
| RECENT COLLECTION SCHEDULES
|--------------------------------------------------------------------------
*/

$recentSchedules = [];

try {

    $stmt = $conn->prepare('
        SELECT
            cs.collection_schedule_id,
            cs.day_of_week,
            cs.schedule_status,
            wc.category_name
        FROM collection_schedule cs
        LEFT JOIN waste_category wc
            ON cs.waste_category_id = wc.waste_category_id
        WHERE cs.barangay_admin_id = ?
        ORDER BY cs.collection_schedule_id DESC
        LIMIT 5
    ');

    $stmt->bind_param('i', $adminId);
    $stmt->execute();

    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {

        $recentSchedules[] = $row;

    }

} catch (Exception $e) {

    $recentSchedules = [];

}


/*
|--------------------------------------------------------------------------
| GARBAGE COLLECTORS
|--------------------------------------------------------------------------
*/

$myCollectors = [];

try {

    $stmt = $conn->prepare('
        SELECT
            gc.garbage_collector_id,
            gc.first_name,
            gc.last_name,
            gc.route_id
        FROM garbage_collector gc
        WHERE gc.route_id IN (
            SELECT route_id
            FROM route
            WHERE barangay_admin_id = ?
        )
        LIMIT 5
    ');

    $stmt->bind_param('i', $adminId);
    $stmt->execute();

    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {

        $myCollectors[] = $row;

    }

} catch (Exception $e) {

    $myCollectors = [];

}


include __DIR__ . '/includes/admin_head.php';

?>


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="content-header dashboard-header">

    <div>

        <div class="dashboard-eyebrow">
            Barangay Administration
        </div>

        <h2>
            Welcome back<?= $currentBarangayAdminName
                ? ', ' . htmlspecialchars($currentBarangayAdminName)
                : '' ?>
        </h2>

        <p>
            Monitor collection schedules, routes, and garbage collector assignments.
        </p>

    </div>

</div>


<!-- =========================================================
     STAT CARDS
========================================================= -->

<div class="stats-grid dashboard-stats">


    <!-- ROUTES -->

    <div class="stat-card modern-stat-card">

        <div class="modern-stat-icon green">

            <svg viewBox="0 0 24 24"
                 width="22"
                 height="22"
                 fill="currentColor">

                <path d="M9 3 5 6.99h3V14h2V6.99h3L9 3zm7 14.01V10h-2v7.01h-3L15 21l4-3.99h-3z"/>

            </svg>

        </div>

        <div class="modern-stat-content">

            <div class="modern-stat-label">
                My Routes
            </div>

            <div class="modern-stat-value">
                <?= number_format($myRoutesCount) ?>
            </div>

            <div class="modern-stat-note">
                Active routes assigned
            </div>

        </div>

    </div>


    <!-- SCHEDULES -->

    <div class="stat-card modern-stat-card">

        <div class="modern-stat-icon blue">

            <svg viewBox="0 0 24 24"
                 width="22"
                 height="22"
                 fill="currentColor">

                <path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 8H5v10h14V10z"/>

            </svg>

        </div>

        <div class="modern-stat-content">

            <div class="modern-stat-label">
                Collection Schedules
            </div>

            <div class="modern-stat-value">
                <?= number_format($mySchedulesCount) ?>
            </div>

            <div class="modern-stat-note">
                Total schedules
            </div>

        </div>

    </div>


    <!-- COLLECTORS -->

    <div class="stat-card modern-stat-card">

        <div class="modern-stat-icon purple">

            <svg viewBox="0 0 24 24"
                 width="22"
                 height="22"
                 fill="currentColor">

                <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm-7 9a7 7 0 0 1 14 0H5z"/>

            </svg>

        </div>

        <div class="modern-stat-content">

            <div class="modern-stat-label">
                Garbage Collectors
            </div>

            <div class="modern-stat-value">
                <?= number_format($myCollectorsCount) ?>
            </div>

            <div class="modern-stat-note">
                Assigned to my routes
            </div>

        </div>

    </div>


    <!-- WASTE CATEGORIES -->

    <div class="stat-card modern-stat-card">

        <div class="modern-stat-icon amber">

            <svg viewBox="0 0 24 24"
                 width="22"
                 height="22"
                 fill="currentColor">

                <path d="M6 7h12l-1 13H7L6 7zm3-4h6l1 2H8l1-2z"/>

            </svg>

        </div>

        <div class="modern-stat-content">

            <div class="modern-stat-label">
                Waste Categories
            </div>

            <div class="modern-stat-value">
                <?= number_format($wasteCategoryCount) ?>
            </div>

            <div class="modern-stat-note">
                Available categories
            </div>

        </div>

    </div>


    <!-- TODAY -->

    <div class="stat-card modern-stat-card today-card">

        <div class="modern-stat-icon green">

            <svg viewBox="0 0 24 24"
                 width="22"
                 height="22"
                 fill="currentColor">

                <path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>

            </svg>

        </div>

        <div class="modern-stat-content">

            <div class="modern-stat-label">
                Scheduled Today
            </div>

            <div class="modern-stat-value">
                <?= number_format($todayCollections) ?>
            </div>

            <div class="modern-stat-note">
                <?= htmlspecialchars($today) ?> collections
            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     CHART ROW 1
========================================================= -->

<div class="dashboard-charts">


    <!-- SCHEDULE STATUS -->

    <div class="panel modern-chart-card">

        <div class="modern-chart-header">

            <div>

                <div class="chart-title-row">

                    <h3>
                        Collection Schedule Status
                    </h3>

                    <span class="chart-badge">
                        Overview
                    </span>

                </div>

                <p>
                    Current status of your collection schedules.
                </p>

            </div>

        </div>


        <?php if (empty($scheduleStatusLabels)): ?>

            <div class="modern-empty">

                <div class="modern-empty-icon">

                    <svg viewBox="0 0 24 24"
                         width="25"
                         height="25"
                         fill="currentColor">

                        <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm0 16H5V5h14v14z"/>

                    </svg>

                </div>

                <strong>No schedule data</strong>

                <span>
                    Schedule information will appear here.
                </span>

            </div>

        <?php else: ?>

            <div class="modern-doughnut-container">

                <canvas id="scheduleStatusChart"></canvas>

            </div>

        <?php endif; ?>

    </div>


    <!-- SCHEDULES BY DAY -->

    <div class="panel modern-chart-card">

        <div class="modern-chart-header">

            <div>

                <div class="chart-title-row">

                    <h3>
                        Collection Schedules
                    </h3>

                    <span class="chart-badge">
                        Weekly
                    </span>

                </div>

                <p>
                    Distribution of collection schedules by day.
                </p>

            </div>

        </div>

        <div class="modern-chart-container">

            <canvas id="scheduleDayChart"></canvas>

        </div>

    </div>

</div>


<!-- =========================================================
     CHART ROW 2
========================================================= -->

<div class="dashboard-charts">


    <!-- WASTE CATEGORY -->

    <div class="panel modern-chart-card">

        <div class="modern-chart-header">

            <div>

                <div class="chart-title-row">

                    <h3>
                        Waste Category Distribution
                    </h3>

                    <span class="chart-badge">
                        Categories
                    </span>

                </div>

                <p>
                    Collection schedules grouped by waste category.
                </p>

            </div>

        </div>


        <?php if (empty($categoryLabels)): ?>

            <div class="modern-empty">

                <div class="modern-empty-icon">

                    <svg viewBox="0 0 24 24"
                         width="25"
                         height="25"
                         fill="currentColor">

                        <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm0 16H5V5h14v14z"/>

                    </svg>

                </div>

                <strong>No category data</strong>

                <span>
                    Waste category information will appear here.
                </span>

            </div>

        <?php else: ?>

            <div class="modern-chart-container">

                <canvas id="wasteCategoryChart"></canvas>

            </div>

        <?php endif; ?>

    </div>


    <!-- COLLECTORS BY ROUTE -->

    <div class="panel modern-chart-card">

        <div class="modern-chart-header">

            <div>

                <div class="chart-title-row">

                    <h3>
                        Collectors by Route
                    </h3>

                    <span class="chart-badge">
                        Staffing
                    </span>

                </div>

                <p>
                    Garbage collectors assigned to each route.
                </p>

            </div>

        </div>


        <?php if (empty($routeLabels)): ?>

            <div class="modern-empty">

                <div class="modern-empty-icon">

                    <svg viewBox="0 0 24 24"
                         width="25"
                         height="25"
                         fill="currentColor">

                        <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm0 16H5V5h14v14z"/>

                    </svg>

                </div>

                <strong>No route data</strong>

                <span>
                    Collector assignments will appear here.
                </span>

            </div>

        <?php else: ?>

            <div class="modern-chart-container">

                <canvas id="collectorsRouteChart"></canvas>

            </div>

        <?php endif; ?>

    </div>

</div>

<!-- =========================================================
     BARANGAY MAP
========================================================= -->

<div class="panel modern-map-card">

    <div class="modern-map-header">
        <div>
            <div class="chart-title-row">
                <h3>Barangay Map</h3>

                <span class="chart-badge">
                    Location Overview
                </span>
            </div>

            <p>
                View the location of your barangay and collection-related locations.
            </p>
        </div>
    </div>

    <div id="barangayMap"></div>

</div>

<!-- =========================================================
     RECENT INFORMATION
========================================================= -->

<div class="dashboard-panels modern-bottom-panels">


    <!-- RECENT SCHEDULES -->

    <div class="panel modern-list-panel">

        <div class="modern-list-header">

            <div>

                <h3>
                    Recent Collection Schedules
                </h3>

                <p>
                    Latest schedules added to your barangay.
                </p>

            </div>

            <a href="schedules.php" class="modern-view-link">
                View all
                <span>→</span>
            </a>

        </div>


        <?php if (empty($recentSchedules)): ?>

            <div class="modern-list-empty">

                <div class="modern-list-empty-icon">
                    +
                </div>

                <div>

                    <strong>
                        No schedules yet
                    </strong>

                    <span>
                        Add your first collection schedule.
                    </span>

                </div>

            </div>

        <?php else: ?>

            <div class="modern-list">

                <?php foreach ($recentSchedules as $s): ?>

                    <div class="modern-list-item">

                        <div class="modern-list-item-icon">

                            <svg viewBox="0 0 24 24"
                                 width="18"
                                 height="18"
                                 fill="currentColor">

                                <path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7z"/>

                            </svg>

                        </div>

                        <div class="modern-list-item-content">

                            <strong>

                                <?= htmlspecialchars(
                                    $s['category_name'] ?? 'Waste'
                                ) ?>

                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    $s['day_of_week']
                                ) ?>

                            </span>

                        </div>

                        <span class="modern-status">

                            <?= htmlspecialchars(
                                $s['schedule_status'] ?? 'Unknown'
                            ) ?>

                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>


    <!-- COLLECTORS -->

    <div class="panel modern-list-panel">

        <div class="modern-list-header">

            <div>

                <h3>
                    Garbage Collectors
                </h3>

                <p>
                    Collectors assigned to your routes.
                </p>

            </div>

            <a href="collectors.php" class="modern-view-link">
                View all
                <span>→</span>
            </a>

        </div>


        <?php if (empty($myCollectors)): ?>

            <div class="modern-list-empty">

                <div class="modern-list-empty-icon">
                    +
                </div>

                <div>

                    <strong>
                        No collectors assigned
                    </strong>

                    <span>
                        Assigned collectors will appear here.
                    </span>

                </div>

            </div>

        <?php else: ?>

            <div class="modern-list">

                <?php foreach ($myCollectors as $c): ?>

                    <div class="modern-list-item">

                        <div class="collector-avatar">

                            <?= htmlspecialchars(
                                strtoupper(
                                    substr(
                                        $c['first_name'],
                                        0,
                                        1
                                    )
                                )
                            ) ?>

                        </div>

                        <div class="modern-list-item-content">

                            <strong>

                                <?= htmlspecialchars(
                                    trim(
                                        $c['first_name']
                                        . ' '
                                        . $c['last_name']
                                    )
                                ) ?>

                            </strong>

                            <span>

                                Route #<?= (int) $c['route_id'] ?>

                            </span>

                        </div>

                        <span class="collector-active">
                            Assigned
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</div>


<!-- =========================================================
     CHART.JS
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script
    async
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDydw54pnA-pG9UE3dHyZ9dzpChfe3cIyI&loading=async&callback=initBarangayMap">
</script>

<script>

    async function initBarangayMap() {

        const { Map } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
        const poblacion = {
            lat: 8.2291757,
            lng: 124.2379914
        };

        const map = new Map(
            document.getElementById("barangayMap"),
            {
                center: poblacion,
                zoom: 17,
                mapId: "DEMO_MAP_ID",
                streetViewControl: false,
                fullscreenControl: true,
                mapTypeControl: false
            }
        );

        new AdvancedMarkerElement({
            map: map,
            position: poblacion,
            title: "Barangay Poblacion, Iligan City"
        });
    }

    window.initBarangayMap = initBarangayMap;

document.addEventListener('DOMContentLoaded', function () {


    /*
    |--------------------------------------------------------------------------
    | GLOBAL CHART SETTINGS
    |--------------------------------------------------------------------------
    */
    Chart.defaults.font.family =
        "'Inter', 'Segoe UI', Arial, sans-serif";

    Chart.defaults.font.size = 12;

    Chart.defaults.color = '#8b95a5';

    /* Disable chart animations */
    Chart.defaults.animation = false;

    /* Disable hover effects */
    Chart.defaults.hover.mode = null;

    /* Disable black tooltip popup */
    Chart.defaults.plugins.tooltip.enabled = false;


    /*
    |--------------------------------------------------------------------------
    | SCHEDULE STATUS
    |--------------------------------------------------------------------------
    */

    const statusCanvas =
        document.getElementById('scheduleStatusChart');

    if (statusCanvas) {

        const statusLabels =
            <?= json_encode($scheduleStatusLabels) ?>;

        const statusValues =
            <?= json_encode($scheduleStatusValues) ?>;


        new Chart(statusCanvas, {

            type: 'doughnut',

            data: {

                labels: statusLabels,

                datasets: [{

                    data: statusValues,

                    borderWidth: 0,

                    spacing: 4,

                    borderRadius: 7,

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '76%',

                plugins: {

                    legend: {

                        position: 'bottom',

                        labels: {

                            usePointStyle: true,

                            pointStyle: 'circle',

                            padding: 18,

                            boxWidth: 8,

                            boxHeight: 8,

                            font: {

                                size: 12,

                                weight: '500'

                            }

                        }

                    },

                }

            },

            plugins: [{

                id: 'centerText',

                beforeDraw(chart) {

                    const {
                        ctx,
                        chartArea
                    } = chart;

                    if (!chartArea) {
                        return;
                    }

                    const total =
                        statusValues.reduce(
                            (a, b) => a + b,
                            0
                        );

                    const centerX =
                        (chartArea.left + chartArea.right) / 2;

                    const centerY =
                        (chartArea.top + chartArea.bottom) / 2;


                    ctx.save();

                    ctx.textAlign = 'center';

                    ctx.textBaseline = 'middle';


                    ctx.fillStyle = '#17202a';

                    ctx.font =
                        '700 30px Inter, Segoe UI, Arial';

                    ctx.fillText(
                        total,
                        centerX,
                        centerY - 8
                    );


                    ctx.fillStyle = '#8b95a5';

                    ctx.font =
                        '500 11px Inter, Segoe UI, Arial';

                    ctx.fillText(
                        'Total schedules',
                        centerX,
                        centerY + 17
                    );


                    ctx.restore();

                }

            }]

        });

    }


    /*
    |--------------------------------------------------------------------------
    | SCHEDULES BY DAY
    |--------------------------------------------------------------------------
    */

    const dayCanvas =
        document.getElementById('scheduleDayChart');

    if (dayCanvas) {

        const dayLabels =
            <?= json_encode($dayLabels) ?>;

        const dayValues =
            <?= json_encode($dayValues) ?>;


        new Chart(dayCanvas, {

            type: 'bar',

            data: {

                labels: dayLabels,

                datasets: [{

                    label: 'Collection Schedules',

                    data: dayValues,

                    borderWidth: 0,

                    borderRadius: 8,

                    borderSkipped: false,

                    barPercentage: 0.58,

                    categoryPercentage: 0.72

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                interaction: {

                    intersect: false,

                    mode: 'index'

                },

                scales: {

                    x: {

                        grid: {

                            display: false

                        },

                        border: {

                            display: false

                        },

                        ticks: {

                            color: '#8b95a5',

                            font: {

                                size: 11,

                                weight: '500'

                            }

                        }

                    },

                    y: {

                        beginAtZero: true,

                        border: {

                            display: false

                        },

                        grid: {

                            color: '#edf0f4',

                            drawTicks: false

                        },

                        ticks: {

                            precision: 0,

                            padding: 10,

                            color: '#a0a8b5'

                        }

                    }

                },

                plugins: {

                    legend: {

                        display: false

                    },

                }

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | WASTE CATEGORY
    |--------------------------------------------------------------------------
    */

    const categoryCanvas =
        document.getElementById('wasteCategoryChart');

    if (categoryCanvas) {

        const categoryLabels =
            <?= json_encode($categoryLabels) ?>;

        const categoryValues =
            <?= json_encode($categoryValues) ?>;


        new Chart(categoryCanvas, {

            type: 'bar',

            data: {

                labels: categoryLabels,

                datasets: [{

                    label: 'Schedules',

                    data: categoryValues,

                    borderWidth: 0,

                    borderRadius: 8,

                    borderSkipped: false,

                    barPercentage: 0.55,

                    categoryPercentage: 0.7

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                indexAxis: 'y',

                interaction: {

                    intersect: false,

                    mode: 'index'

                },

                scales: {

                    x: {

                        beginAtZero: true,

                        border: {

                            display: false

                        },

                        grid: {

                            color: '#edf0f4',

                            drawTicks: false

                        },

                        ticks: {

                            precision: 0,

                            padding: 8,

                            color: '#a0a8b5'

                        }

                    },

                    y: {

                        border: {

                            display: false

                        },

                        grid: {

                            display: false

                        },

                        ticks: {

                            color: '#697586',

                            font: {

                                size: 12,

                                weight: '500'

                            }

                        }

                    }

                },

                plugins: {

                    legend: {

                        display: false

                    },
                }

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | COLLECTORS BY ROUTE
    |--------------------------------------------------------------------------
    */

    const routeCanvas =
        document.getElementById('collectorsRouteChart');

    if (routeCanvas) {

        const routeLabels =
            <?= json_encode($routeLabels) ?>;

        const routeValues =
            <?= json_encode($routeValues) ?>;


        new Chart(routeCanvas, {

            type: 'bar',

            data: {

                labels: routeLabels,

                datasets: [{

                    label: 'Garbage Collectors',

                    data: routeValues,

                    borderWidth: 0,

                    borderRadius: 8,

                    borderSkipped: false,

                    barPercentage: 0.58,

                    categoryPercentage: 0.72

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                scales: {

                    x: {

                        grid: {

                            display: false

                        },

                        border: {

                            display: false

                        },

                        ticks: {

                            color: '#8b95a5',

                            font: {

                                size: 11,

                                weight: '500'

                            }

                        }

                    },

                    y: {

                        beginAtZero: true,

                        border: {

                            display: false

                        },

                        grid: {

                            color: '#edf0f4',

                            drawTicks: false

                        },

                        ticks: {

                            precision: 0,

                            padding: 10,

                            color: '#a0a8b5'

                        }

                    }

                },

                plugins: {

                    legend: {

                        display: false

                    },
                }

            }

        });

    }

});

</script>


<!-- =========================================================
     MODERN DASHBOARD STYLES
========================================================= -->

<style>
/* =========================================================
   BARANGAY MAP
========================================================= */

.modern-map-card {
    margin-bottom: 18px;
    overflow: hidden;
    border: 1px solid #e8ecf1;
    border-radius: 17px;
    background: #ffffff;
    box-shadow: 0 4px 18px rgba(20, 30, 50, 0.045);
}

.modern-map-header {
    display: flex;
    justify-content: space-between;
    padding: 21px 23px 14px;
}

.modern-map-header h3 {
    margin: 0;
    color: #17202a;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: -0.2px;
}

.modern-map-header p {
    margin: 5px 0 0;
    color: #929ba8;
    font-size: 12px;
    line-height: 1.5;
}

#barangayMap {
    width: 100%;
    height: 420px;
}

/* =========================================================
   PAGE HEADER
========================================================= */

.dashboard-header {
    margin-bottom: 24px;
}

.dashboard-eyebrow {
    margin-bottom: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #8b95a5;
}


/* =========================================================
   STAT CARDS
========================================================= */

.dashboard-stats {
    gap: 16px;
    margin-bottom: 22px;
}

.modern-stat-card {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    min-height: 125px;
    padding: 20px;
    border: 1px solid #e9edf2;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 4px 18px rgba(20, 30, 50, 0.045);
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.modern-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(20, 30, 50, 0.08);
}

.modern-stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 44px;
    width: 44px;
    height: 44px;
    border-radius: 12px;
}

.modern-stat-icon.green {
    background: #eaf8f0;
    color: #22a06b;
}

.modern-stat-icon.blue {
    background: #edf4ff;
    color: #377dff;
}

.modern-stat-icon.purple {
    background: #f3edff;
    color: #8254d8;
}

.modern-stat-icon.amber {
    background: #fff6df;
    color: #d89b16;
}

.modern-stat-content {
    min-width: 0;
}

.modern-stat-label {
    margin-bottom: 4px;
    color: #7b8494;
    font-size: 12px;
    font-weight: 600;
}

.modern-stat-value {
    color: #17202a;
    font-size: 28px;
    line-height: 1.1;
    font-weight: 750;
    letter-spacing: -0.7px;
}

.modern-stat-note {
    margin-top: 7px;
    color: #a0a8b5;
    font-size: 11px;
}


/* =========================================================
   CHART GRID
========================================================= */

.dashboard-charts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 18px;
}


/* =========================================================
   CHART CARD
========================================================= */

.modern-chart-card {
    min-width: 0;
    min-height: 420px;
    padding: 0;
    overflow: hidden;
    border: 1px solid #e8ecf1;
    border-radius: 17px;
    background: #ffffff;
    box-shadow: 0 4px 18px rgba(20, 30, 50, 0.045);
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.modern-chart-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 9px 28px rgba(20, 30, 50, 0.075);
}


/* =========================================================
   CHART HEADER
========================================================= */

.modern-chart-header {
    display: flex;
    justify-content: space-between;
    padding: 21px 23px 8px;
}

.chart-title-row {
    display: flex;
    align-items: center;
    gap: 9px;
}

.modern-chart-header h3 {
    margin: 0;
    color: #17202a;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: -0.2px;
}

.modern-chart-header p {
    margin: 5px 0 0;
    color: #929ba8;
    font-size: 12px;
    line-height: 1.5;
}

.chart-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 8px;
    border: 1px solid #edf0f3;
    border-radius: 7px;
    background: #f8f9fb;
    color: #7d8795;
    font-size: 10px;
    font-weight: 650;
    white-space: nowrap;
}


/* =========================================================
   CHART CONTAINERS
========================================================= */

.modern-chart-container {
    position: relative;
    width: 100%;
    height: 325px;
    padding: 10px 22px 22px;
    box-sizing: border-box;
}

.modern-doughnut-container {
    position: relative;
    width: 100%;
    max-width: 400px;
    height: 325px;
    margin: 0 auto;
    padding: 8px 20px 18px;
    box-sizing: border-box;
}


/* =========================================================
   EMPTY STATES
========================================================= */

.modern-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 290px;
    margin: 15px 20px 20px;
    border: 1px dashed #e0e5eb;
    border-radius: 13px;
    background: #fafbfc;
    text-align: center;
}

.modern-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin-bottom: 12px;
    border-radius: 13px;
    background: #f0f3f6;
    color: #9aa3af;
}

.modern-empty strong {
    color: #4e5968;
    font-size: 13px;
}

.modern-empty span {
    max-width: 240px;
    margin-top: 5px;
    color: #9ba4b1;
    font-size: 11px;
}


/* =========================================================
   BOTTOM PANELS
========================================================= */

.modern-bottom-panels {
    gap: 18px;
    margin-top: 2px;
}

.modern-list-panel {
    overflow: hidden;
    padding: 0;
    border: 1px solid #e8ecf1;
    border-radius: 17px;
    background: #ffffff;
    box-shadow: 0 4px 18px rgba(20, 30, 50, 0.045);
}


/* =========================================================
   LIST HEADER
========================================================= */

.modern-list-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    padding: 21px 23px 14px;
}

.modern-list-header h3 {
    margin: 0;
    color: #17202a;
    font-size: 16px;
    font-weight: 700;
}

.modern-list-header p {
    margin: 5px 0 0;
    color: #929ba8;
    font-size: 12px;
}

.modern-view-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #3d78d8;
    font-size: 11px;
    font-weight: 650;
    text-decoration: none;
    white-space: nowrap;
}

.modern-view-link:hover {
    color: #245eb9;
}

.modern-view-link span {
    font-size: 15px;
    transition: transform 0.2s ease;
}

.modern-view-link:hover span {
    transform: translateX(3px);
}


/* =========================================================
   LIST
========================================================= */

.modern-list {
    border-top: 1px solid #f0f2f5;
}

.modern-list-item {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 62px;
    padding: 9px 23px;
    border-bottom: 1px solid #f0f2f5;
}

.modern-list-item:last-child {
    border-bottom: none;
}

.modern-list-item-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 35px;
    width: 35px;
    height: 35px;
    border-radius: 9px;
    background: #f1f6ff;
    color: #4c83dd;
}

.modern-list-item-content {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}

.modern-list-item-content strong {
    overflow: hidden;
    color: #3b4654;
    font-size: 12px;
    font-weight: 650;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.modern-list-item-content span {
    margin-top: 3px;
    color: #9aa3af;
    font-size: 10px;
}


/* =========================================================
   STATUS
========================================================= */

.modern-status {
    padding: 5px 9px;
    border-radius: 7px;
    background: #f2f7f4;
    color: #4b8b68;
    font-size: 10px;
    font-weight: 650;
    white-space: nowrap;
}


/* =========================================================
   COLLECTOR AVATAR
========================================================= */

.collector-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 35px;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #f0edff;
    color: #7658c9;
    font-size: 12px;
    font-weight: 700;
}

.collector-active {
    padding: 5px 9px;
    border-radius: 7px;
    background: #edf8f2;
    color: #3e8a61;
    font-size: 10px;
    font-weight: 650;
}


/* =========================================================
   EMPTY LIST
========================================================= */

.modern-list-empty {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 130px;
    padding: 20px 23px;
    border-top: 1px solid #f0f2f5;
}

.modern-list-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #f2f4f7;
    color: #9aa3af;
    font-size: 18px;
}

.modern-list-empty div:last-child {
    display: flex;
    flex-direction: column;
}

.modern-list-empty strong {
    color: #5c6673;
    font-size: 12px;
}

.modern-list-empty span {
    margin-top: 3px;
    color: #9aa3af;
    font-size: 10px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .dashboard-charts {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 900px) {

    .dashboard-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

}


@media (max-width: 600px) {

    .dashboard-stats {
        grid-template-columns: 1fr;
    }

    .modern-stat-card {
        min-height: 110px;
    }

    .modern-chart-card {
        min-height: 390px;
        border-radius: 14px;
    }

    .modern-chart-header {
        padding: 18px 18px 8px;
    }

    .modern-chart-container {
        height: 290px;
        padding: 8px 12px 18px;
    }

    .modern-doughnut-container {
        height: 290px;
        padding: 5px 10px 15px;
    }

    .chart-badge {
        display: none;
    }

    .modern-list-header {
        padding: 18px;
    }

    .modern-list-item {
        padding: 9px 18px;
    }

    .modern-list-empty {
        padding: 18px;
    }

}

</style>


<?php include __DIR__ . '/includes/admin_foot.php'; ?>