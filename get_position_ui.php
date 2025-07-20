<?php
require_once 'db.php';

$orgId = $_GET['org_id'] ?? null;
if (!$orgId) {
    echo "Organization ID missing.";
    exit;
}

// Predefined positions
$positions = ['President', 'VP Internal', 'VP External', 'Secretary', 'PRO', 'Treasurer', 'Auditor'];

echo '<h3>Assign Candidates</h3>';
echo '<form id="candidateForm">';
foreach ($positions as $position) {
    $posId = strtolower(str_replace(' ', '_', $position));
    echo "<div class='position-block'>";
    echo "<h4>$position</h4>";
    echo "<input type='text' placeholder='Search student...' class='search-box' id='search-$posId'>";
    echo "<div class='search-results' id='results-$posId'></div>";
    echo "<div id='{$posId}-candidates' class='candidate-list'></div>";
    echo "</div>";
}
echo "<input type='hidden' name='org_id' value='" . htmlspecialchars($orgId) . "'>";
echo "<button type='submit'>Save Candidates</button>";
echo '</form>';
?>

<!-- Now place the script here -->
<script>
function addCandidateToPosition(position, student) {
    const container = document.getElementById(`${position}-candidates`);
    const candidateItem = document.createElement('div');
    candidateItem.className = 'candidate-item';
    candidateItem.innerHTML = `
        <span>${student.full_name} (${student.student_number})</span>
        <input type="hidden" name="candidates[${position}][]" value="${student.id}">
        <button class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
    `;
    container.appendChild(candidateItem);
}

function searchStudents(position, orgId, searchInput, resultContainer) {
    const query = searchInput.value;

    if (query.length < 2) {
        resultContainer.innerHTML = '';
        return;
    }

    fetch(`get_org_members.php?org_id=${orgId}&query=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            resultContainer.innerHTML = '';
            if (data.length === 0) {
                resultContainer.innerHTML = '<div>No matching students found.</div>';
                return;
            }

            data.forEach(student => {
                const div = document.createElement('div');
                div.classList.add('student-suggestion');
                div.textContent = `${student.full_name} (${student.student_number}) - ${student.course} / ${student.specialization}`;

                div.addEventListener('click', () => {
                    addCandidateToPosition(position, student);
                    resultContainer.innerHTML = '';
                    searchInput.value = '';
                });

                resultContainer.appendChild(div);
            });
        })
        .catch(err => {
            console.error('Search error:', err);
        });
}

// Activate search input listeners
document.querySelectorAll('.search-box').forEach(input => {
    const position = input.id.replace('search-', '');
    const resultDiv = document.getElementById('results-' + position);
    const orgId = document.querySelector('input[name="org_id"]').value;

    input.addEventListener('input', () => {
        searchStudents(position, orgId, input, resultDiv);
    });
});
</script>
