<?php
require 'db.php';

$username = 'admin';
$password = 'admin123'; // Change this if you want a different password
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("REPLACE INTO users (username, password, role) VALUES (?, ?, 'admin')");
$stmt->bind_param("ss", $username, $hash);
if ($stmt->execute()) {
    echo "<h1 style='color: green; text-align: center;'>Admin reset successful!</h1>";
    echo "<p style='text-align: center;'>Username: <strong>$username</strong><br>Password: <strong>$password</strong></p>";
    echo "<p style='text-align: center;'><a href='login.php'>Go to Login</a> | <a href='reset-admin.php?delete=1'>Delete this file</a></p>";
} else {
    echo "<h1 style='color: red;'>Error: " . $conn->error . "</h1>";
}
$stmt->close();
?>