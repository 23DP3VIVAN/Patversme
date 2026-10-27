<?php

declare(strict_types=1);

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

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function readJsonBody(): array
{
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return $_POST;
}

function respond(int $status, array $result): void
{
    http_response_code($status);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

function textLength(string $value): int
{
    $length = preg_match_all('/./us', $value);
    return $length === false ? PHP_INT_MAX : $length;
}

function requiredText(array $data, string $field, int $maxLength): string
{
    $value = $data[$field] ?? null;
    if (!is_string($value)) {
        throw new InvalidArgumentException('Aizpildiet laukus korekti.');
    }

    $value = trim($value);
    if ($value === '' || textLength($value) > $maxLength) {
        throw new InvalidArgumentException('Aizpildiet laukus korekti.');
    }

    return $value;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(405, [
            'success' => false,
            'error' => 'Metode nav atļauta.'
        ]);
    }

    $data = readJsonBody();
    $action = $data['action'] ?? '';

    if ($action === '' && isset($data['registerEmail'])) {
        $action = 'register';
    }

    if ($action === '' && isset($data['loginEmail'])) {
        $action = 'login';
    }

    // REGISTRATION
    if ($action === 'register') {
        $firstName = requiredText($data, 'firstName', 30);
        $lastName = requiredText($data, 'lastName', 30);
        $email = strtolower(requiredText($data, 'email', 40));
        $password = $data['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'Nederīga e-pasta adrese.'
            );
        }

        if (!is_string($password) || textLength($password) > 1024 ||
            preg_match('/^(?=.*\p{Ll})(?=.*\p{Lu})(?=.*\p{N})(?=.*[^\p{L}\p{N}\s])\S{15,}$/uD', $password) !== 1) {
            throw new InvalidArgumentException(
                'Parolei jābūt vismaz 15 rakstzīmes garai un jāsatur lielie un mazie burti, cipars un speciālā rakstzīme.'
            );
        }

        $pdo = getDatabaseConnection();
        $check = $pdo->prepare(
            'SELECT user_id FROM users WHERE email = ?'
        );
        $check->execute([$email]);

        if ($check->fetch()) {
            respond(409, [
                'success' => false,
                'error' => 'Šis e-pasts jau ir reģistrēts.'
            ]);
        }

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $statement = $pdo->prepare(
            'INSERT INTO users
            (first_name, last_name, email, password)
            VALUES (?, ?, ?, ?)'
        );

        $statement->execute([
            $firstName,
            $lastName,
            $email,
            $passwordHash
        ]);

        $userId = (int) $pdo->lastInsertId();

        $roleStatement = $pdo->prepare('SELECT role FROM users WHERE user_id = ?');
        $roleStatement->execute([$userId]);
        $role = $roleStatement->fetchColumn();

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $role;

        respond(201, [
            'success' => true,
            'message' => 'Reģistrācija veiksmīga.',
            'user_id' => $userId
        ]);
    }

    if ($action === 'login') {
        $email = strtolower(requiredText($data, 'email', 40));
        $password = $data['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password) ||
            $password === '' || textLength($password) > 1024) {
            throw new InvalidArgumentException('Ievadiet derīgu e-pastu un paroli.');
        }

        $pdo = getDatabaseConnection();
        $statement = $pdo->prepare(
            'SELECT user_id AS id, first_name, last_name, email, password, role
             FROM users
             WHERE email = ?'
        );

        $statement->execute([$email]);
        $user = $statement->fetch();

        if (
            !$user ||
            !password_verify($password, $user['password'])
        ) {
            respond(401, [
                'success' => false,
                'error' => 'Nepareizs e-pasts vai parole.'
            ]);
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = $user['role'];

        unset($user['password']);

        respond(200, [
            'success' => true,
            'message' => 'Pieslēgšanās veiksmīga.',
            'user' => $user
        ]);
    }

    throw new InvalidArgumentException(
        'Nezināma darbība.'
    );

} catch (InvalidArgumentException $exception) {
    respond(400, [
        'success' => false,
        'error' => $exception->getMessage()
    ]);

} catch (PDOException $exception) {
    error_log($exception->getMessage());

    if (($exception->errorInfo[1] ?? null) === 1062) {
        respond(409, [
            'success' => false,
            'error' => 'Šis e-pasts jau ir reģistrēts.'
        ]);
    }

    respond(500, [
        'success' => false,
        'error' => 'Neizdevās apstrādāt pieprasījumu.'
    ]);
}
