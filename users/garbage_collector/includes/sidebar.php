<?php

if (!isset($activePage)) { $activePage = ''; }

$navItems = [
    'dashboard' => ['Dashboard', 'dashboard.php', 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'],
    'route'     => ['My Route', 'route.php', 'M9 3 5 6.99h3V14h2V6.99h3L9 3zm7 14.01V10h-2v7.01h-3L15 21l4-3.99h-3z'],
    'schedule'  => ['My Schedule', 'schedule.php', 'M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 8H5v10h14V10z'],
    'truck'     => ['My Truck', 'truck.php', 'M3 6h11v10H3V6zm11 3h3l4 4v3h-7V9z'],
    'location'  => ['My Location', 'location.php', 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z'],
    'waste'     => ['Waste Categories', 'waste-categories.php', 'M6 7h12l-1 13H7L6 7zm3-4h6l1 2H8l1-2z'],
];
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <a href="dashboard.php" class="logo">
            <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M7 19a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H7z" />
                <path d="M12 7v10M9 10l3-3 3 3M9 14l3 3 3-3" />
            </svg>
            <div class="logo-text">
                <span class="brand-name"><?= defined('SITE_NAME') ? SITE_NAME : 'ECollect'; ?></span>
                <span class="brand-sub">Garbage Collector Panel</span>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-group">
            <ul>
                <?php foreach ($navItems as $key => $item): [$label, $link, $iconPath] = $item; ?>
                    <li>
                        <a href="<?= htmlspecialchars($link) ?>" class="sidebar-link<?= $activePage === $key ? ' active' : '' ?>">
                            <svg class="nav-icon" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="<?= $iconPath ?>"/></svg>
                            <span><?= htmlspecialchars($label) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-link logout-link">
            <svg class="nav-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Log Out</span>
        </a>
    </div>
</aside>
