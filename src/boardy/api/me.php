<?php
// Единые настройки сессии (как в login.php)
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

function generate_jwt($user_id, $user_name) {
    $secret_key = 'your-secret-key-change-me'; 

    $header = rtrim(strtr(base64_encode(json_encode(
        ['alg' => 'HS256', 'typ' => 'JWT']
    )), '+/', '-_'), '=');

    $payload = rtrim(strtr(base64_encode(json_encode([
        'user_id' => $user_id,
        'name'    => $user_name,
        'exp'     => time() + 3600
    ])), '+/', '-_'), '=');

    $signature = rtrim(strtr(base64_encode(
        hash_hmac('sha256', "$header.$payload", $secret_key, true)
    ), '+/', '-_'), '=');

    return "$header.$payload.$signature";
}

$jwt = generate_jwt($_SESSION['user_id'], $_SESSION['user_name']);

header('Content-Type: application/json');
echo json_encode(['token' => $jwt]);
