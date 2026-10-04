<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loginFail(string $role, string $message): void {
    $query = http_build_query([
        'login_error' => $message,
        'login_role'  => $role,
    ]);
    header('Location: index.php?' . $query . '#cta');
    exit;
}

function verifyPassword(string $submitted, string $stored): bool {
    if (password_verify($submitted, $stored)) {
        return true;
    }
    return hash_equals($stored, $submitted);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$role     = $_POST['role'] ?? '';
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($role === '' || $email === '' || $password === '') {
    loginFail($role, 'Please fill in all fields.');
}

switch ($role) {

    case 'lgu_admin':
        try {
            $stmt = $conn->prepare('SELECT lgu_admin_id, email, password FROM lgu_admin WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $admin = $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            $admin = null;
        }

        if ($admin && verifyPassword($password, $admin['password'])) {
            session_regenerate_id(true);

            $_SESSION['lgu_admin_id']    = $admin['lgu_admin_id'];
            $_SESSION['lgu_admin_email'] = $admin['email'];

            // Optional: if you have a name column
            $_SESSION['lgu_admin_name']  = $admin['email'];

            header('Location: users/lgu_admin/dashboard.php');
            exit;
        }
        loginFail($role, 'Invalid lgu admin email or password.');
        break;

    case 'barangay':
        try {
            $stmt = $conn->prepare('SELECT barangay_admin_id, email, password, firstname, lastname FROM barangay_admin WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $bAdmin = $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            $bAdmin = null;
        }

        if ($bAdmin && verifyPassword($password, $bAdmin['password'])) {
            session_regenerate_id(true);
            $_SESSION['barangay_admin_id']    = $bAdmin['barangay_admin_id'];
            $_SESSION['barangay_admin_email'] = $bAdmin['email'];
            $_SESSION['barangay_admin_name']  = trim($bAdmin['firstname'] . ' ' . $bAdmin['lastname']);
            header('Location: users/barangay_admin/dashboard.php');
            exit;
        }
        loginFail($role, 'Invalid barangay admin email or password.');
        break;

    case 'resident':
        try {
            $stmt = $conn->prepare('SELECT resident_id, email, password, first_name FROM resident WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $resident = $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            $resident = null;
        }

        if ($resident && verifyPassword($password, $resident['password'])) {
            session_regenerate_id(true);
            $_SESSION['resident_id']    = $resident['resident_id'];
            $_SESSION['resident_email'] = $resident['email'];
            $_SESSION['resident_name']  = $resident['first_name'];
            header('Location: coming-soon.php?role=resident');
            exit;
        }
        loginFail($role, 'Invalid resident email or password.');
        break;

    case 'garbage_collector':
        try {
            $stmt = $conn->prepare('SELECT garbage_collector_id, email, password, first_name, last_name FROM garbage_collector WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $collector = $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            $collector = null;
        }

        if ($collector && verifyPassword($password, $collector['password'])) {
            session_regenerate_id(true);
            $_SESSION['collector_id']    = $collector['garbage_collector_id'];
            $_SESSION['collector_email'] = $collector['email'];
            $_SESSION['collector_name']  = trim($collector['first_name'] . ' ' . $collector['last_name']);
            header('Location: users/garbage_collector/dashboard.php');
            exit;
        }
        loginFail($role, 'Invalid garbage collector email or password.');
        break;

    default:
        loginFail($role, 'Please select a valid account type.');
}
