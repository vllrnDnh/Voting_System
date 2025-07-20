<?php
require 'db.php';

$orgId = $_GET['org_id'] ?? 0;
if (!$orgId) {
    echo "❌ Invalid organization ID.";
    exit;
}

// Fetch all candidates for this org
$sql = "SELECT c.user_id, c.position, u.full_name, u.student_number, u.course
        FROM candidates c
        JOIN users u ON c.user_id = u.id
        WHERE c.organization_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orgId);
$stmt->execute();
$result = $stmt->get_result();

$grouped = [];
while ($row = $result->fetch_assoc()) {
    $grouped[$row['position']][] = $row;
}

// Output HTML
foreach ($grouped as $position => $candidates) {
    echo "<div class='position-group'>";
    echo "<h3>" . htmlspecialchars($position) . "</h3>";
    echo "<ul class='candidate-list'>";
    foreach ($candidates as $c) {
        echo "<li><strong>" . htmlspecialchars($c['full_name']) . "</strong> (" . $c['student_number'] . ") - " . htmlspecialchars($c['course']) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
}
?>
