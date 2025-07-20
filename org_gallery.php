<?php
require_once 'includes/db.php';
session_start();

$query = "SELECT * FROM organizations WHERE is_visible = 1";
if ($_SESSION['role'] === 'admin') {
    $query = "SELECT * FROM organizations";
}
$result = $conn->query($query);
?>

<div class="dashboard-header">Dashboard</div>

<div class="org-gallery">
<?php while ($row = $result->fetch_assoc()):
    $logoPath = '/FeuVoteSys/' . htmlspecialchars($row['logo_path']);
    $isVisible = (bool)$row['is_visible'];
?>
    <div class="org-box <?= $isVisible ? '' : 'hidden-org' ?>">
        <img class="<?= $isVisible ? '' : 'blurred' ?>" src="<?= $logoPath ?>" alt="<?= htmlspecialchars($row['name']) ?>">
        <p><?= htmlspecialchars($row['name']) ?></p>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <form method="POST" action="toggle_org_visibility.php" style="margin-top: 5px;">
            <input type="hidden" name="org_id" value="<?= $row['id'] ?>">
            <input type="hidden" name="visibility" value="<?= $isVisible ? 0 : 1 ?>">
            <button type="submit"><?= $isVisible ? 'Hide' : 'Show' ?></button>
        </form>
        <?php endif; ?>
    </div>
<?php endwhile; ?>
</div>
