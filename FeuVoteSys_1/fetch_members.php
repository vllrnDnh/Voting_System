<?php
require 'db.php';
$orgId = intval($_GET['org_id']);
$query = "SELECT id, full_name FROM users WHERE FIND_IN_SET(?, organizations)";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $orgId);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}
echo json_encode($members);
