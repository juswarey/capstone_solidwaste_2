<?php
$pageTitle  = 'Garbage Collectors';
$activePage = 'collectors';
include 'includes/auth.php';
require_once __DIR__ . '/../../route_helpers.php';
ensureRouteScheduleColumns($conn);

$errors  = [];
$success = '';

/**
 * A bcrypt hash from password_hash() is 60 characters. If the live database
 * has a shorter `password` column (older schema, manual edits...) MariaDB
 * silently truncates the hash and that account can never log in. Widen the
 * column automatically when that is the case.
 */
function ensureCollectorPasswordColumn(mysqli $conn): void
{
    $res = $conn->query("SHOW COLUMNS FROM garbage_collector LIKE 'password'");
    $col = $res ? $res->fetch_assoc() : null;
    if ($col && preg_match('/^(?:var)?char\((\d+)\)/i', $col['Type'], $m) && (int) $m[1] < 255) {
        $conn->query('ALTER TABLE garbage_collector MODIFY `password` VARCHAR(255) NOT NULL');
    }
}

/** Reads the stored hash back and confirms the typed password really matches it. */
function collectorPasswordWorks(mysqli $conn, int $id, string $plain): bool
{
    $stmt = $conn->prepare('SELECT password FROM garbage_collector WHERE garbage_collector_id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row && password_verify($plain, $row['password']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    try {
        if ($formAction === 'delete') {
            $id = (int) ($_POST['garbage_collector_id'] ?? 0);
            $stmt = $conn->prepare('DELETE FROM garbage_collector WHERE garbage_collector_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $success = 'Collector deleted.';
        } else {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName  = trim($_POST['last_name'] ?? '');
            // Store emails lower-case + trimmed so what is saved always matches what is typed at login.
            $email     = strtolower(trim($_POST['email'] ?? ''));
            $password  = $_POST['password'] ?? '';
            $truckId   = (int) ($_POST['truck_id'] ?? 0);
            $id        = (int) ($_POST['garbage_collector_id'] ?? 0);

            if ($firstName === '' || $lastName === '' || $email === '' || $truckId <= 0) {
                $errors[] = 'All fields including the truck are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            } elseif ($formAction === 'add' && $password === '') {
                $errors[] = 'Password is required when creating a new account.';
            } else {
                // Friendly duplicate-email check (instead of a fatal "Duplicate entry" error).
                $stmt = $conn->prepare('SELECT garbage_collector_id FROM garbage_collector WHERE email = ? AND garbage_collector_id <> ? LIMIT 1');
                $stmt->bind_param('si', $email, $id);
                $stmt->execute();

                if ($stmt->get_result()->fetch_assoc()) {
                    $errors[] = 'A collector with that email address already exists.';
                } else {
                    ensureCollectorPasswordColumn($conn);

                    if ($formAction === 'update' && $id > 0) {
                        if ($password !== '') {
                            $hashed = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare('UPDATE garbage_collector SET first_name=?, last_name=?, email=?, password=?, truck_id=? WHERE garbage_collector_id=?');
                            $stmt->bind_param('ssssii', $firstName, $lastName, $email, $hashed, $truckId, $id);
                        } else {
                            $stmt = $conn->prepare('UPDATE garbage_collector SET first_name=?, last_name=?, email=?, truck_id=? WHERE garbage_collector_id=?');
                            $stmt->bind_param('sssii', $firstName, $lastName, $email, $truckId, $id);
                        }
                        $stmt->execute();

                        if ($password !== '' && !collectorPasswordWorks($conn, $id, $password)) {
                            $errors[] = 'The collector was updated, but the new password could not be saved correctly. Please try setting it again.';
                        } else {
                            $success = 'Collector updated.';
                        }
                    } else {
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare('INSERT INTO garbage_collector (first_name, last_name, email, password, truck_id) VALUES (?, ?, ?, ?, ?)');
                        $stmt->bind_param('ssssi', $firstName, $lastName, $email, $hashed, $truckId);
                        $stmt->execute();
                        $newId = (int) $conn->insert_id;

                        // Prove the account can actually log in before reporting success.
                        if (!collectorPasswordWorks($conn, $newId, $password)) {
                            $del = $conn->prepare('DELETE FROM garbage_collector WHERE garbage_collector_id = ?');
                            $del->bind_param('i', $newId);
                            $del->execute();
                            $errors[] = 'The account was not created because its password could not be stored correctly. Please try again.';
                        } else {
                            $success = 'Collector added. They can now log in with ' . $email . '.';
                        }
                    }
                }
            }
        }
    } catch (mysqli_sql_exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}

$trucks = $conn->query('SELECT truck_id, plate_number FROM truck ORDER BY plate_number');

$list = $conn->query('
    SELECT gc.*, t.plate_number, r.zone_name
    FROM garbage_collector gc
    LEFT JOIN truck t ON gc.truck_id = t.truck_id
    LEFT JOIN route r ON gc.route_id = r.route_id
    ORDER BY gc.last_name, gc.first_name
');

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare('SELECT * FROM garbage_collector WHERE garbage_collector_id = ?');
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

include 'includes/layout_top.php';
?>

<div class="content-header">
    <div>
        <h2>Garbage Collectors</h2>
        <p>Manage collector/driver accounts and assign them to a truck. Routes and schedules are assigned by the Barangay Admin.</p>
    </div>
</div>

<?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div><?php endif; ?>

<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-header">
        <h3><?= $editing ? 'Edit Collector' : 'Add a New Collector' ?></h3>
        <?php if ($editing): ?><a href="collectors.php" class="panel-link">Cancel edit</a><?php endif; ?>
    </div>
    <form method="POST" action="collectors.php" autocomplete="off">
        <input type="hidden" name="form_action" value="<?= $editing ? 'update' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="garbage_collector_id" value="<?= (int) $editing['garbage_collector_id'] ?>"><?php endif; ?>
        <div class="form-group"><label>First Name</label><input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($editing['first_name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($editing['last_name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" autocomplete="off" value="<?= htmlspecialchars($editing['email'] ?? '') ?>" required></div>
        <div class="form-group"><label>Password <?= $editing ? '(leave blank to keep current)' : '' ?></label><input type="password" name="password" class="form-control" autocomplete="new-password" <?= $editing ? '' : 'required' ?>></div>
        <div class="form-group">
            <label>Truck</label>
            <select name="truck_id" class="form-control" required>
                <option value="">Select a truck&hellip;</option>
                <?php $trucks->data_seek(0); while ($t = $trucks->fetch_assoc()): ?>
                    <option value="<?= (int) $t['truck_id'] ?>" <?= (isset($editing) && (int) $editing['truck_id'] === (int) $t['truck_id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['plate_number']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Update Collector' : 'Add Collector' ?></button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Route</th><th>Truck</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($list->num_rows === 0): ?>
                <tr><td colspan="5">No collectors yet. Add one above.</td></tr>
            <?php else: while ($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['route_id'] ? routeLabel($row) : 'Not assigned yet') ?></td>
                    <td><?= htmlspecialchars($row['plate_number'] ?? '—') ?></td>
                    <td>
                        <a href="collectors.php?edit=<?= (int) $row['garbage_collector_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <form method="POST" action="collectors.php" style="display:inline;" onsubmit="return confirm('Delete this collector?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="garbage_collector_id" value="<?= (int) $row['garbage_collector_id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/layout_bottom.php'; ?>