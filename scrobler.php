<?php
$config = require 'config.php';

// Replace with your Discord webhook URL
$discordWebhookUrl = $config['discordWebhookUrl'];

// Capture incoming POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate the data (ensure it's coming from Web Scrobbler)
if (!isset($data['track'], $data['artist'], $data['album'], $data['api_key'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
    exit;
}

// Extract data
$track = htmlspecialchars($data['track']);
$artist = htmlspecialchars($data['artist']);
$album = htmlspecialchars($data['album']);
$scrobblerUrl = htmlspecialchars($data['url'] ?? '');
$apiKey = htmlspecialchars($data['api_key']);

// Validate API key and get Discord username
$pdo = new PDO(
    'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['dbname'],
    $config['db']['username'],
    $config['db']['password']
);
$stmt = $pdo->prepare('SELECT users.discord_username FROM api_keys JOIN users ON api_keys.user_id = users.id WHERE api_keys.api_key = ? AND api_keys.valid = 1');
$stmt->execute([$apiKey]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API key']);
    exit;
}

$discordUsername = htmlspecialchars($user['discord_username']);

// Create the Discord embed payload
$embed = [
    "embeds" => [
        [
            "title" => $track,
            "description" => "Artist: **$artist**\nAlbum: **$album**\nUser: **$discordUsername**",
            "url" => $scrobblerUrl,
            "color" => 7506394, // Example embed colour (blue-ish)
        ]
    ]
];

// Send the embed to Discord
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $discordWebhookUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($embed));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute and handle the response
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 204) {
    // Success (204 means no content, which is Discord's standard response)
    echo json_encode(['status' => 'success']);
} else {
    // Failure
    echo json_encode(['status' => 'error', 'response' => $response]);
}
?>
