<?php
if (php_sapi_name() !== 'cli' && !isset($_SERVER['HTTP_HOST'])) {
    http_response_code(403);
    exit('Access denied');
}

return [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'verty603_scrobbler',
        'username' => 'verty603_scrobbler',
        'password' => 'gmjFFEE4QzkGhvhQz5Su',
    ],
    'discordWebhookUrl' => 'https://discord.com/api/webhooks/1333405288782495744/22coUZOuhML6YZCOmxiDw0666kFbpkLH9U_InUk0ZHWEzUAjdWPrj85D6-PNGQ3_5_t_',
];
?>