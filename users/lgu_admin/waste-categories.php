<?php
$pageTitle  = 'Waste Categories';
$activePage = 'waste';
include 'includes/auth.php';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $id = (int) ($_POST['waste_category_id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM waste_category WHERE waste_category_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $success = 'Waste category deleted.';
    } else {
        $name       = trim($_POST['category_name'] ?? '');
        $guidelines = trim($_POST['disposal_guidelines'] ?? '');
        $id         = (int) ($_POST['waste_category_id'] ?? 0);

        if ($name === '') {
            $errors[] = 'Category name is required.';
        } else {
            if ($formAction === 'update' && $id > 0) {
                $stmt = $conn->prepare('UPDATE waste_category SET category_name=?, disposal_guidelines=? WHERE waste_category_id=?');
                $stmt->bind_param('ssi', $name, $guidelines, $id);
                $stmt->execute();
                $success = 'Waste category updated.';
            } else {
                $stmt = $conn->prepare('INSERT INTO waste_category (category_name, disposal_guidelines) VALUES (?, ?)');
                $stmt->bind_param('ss', $name, $guidelines);
                $stmt->execute();
                $success = 'Waste category added.';
            }
        }
    }
}

$list = $conn->query('SELECT * FROM waste_category ORDER BY category_name');

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare('SELECT * FROM waste_category WHERE waste_category_id = ?');
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

include 'includes/layout_top.php';
?>

<div class="content-header">
    <div>
        <h2>Waste Categories</h2>
        <p>Manage the waste categories and disposal guidelines used across all barangays.</p>
    </div>
</div>

<?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div><?php endif; ?>

<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-header">
        <h3><?= $editing ? 'Edit Waste Category' : 'Add a New Waste Category' ?></h3>
        <?php if ($editing): ?><a href="waste-categories.php" class="panel-link">Cancel edit</a><?php endif; ?>
    </div>
    <form method="POST" action="waste-categories.php">
        <input type="hidden" name="form_action" value="<?= $editing ? 'update' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="waste_category_id" value="<?= (int) $editing['waste_category_id'] ?>"><?php endif; ?>
        <div class="form-group"><label>Category Name</label><input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($editing['category_name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Disposal Guidelines</label><textarea name="disposal_guidelines" class="form-control" rows="3"><?= htmlspecialchars($editing['disposal_guidelines'] ?? '') ?></textarea></div>
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Update Category' : 'Add Category' ?></button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Category</th><th>Disposal Guidelines</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($list->num_rows === 0): ?>
                <tr><td colspan="3">No waste categories yet. Add one above.</td></tr>
            <?php else: while ($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= nl2br(htmlspecialchars(mb_strimwidth($row['disposal_guidelines'] ?? '', 0, 100, '…'))) ?></td>
                    <td>
                        <a href="waste-categories.php?edit=<?= (int) $row['waste_category_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <form method="POST" action="waste-categories.php" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="waste_category_id" value="<?= (int) $row['waste_category_id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/layout_bottom.php'; ?>
