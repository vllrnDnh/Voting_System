<?php
require_once 'db.php';

if (!isset($_GET['org_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing org_id"]);
    exit;
}

$org_id = intval($_GET['org_id']);

$stmt = $conn->prepare("SELECT id, full_name, student_number FROM users WHERE FIND_IN_SET(?, organizations)");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

header('Content-Type: application/json');
echo json_encode($members);
