<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['collector_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $conn->prepare('SELECT garbage_collector_id, email, password, first_name, last_name FROM garbage_collector WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $collector = $stmt->get_result()->fetch_assoc();

            $verified = false;
            if ($collector) {
                // Supports properly hashed passwords (password_hash) as well as
                // a plain-text row that may have been inserted manually via
                // phpMyAdmin for a default account. If the match happens on
                // the plain-text branch, strongly consider re-hashing it.
                if (password_verify($password, $collector['password'])) {
                    $verified = true;
                } elseif (hash_equals((string) $collector['password'], $password)) {
                    $verified = true;
                }
            }

            if ($verified) {
                session_regenerate_id(true);
                $_SESSION['collector_id']    = $collector['garbage_collector_id'];
                $_SESSION['collector_email'] = $collector['email'];
                $_SESSION['collector_name']  = trim($collector['first_name'] . ' ' . $collector['last_name']);

                header('Location: dashboard.php');
                exit;
            }

            $errors[] = 'Invalid email or password.';
        } catch (Exception $e) {
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garbage Collector Login | <?= defined('SITE_NAME') ? SITE_NAME : 'ECollect'; ?></title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-auth-wrapper">
        <div class="admin-auth-card hero-card-glass">
            <div class="admin-auth-brand">
                <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M7 19a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H7z" />
                    <path d="M12 7v10M9 10l3-3 3 3M9 14l3 3 3-3" />
                </svg>
                <span class="brand-name"><?= defined('SITE_NAME') ? SITE_NAME : 'ECollect'; ?></span>
            </div>

            <h2 class="text-center" style="margin-bottom:0.25rem;">Garbage Collector Login</h2>
            <p class="text-center" style="margin-bottom:1.5rem;">View your route, schedule, and truck assignment.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="auth-form active">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="collector@iligan.gov.ph" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>

            <p class="text-center" style="margin-top:1.5rem;font-size:0.85rem;">
                Accounts are created by the LGU Admin &mdash; no self-registration.
            </p>
            <p class="text-center" style="margin-top:0.5rem;font-size:0.85rem;">
                <a href="../../index.php">&larr; Back to main site</a>
            </p>
        </div>
    </div>
</body>
</html>
