<?php
$pageTitle  = 'Announcements';
$activePage = 'announcements';
include 'includes/auth.php';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $id = (int) ($_POST['announcement_id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM announcement WHERE announcement_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $success = 'Announcement deleted.';
    } else {
        $title      = trim($_POST['title'] ?? '');
        $content    = trim($_POST['content'] ?? '');
        $expiryDate = $_POST['expiry_date'] ?? null;
        $id         = (int) ($_POST['announcement_id'] ?? 0);
        if ($expiryDate === '') { $expiryDate = null; }

        if ($title === '' || $content === '') {
            $errors[] = 'Title and content are required.';
        } else {
            if ($formAction === 'update' && $id > 0) {
                $stmt = $conn->prepare('UPDATE announcement SET title=?, content=?, expiry_date=? WHERE announcement_id=?');
                $stmt->bind_param('sssi', $title, $content, $expiryDate, $id);
                $stmt->execute();
                $success = 'Announcement updated.';
            } else {
                $stmt = $conn->prepare('INSERT INTO announcement (lgu_admin_id, title, content, published_timestamp, expiry_date) VALUES (?, ?, ?, NOW(), ?)');
                $stmt->bind_param('isss', $adminId, $title, $content, $expiryDate);
                $stmt->execute();
                $success = 'Announcement posted.';
            }
        }
    }
}

$list = $conn->query('SELECT * FROM announcement ORDER BY published_timestamp DESC');

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare('SELECT * FROM announcement WHERE announcement_id = ?');
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

include 'includes/layout_top.php';
?>

<div class="content-header">
    <div>
        <h2>Announcements</h2>
        <p>Post announcements visible to residents.</p>
    </div>
</div>

<?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><p><?= htmlspecialchars($success) ?></p></div><?php endif; ?>

<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-header">
        <h3><?= $editing ? 'Edit Announcement' : 'Post a New Announcement' ?></h3>
        <?php if ($editing): ?><a href="announcements.php" class="panel-link">Cancel edit</a><?php endif; ?>
    </div>
    <form method="POST" action="announcements.php">
        <input type="hidden" name="form_action" value="<?= $editing ? 'update' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="announcement_id" value="<?= (int) $editing['announcement_id'] ?>"><?php endif; ?>
        <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required></div>
        <div class="form-group"><label>Content</label><textarea name="content" class="form-control" rows="4" required><?= htmlspecialchars($editing['content'] ?? '') ?></textarea></div>
        <div class="form-group"><label>Expiry Date (optional)</label><input type="date" name="expiry_date" class="form-control" value="<?= htmlspecialchars($editing['expiry_date'] ?? '') ?>"></div>
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Update Announcement' : 'Post Announcement' ?></button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Title</th><th>Published</th><th>Expires</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($list->num_rows === 0): ?>
                <tr><td colspan="4">No announcements yet. Post one above.</td></tr>
            <?php else: while ($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= $row['published_timestamp'] ? date('M d, Y', strtotime($row['published_timestamp'])) : '—' ?></td>
                    <td><?= $row['expiry_date'] ? date('M d, Y', strtotime($row['expiry_date'])) : '—' ?></td>
                    <td>
                        <a href="announcements.php?edit=<?= (int) $row['announcement_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <form method="POST" action="announcements.php" style="display:inline;" onsubmit="return confirm('Delete this announcement?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="announcement_id" value="<?= (int) $row['announcement_id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#c62828;border-color:#f5c6cb;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/layout_bottom.php'; ?>
