console.log("✅ candidate_assignment.js loaded");

const positions = [
  "President",
  "VP Internal",
  "VP External",
  "Secretary",
  "PRO",
  "Treasurer",
  "Auditor"
];

function initializeCandidateAssignment() {
  console.log("✅ initializeCandidateAssignment() called");
  const orgId = document.getElementById('organizationSelect').value;
  const container = document.getElementById('positionsContainer');
  container.classList.add('position-grid');
  container.innerHTML = '';

  if (!orgId) return;

  positions.forEach(pos => {
    const block = document.createElement('div');
    block.classList.add('position-block');
    block.dataset.position = pos;

    const posId = pos.toLowerCase().replace(/\s+/g, '_');

    block.innerHTML = `
      <h3>${pos}</h3>
      <input type="text" class="search-input" data-position="${pos}" placeholder="Search students...">
      <div class="candidate-results-overlay" data-position="${pos}"></div>
      <div class="assigned-candidates" data-position="${pos}"><strong>Assigned:</strong></div>
    `;

    container.appendChild(block);
  });
}

function assignCandidate(position, user) {
  const assignedBox = document.querySelector(`.assigned-candidates[data-position="${position}"]`);

  if (assignedBox.querySelector(`[data-id="${user.id}"]`)) return;

  const div = document.createElement('div');
  div.classList.add('candidate-box');
  div.dataset.id = user.id;
  div.innerText = `${user.full_name} (${user.student_number})`;

  const removeBtn = document.createElement('button');
  removeBtn.innerText = 'Remove';
  removeBtn.style.marginLeft = '10px';
  removeBtn.addEventListener('click', () => div.remove());

  div.appendChild(removeBtn);
  assignedBox.appendChild(div);
}

function saveCandidates() {
  const orgId = document.getElementById('organizationSelect').value;
  const candidates = [];

  document.querySelectorAll('.assigned-candidates').forEach(section => {
    const position = section.dataset.position;
    section.querySelectorAll('.candidate-box').forEach(box => {
      candidates.push({
        user_id: parseInt(box.dataset.id),
        position: position
      });
    });
  });

  fetch('assign_candidates.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      organization_id: orgId,
      candidates: candidates
    })
  })
    .then(res => res.text())
    .then(msg => {
      alert(msg);
    })
    .catch(err => {
      console.error('Save failed', err);
      alert('Error saving candidates.');
    });
}

document.addEventListener('input', function (e) {
  if (e.target.classList.contains('search-input')) {
    const keyword = e.target.value.trim();
    const position = e.target.dataset.position;
    const orgId = document.getElementById('organizationSelect').value;
    const resultsBox = document.querySelector(`.candidate-results-overlay[data-position="${position}"]`);

    if (keyword.length < 2 || !orgId) {
      resultsBox.style.display = 'none';
      return;
    }

    fetch(`get_org_members.php?org_id=${orgId}&query=${encodeURIComponent(keyword)}`)
      .then(res => res.json())
      .then(data => {
        resultsBox.innerHTML = '';
        if (data.length === 0) {
          resultsBox.style.display = 'none';
          return;
        }

        data.forEach(user => {
          const div = document.createElement('div');
          div.classList.add('candidate-box');
          div.dataset.id = user.id;
          div.innerText = `${user.full_name} (${user.student_number})`;
          div.addEventListener('click', () => {
            assignCandidate(position, user);
            resultsBox.style.display = 'none';
          });
          resultsBox.appendChild(div);
        });

        resultsBox.style.display = 'block';
      });
  }
});

document.addEventListener('click', function (e) {
  if (!e.target.classList.contains('search-input') && !e.target.closest('.candidate-results-overlay')) {
    document.querySelectorAll('.candidate-results-overlay').forEach(box => box.style.display = 'none');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const orgSelect = document.getElementById('organizationSelect');
  if (orgSelect) {
    orgSelect.addEventListener('change', initializeCandidateAssignment);
  }

  const saveBtn = document.getElementById('saveCandidatesBtn');
  if (saveBtn) {
    saveBtn.addEventListener('click', saveCandidates);
  }
});
