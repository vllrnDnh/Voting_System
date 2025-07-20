<?php
session_start();
require_once 'db.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$orgId = $_POST['org_id'] ?? null;
$visibility = $_POST['visibility'] ?? null;

if ($orgId !== null && $visibility !== null) {
    $stmt = $conn->prepare("UPDATE organizations SET is_visible = ? WHERE id = ?");
    $stmt->bind_param("ii", $visibility, $orgId);
    $stmt->execute();
    $stmt->close();
}

header("Location: admin_dashboard.php");
exit;
