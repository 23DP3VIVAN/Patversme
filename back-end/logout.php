<?php
session_start();

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

header("Content-Type: application/json; charset=UTF-8");

echo json_encode([
    "success" => true,
    "message" => "Lietotājs ir izrakstījies"
]);
?>
