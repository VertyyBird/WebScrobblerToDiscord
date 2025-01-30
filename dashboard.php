<?php
session_start();
$config = require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = new PDO(
    'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['dbname'],
    $config['db']['username'],
    $config['db']['password']
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discordUsername = $_POST['discord_username'];
    $userId = $_SESSION['user_id'];

    // Update Discord username
    $stmt = $pdo->prepare('UPDATE users SET discord_username = ? WHERE id = ?');
    $stmt->execute([$discordUsername, $userId]);

    // Generate a unique API key
    try {
        do {
            $apiKey = bin2hex(random_bytes(16));
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM api_keys WHERE api_key = ?');
            $stmt->execute([$apiKey]);
            $count = $stmt->fetchColumn();
        } while ($count > 0);

        // Save API key to database
        $stmt = $pdo->prepare('INSERT INTO api_keys (user_id, api_key) VALUES (?, ?)');
        $stmt->execute([$userId, $apiKey]);
        $apiUrl = "https://discord.verty.gay/scrobler.php?api_key=$apiKey";
        $message = "API key issued successfully: $apiKey<br>Use this URL to access the API: <a href=\"$apiUrl\">$apiUrl</a>";
    } catch (Exception $e) {
        error_log("Error in dashboard.php: " . $e->getMessage());
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

    <?php if (isset($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="discord_username">Discord Username:</label>
        <input type="text" id="discord_username" name="discord_username" placeholder="Discord Username" required>
        <button type="submit">Generate API Key</button>
    </form>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <br><a href="backdoor.php"><button>Admin Page</button></a>
    <?php endif; ?>
    <br><a href="logout.php"><button>Logout</button></a>
</body>
</html>