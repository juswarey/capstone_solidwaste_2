<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Dashboard';
}

if (!isset($activePage)) {
    $activePage = 'dashboard';
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($pageTitle) ?>
        | LGU Admin &mdash;
        <?= defined('SITE_NAME') ? SITE_NAME : 'ECollect'; ?>
    </title>

    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body class="admin-body">

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="admin-main">

    <header class="admin-topbar">

        <div class="topbar-left">

            <button
                id="sidebarToggle"
                class="hamburger-menu admin-hamburger"
                aria-label="Toggle sidebar"
            >
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>

            <h1 class="topbar-title">
                <?= htmlspecialchars($pageTitle) ?>
            </h1>

        </div>

        <div class="topbar-right">

            <div class="topbar-clock-wrap">
                <span id="clock" class="digital-clock">
                    00:00:00 AM
                </span>

                <span class="header-date">
                    <?= date('F d, Y'); ?>
                </span>
            </div>

            <button
                id="adminThemeToggle"
                class="btn-icon"
                aria-label="Toggle dark mode"
            >
                <svg
                    class="sun-icon"
                    viewBox="0 0 24 24"
                    width="18"
                    height="18"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/>
                    <line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>

                <svg
                    class="moon-icon"
                    viewBox="0 0 24 24"
                    width="18"
                    height="18"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>

            <div class="admin-profile">

                <div class="admin-avatar">
                    <?= htmlspecialchars(
                        strtoupper(
                            substr($adminName ?: $adminEmail, 0, 1)
                        )
                    ) ?>
                </div>

                <span class="admin-name">
                    <?= htmlspecialchars($adminName ?: $adminEmail) ?>
                </span>

            </div>

        </div>

    </header>

    <main class="admin-content">