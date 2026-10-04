<?php
session_start();
include '../../config.php';
include '../../db.php';

$countRow = $conn->query('SELECT COUNT(*) AS c FROM lgu_admin')->fetch_assoc();
if ((int) $countRow['c'] > 0) {
    header('Location: login.php');
    exit;
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');

    if ($email === '' || $password === '' || $firstName === '' || $lastName === '') {
        $errors[] = 'All fields are required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO lgu_admin (email, password, first_name, last_name) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $email, $hashed, $firstName, $lastName);
        $stmt->execute();
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create LGU Admin | <?= defined('SITE_NAME') ? SITE_NAME : 'ECollect'; ?></title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-auth-wrapper">
        <div class="admin-auth-card hero-card-glass">
            <h2 class="text-center" style="margin-bottom:0.25rem;">Create First LGU Admin</h2>
            <p class="text-center" style="margin-bottom:1.5rem;">One-time setup &mdash; only works while lgu_admin table is empty.</p>

            <?php if ($success): ?>
                <div class="alert alert-success"><p>Admin account created successfully.</p></div>
                <a href="login.php" class="btn btn-primary btn-block">Go to Login &rarr;</a>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="setup_admin.php">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Create Admin Account</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
