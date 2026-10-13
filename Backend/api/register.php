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

$firstName = trim($data['first_name'] ?? '');
$lastName = trim($data['last_name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$birthDate = trim($data['birth_date'] ?? '');

$errors = [];

if ($firstName === '' || mb_strlen($firstName) > 30) {
    $errors['first_name'] = 'Ievadiet vārdu (līdz 30 rakstzīmēm).';
}

if ($lastName === '' || mb_strlen($lastName) > 30) {
    $errors['last_name'] = 'Ievadiet uzvārdu (līdz 30 rakstzīmēm).';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 40) {
    $errors['email'] = 'Ievadiet derīgu e-pasta adresi.';
}

if (strlen($password) < 8) {
    $errors['password'] = 'Parolei jābūt vismaz 8 rakstzīmes garai.';
}

$date = DateTime::createFromFormat('!Y-m-d', $birthDate);

if (!$date || $date->format('Y-m-d') !== $birthDate) {
    $errors['birth_date'] = 'Ievadiet derīgu dzimšanas datumu.';
} elseif ($birthDate > date('Y-m-d')) {
    $errors['birth_date'] = 'Dzimšanas datums nevar būt nākotnē.';
}

if (!empty($errors)) {
    http_response_code(422);

    echo json_encode([
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {
    $check = $pdo->prepare(
        'SELECT user_id FROM users WHERE email = :email'
    );

    $check->execute(['email' => $email]);

    if ($check->fetch()) {
        http_response_code(409);

        echo json_encode([
            'error' => 'Šis e-pasts jau ir reģistrēts.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO users
            (first_name, last_name, password, email, birth_date)
         VALUES
            (:first_name, :last_name, :password, :email, :birth_date)'
    );

    $stmt->execute([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'password' => $hashedPassword,
        'email' => $email,
        'birth_date' => $birthDate
    ]);

    http_response_code(201);

    echo json_encode([
        'message' => 'Reģistrācija veiksmīga!'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'error' => 'Reģistrācija neizdevās. Mēģiniet vēlreiz.'
    ], JSON_UNESCAPED_UNICODE);
}
