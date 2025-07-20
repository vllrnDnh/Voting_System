
function submitVote() {
    if (!confirm("Are you sure you want to submit your vote? This cannot be undone.")) return;

    const form = document.getElementById('voteForm');
    if (!form) {
        alert("⚠️ Voting form not found!");
        return;
    }

    const votes = [];
    const fieldsets = form.querySelectorAll('fieldset');

    fieldsets.forEach(fs => {
        const legend = fs.querySelector('legend');
        if (!legend) return;

        const position = legend.textContent;
        const checked = fs.querySelector('input[type="radio"]:checked');
        if (checked) {
            votes.push({
                position: position,
                candidate_id: checked.value
            });
        }
    });

    if (votes.length === 0) {
        alert("⚠️ You must vote for at least one position.");
        return;
    }

    fetch('submit_vote.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({votes: votes})
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg);
        document.getElementById('voteView').style.display = 'none';
        document.querySelector('.org-gallery').style.display = 'flex';
    })
    .catch(err => {
        console.error("❌ Error submitting vote:", err);
        alert("Something went wrong. Try again later.");
    });
}
