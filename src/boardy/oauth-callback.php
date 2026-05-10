<?php
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);
session_start();
require_once 'db.php';

$client_id = 'Ov23liom9xW12Y2V3rii';
$client_secret = 'CLIENT_SECRET';

if (($_GET['state'] ?? '') !== ($_SESSION['oauth_state'] ?? '')) {
    die('Invalid state — possible CSRF attack');
}

$ch = curl_init('https://github.com/login/oauth/access_token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'code'          => $_GET['code'],
    ]),
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

$access_token = $response['access_token'] ?? null;
if (!$access_token) die('Ошибка получения токена от GitHub');

$ch = curl_init('https://api.github.com/user');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $access_token",
        'User-Agent: BoardyApp'
    ],
    CURLOPT_RETURNTRANSFER => true,
]);
$profile = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($profile['id']) || empty($profile['login'])) {
    die('Не удалось получить данные профиля');
}

$stmt = $pdo->prepare('SELECT id, name FROM users WHERE github_id = ?');
$stmt->execute([$profile['id']]);
$user = $stmt->fetch();

if (!$user) {
    $stmt = $pdo->prepare('INSERT INTO users (name, github_id) VALUES (?, ?)');
    $stmt->execute([$profile['login'], $profile['id']]);
    $user = ['id' => $pdo->lastInsertId(), 'name' => $profile['login']];
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];

$_SESSION['github_login'] = true;

header('Location: /messages.php');
exit;
