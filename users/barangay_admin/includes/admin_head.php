<?php
/**
 * Shared page shell (opening half).
 * Expects $pageTitle and $activePage to be set before including this file.
 */
if (!isset($pageTitle)) { $pageTitle = 'Dashboard'; }
if (!isset($activePage)) { $activePage = 'dashboard'; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Barangay Admin &mdash; <?= defined('SITE_NAME') ? SITE_NAME : 'ECollect'; ?></title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body class="admin-body">

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="admin-main">
    <?php include __DIR__ . '/topbar.php'; ?>
    <main class="admin-content">
