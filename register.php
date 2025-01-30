<?php
// register.php
session_start();
$config = require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $role = $_POST['role'] ?? 'user'; // Default role is 'user'

    // Check if username or email already exists
    $pdo = new PDO(
        'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['dbname'],
        $config['db']['username'],
        $config['db']['password']
    );
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "Username or email already in use.";
    } else {
        // Save user to database
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, plain_password, role) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$username, $email, $hashedPassword, $password, $role]);

        // Set success message and redirect to login page
        $_SESSION['success_message'] = "Registration successful! Please log in.";
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" placeholder="Username" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Email" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <br>
        <label for="role">Role:</label>
        <select id="role" name="role">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
        <br>
        <button type="submit">Register</button>
    </form>
</body>
</html>