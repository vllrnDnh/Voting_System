<?php
/* ── session / db ─────────────────────────────────────────────── */
session_start();
require_once 'db.php';

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin','user'])) {
    header("Location: login.php"); exit;
}
// Check if voting is allowed
$voting_open = true;
$now = date('Y-m-d H:i:s');

$voteStmt = $conn->query("SELECT end_time FROM vote_settings LIMIT 1");
if ($voteStmt && $voteStmt->num_rows > 0) {
    $endRow = $voteStmt->fetch_assoc();
    $voting_open = $now < $endRow['end_time'];
}

$role     = $_SESSION['role'];
$username = $_SESSION['username'];
$voted = false;
$userId = $_SESSION['user_id'] ?? 0;

if ($userId) {
    $stmt = $conn->prepare("SELECT 1 FROM votes WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    $voted = $stmt->num_rows > 0;
    $stmt->close();
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
    <link rel="stylesheet" href="style.css">
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
    margin-top:100px;
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
        .blurred-org {
            filter: blur(4px);
            pointer-events: none;
        }

/* Voting Form Styles */
/* Voting Form Layout */
#voteView {
    max-width: 900px;
    margin: 30px auto;
    background: #f9f9f9;
    padding: 30px;
    border-radius: 12px;
      margin-top:1000px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

#voteForm fieldset {
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
    background-color: #fff;
}

#voteForm legend {
    font-weight: bold;
    font-size: 1.2em;
    margin-bottom: 15px;
}

#voteForm .candidate-options {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

#voteForm label {
    display: flex;
    align-items: center;
    gap: 8px;
  
    background: #e9f0ff;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.2s;
}

#voteForm label:hover {
    background: #d0e2ff;
}

#voteForm input[type="radio"] {
    transform: scale(1.2);
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
            <button><i class="bi bi-heart"></i></button>
            <button><i class="bi bi-at"></i></button>
            <button><i class="bi bi-chat-left-dots"></i></button>
           
            <button id="logoutBtn" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
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
<?php if ($voted): ?>
    <button disabled class="already-voted">
        ✅ You already voted
    </button>
<?php elseif (!$voting_open): ?>
    <button disabled title="Voting has ended">🛑 Voting Closed</button>
<?php else: ?>
    <button id="voteBtn"><i class="bi bi-check2-square"></i><span>Vote Here!</span></button>
<?php endif; ?>




                </nav>
            </div>
        </div>
    </aside>
    <div class="dashboard-header">
    <h1>Dashboard</h1>
</div>
    <!-- Main content -->
    <div class="resume-container">
        <!-- Countdown display (empty at first) -->
<div id="countdown" style="margin-top: 80px; text-align:center; font-weight:bold; font-size:18px; color:#1a252f;">
⏳ Loading countdown...
</div>


        <?php
$endTimeJS = '';
$res = $conn->query("SELECT end_time FROM vote_settings LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $endTimeJS = $row['end_time'];
}
?>

<script>
const endTime = new Date("<?php echo $endTimeJS; ?>").getTime();
const countdown = document.getElementById('countdown');

function updateCountdown() {
    const now = new Date().getTime();
    const diff = endTime - now;

    if (diff <= 0) {
        countdown.innerHTML = "🛑 Voting is now closed!";
        document.getElementById('voteBtn')?.setAttribute('disabled', true);
        return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hrs = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const secs = Math.floor((diff % (1000 * 60)) / 1000);

    countdown.innerHTML = `⏳ Voting ends in: ${days}d ${hrs}h ${mins}m ${secs}s`;
}

setInterval(updateCountdown, 1000);
</script>

        <div id="voteView" style="display:none;">
            <button onclick="backToDashboard()">Back</button>

    <h2>Cast Your Vote</h2>
    <form id="voteForm"></form>
   <button type="button" onclick="submitVote()">Submit Vote</button>

</div>

     <div class="org-gallery">
    <?php
    require_once 'db.php';
    $query = "SELECT * FROM organizations WHERE is_visible = 1";
    if ($_SESSION['role'] === 'admin') {
        $query = "SELECT * FROM organizations"; // Admins see all, even hidden
    }
    $result = $conn->query($query);

   while ($row = $result->fetch_assoc()) {
   $logoPath = '/FeuVoteSys/' . htmlspecialchars($row['logo_path']);

    // Add blur class only if admin AND hidden
    $blurClass = ($_SESSION['role'] === 'admin' && !$row['is_visible']) ? ' blurred-org' : '';

    echo '<div class="org-box' . $blurClass . '">';
    echo '<img src="' . $logoPath . '" alt="' . htmlspecialchars($row['name']) . '">';
    echo '<p>' . htmlspecialchars($row['name']) . '</p>';

    // Only admin sees the button
    if ($_SESSION['role'] === 'admin') {
        $toggleText = $row['is_visible'] ? 'Hide' : 'Show';
        echo '<form method="POST" action="toggle_org_visibility.php" style="margin-top: 5px;">
                <input type="hidden" name="org_id" value="' . $row['id'] . '">
                <input type="hidden" name="visibility" value="' . ($row['is_visible'] ? 0 : 1) . '">
                <button type="submit">' . $toggleText . '</button>
              </form>';
    }

    echo '</div>';
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
        <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="edit-grid">

            <input name="full_name"       value="<?php echo htmlspecialchars($full_name); ?>"       placeholder="Full Name" required>
            <input name="student_number"  value="<?php echo htmlspecialchars($student_number); ?>"  placeholder="Student Number" required>
            <input name="email"           value="<?php echo htmlspecialchars($email); ?>"           placeholder="Email Address" type="email" required>

            <input name="phone_number"    value="<?php echo htmlspecialchars($phone_number); ?>"    placeholder="Phone Number">
            <input name="course"          value="<?php echo htmlspecialchars($course); ?>"          placeholder="Course">
            <input name="specialization"  value="<?php echo htmlspecialchars($specialization); ?>"  placeholder="Specialization">

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
</div>

<!-- JS -->
<script>
/* toggle profile overlay */
const profileBtn   = document.getElementById('profileBtn');
const dashboardBtn = document.getElementById('dashboardBtn');
const resumeCont   = document.querySelector('.resume-container');
const profileSet   = document.getElementById('profileSettings');

profileBtn.addEventListener('click', ()=>{
    resumeCont.style.display='none';
    profileSet.style.display='flex';
});
dashboardBtn.addEventListener('click', ()=>{
    profileSet.style.display='none';
    resumeCont.style.display='flex';
});

/* yes / no org toggle */
function showOrgEdit(show){
    document.getElementById('orgOptionsEdit').style.display = show ? 'flex':'none';
}

/* logout confirm */
document.getElementById('logoutBtn').addEventListener('click',()=>{
    if(confirm('Are you sure you want to log out?')) location.href='logout.php';
});
</script>
<script>
function showVoteView() {
    document.querySelector('.org-gallery').style.display = 'none';
    document.getElementById('voteView').style.display = 'block';

    fetch('get_candidates.php')
        .then(res => res.json())
        .then(data => {
            const form = document.getElementById('voteForm');
            form.innerHTML = '';

            const grouped = {};
            data.forEach(c => {
                if (!grouped[c.position]) grouped[c.position] = [];
                grouped[c.position].push(c);
            });

            for (const pos in grouped) {
                const group = grouped[pos];
                const fieldset = document.createElement('fieldset');

                const legend = document.createElement('legend');
                legend.textContent = pos;
                fieldset.appendChild(legend);

                group.forEach(cand => {
                    const label = document.createElement('label');
                    label.innerHTML = `
                        <input type="radio" name="${pos}" value="${cand.user_id}">
                        ${cand.full_name}
                    `;
                    fieldset.appendChild(label);
                    fieldset.appendChild(document.createElement('br'));
                });

                form.appendChild(fieldset);
            }
        });
}



</script>
<script>
function submitVote() {
    if (!confirm("Are you sure you want to submit your vote? This cannot be undone.")) return;

    const votes = [];
    const form = document.getElementById('voteForm');
    const fieldsets = form.querySelectorAll('fieldset');

    fieldsets.forEach(fs => {
        const position = fs.querySelector('legend').textContent;
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
        body: JSON.stringify({ votes })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("✅ Your vote was submitted!");
            location.reload();
        } else {
            alert("❌ " + (data.error || 'Vote failed'));
        }
    });
}
</script>

<script>
document.getElementById('voteBtn').addEventListener('click', showVoteView);
</script>
<script>
function backToDashboard() {
    document.getElementById('voteView').style.display = 'none';
document.querySelector('.org-gallery').style.display = 'none';

}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const voteBtn = document.getElementById('voteBtn');
    if (voteBtn) {
        voteBtn.addEventListener('click', showVoteView);
    } else {
        console.error("❌ voteBtn not found in DOM");
    }
});
</script>

</body>
</html> 