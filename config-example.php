<?php
if (php_sapi_name() !== 'cli' && !isset($_SERVER['HTTP_HOST'])) {
    http_response_code(403);
    exit('Access denied');
}

return [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'DB',
        'username' => 'NAME',
        'password' => 'PASS',
    ],
    'discordWebhookUrl' => 'https://discord.com/api/webhooks/',
];
?>