<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// 🆔 Optional: you can also get the user ID
$username = $_SESSION['username'];
$userId = $_SESSION['user_id'] ?? 0;
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'];

if (!is_array($data['votes']) || empty($data['votes'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No votes submitted']);
    exit;
}

// Prevent double voting
$check = $conn->prepare("SELECT 1 FROM votes WHERE user_id = ?");
$check->bind_param("i", $user_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['error' => 'You already voted.']);
    exit;
}
$check->close();

$stmt = $conn->prepare("
    INSERT INTO votes (user_id, candidate_id, position, organization_id)
    VALUES (?, ?, ?, ?)
");

foreach ($data['votes'] as $vote) {
    $candidate_id = (int)$vote['candidate_id'];
    $position     = $vote['position'];

    // Get org ID from candidate
    $orgRes = $conn->prepare("SELECT organization_id FROM candidates WHERE user_id = ? AND position = ?");
    $orgRes->bind_param("is", $candidate_id, $position);
    $orgRes->execute();
    $orgRes->bind_result($org_id);
    $orgRes->fetch();
    $orgRes->close();

    if (!$org_id) continue;

    $stmt->bind_param("iisi", $user_id, $candidate_id, $position, $org_id);
    $stmt->execute();
}
$stmt->close();

echo json_encode(['success' => 'Vote submitted']);
