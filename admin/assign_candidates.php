<?php
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['organization_id']) || !isset($data['candidates'])) {
    http_response_code(400);
    echo "Invalid data.";
    exit;
}

$org_id = intval($data['organization_id']);
$candidates = $data['candidates'];

$inserted = 0;

foreach ($candidates as $c) {
    $user_id = intval($c['user_id']);
    $position = trim($c['position']);

    // Check if already assigned
    $check = $conn->prepare("SELECT id FROM candidates WHERE user_id = ? AND organization_id = ? AND position = ?");
    $check->bind_param("iis", $user_id, $org_id, $position);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        // Insert
        $stmt = $conn->prepare("INSERT INTO candidates (user_id, organization_id, position) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $org_id, $position);
        if ($stmt->execute()) {
            $inserted++;
        }
    }
}

echo "$inserted candidates saved successfully.";