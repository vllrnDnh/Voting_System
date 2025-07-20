<?php
session_start();
require_once '../includes/db.php';

// Proper authentication check
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Security token mismatch. Please try again.";
    header("Location: admin_dashboard.php");
    exit;
}

// Input validation and sanitization
$orgId = filter_input(INPUT_POST, 'org_id', FILTER_VALIDATE_INT);
$visibility = filter_input(INPUT_POST, 'visibility', FILTER_VALIDATE_INT);

if ($orgId === false || $visibility === false || !in_array($visibility, [0, 1])) {
    $_SESSION['error'] = "Invalid input data";
    header("Location: admin_dashboard.php");
    exit;
}

// Use prepared statement with error handling
$stmt = $conn->prepare("UPDATE organizations SET is_visible = ? WHERE id = ?");
if (!$stmt) {
    $_SESSION['error'] = "Database error occurred";
    header("Location: admin_dashboard.php");
    exit;
}

$stmt->bind_param("ii", $visibility, $orgId);
if (!$stmt->execute()) {
    $_SESSION['error'] = "Failed to update organization visibility";
} else {
    $_SESSION['success'] = "Organization visibility updated successfully";
}
$stmt->close();

header("Location: admin_dashboard.php");
exit;
