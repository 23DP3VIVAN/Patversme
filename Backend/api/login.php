
<?php

header('Content-Type: application/json; charset=utf-8');

session_start();

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'error' => 'Nepareiza pieprasījuma metode.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    $data = $_POST;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $password === ''
) {
    http_response_code(422);

    echo json_encode([
        'error' => 'Ievadiet derīgu e-pastu un paroli.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT user_id, first_name, last_name, email, password, role
         FROM users
         WHERE email = :email'
    );

    $stmt->execute([
        'email' => $email
    ]);

    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);

        echo json_encode([
            'error' => 'Nepareizs e-pasts vai parole.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['role'] = $user['role'];

    echo json_encode([
        'message' => 'Pieteikšanās veiksmīga!',
        'user' => [
            'user_id' => $user['user_id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'error' => 'Neizdevās pieslēgties kontam.'
    ], JSON_UNESCAPED_UNICODE);
}
