<?php
$host = 'localhost';
$user = 'root'; // default for XAMPP
$pass = ''; // your MySQL password
$dbname = 'user_system';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
