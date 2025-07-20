<?php
require_once 'includes/db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - TECHVote</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>

<body class="register-page">
    <!-- fixed top bar -->
    <header class="topbar">
        <h1><span style="font-weight:700">TECHV</span>ote
            <small style="font-size:14px;font-weight:500">by TJA</small>
        </h1>
        <button class="vote-btn" onclick="location.href='login.php'">
            <i class="bi bi-box-arrow-right"></i> exit
        </button>
    </header>

    <section class="hero-green">
        <div class="register-container">
            <div class="register-header">
                <h2>Every vote is a voice heard, every voice a step forward</h2>
                <p>student leaders don’t just shape elections, they shape futures</p>

                <div class="logo">TECHVote by TJA</div>
            </div>

            <form class="register-form" method="POST" action="process_register.php">
                <?php
                if (isset($_SESSION['reg_errors'])) {
                    foreach ($_SESSION['reg_errors'] as $e) {
                        echo "<p class='error-text'>$e</p>";
                    }
                    unset($_SESSION['reg_errors']);
                }
                $selectedOrgs = $_SESSION['reg_organizations'] ?? [];
                ?>

                <input name="student_number"  placeholder="Student Number"             required>
                <input name="email"           placeholder="Email Address"              type="email" required>
                <input name="full_name"       placeholder="Last Name, First Name M.I." required>
                <input name="phone_number"    placeholder="Phone Number"               required>
                <input name="course"          placeholder="Year Level (ex. 1st Year)"                     required>
                <input name="birthdate"       type="date"                              required>
                <input name="specialization"  placeholder="Degree Program (ex. BSCSSE)"             required>
                <input name="username"        placeholder="Username"                   required>
                <input name="password"         placeholder="Password"        type="password" required>
                <input name="confirm_password" placeholder="Confirm Password"
                       class="full-width" type="password" required>

                <!-- role selector -->
                <select name="role" class="full-width" required>
                    <option value="" disabled selected>-- Select Role --</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>

                <!-- organisation block -->
                <div class="org-box" style="color: black;">
                    <p class="org-question">Are you a part of any student organizations?</p>

                    <label style="margin-right:20px;">
                        <input type="radio" name="is_org_member" value="yes"
                               onclick="showOrg(true)"> Yes
                    </label>
                    <label>
                        <input type="radio" name="is_org_member" value="no"
                               onclick="showOrg(false)" checked> No
                    </label>

                    <div id="orgOptions" style="display:none; margin-top:18px;">
                        <?php
                        $orgs = $conn->query("SELECT id, name FROM organizations WHERE is_visible = 1");
                        while ($org = $orgs->fetch_assoc()) {
                            $id = (int)$org['id'];
                            $name = htmlspecialchars($org['name']);
                            $checked = in_array($id, $selectedOrgs) ? 'checked' : '';
                            echo "<label><input type='checkbox' name='organizations[]' value='$id' $checked> $name</label>";
                        }
                        ?>
                    </div>
                </div>
                        <button type="submit">Sign Up</button>
                <!-- <button type="submit" style="background-color: black; color: white; border: none; padding: 12px 24px; font-weight: bold; border-radius: 6px; cursor: pointer;">Sign Up</button> -->

            </form>
        </div>
    </section>

    <script>
        function showOrg(show) {
            document.getElementById('orgOptions').style.display = show ? 'block' : 'none';
        }
    </script>
</body>
</html>
