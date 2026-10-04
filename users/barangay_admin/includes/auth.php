<?php
require_once __DIR__ . '/bootstrap.php';

if (empty($_SESSION['barangay_admin_id'])) {
    header('Location: login.php');
    exit;
}

$currentBarangayAdminId    = $_SESSION['barangay_admin_id'];
$currentBarangayAdminEmail = $_SESSION['barangay_admin_email'] ?? '';
$currentBarangayAdminName  = $_SESSION['barangay_admin_name'] ?? '';
