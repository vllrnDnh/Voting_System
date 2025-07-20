<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['has_voted' => false]);
    exit;
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($uid);
$stmt->fetch();
$stmt->close();

$result = $conn->query("SELECT COUNT(*) FROM votes WHERE user_id = $uid");
$count = $result->fetch_row()[0];

echo json_encode(['has_voted' => $count > 0]);
?>
