<?php
// backdoor.php - admin hub page
session_start();
$config = require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Hub</title>
</head>
<body>
    <h1>Admin Hub</h1>
    <p><button onclick="location.href='dashboard.php'">Back to Dashboard</button></p>
    <p><button onclick="location.href='users.php'">Manage Users</button></p>
    <p><button onclick="location.href='logout.php'">Logout</button></p>
</body>
</html>