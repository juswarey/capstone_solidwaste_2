<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../db.php';

if (empty($_SESSION['lgu_admin_id'])) {
    header('Location: ../../../index.php');
    exit;
}

$adminId    = $_SESSION['lgu_admin_id'];
$adminEmail = $_SESSION['lgu_admin_email'] ?? '';
$adminName  = '';

$stmt = $conn->prepare("
    SELECT first_name, last_name, email
    FROM lgu_admin
    WHERE lgu_admin_id = ?
    LIMIT 1
");

$stmt->bind_param('i', $adminId);
$stmt->execute();

$admin = $stmt->get_result()->fetch_assoc();

if ($admin) {
    $adminName  = trim($admin['first_name'] . ' ' . $admin['last_name']);
    $adminEmail = $admin['email'];
}