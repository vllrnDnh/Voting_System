<?php
require_once 'includes/db.php';
session_start();

/* ---------- 1. Collect input ------------------------------ */
$full_name       = trim($_POST['full_name']        ?? '');
$username        = trim($_POST['username']         ?? '');
$password        =        $_POST['password']       ?? '';
$confirm         =        $_POST['confirm_password'] ?? '';

$role            = $_POST['role'] ?? 'user';
$role            = in_array($role, ['admin','user'], true) ? $role : 'user';

$email           = trim($_POST['email']            ?? '');
$student_number  = trim($_POST['student_number']   ?? '');
$phone_number    = trim($_POST['phone_number']     ?? '');
$course          = trim($_POST['course']           ?? '');
$specialization  = trim($_POST['specialization']   ?? '');
$birthdate_raw   =        $_POST['birthdate']      ?? '';
$birthdate       = $birthdate_raw ? date('Y-m-d', strtotime($birthdate_raw)) : null;

$is_org_member   = ($_POST['is_org_member'] ?? 'no') === 'yes';
$orgs_array      = $_POST['organizations'] ?? [];
$organizations   = $orgs_array ? implode(',', $orgs_array) : '';

$errors = [];

/* ---------- 2. Validate ----------------------------------- */
if (!$full_name || !$username || !$password || !$confirm) {
    $errors[] = "Please fill in all required fields.";
}
if ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
}

/* ---------- 3. Uniqueness check --------------------------- */
if (!$errors) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR student_number = ?");
    $stmt->bind_param("ss", $username, $student_number);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows) $errors[] = "Username or student number already exists.";
    $stmt->close();
}

/* ---------- 4. Insert user ------------------------------- */
if (!$errors) {
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("
        INSERT INTO users
          (full_name, username, password, role,
           student_number, email, phone_number, course, specialization, dob,
           org_member, organizations)
        VALUES (?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $org_member_int = $is_org_member ? 1 : 0;
    $stmt->bind_param(
        "ssssssssssis",
        $full_name, $username, $hashed, $role,
        $student_number, $email, $phone_number,
        $course, $specialization, $birthdate,
        $org_member_int, $organizations
    );
    $stmt->execute();
    $stmt->close();

    $user_id = $conn->insert_id;

    // ✅ Insert org memberships into junction table
    if ($is_org_member && !empty($orgs_array)) {
        $stmt = $conn->prepare("INSERT INTO user_organizations (user_id, org_id) VALUES (?, ?)");
        foreach ($orgs_array as $org_id) {
            $org_id = intval($org_id); // extra safety
            $stmt->bind_param("ii", $user_id, $org_id);
            $stmt->execute();
        }
        $stmt->close();
    }

    // Log registration
    $stmt = $conn->prepare("INSERT INTO logs (username, action, role) VALUES (?, 'register', ?)");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $stmt->close();

    // Cleanup session and redirect
    unset($_SESSION['reg_organizations']);
    header("Location: login.php?registered=1");
    exit;
}

/* ---------- 5. On error, show messages -------------------- */
if ($errors) {
    $_SESSION['reg_errors'] = $errors;
    $_SESSION['reg_organizations'] = $orgs_array;
    header("Location: register.php");
    exit;
}
