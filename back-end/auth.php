
<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

function readJsonBody(): array
{
    $rawBody = file_get_contents('php://input');

    if ($rawBody === false || trim($rawBody) === '') {
        return [];
    }

    $data = json_decode($rawBody, true);

    if (!is_array($data)) {
        throw new InvalidArgumentException('Nederīgs JSON.');
    }

    return $data;
}

function respond(int $status, array $result): void
{
    http_response_code($status);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(405, [
            'success' => false,
            'error' => 'Metode nav atļauta.'
        ]);
    }

    $data = readJsonBody();
    $pdo = getDatabaseConnection();
    $action = $data['action'] ?? '';

    // REGISTRATION
    if ($action === 'register') {
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (
            $firstName === '' ||
            $lastName === '' ||
            $email === '' ||
            !is_string($password) ||
            $password === ''
        ) {
            throw new InvalidArgumentException(
                'Aizpildiet visus laukus.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'Nederīga e-pasta adrese.'
            );
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException(
                'Parolei jābūt vismaz 8 simbolus garai.'
            );
        }

        $check = $pdo->prepare(
            'SELECT user_id FROM users WHERE email = ?'
        );
        $check->execute([$email]);

        if ($check->fetch()) {
            throw new InvalidArgumentException(
                'Šis e-pasts jau ir reģistrēts.'
            );
        }

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $statement = $pdo->prepare(
            'INSERT INTO users
            (first_name, last_name, email, password, role)
            VALUES (?, ?, ?, ?, ?)'
        );

        $statement->execute([
            $firstName,
            $lastName,
            $email,
            $passwordHash,
            'user'
        ]);

        $userId = (int) $pdo->lastInsertId();

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = 'user';

        respond(201, [
            'success' => true,
            'message' => 'Reģistrācija veiksmīga.',
            'user_id' => $userId
        ]);
    }

    // LOGIN
    if ($action === 'login') {
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (
            $email === '' ||
            !is_string($password) ||
            $password === ''
        ) {
            throw new InvalidArgumentException(
                'Ievadiet e-pastu un paroli.'
            );
        }

        $statement = $pdo->prepare(
            'SELECT user_id, first_name, last_name, email, password, role
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

        $_SESSION['user_id'] = (int) $user['user_id'];
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

    respond(500, [
        'success' => false,
        'error' => 'Datubāzes kļūda.'
    ]);
}
