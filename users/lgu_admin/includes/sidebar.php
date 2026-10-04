<?php
$navItems = [
    'dashboard'      => ['Dashboard', 'dashboard.php'],
    'barangayadmins' => ['Barangay Admins', 'barangay-admins.php'],
    'collectors'     => ['Garbage Collectors', 'collectors.php'],
    'trucks'         => ['Trucks', 'trucks.php'],
    'waste'          => ['Waste Categories', 'waste-categories.php'],
    'routes'         => ['Routes', 'routes.php'],
    'announcements'  => ['Announcements', 'announcements.php'],
    'feedback'       => ['Feedback Reports', 'feedback.php'],
    'residents'      => ['Residents', 'residents.php'],
    'reports'        => ['Reports', 'reports.php'],
];
?>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">

    <div class="sidebar-brand">
        <a href="dashboard.php" class="logo">
            <svg viewBox="0 0 24 24" width="30" height="30"
                 fill="none" stroke="currentColor" stroke-width="2">
                <path d="M7 19a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H7z" />
                <path d="M12 7v10M9 10l3-3 3 3M9 14l3 3 3-3" />
            </svg>

            <div class="logo-text">
                <span class="brand-name">
                    <?= defined('SITE_NAME') ? SITE_NAME : 'ECollect'; ?>
                </span>
                <span class="brand-sub">LGU Admin Panel</span>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-group">
            <ul>

                <?php foreach ($navItems as $key => $item): ?>
                    <li>
                        <a
                            href="<?= htmlspecialchars($item[1]) ?>"
                            class="sidebar-link<?= $activePage === $key ? ' active' : '' ?>"
                        >
                            <?php if ($key === 'dashboard'): ?>

                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                    <path d="M3 3h7v7H3V3zm11 0h7v7h-7V3zM3 14h7v7H3v-7zm11 0h7v7h-7v-7z"/>
                                </svg>

                            <?php elseif ($key === 'barangayadmins'): ?>

                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                    <path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3z"/>
                                    <path d="M8 11c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3z"/>
                                    <path d="M16 13c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                    <path d="M8 13c-2.33 0-7 1.17-7 3.5V19h7v-2.5c0-.93.32-1.76.88-2.47A8.2 8.2 0 0 0 8 13z"/>
                                </svg>

                            <?php elseif ($key === 'collectors'): ?>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                    <path d="M12 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/>
                                    <path d="M5 21c0-4.42 3.13-8 7-8s7 3.58 7 8H5z"/>
                                </svg>

                            <?php elseif ($key === 'trucks'): ?>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                    <path d="M3 6h11v10H3V6zm11 3h3l4 4v3h-7V9z"/>
                                    <circle cx="7" cy="18" r="2"/>
                                    <circle cx="18" cy="18" r="2"/>
                                </svg>

                            <?php elseif ($key === 'waste'): ?>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                    <path d="M6 7h12l-1 14H7L6 7z"/>
                                    <path d="M9 3h6l1 2H8l1-2z"/>
                                    <path d="M4 5h16v2H4V5z"/>
                                </svg>

                            <?php elseif ($key === 'routes'): ?>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                    <path d="M7 4a3 3 0 1 0 0 6h10a3 3 0 1 0 0-6H7zm0 2h10a1 1 0 1 1 0 2H7a1 1 0 1 1 0-2z"/>
                                    <path d="M7 14a3 3 0 1 0 0 6h10a3 3 0 1 0 0-6H7zm0 2h10a1 1 0 1 1 0 2H7a1 1 0 1 1 0-2z"/>
                                </svg>

                            <?php elseif ($key === 'announcements'): ?>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                    <path d="M3 10v4h4l5 4V6l-5 4H3zm12.5 2a3.5 3.5 0 0 0-2.5-3.35v6.7A3.5 3.5 0 0 0 15.5 12z"/>
                                    <path d="M17 7.5a6 6 0 0 1 0 9"/>
                                </svg>

                            <?php elseif ($key === 'feedback'): ?>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                    <path d="M4 3h16a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H9l-5 4v-4H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                                </svg>

                            <?php elseif ($key === 'residents'): ?>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 21c0-4 3.58-7 8-7s8 3 8 7H4z"/>
                                </svg>

                            <?php elseif ($key === 'reports'): ?>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                    <path d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm3 12h2v3H8v-3zm3-5h2v8h-2v-8zm3 3h2v5h-2v-5z"/>
                                </svg>

                            <?php endif; ?>

                            <span><?= htmlspecialchars($item[0]) ?></span>

                        </a>
                    </li>
                <?php endforeach; ?>

            </ul>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-link logout-link">

            <svg viewBox="0 0 24 24" width="18" height="18"
                 fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <path d="M16 17l5-5-5-5"/>
                <path d="M21 12H9"/>
            </svg>

            <span>Log Out</span>

        </a>
    </div>

</aside>