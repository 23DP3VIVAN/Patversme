<?php
$secureSessionCookie = isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secureSessionCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode([
        'success' => false,
        'error' => 'Šī metode nav atļauta.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

session_unset();
$cookieParams = session_get_cookie_params();
setcookie(
    session_name(),
    '',
    time() - 42000,
    $cookieParams['path'],
    $cookieParams['domain'],
    $cookieParams['secure'],
    $cookieParams['httponly']
);
session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Lietotājs ir izrakstījies"
]);
?>
