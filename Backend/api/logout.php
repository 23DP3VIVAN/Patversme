
<?php

header('Content-Type: application/json; charset=utf-8');

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'error' => 'Nepareiza pieprasījuma metode.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

echo json_encode([
    'message' => 'Jūs esat izgājis no konta.'
], JSON_UNESCAPED_UNICODE);
