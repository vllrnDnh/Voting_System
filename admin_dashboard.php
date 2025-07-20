<?php
/* ── session / db ─────────────────────────────────────────────── */
session_start();
require_once 'includes/db.php';

// Only allow admin access to admin dashboard
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); 
    exit;
}

    $role     = $_SESSION['role'];
$username = $_SESSION['username'];

// Handle session alerts before any HTML output
$profile_updated = false;
$error_message = '';
$success_message = '';
    if (isset($_SESSION['profile_updated'])) {
        $profile_updated = true;
        unset($_SESSION['profile_updated']);
    }
    if (isset($_SESSION['error'])) {
        $error_message = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        $success_message = $_SESSION['success'];
        unset($_SESSION['success']);
    }

    // Generate CSRF token for security
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    /* ── pull all profile fields ──────────────────────────────────── */
    $stmt = $conn->prepare("
        SELECT profile_pic, full_name,
            student_number, email, phone_number, course, specialization,
            dob, org_member, organizations
        FROM users
        WHERE username = ?
    ");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    $stmt->bind_result(
        $profile_pic, $full_name,
        $student_number, $email, $phone_number, $course, $specialization,
        $birthdate, $is_org_member, $organizations
    );
    $stmt->fetch();  $stmt->close();

    /* defaults */
    $profile_pic    = $profile_pic ?: 'default.jpg';
    $full_name      = $full_name   ?: '';
    $student_number = $student_number ?: '';
    $email          = $email          ?: '';
    $phone_number   = $phone_number   ?: '';
    $course         = $course         ?: '';
    $specialization = $specialization ?: '';
    $birthdate      = $birthdate      ?: '';
    $is_org_member  = (int)$is_org_member;
    $organizations  = $organizations  ?: '';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo ucfirst($role); ?> Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            width: 100%;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            
            background-color: #7e978cff;
            z-index: 20; /* Make sure it's above label only if needed */
        }

        .container {
        width: 100%;
        display: flex;
        flex-direction: column;
        }

        .sidenav {
            width: 250px;
            background-color: #1a252f;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .main-content {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 100px 20px 20px; /* Top padding added */
            box-sizing: border-box;
            width: 100%;
            max-width: 100vw;
            margin:0 auto;
        }

        .dashboard-label {
            position: fixed;
            top: 0;
            left: 250px; /* Exactly matches sidebar width */
            width: calc(100% - 250px); /* Remaining space */
            background-color: #1e3a8a;
            color: white;
            font-size: 24px;
            font-weight: bold;
            padding: 20px;
            box-sizing: border-box;
            z-index: 10;
        }

        .dashboard-header {
            background-color: #34495e;
            color: white;
            padding: 12px 20px;
            font-size: 22px;
            font-weight: bold;
            margin: 0;
            border-radius: 0 0 8px 0;
            position: absolute;
            top: 0;
            left: 70px; /* matches sidebar */
            right: 0;   /* stretch to the right */
            height: 60px;
            display: flex;
            align-items: center;
            z-index: 2;
        }
        /* Org Gallery Container */
        .org-gallery {
            padding-left: 50px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }
        /* Each Org Box */
        .org-box {
            width: 250px;
            height: 250px;
            
            position: relative;
            aspect-ratio: 1 / 1;
            border-radius: 12px;
            overflow: hidden;
            background-color: #f0f0f0;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            cursor: pointer;
            box-sizing: border-box;
            padding:0;
                flex-direction: column;
            justify-content: flex-end;
            text-align: center;
        }

        .org-box:hover {
            transform: scale(1.02);
        }

        /* Fill the entire box with image */
        .org-box img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* ensures full coverage */
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            object-fit: cover;
            z-index: 1;
        }

        /* Text label spans full width */
        .org-box p {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 25%;
            margin: 0;
            background: rgba(0, 0, 0, 0.5); /* semi-transparent */
            color: #fff;
            text-align: center;
            line-height: 62.5px; /* 25% of 250px = 62.5px */
            font-weight: bold;
        }

        .org-box form {
            position: absolute;
            top: 8px;
            right: 8px;
            margin: 0;
            padding: 0;
            background: none;
            border: none;
            z-index: 2;
            width: auto;
            height: auto;
        }

        .org-box form button {
            background: rgba(0, 0, 0, 0.6); /* Optional: add slight dark bg */
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .org-box button {
            background-color: rgba(255, 255, 255, 0.85);
            border: none;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .org-box button:hover {
            background-color: #ddd;
        }
                @media (max-width: 1024px) {
            .org-box {
                width: calc(33.333% - 20px); /* 3 per row */
            }
        }
        .position-group {
        border: 1px solid #ccc;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        }

        .position-group h3 {
        margin-top: 0;
        color: #1e3a8a;
        }

        .candidate-list {
        list-style: none;
        padding-left: 20px;
        }

        .candidate-list li {
        margin: 5px 0;
        }

        @media (max-width: 768px) {
            .org-box {
                width: calc(50% - 20px); /* 2 per row */
            }
        }

        @media (max-width: 480px) {
            .org-box {
                width: 100%; /* full width on phones */
            }
        }
        .hidden-org {
            filter: blur(4px);
            pointer-events: none; /* Optional: prevent clicks */
            opacity: 0.6;
        }
        .blurred {
            filter: blur(4px);
            opacity: 0.5;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-table th, .user-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .user-table th {
            background-color: #f3f3f3;
        }.position-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
        }

        .position-block {
        border: 1px solid #ccc;
        padding: 16px;
        border-radius: 10px;
        position: relative;
        background: white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .candidate-results-overlay {
        position: absolute;
        top: 65px;
        left: 16px;
        right: 16px;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        max-height: 200px;
        overflow-y: auto;
        z-index: 10;
        display: none;
        }

        .candidate-results-overlay div {
        padding: 10px;
        cursor: pointer;
        }

        .candidate-results-overlay div:hover {
        background-color: #f0f0f0;
        }

        .assigned-candidates .candidate-box {
        background: #eaf7ff;
        margin-top: 6px;
        padding: 6px 8px;
        border-radius: 4px;
        font-size: 14px;
        }

    </style>

</head>

<body>
    <?php if (isset($_SESSION['profile_updated'])): unset($_SESSION['profile_updated']); ?>
    <script>window.onload=()=>alert("✅ Profile updated successfully!");</script>
    <?php endif; ?>

<div class="container-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="left">
            <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile">
            <button id="profileBtn"><i class="bi bi-person-circle"></i></button>
            <button id="logoutBtn" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
            <button onclick="showCandidateAssignment()">Assign Candidates</button>
            <button onclick="viewAssignedCandidates()">View Assigned Candidates</button>
        </div>

        <div class="right">
            <div class="right-inner">
                <div class="header">
                    <div>
                        <h2>Welcome</h2>
                        <h3><?php echo htmlspecialchars($username); ?>!</h3>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <nav>
                    <button id="dashboardBtn"><i class="bi bi-columns-gap"></i><span>Dashboard</span></button>
                    <button><i class="bi bi-people-fill"></i><span>Organizations</span></button>
                    <ul class="submenu">
                        <li>Academic <span class="badge">9</span></li>
                        <li>Non-Academic <span class="badge">4</span></li>
                    </ul>
                    <button onclick="location.href='vote.php'">
                        <i class="bi bi-check2-square"></i><span>Vote Here!</span>
                    </button>
                </nav>
            </div>
        </div>
    </aside>
    
    <!-- Main content -->
 <div class="main-content">
<div class="dashboard-header">Dashboard</div>

<div class="org-gallery">
    <?php
        // Database connection is already included at the top
        
        // Use prepared statements for better security
        if ($_SESSION['role'] === 'admin') {
            $stmt = $conn->prepare("SELECT * FROM organizations");
        } else {
            $stmt = $conn->prepare("SELECT * FROM organizations WHERE is_visible = 1");
        }
        
        if (!$stmt) {
            error_log("Database prepare failed: " . $conn->error);
            echo '<div class="error">Unable to load organizations at this time.</div>';
        } else {
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result) {
                error_log("Database execute failed: " . $stmt->error);
                echo '<div class="error">Unable to load organizations at this time.</div>';
            } else {
                while ($row = $result->fetch_assoc()) {
                    $logoPath = htmlspecialchars($row['logo_path']);
                    $orgName = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                    $orgClass = $row['is_visible'] ? '' : 'hidden-org';

                echo '<div class="org-box">';
                echo '<img class="' . ($row['is_visible'] ? '' : 'blurred') . '" src="' . $logoPath . '" alt="' . $orgName . '">';
                echo '<p>' . $orgName . '</p>';

                    if ($_SESSION['role'] === 'admin') {
                        $toggleText = $row['is_visible'] ? 'Hide' : 'Show';
                        echo '<form method="POST" action="toggle_org_visibility.php" style="margin-top: 5px;">
                                <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
                                <input type="hidden" name="org_id" value="' . (int)$row['id'] . '">
                                <input type="hidden" name="visibility" value="' . ($row['is_visible'] ? 0 : 1) . '">
                                <button type="submit">' . $toggleText . '</button>
                            </form>';
                    }

                    echo '</div>';
                }
            }
            $stmt->close();
        }
    ?>
</div>

        </div>
</div>


<!-- Profile Settings overlay -->
<div id="profileSettings" style="display:none;">
    <div class="profile-card profile-grid">

        <!-- avatar + name -->
        <div class="avatar-block">
            <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile">
            <h3><?php echo htmlspecialchars($username); ?></h3>
            <p><?php echo htmlspecialchars($full_name); ?></p>
        </div>

        <!-- edit form -->
        <form action="upload_profile.php" method="POST" enctype="multipart/form-data" class="edit-grid">

            <input name="full_name"       value="<?php echo htmlspecialchars($full_name); ?>"       placeholder="Full Name" required>
            <input name="student_number"  value="<?php echo htmlspecialchars($student_number); ?>"  placeholder="Student Number" required>
            <input name="email"           value="<?php echo htmlspecialchars($email); ?>"           placeholder="Email Address" type="email" required>

            <input name="phone_number"    value="<?php echo htmlspecialchars($phone_number); ?>"    placeholder="Phone Number">
            <input name="course"          value="<?php echo htmlspecialchars($course); ?>"          placeholder="Year Level (ex. 1st Year)">
            <input name="specialization"  value="<?php echo htmlspecialchars($specialization); ?>"  placeholder="Degree Program (ex. BSCSSE)">

            <input name="birthdate"       value="<?php echo htmlspecialchars($birthdate); ?>"       type="date">

            <input name="username"        value="<?php echo htmlspecialchars($username); ?>"        placeholder="Username" required>

            <input name="password" class="full-span" type="password" placeholder="New Password (leave blank)">

            <!-- org toggle -->
            <div class="full-span org-toggle">
                <label>Part of any student organizations?</label>
                <label><input type="radio" name="is_org_member" value="yes"
                       <?php echo $is_org_member ? 'checked':''; ?>
                       onclick="showOrgEdit(true)"> Yes</label>
                <label><input type="radio" name="is_org_member" value="no"
                       <?php echo !$is_org_member ? 'checked':''; ?>
                       onclick="showOrgEdit(false)"> No</label>
            </div>

            <div id="orgOptionsEdit" class="full-span org-box"
                 style="display:<?php echo $is_org_member?'flex':'none'; ?>">
                <?php
                $orgSet = array_flip(explode(',',$organizations));
                foreach (['ACEasdasddasdasdasdasdasS','ACMasdasdasdasdasd','JPasdasddasdasdasdCS','asdasdasdasdasdadadasdasdadas','asdasdasdasdasdadadasdasdadas','asdasdasdasdasdadadasdasdadas','Others'] as $o){
                    $ck = isset($orgSet[$o]) ? 'checked':'';
                    echo "<label><input type='checkbox' name='organizations[]' value='$o' $ck> $o</label>";
                }
                ?>
            </div>

            <label class="file-btn full-span">
                <i class="bi bi-upload"></i> Upload Picture
                <input type="file" name="profilePic" accept="image/*">
            </label>

            <button class="save-btn full-span" type="submit">Save Changes</button>
        </form>
    </div>

    <?php if (isset($_GET['assign_candidates'])): ?>
        <div class="main-content">
            <h2>Assign Candidates to Positions</h2>
                <form id="org-select-form">
                    <label for="organization">Select Organization:</label>
                    <select id="organization" name="organization" required>
                        <option value="">-- Choose --</option>
                        <?php
                        // Database connection is already included at the top
                        $orgs = $conn->query("SELECT * FROM organizations WHERE is_visible = 1");
                        while ($org = $orgs->fetch_assoc()) {
                            echo '<option value="' . $org['id'] . '">' . htmlspecialchars($org['name']) . '</option>';
                        }
                        ?>
                    </select>
                </form>

                    <div id="positions-area" style="margin-top: 30px; display:none;">
                        <?php
                        $positions = ['President', 'VP Internal', 'VP External', 'Secretary', 'PRO', 'Treasurer', 'Auditor'];
                        foreach ($positions as $pos):
                        ?>
                        <div class="position-block" data-position="<?php echo $pos; ?>">
                            <h4><?php echo $pos; ?></h4>
                            <input type="text" class="student-search" placeholder="Search student">
                            <ul class="search-results"></ul> <!-- This must be right after input -->

                            <div class="selected-candidates"></div>
                        </div>

                        <?php endforeach; ?>
                            <button id="saveCandidatesBtn" style="margin-top: 20px;">Save Candidates</button>
                    </div>

                <?php // Candidate assignment UI will be loaded dynamically via JavaScript ?>
        </div>


</body>
</html>

<script>
document.getElementById('saveCandidatesBtn').addEventListener('click', () => {
    const orgId = document.getElementById('organizationSelect').value;
    const assignments = {};

    document.querySelectorAll('.position-block').forEach(block => {
        const position = block.getAttribute('data-position');
        const selected = block.querySelectorAll('.candidate-box');
        assignments[position] = [];

        selected.forEach(box => {
            assignments[position].push(parseInt(box.dataset.id));
        });
    });

    fetch('admin/assign_candidates.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ org_id: orgId, assignments: assignments })
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            alert('Candidates saved successfully!');
        } else {
            alert('Error saving candidates.');
        }
    });
});
        const orgSelect = document.getElementById('organization');
        const positionsArea = document.getElementById('positionsContainer');
        const searchInputs = document.querySelectorAll('.student-search');

        orgSelect.addEventListener('change', () => {
            positionsArea.style.display = orgSelect.value ? 'block' : 'none';
            document.querySelectorAll('.candidate-list').forEach(list => list.innerHTML = '');
        });

        searchInputs.forEach(input => {
            input.addEventListener('input', () => {
                const position = input.dataset.position;
                const query = input.value.trim();
                const orgId = orgSelect.value;
                const resultDiv = document.querySelector(`.search-results[data-position="${position}"]`);

                if (query.length < 2 || !orgId) {
                    resultDiv.innerHTML = '';
                    return;
                }

                fetch(`get_org_members.php?org_id=${orgId}&query=${query}`)
                    .then(res => res.json())
                    .then(data => {
                        resultDiv.innerHTML = '';
                        data.forEach(user => {
                            const div = document.createElement('div');
                            div.textContent = `${user.full_name} (${user.course})`;
                            div.style.cursor = 'pointer';
                            div.addEventListener('click', () => {
                                const li = document.createElement('li');
                                li.textContent = `${user.full_name} (${user.course})`;
                                li.dataset.userId = user.id;

                                const removeBtn = document.createElement('button');
                                removeBtn.textContent = 'Remove';
                                removeBtn.style.marginLeft = '10px';
                                removeBtn.addEventListener('click', () => li.remove());
                                li.appendChild(removeBtn);

                                const list = document.querySelector(`.candidate-list[data-position="${position}"]`);
                                // Prevent duplicates
                                if (![...list.children].some(l => l.dataset.userId === user.id.toString())) {
                                    list.appendChild(li);
                                }
                            });
                            resultDiv.appendChild(div);
                        });
                    });
            });
        });

        document.getElementById('save-candidates').addEventListener('click', () => {
            const orgId = orgSelect.value;
            if (!orgId) return alert('Select an organization first');

            const payload = {
                org_id: orgId,
                candidates: {}
            };

            document.querySelectorAll('.candidate-list').forEach(list => {
                const position = list.dataset.position;
                payload.candidates[position] = [...list.children].map(li => li.dataset.userId);
            });

            fetch('admin/assign_candidates.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.text())
            .then(response => {
                alert(response);
            });
        });
    </script>

    <style>
        .position-block {
            margin-bottom: 30px;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 10px;
        }
.position-block {
  border: 1px solid #ccc;
  border-radius: 10px;
  background: #fff;
  padding: 16px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  position: relative;
  min-height: 220px;
}

        .search-results div:hover {
            background: #eee;
        }

        .search-results {
            background: #f9f9f9;
            border: 1px solid #ddd;
            max-height: 100px;
            overflow-y: auto;
            margin-top: 5px;
            padding: 5px;
        }

        .candidate-list {
            list-style-type: none;
            padding: 0;
            margin-top: 10px;
        }
    </style>
<?php endif; ?>

</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile settings toggle
    const profileBtn = document.getElementById('profileBtn');
    const dashboardBtn = document.getElementById('dashboardBtn');
    const mainContent = document.querySelector('.main-content');
    const profileSet = document.getElementById('profileSettings');

    if (profileBtn) {
        profileBtn.addEventListener('click', () => {
            mainContent.style.display = 'none';
            profileSet.style.display = 'flex';
        });
    }

    if (dashboardBtn) {
        dashboardBtn.addEventListener('click', () => {
            profileSet.style.display = 'none';
            mainContent.style.display = 'flex';
        });
    }

    // Logout confirmation
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to log out?')) {
                location.href = 'logout.php';
            }
        });
    }

    // Candidate assignment functionality
    const saveCandidatesBtn = document.getElementById('saveCandidatesBtn');
    if (saveCandidatesBtn && !saveCandidatesBtn.hasAttribute('data-listener')) {
        saveCandidatesBtn.setAttribute('data-listener', 'true');
        saveCandidatesBtn.addEventListener('click', () => {
            const orgId = document.getElementById('organizationSelect')?.value;
            if (!orgId) {
                alert('Please select an organization first');
                return;
            }

            const assignments = {};
            document.querySelectorAll('.position-block').forEach(block => {
                const position = block.getAttribute('data-position');
                const selected = block.querySelectorAll('.candidate-box');
                assignments[position] = [];

                selected.forEach(box => {
                    assignments[position].push(parseInt(box.dataset.id));
                });
            });

            fetch('admin/assign_candidates.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ org_id: orgId, assignments: assignments })
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    alert('Candidates saved successfully!');
                } else {
                    alert('Error saving candidates: ' + (response.message || 'Unknown error'));
                    console.error(response);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Network error occurred');
            });
        });
    }
});

// Organization toggle function
function showOrgEdit(show) {
    const orgOptions = document.getElementById('orgOptionsEdit');
    if (orgOptions) {
        orgOptions.style.display = show ? 'flex' : 'none';
    }
}

// Candidate assignment functions
function showCandidateAssignment() {
    fetch('admin/candidate_assignment_ui.php')
        .then(res => res.text())
        .then(html => {
            document.querySelector('.main-content').innerHTML = html;
            
            // Wait for DOM to update, then attach events
            setTimeout(() => {
                const saveBtn = document.getElementById('saveCandidatesBtn');
                if (saveBtn && !saveBtn.hasAttribute('data-listener')) {
                    saveBtn.setAttribute('data-listener', 'true');
                    saveBtn.addEventListener('click', () => {
                        // Trigger save functionality
                        document.dispatchEvent(new Event('saveCandidates'));
                    });
                }
                
                const orgSelect = document.getElementById('organizationSelect');
                if (orgSelect && !orgSelect.hasAttribute('data-listener')) {
                    orgSelect.setAttribute('data-listener', 'true');
                    orgSelect.addEventListener('change', () => {
                        // Initialize candidate assignment for selected org
                        if (typeof initializeCandidateAssignment === 'function') {
                            initializeCandidateAssignment();
                        }
                    });
                }
            }, 100);
        })
        .catch(err => {
            console.error('Error loading assignment UI:', err);
            alert('Failed to load candidate assignment interface');
        });
}

function viewAssignedCandidates() {
    const orgId = prompt("Enter organization ID to view assigned candidates:");
    
    if (!orgId || isNaN(orgId)) {
        if (orgId !== null) alert('Please enter a valid organization ID');
        return;
    }

    fetch(`admin/view_assigned_candidates.php?org_id=${encodeURIComponent(orgId)}`)
        .then(res => res.text())
        .then(html => {
            document.querySelector('.main-content').innerHTML = `
                <h2>Assigned Candidates</h2>
                <button onclick="location.reload()">← Back to Dashboard</button>
                ${html}
            `;
        })
        .catch(err => {
            console.error("Failed to load candidate list", err);
            alert('Failed to load assigned candidates');
        });
}
</script>
</body>
</html>
