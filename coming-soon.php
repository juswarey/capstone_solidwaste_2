<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_GET['role'] ?? '';

$roleInfo = [
    'resident'  => ['label' => 'Resident', 'name' => $_SESSION['resident_name'] ?? '', 'loggedIn' => !empty($_SESSION['resident_id'])],
    'collector' => ['label' => 'Garbage Collector', 'name' => $_SESSION['collector_name'] ?? '', 'loggedIn' => !empty($_SESSION['collector_id'])],
];

$info = $roleInfo[$role] ?? null;

// If someone hits this page directly without a valid session for that role, send them home.
if (!$info || !$info['loggedIn']) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($info['label']) ?> Dashboard | <?= defined('SITE_NAME') ? SITE_NAME : 'ECollect'; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;background:linear-gradient(135deg, var(--primary-light) 0%, var(--bg-body) 100%);">
        <div class="empty-state-card" style="background-color:var(--bg-surface);">
            <div class="empty-state-icon">🚧</div>
            <h3><?= htmlspecialchars($info['label']) ?> Dashboard &mdash; Coming Soon</h3>
            <p>
                Hi<?= $info['name'] ? ' ' . htmlspecialchars($info['name']) : '' ?>! You're logged in successfully,
                but the <?= htmlspecialchars(strtolower($info['label'])) ?> dashboard hasn't been built yet.
                Check back soon.
            </p>
            <a href="logout.php" class="btn btn-outline btn-sm" style="margin-top:1rem;">Log Out</a>
            <a href="index.php" class="btn btn-primary btn-sm" style="margin-top:1rem;margin-left:0.5rem;">Back to Home</a>
        </div>
    </div>
</body>
</html>
