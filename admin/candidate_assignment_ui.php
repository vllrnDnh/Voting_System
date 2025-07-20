<?php
include 'db.php';
$result = mysqli_query($conn, "SELECT id, name FROM organizations WHERE is_visible = 1");
?>

<div class="candidate-assignment">
    <h2>Select Organization</h2>
    <select id="organizationSelect">
        <option value="">-- Select an organization --</option>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <div id="positionsContainer" style="margin-top: 30px;"></div>
    <button id="saveCandidatesBtn" style="margin-top: 20px;">Save Candidates</button>
</div>

<script>
// Inline fallback if external JS isn't working
document.getElementById('organizationSelect').addEventListener('change', () => {
    if (typeof initializeCandidateAssignment === 'function') {
        initializeCandidateAssignment();
    } else {
        console.error("initializeCandidateAssignment() not found. Check if candidate_assignment.js is loaded.");
    }
});

document.getElementById('saveCandidatesBtn').addEventListener('click', () => {
    if (typeof saveCandidates === 'function') {
        saveCandidates();
    } else {
        console.error("saveCandidates() not found. Check if candidate_assignment.js is loaded.");
    }

  document.getElementById('organizationSelect').addEventListener('change', function () {
    console.log("✅ org selected: " + this.value);
    if (typeof initializeCandidateAssignment === 'function') {
      initializeCandidateAssignment();
    } else {
      console.error("initializeCandidateAssignment not found.");
    }
  });
});

</script>
