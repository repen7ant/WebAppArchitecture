<?php
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
session_start();

$client_id = 'Ov23liom9xW12Y2V3rii';

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
    'client_id'    => $client_id,
    'redirect_uri' => 'https://boardy.emrysdev.xyz/oauth-callback.php',
    'scope'        => 'read:user',
    'state'        => $state,
]);

header("Location: https://github.com/login/oauth/authorize?$params");
exit;
