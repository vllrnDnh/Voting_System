<?php
session_start();
require_once 'db.php';

if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

if (isset($_POST['end_time'])) {
    $endTime = $_POST['end_time'];

    // Replace old setting or insert new
    $stmt = $conn->prepare("SELECT id FROM vote_settings LIMIT 1");
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $update = $conn->prepare("UPDATE vote_settings SET end_time = ? WHERE id = 1");
        $update->bind_param("s", $endTime);
        $update->execute();
        $update->close();
    } else {
        $stmt->close();
        $insert = $conn->prepare("INSERT INTO vote_settings (end_time) VALUES (?)");
        $insert->bind_param("s", $endTime);
        $insert->execute();
        $insert->close();
    }

    header("Location: admin_dashboard.php?timer_updated=1");
    exit;
}
?>
