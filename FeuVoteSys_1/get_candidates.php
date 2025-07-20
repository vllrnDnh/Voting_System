<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode([]);
    exit;
}

$username = $_SESSION['username'];

// Step 1: Get the org IDs from user's record
$stmt = $conn->prepare("SELECT organizations FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($orgString);
$stmt->fetch();
$stmt->close();

if (!$orgString) {
    echo json_encode([]);
    exit;
}

// Convert string to array of integers
$orgIds = array_map('intval', array_filter(explode(',', $orgString)));

if (empty($orgIds)) {
    echo json_encode([]);
    exit;
}

// Step 2: Use org IDs to fetch candidates
$placeholders = implode(',', array_fill(0, count($orgIds), '?'));
$types = str_repeat('i', count($orgIds));
$sql = "
    SELECT c.user_id, c.position, u.full_name
    FROM candidates c
    JOIN users u ON c.user_id = u.id
    WHERE c.organization_id IN ($placeholders)
";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$orgIds);
$stmt->execute();
$result = $stmt->get_result();

$candidates = [];
while ($row = $result->fetch_assoc()) {
    $candidates[] = $row;
}

echo json_encode($candidates);
