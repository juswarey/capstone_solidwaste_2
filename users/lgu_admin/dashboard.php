<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

include 'includes/auth.php';


// =========================================================
// HELPER FUNCTIONS
// =========================================================

function countRows($conn, $table)
{
    $allowedTables = [
        'barangay_admin',
        'garbage_collector',
        'truck',
        'route',
        'waste_category',
        'resident',
        'announcement',
        'feedback_report',
        'reports',
        'collection_schedule'
    ];

    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    $result = $conn->query(
        "SELECT COUNT(*) AS c FROM `$table`"
    );

    return $result
        ? (int) $result->fetch_assoc()['c']
        : 0;
}


// =========================================================
// SUMMARY STATISTICS
// =========================================================

$stats = [
    'barangayadmins' => countRows($conn, 'barangay_admin'),
    'collectors'      => countRows($conn, 'garbage_collector'),
    'trucks'          => countRows($conn, 'truck'),
    'routes'          => countRows($conn, 'route'),
    'waste'           => countRows($conn, 'waste_category'),
    'residents'       => countRows($conn, 'resident'),
    'announcements'   => countRows($conn, 'announcement'),
    'feedback'        => countRows($conn, 'feedback_report'),
    'reports'         => countRows($conn, 'reports'),
    'schedules'       => countRows($conn, 'collection_schedule'),
];


// =========================================================
// TODAY'S COLLECTIONS
// =========================================================

$today = date('l');

$todayCollections = 0;

$todayStmt = $conn->prepare(
    "SELECT COUNT(*) AS c
     FROM collection_schedule
     WHERE day_of_week = ?"
);

if ($todayStmt) {

    $todayStmt->bind_param(
        's',
        $today
    );

    $todayStmt->execute();

    $result = $todayStmt->get_result();

    if ($result) {
        $todayCollections =
            (int) $result->fetch_assoc()['c'];
    }
}


// =========================================================
// SCHEDULE STATUS
// =========================================================

$scheduleStatus = [];

$statusResult = $conn->query(
    "SELECT
        schedule_status,
        COUNT(*) AS total
     FROM collection_schedule
     GROUP BY schedule_status"
);

if ($statusResult) {

    while ($row = $statusResult->fetch_assoc()) {

        $status = trim(
            $row['schedule_status'] ?? ''
        );

        if ($status === '') {
            $status = 'Unknown';
        }

        $scheduleStatus[] = [
            'status' => $status,
            'total'  => (int) $row['total']
        ];
    }
}


// =========================================================
// COLLECTIONS BY DAY
// =========================================================

$dayOrder = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday'
];

$dayCounts = [];

foreach ($dayOrder as $day) {
    $dayCounts[$day] = 0;
}

$dayResult = $conn->query(
    "SELECT
        day_of_week,
        COUNT(*) AS total
     FROM collection_schedule
     GROUP BY day_of_week"
);

if ($dayResult) {

    while ($row = $dayResult->fetch_assoc()) {

        $day = trim(
            $row['day_of_week'] ?? ''
        );

        if (isset($dayCounts[$day])) {

            $dayCounts[$day] =
                (int) $row['total'];
        }
    }
}


// =========================================================
// WASTE CATEGORY DISTRIBUTION
// =========================================================

$wasteCategories = [];

$wasteResult = $conn->query(
    "SELECT
        wc.category_name,
        COUNT(cs.collection_schedule_id) AS total
     FROM waste_category wc
     LEFT JOIN collection_schedule cs
        ON cs.waste_category_id =
           wc.waste_category_id
     GROUP BY
        wc.waste_category_id,
        wc.category_name
     ORDER BY total DESC"
);

if ($wasteResult) {

    while ($row = $wasteResult->fetch_assoc()) {

        $wasteCategories[] = [
            'category' =>
                $row['category_name'] ?? 'Unknown',

            'total' =>
                (int) $row['total']
        ];
    }
}


// =========================================================
// RESIDENT REPORT TYPES
// =========================================================

$reportTypes = [];

$reportResult = $conn->query(
    "SELECT
        report_type,
        COUNT(*) AS total
     FROM feedback_report
     GROUP BY report_type
     ORDER BY total DESC"
);

if ($reportResult) {

    while ($row = $reportResult->fetch_assoc()) {

        $type = trim(
            $row['report_type'] ?? ''
        );

        if ($type === '') {
            $type = 'Unknown';
        }

        $reportTypes[] = [
            'type'  => $type,
            'total' => (int) $row['total']
        ];
    }
}


// =========================================================
// RECENT FEEDBACK REPORTS
// =========================================================

$recentFeedback = $conn->query(
    "SELECT
        fr.feedback_report_id,
        fr.report_type,
        r.first_name,
        r.last_name
     FROM feedback_report fr
     LEFT JOIN resident r
        ON fr.resident_id = r.resident_id
     ORDER BY
        fr.feedback_report_id DESC
     LIMIT 6"
);


// =========================================================
// RECENT ANNOUNCEMENTS
// =========================================================

$recentAnnouncements = $conn->query(
    "SELECT
        title,
        published_timestamp
     FROM announcement
     ORDER BY
        published_timestamp DESC
     LIMIT 6"
);


// =========================================================
// RECENT REPORT RECORDS
// =========================================================

$recentReports = $conn->query(
    "SELECT
        report_type,
        generated_timestamp
     FROM reports
     ORDER BY
        report_id DESC
     LIMIT 6"
);


// =========================================================
// CHART JSON DATA
// =========================================================

$scheduleStatusLabels = [];
$scheduleStatusValues = [];

foreach ($scheduleStatus as $item) {

    $scheduleStatusLabels[] =
        $item['status'];

    $scheduleStatusValues[] =
        $item['total'];
}


$wasteLabels = [];
$wasteValues = [];

foreach ($wasteCategories as $item) {

    $wasteLabels[] =
        $item['category'];

    $wasteValues[] =
        $item['total'];
}


$reportLabels = [];
$reportValues = [];

foreach ($reportTypes as $item) {

    $reportLabels[] =
        $item['type'];

    $reportValues[] =
        $item['total'];
}


include 'includes/layout_top.php';
?>


<!-- =====================================================
     DASHBOARD
===================================================== -->

<div class="lgu-dashboard">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="dashboard-header">

        <div>

            <span class="dashboard-eyebrow">
                LGU ADMINISTRATION
            </span>

            <h2>
                Welcome back<?= $adminName
                    ? ', ' . htmlspecialchars($adminName)
                    : ''
                ?>
            </h2>

            <p>
                System-wide overview of the barangay
                waste collection network.
            </p>

        </div>

    </div>


    <!-- =================================================
         STAT CARDS
    ================================================== -->

    <div class="lgu-stats-grid">


        <!-- Barangay Admins -->
        <div class="lgu-stat-card">

            <div class="lgu-stat-icon green">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3L12 3z"/>
                </svg>
            </div>

            <div>

                <div class="lgu-stat-value">
                    <?= number_format($stats['barangayadmins']) ?>
                </div>

                <div class="lgu-stat-label">
                    Barangay Admins
                </div>

            </div>

        </div>


        <!-- Collectors -->
        <div class="lgu-stat-card">

            <div class="lgu-stat-icon blue">
                <svg viewBox="0 0 24 24">
                    <path d="M4 16a2 2 0 1 0 4 0 2 2 0 0 0-4 0zm12 0a2 2 0 1 0 4 0 2 2 0 0 0-4 0zM3 7h11v7H6.5A2.5 2.5 0 0 0 4 16.5V7zm11 0h2.5L20 11v5h-2"/>
                </svg>
            </div>

            <div>

                <div class="lgu-stat-value">
                    <?= number_format($stats['collectors']) ?>
                </div>

                <div class="lgu-stat-label">
                    Garbage Collectors
                </div>

            </div>

        </div>


        <!-- Trucks -->
        <div class="lgu-stat-card">

            <div class="lgu-stat-icon amber">
                <svg viewBox="0 0 24 24">
                    <path d="M3 13h11V6H3v7zm12 0h2.5l2.5 3v-3h1V9.5L18.5 7H15v6zM6 18a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>
                </svg>
            </div>

            <div>

                <div class="lgu-stat-value">
                    <?= number_format($stats['trucks']) ?>
                </div>

                <div class="lgu-stat-label">
                    Fleet Trucks
                </div>

            </div>

        </div>


        <!-- Routes -->
        <div class="lgu-stat-card">

            <div class="lgu-stat-icon purple">
                <svg viewBox="0 0 24 24">
                    <path d="M6 3a3 3 0 1 0 3 3c0-.35-.06-.69-.18-1H15a3 3 0 1 1 0 2H9.82A3 3 0 1 0 9 11v2.18A3 3 0 1 0 11 16v-2.18a3 3 0 0 0-2-2.82V9h6a3 3 0 1 0 0-2H9.82A3 3 0 0 0 6 3z"/>
                </svg>
            </div>

            <div>

                <div class="lgu-stat-value">
                    <?= number_format($stats['routes']) ?>
                </div>

                <div class="lgu-stat-label">
                    Total Routes
                </div>

            </div>

        </div>


        <!-- Residents -->
        <div class="lgu-stat-card">

            <div class="lgu-stat-icon green">
                <svg viewBox="0 0 24 24">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                </svg>
            </div>

            <div>

                <div class="lgu-stat-value">
                    <?= number_format($stats['residents']) ?>
                </div>

                <div class="lgu-stat-label">
                    Residents
                </div>

            </div>

        </div>


        <!-- Schedules -->
        <div class="lgu-stat-card">

            <div class="lgu-stat-icon blue">
                <svg viewBox="0 0 24 24">
                    <path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 8H5v10h14V10z"/>
                </svg>
            </div>

            <div>

                <div class="lgu-stat-value">
                    <?= number_format($stats['schedules']) ?>
                </div>

                <div class="lgu-stat-label">
                    Collection Schedules
                </div>

            </div>

        </div>


        <!-- Feedback -->
        <div class="lgu-stat-card">

            <div class="lgu-stat-icon amber">
                <svg viewBox="0 0 24 24">
                    <path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>
                </svg>
            </div>

            <div>

                <div class="lgu-stat-value">
                    <?= number_format($stats['feedback']) ?>
                </div>

                <div class="lgu-stat-label">
                    Resident Reports
                </div>

            </div>

        </div>


        <!-- Today -->
        <div class="lgu-stat-card highlight">

            <div class="lgu-stat-icon green">
                <svg viewBox="0 0 24 24">
                    <path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 8H5v10h14V10z"/>
                </svg>
            </div>

            <div>

                <div class="lgu-stat-value">
                    <?= number_format($todayCollections) ?>
                </div>

                <div class="lgu-stat-label">
                    Scheduled Today
                </div>

                <div class="lgu-stat-small">
                    <?= htmlspecialchars($today) ?>
                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         CHART ROW 1
    ================================================== -->

    <div class="dashboard-chart-grid">


        <!-- Collection Activity -->

        <div class="dashboard-card chart-card large">

            <div class="dashboard-card-header">

                <div>

                    <span class="card-eyebrow">
                        COLLECTION ACTIVITY
                    </span>

                    <h3>
                        Weekly Collection Schedule
                    </h3>

                </div>

                <span class="chart-info">
                    <?= number_format($stats['schedules']) ?>
                    total schedules
                </span>

            </div>

            <div class="chart-container">

                <canvas id="weeklyCollectionChart"></canvas>

            </div>

        </div>


        <!-- Schedule Status -->

        <div class="dashboard-card chart-card">

            <div class="dashboard-card-header">

                <div>

                    <span class="card-eyebrow">
                        SCHEDULE STATUS
                    </span>

                    <h3>
                        Current Status
                    </h3>

                </div>

            </div>

            <div class="chart-container doughnut-container">

                <canvas id="scheduleStatusChart"></canvas>

            </div>

        </div>

    </div>


    <!-- =================================================
         CHART ROW 2
    ================================================== -->

    <div class="dashboard-chart-grid">


        <!-- Waste -->

        <div class="dashboard-card chart-card">

            <div class="dashboard-card-header">

                <div>

                    <span class="card-eyebrow">
                        WASTE MANAGEMENT
                    </span>

                    <h3>
                        Waste Category Distribution
                    </h3>

                </div>

            </div>

            <div class="chart-container">

                <canvas id="wasteCategoryChart"></canvas>

            </div>

        </div>


        <!-- Reports -->

        <div class="dashboard-card chart-card">

            <div class="dashboard-card-header">

                <div>

                    <span class="card-eyebrow">
                        RESIDENT FEEDBACK
                    </span>

                    <h3>
                        Report Types
                    </h3>

                </div>

                <a
                    href="feedback.php"
                    class="dashboard-link"
                >
                    View all →
                </a>

            </div>

            <?php if (empty($reportTypes)): ?>

                <div class="dashboard-empty chart-empty">

                    <div class="empty-icon">—</div>

                    <strong>No resident feedback yet</strong>

                    <span>
                        Feedback submitted by residents will appear here.
                    </span>

                </div>

            <?php else: ?>

                <div class="chart-container">

                    <canvas id="residentReportsChart"></canvas>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- =================================================
         RECENT ACTIVITY
    ================================================== -->

    <div class="dashboard-content-grid">


        <!-- Recent Feedback -->

        <div class="dashboard-card">

            <div class="dashboard-card-header">

                <div>

                    <span class="card-eyebrow">
                        COMMUNITY
                    </span>

                    <h3>
                        Recent Resident Reports
                    </h3>

                </div>

                <a
                    href="feedback.php"
                    class="dashboard-link"
                >
                    View all →
                </a>

            </div>


            <?php if (
                !$recentFeedback ||
                $recentFeedback->num_rows === 0
            ): ?>

                <div class="dashboard-empty">

                    <div class="empty-icon">
                        —
                    </div>

                    <strong>
                        No resident reports yet
                    </strong>

                    <span>
                        Reports submitted by residents
                        will appear here.
                    </span>

                </div>

            <?php else: ?>

                <div class="activity-list">

                    <?php while (
                        $f = $recentFeedback->fetch_assoc()
                    ): ?>

                        <?php
                        $residentName = trim(
                            ($f['first_name'] ?? '') .
                            ' ' .
                            ($f['last_name'] ?? '')
                        );

                        if ($residentName === '') {
                            $residentName = 'Unknown resident';
                        }
                        ?>

                        <div class="activity-item">

                            <div class="activity-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>
                                </svg>
                            </div>

                            <div class="activity-main">

                                <strong>
                                    <?= htmlspecialchars(
                                        $f['report_type'] ?? 'Report'
                                    ) ?>
                                </strong>

                                <span>
                                    <?= htmlspecialchars(
                                        $residentName
                                    ) ?>
                                </span>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php endif; ?>

        </div>


        <!-- Announcements -->

        <div class="dashboard-card">

            <div class="dashboard-card-header">

                <div>

                    <span class="card-eyebrow">
                        COMMUNICATION
                    </span>

                    <h3>
                        Recent Announcements
                    </h3>

                </div>

                <a
                    href="announcements.php"
                    class="dashboard-link"
                >
                    View all →
                </a>

            </div>


            <?php if (
                !$recentAnnouncements ||
                $recentAnnouncements->num_rows === 0
            ): ?>

                <div class="dashboard-empty">

                    <div class="empty-icon">
                        —
                    </div>

                    <strong>
                        No announcements yet
                    </strong>

                    <span>
                        Published announcements will
                        appear here.
                    </span>

                </div>

            <?php else: ?>

                <div class="announcement-list">

                    <?php while (
                        $a = $recentAnnouncements->fetch_assoc()
                    ): ?>

                        <div class="announcement-item">

                            <div>

                                <strong>
                                    <?= htmlspecialchars(
                                        $a['title']
                                    ) ?>
                                </strong>

                                <span>
                                    <?= $a['published_timestamp']
                                        ? date(
                                            'M d, Y',
                                            strtotime(
                                                $a['published_timestamp']
                                            )
                                        )
                                        : ''
                                    ?>
                                </span>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- =================================================
         SYSTEM OVERVIEW
    ================================================== -->

    <div class="dashboard-card system-overview">

        <div class="dashboard-card-header">

            <div>

                <span class="card-eyebrow">
                    SYSTEM OVERVIEW
                </span>

                <h3>
                    Waste Collection Network
                </h3>

            </div>

        </div>


        <div class="overview-grid">

            <div class="overview-item">

                <span class="overview-label">
                    Barangay Administrators
                </span>

                <strong>
                    <?= number_format(
                        $stats['barangayadmins']
                    ) ?>
                </strong>

            </div>


            <div class="overview-item">

                <span class="overview-label">
                    Garbage Collectors
                </span>

                <strong>
                    <?= number_format(
                        $stats['collectors']
                    ) ?>
                </strong>

            </div>


            <div class="overview-item">

                <span class="overview-label">
                    Fleet Trucks
                </span>

                <strong>
                    <?= number_format(
                        $stats['trucks']
                    ) ?>
                </strong>

            </div>


            <div class="overview-item">

                <span class="overview-label">
                    Collection Routes
                </span>

                <strong>
                    <?= number_format(
                        $stats['routes']
                    ) ?>
                </strong>

            </div>


            <div class="overview-item">

                <span class="overview-label">
                    Waste Categories
                </span>

                <strong>
                    <?= number_format(
                        $stats['waste']
                    ) ?>
                </strong>

            </div>


            <div class="overview-item">

                <span class="overview-label">
                    Registered Residents
                </span>

                <strong>
                    <?= number_format(
                        $stats['residents']
                    ) ?>
                </strong>

            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     CHART.JS
===================================================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

    /*
     * ----------------------------------------------------
     * GLOBAL CHART SETTINGS
     * ----------------------------------------------------
     *
     * No hover popup.
     * No hover movement.
     * No animation.
     */

    Chart.defaults.font.family =
        "'Inter', 'Segoe UI', Arial, sans-serif";

    Chart.defaults.font.size = 12;

    Chart.defaults.color = '#8b95a5';

    Chart.defaults.animation = false;

    Chart.defaults.hover.mode = null;

    Chart.defaults.plugins.tooltip.enabled = false;


    /*
     * ----------------------------------------------------
     * WEEKLY COLLECTION CHART
     * ----------------------------------------------------
     */

    new Chart(
        document.getElementById(
            'weeklyCollectionChart'
        ),
        {
            type: 'bar',

            data: {

                labels: <?= json_encode(
                    array_keys($dayCounts)
                ) ?>,

                datasets: [

                    {
                        label: 'Schedules',

                        data: <?= json_encode(
                            array_values($dayCounts)
                        ) ?>,

                        borderRadius: 7,

                        borderSkipped: false

                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {
                        display: false
                    },

                    tooltip: {
                        enabled: false
                    }

                },

                hover: {
                    mode: null
                },

                scales: {

                    x: {

                        grid: {
                            display: false
                        },

                        border: {
                            display: false
                        }

                    },

                    y: {

                        beginAtZero: true,

                        ticks: {
                            precision: 0
                        },

                        grid: {
                            color: '#eef1f5'
                        },

                        border: {
                            display: false
                        }

                    }

                }

            }

        }
    );


    /*
     * ----------------------------------------------------
     * SCHEDULE STATUS
     * ----------------------------------------------------
     */

    new Chart(
        document.getElementById(
            'scheduleStatusChart'
        ),
        {
            type: 'doughnut',

            data: {

                labels: <?= json_encode(
                    $scheduleStatusLabels
                ) ?>,

                datasets: [

                    {
                        data: <?= json_encode(
                            $scheduleStatusValues
                        ) ?>,

                        borderWidth: 0,

                        spacing: 3,

                        hoverOffset: 0
                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '72%',

                plugins: {

                    legend: {

                        position: 'bottom',

                        labels: {

                            usePointStyle: true,

                            pointStyle: 'circle',

                            padding: 18
                        }

                    },

                    tooltip: {
                        enabled: false
                    }

                },

                hover: {
                    mode: null
                }

            }

        }
    );


    /*
     * ----------------------------------------------------
     * WASTE CATEGORY
     * ----------------------------------------------------
     */

    new Chart(
        document.getElementById(
            'wasteCategoryChart'
        ),
        {
            type: 'bar',

            data: {

                labels: <?= json_encode(
                    $wasteLabels
                ) ?>,

                datasets: [

                    {
                        label: 'Schedules',

                        data: <?= json_encode(
                            $wasteValues
                        ) ?>,

                        borderRadius: 7,

                        borderSkipped: false
                    }

                ]

            },

            options: {

                indexAxis: 'y',

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {
                        display: false
                    },

                    tooltip: {
                        enabled: false
                    }

                },

                hover: {
                    mode: null
                },

                scales: {

                    x: {

                        beginAtZero: true,

                        ticks: {
                            precision: 0
                        },

                        grid: {
                            color: '#eef1f5'
                        },

                        border: {
                            display: false
                        }

                    },

                    y: {

                        grid: {
                            display: false
                        },

                        border: {
                            display: false
                        }

                    }

                }

            }

        }
    );


    /*
     * ----------------------------------------------------
     * RESIDENT REPORT TYPES
     * ----------------------------------------------------
     */

    new Chart(
        document.getElementById(
            'residentReportsChart'
        ),
        {
            type: 'doughnut',

            data: {

                labels: <?= json_encode(
                    $reportLabels
                ) ?>,

                datasets: [

                    {
                        data: <?= json_encode(
                            $reportValues
                        ) ?>,

                        borderWidth: 0,

                        spacing: 3,

                        hoverOffset: 0
                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '68%',

                plugins: {

                    legend: {

                        position: 'bottom',

                        labels: {

                            usePointStyle: true,

                            pointStyle: 'circle',

                            padding: 15
                        }

                    },

                    tooltip: {
                        enabled: false
                    }

                },

                hover: {
                    mode: null
                }

            }

        }
    );

</script>


<!-- =====================================================
     DASHBOARD CSS
===================================================== -->

<style>

    /* =================================================
       MAIN
    ================================================= */

    .lgu-dashboard {
        width: 100%;
    }


    /* =================================================
       HEADER
    ================================================= */

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 24px;
    }


    .dashboard-eyebrow,
    .card-eyebrow {
        display: block;
        color: #8b95a5;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }


    .dashboard-header h2 {
        margin: 0 0 6px;
        color: #000000;
        font-size: 28px;
        font-weight: 700;
    }


    .dashboard-header p {
        margin: 0;
        color: #7b8494;
        font-size: 14px;
    }



    /* =================================================
       STAT CARDS
    ================================================= */

    .lgu-stats-grid {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }


    .lgu-stat-card {
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 84px;
        padding: 15px;
        background: #fff;
        border: 1px solid #e6eaf0;
        border-radius: 13px;
        box-shadow:
            0 5px 20px rgba(15, 23, 42, 0.035);
    }


    .lgu-stat-card.highlight {
        border-color: #cfe8d7;
        background: #fbfefc;
    }


    .lgu-stat-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 10px;
    }


    .lgu-stat-icon svg {
        width: 20px;
        height: 20px;
        fill: currentColor;
    }


    .lgu-stat-icon.green {
        background: #eaf7ef;
        color: #19924a;
    }


    .lgu-stat-icon.blue {
        background: #eaf2ff;
        color: #3478db;
    }


    .lgu-stat-icon.amber {
        background: #fff5df;
        color: #c27a09;
    }


    .lgu-stat-icon.purple {
        background: #f1edff;
        color: #7357c7;
    }


    .lgu-stat-value {
        color: #1f2937;
        font-size: 23px;
        line-height: 1.1;
        font-weight: 700;
    }


    .lgu-stat-label {
        margin-top: 4px;
        color: #7d8796;
        font-size: 11px;
    }


    .lgu-stat-small {
        margin-top: 2px;
        color: #19924a;
        font-size: 10px;
        font-weight: 600;
    }


    /* =================================================
       CARDS
    ================================================= */

    .dashboard-card {
        background: #fff;
        border: 1px solid #e6eaf0;
        border-radius: 14px;
        box-shadow:
            0 5px 20px rgba(15, 23, 42, 0.035);
        overflow: hidden;
    }


    .dashboard-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        padding: 19px 20px 12px;
    }


    .dashboard-card-header h3 {
        margin: 0;
        color: #293241;
        font-size: 15px;
        font-weight: 700;
    }


    .chart-info {
        color: #9aa3b1;
        font-size: 10px;
        white-space: nowrap;
    }


    .dashboard-link {
        color: #2563eb;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }


    /* =================================================
       CHART GRID
    ================================================= */

    .dashboard-chart-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.6fr)
            minmax(0, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }


    .chart-card {
        min-width: 0;
    }


    .chart-card.large {
        min-width: 0;
    }


    .chart-container {
        position: relative;
        height: 280px;
        padding: 10px 18px 18px;
    }


    .doughnut-container {
        height: 280px;
        max-width: 100%;
    }


    /* =================================================
       RECENT CONTENT
    ================================================= */

    .dashboard-content-grid {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }


    .activity-list,
    .announcement-list {
        padding: 0 20px 10px;
    }


    .activity-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-top: 1px solid #f0f2f5;
    }


    .activity-item:first-child {
        border-top: 0;
    }


    .activity-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 9px;
        background: #f1f5f9;
        color: #64748b;
    }


    .activity-icon svg {
        width: 16px;
        height: 16px;
        fill: currentColor;
    }


    .activity-main {
        min-width: 0;
    }


    .activity-main strong {
        display: block;
        color: #374151;
        font-size: 12px;
        font-weight: 600;
    }


    .activity-main span {
        display: block;
        margin-top: 3px;
        color: #969fac;
        font-size: 11px;
    }


    .announcement-item {
        padding: 12px 0;
        border-top: 1px solid #f0f2f5;
    }


    .announcement-item:first-child {
        border-top: 0;
    }


    .announcement-item strong {
        display: block;
        color: #374151;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }


    .announcement-item span {
        display: block;
        margin-top: 4px;
        color: #969fac;
        font-size: 10px;
    }


    /* =================================================
       EMPTY
    ================================================= */

    .dashboard-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 160px;
        padding: 20px;
        text-align: center;
    }


    .empty-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 9px;
        border-radius: 10px;
        background: #f3f5f7;
        color: #9aa3b1;
        font-size: 18px;
    }


    .dashboard-empty strong {
        color: #4b5563;
        font-size: 12px;
    }


    .dashboard-empty span {
        max-width: 260px;
        margin-top: 4px;
        color: #9aa3b1;
        font-size: 10px;
        line-height: 1.5;
    }


    /* =================================================
       SYSTEM OVERVIEW
    ================================================= */

    .system-overview {
        margin-bottom: 20px;
    }


    .overview-grid {
        display: grid;
        grid-template-columns:
            repeat(6, minmax(0, 1fr));
        padding: 5px 20px 20px;
    }


    .overview-item {
        padding: 13px 15px;
        border-right: 1px solid #edf0f3;
    }


    .overview-item:last-child {
        border-right: 0;
    }


    .overview-label {
        display: block;
        margin-bottom: 7px;
        color: #969fac;
        font-size: 10px;
    }


    .overview-item strong {
        color: #293241;
        font-size: 19px;
    }


    /* =================================================
       RESPONSIVE
    ================================================= */

    @media (max-width: 1200px) {

        .lgu-stats-grid {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
        }

        .overview-grid {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
        }

        .overview-item {
            border-bottom: 1px solid #edf0f3;
        }

    }


    @media (max-width: 900px) {

        .dashboard-chart-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-content-grid {
            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 700px) {

        .lgu-stats-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .dashboard-header {
            flex-direction: column;
        }


        .overview-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

    }


    @media (max-width: 480px) {

        .lgu-stats-grid {
            grid-template-columns: 1fr;
        }

        .overview-grid {
            grid-template-columns: 1fr;
        }

        .chart-container {
            height: 250px;
        }

    }

</style>


<?php include 'includes/layout_bottom.php'; ?>