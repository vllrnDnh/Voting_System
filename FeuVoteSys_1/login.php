<?php
require 'db.php';
session_start();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");

    $stmt->bind_param("s", $username);
    $stmt->execute(); $stmt->store_result();

   if ($stmt->num_rows === 1) {
    $stmt->bind_result($user_id, $hashed, $role);
    $stmt->fetch();
    if (password_verify($password, $hashed)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role']     = $role;

        $stmt = $conn->prepare("INSERT INTO logs (username, action, role) VALUES (?, 'login', ?)");
        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();

        header("Location: ".($role === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
        exit;
    }
}

    $errors[] = "Invalid credentials.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | TECHVote</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ── picture‑2 split layout ───────────────────────────────────────────── */
        .split-wrapper{display:flex;min-height:100vh;}
        .left-pane{flex:1;background:#5f7f72;color:#fff;display:flex;flex-direction:column;
                   justify-content:center;padding:0 5%;}
        .left-pane h1{font-size:42px;line-height:1.1;font-weight:700;margin:0;}
        .left-pane p.caption{font-size:14px;margin:4px 0 40px;}
        .left-pane .blurb{background:#fff;color:#2c3e50;border-radius:12px;padding:28px;
                          font-size:14px;max-width:420px;}
        .right-pane{flex:1;display:flex;justify-content:center;align-items:center;}
        @media(max-width:860px){.split-wrapper{flex-direction:column;}
                                .right-pane{padding:60px 0;}}
    </style>
</head>
<body class="login-page">
<div class="split-wrapper">
    <!-- Quote side -->
    <div class="left-pane">
        <h1>Every vote is a voice heard,<br>every voice a step forward</h1>
        <p class="caption">student leaders don’t just shape elections, they shape futures</p>

        <div class="blurb">
            The innovative platform designed to streamline the voting process for student
            organizations at the FEU Institute of Technology. Our automated voting system ensures
            a secure, transparent, and efficient way for students to participate in elections,
            empowering them to make their voices heard.
        </div>
    </div>

    <!-- Login card -->
    <div class="right-pane image-bg">
        <form method="post" class="auth-form" style="background:rgba(255,255,255,.95);">
            <h2 style="margin-top:0">Login</h2>
            <?php foreach ($errors as $e) echo "<p class='error-text'>$e</p>"; ?>
            <?php if (isset($_GET['registered'])) echo "<p class='success-text'>Registration successful. Please log in.</p>"; ?>

            <input type="text"     name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <a href="register.php">Don't have an account? Register</a>
        </form>
    </div>
</div>
</body>
</html>
