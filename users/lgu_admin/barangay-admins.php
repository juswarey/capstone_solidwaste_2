<?php
$pageTitle  = 'Barangay Admins';
$activePage = 'barangayadmins';
include 'includes/auth.php';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $id = (int) ($_POST['barangay_admin_id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM barangay_admin WHERE barangay_admin_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $success = 'Barangay admin deleted.';
    } else {
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname  = trim($_POST['lastname'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $id        = (int) ($_POST['barangay_admin_id'] ?? 0);

        if ($firstname === '' || $lastname === '' || $email === '') {
            $errors[] = 'First name, last name, and email are required.';
        } elseif ($formAction === 'add' && $password === '') {
            $errors[] = 'Password is required when creating a new account.';
        } else {
            if ($formAction === 'update' && $id > 0) {
                if ($password !== '') {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare('UPDATE barangay_admin SET firstname=?, lastname=?, email=?, password=? WHERE barangay_admin_id=?');
                    $stmt->bind_param('ssssi', $firstname, $lastname, $email, $hashed, $id);
                } else {
                    $stmt = $conn->prepare('UPDATE barangay_admin SET firstname=?, lastname=?, email=? WHERE barangay_admin_id=?');
                    $stmt->bind_param('sssi', $firstname, $lastname, $email, $id);
                }
                $stmt->execute();
                $success = 'Barangay admin updated.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO barangay_admin (firstname, lastname, email, password) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssss', $firstname, $lastname, $email, $hashed);
                $stmt->execute();
                $success = 'Barangay admin account created.';
            }
        }
    }
}

$list = $conn->query('SELECT * FROM barangay_admin ORDER BY lastname, firstname');

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare('SELECT * FROM barangay_admin WHERE barangay_admin_id = ?');
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

include 'includes/layout_top.php';
?>

<div class="content-header">
    <div>
        <h2>Barangay Admins</h2>
        <p>Create and manage barangay admin accounts. There's no self-registration &mdash; you create these accounts directly.</p>
    </div>
</div>

<?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div><?php endif; ?>

<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-header">
        <h3><?= $editing ? 'Edit Barangay Admin' : 'Add a New Barangay Admin' ?></h3>
        <?php if ($editing): ?><a href="barangay-admins.php" class="panel-link">Cancel edit</a><?php endif; ?>
    </div>
    <form method="POST" action="barangay-admins.php">
        <input type="hidden" name="form_action" value="<?= $editing ? 'update' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="barangay_admin_id" value="<?= (int) $editing['barangay_admin_id'] ?>"><?php endif; ?>
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="firstname" class="form-control" value="<?= htmlspecialchars($editing['firstname'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="lastname" class="form-control" value="<?= htmlspecialchars($editing['lastname'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editing['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Password <?= $editing ? '(leave blank to keep current password)' : '' ?></label>
            <input type="password" name="password" class="form-control" autocomplete="new-password" <?= $editing ? '' : 'required' ?>>
        </div>
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Update Account' : 'Create Account' ?></button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($list->num_rows === 0): ?>
                <tr><td colspan="3">No barangay admins yet. Add one above.</td></tr>
            <?php else: while ($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <a href="barangay-admins.php?edit=<?= (int) $row['barangay_admin_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <form method="POST" action="barangay-admins.php" style="display:inline;" onsubmit="return confirm('Delete this account?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="barangay_admin_id" value="<?= (int) $row['barangay_admin_id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/layout_bottom.php'; ?>