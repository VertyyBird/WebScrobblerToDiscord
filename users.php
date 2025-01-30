<?php
session_start();
$config = require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO(
    'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['dbname'],
    $config['db']['username'],
    $config['db']['password']
);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $userId = $_POST['user_id'];

    // Prevent the current user from suspending or deleting themselves
    if ($userId == $_SESSION['user_id'] && ($action === 'suspend' || $action === 'delete')) {
        $message = "You cannot suspend or delete yourself.";
    } else {
        try {
            if ($action === 'suspend') {
                $stmt = $pdo->prepare('UPDATE users SET suspended = 1 WHERE id = ?');
                $stmt->execute([$userId]);
                $message = "User suspended successfully.";
            } elseif ($action === 'unsuspend') {
                $stmt = $pdo->prepare('UPDATE users SET suspended = 0 WHERE id = ?');
                $stmt->execute([$userId]);
                $message = "User unsuspended successfully.";
            } elseif ($action === 'delete') {
                // Move user to archive table
                $stmt = $pdo->prepare('INSERT INTO users_archive SELECT * FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                // Delete user from users table
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                $message = "User deleted successfully.";
            } elseif ($action === 'revoke_api_key') {
                $stmt = $pdo->prepare('UPDATE api_keys SET valid = 0 WHERE user_id = ?');
                $stmt->execute([$userId]);
                $message = "API key revoked successfully.";
            } elseif ($action === 'issue_api_key') {
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
                    error_log("Error in users.php: " . $e->getMessage());
                    $message = "Error: " . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch users
$stmt = $pdo->query('SELECT * FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if users have API keys
foreach ($users as &$user) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM api_keys WHERE user_id = ? AND valid = 1');
    $stmt->execute([$user['id']]);
    $user['has_api_key'] = $stmt->fetchColumn() > 0;
}

// Separate users into admins and regular users
$admins = array_filter($users, function($user) {
    return $user['role'] === 'admin';
});
$regularUsers = array_filter($users, function($user) {
    return $user['role'] !== 'admin';
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
</head>
<body>
    <h1>Manage Users</h1>

    <button onclick="window.location.href='backdoor.php'">Back to Admin Hub</button>
    <button onclick="window.location.href='logout.php'">Logout</button>

    <?php if ($message): ?>
        <p><?php echo $message; ?></p> <!-- Remove htmlspecialchars here -->
    <?php endif; ?>

    <h2>Admins</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>API Key</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo $user['has_api_key'] ? '✅' : '❌'; ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="action" value="<?php echo $user['suspended'] ? 'unsuspend' : 'suspend'; ?>">
                        <button type="submit" <?php echo ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <?php echo $user['suspended'] ? 'Unsuspend' : 'Suspend'; ?>
                        </button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" <?php echo ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>Delete</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="action" value="revoke_api_key">
                        <button type="submit" <?php echo !$user['has_api_key'] ? 'disabled' : ''; ?>>Revoke API Key</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="action" value="issue_api_key">
                        <button type="submit" <?php echo $user['has_api_key'] ? 'disabled' : ''; ?>>Issue API Key</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Regular Users</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>API Key</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($regularUsers as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo $user['has_api_key'] ? '✅' : '❌'; ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="action" value="<?php echo $user['suspended'] ? 'unsuspend' : 'suspend'; ?>">
                        <button type="submit">
                            <?php echo $user['suspended'] ? 'Unsuspend' : 'Suspend'; ?>
                        </button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit">Delete</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="action" value="revoke_api_key">
                        <button type="submit" <?php echo !$user['has_api_key'] ? 'disabled' : ''; ?>>Revoke API Key</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="action" value="issue_api_key">
                        <button type="submit" <?php echo $user['has_api_key'] ? 'disabled' : ''; ?>>Issue API Key</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>