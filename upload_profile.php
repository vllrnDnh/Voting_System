<?php
session_start();
require_once 'db.php';

/* ── Access guard ─────────────────────────────────────────────────── */
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$oldUsername = $_SESSION['username'];

/* ── Gather & sanitise input ──────────────────────────────────────── */
$newUsername     = trim($_POST['username']        ?? '');
$full_name       = trim($_POST['full_name']       ?? '');
$student_number  = trim($_POST['student_number']  ?? '');
$email           = trim($_POST['email']           ?? '');
$phone_number    = trim($_POST['phone_number']    ?? '');
$course          = trim($_POST['course']          ?? '');
$specialization  = trim($_POST['specialization']  ?? '');
$birth_raw       =        $_POST['birthdate']     ?? '';
$birthdate       = $birth_raw ? date('Y-m-d', strtotime($birth_raw)) : null;

$is_org_member   = ($_POST['is_org_member'] ?? 'no') === 'yes';
$orgs_array      = $_POST['organizations'] ?? [];
$organizations   = $orgs_array ? implode(',', $orgs_array) : '';

$password        = $_POST['password'] ?? '';

/* ── Build dynamic UPDATE statement ───────────────────────────────── */
$fields = "
    username=?, full_name=?, student_number=?, email=?, phone_number=?,
    course=?, specialization=?, dob=?, org_member=?, organizations=?";
$params = [
    $newUsername, $full_name, $student_number, $email, $phone_number,
    $course, $specialization, $birthdate, $is_org_member ? 1 : 0, $organizations
];
$types  = "ssssssssis";

/* optional password change */
if ($password !== '') {
    $fields .= ", password=?";
    $params[] = password_hash($password, PASSWORD_BCRYPT);
    $types   .= "s";
}

/* optional profile‑picture change */
if (!empty($_FILES['profilePic']['name'])) {
    $targetDir  = "uploads/";
    $fileName   = uniqid() . '.' . pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION);
    $targetPath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetPath)) {
        $fields .= ", profile_pic=?";
        $params[] = $fileName;
        $types   .= "s";
    }
}

/* where clause */
$fields .= " WHERE username=?";
$params[] = $oldUsername;
$types   .= "s";

/* ── Execute ──────────────────────────────────────────────────────── */
$sql  = "UPDATE users SET $fields";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->close();

/* refresh session username if it changed */
$_SESSION['username'] = $newUsername;
$_SESSION['profile_updated'] = 1;

/* redirect to the correct dashboard */
$dest = ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php';
header("Location: $dest");
exit;
