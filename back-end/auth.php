
<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function readJsonBody(): array
{
    $rawBody = file_get_contents('php://input');

    if ($rawBody === false || trim($rawBody) === '') {
        return [];
    }

    $data = json_decode($rawBody, true);

    if (!is_array($data)) {
        throw new InvalidArgumentException('Invalid JSON.');
    }

    return $data;
}

try {
    $pdo = getDatabaseConnection();
    $data = readJsonBody();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed.']);
        exit;
    }

    $action = $data['action'] ?? '';

    // REGISTRATION
    if ($action === 'register') {
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if ($firstName === '' || $lastName === '' ||
            $email === '' || $password === '') {
            throw new InvalidArgumentException(
                'All fields are required.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'Invalid email address.'
            );
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException(
                'Password must contain at least 8 characters.'
            );
        }

        $check = $pdo->prepare(
            'SELECT user_id FROM users WHERE email = :email'
        );
        $check->execute([':email' => $email]);

        if ($check->fetch()) {
            throw new InvalidArgumentException(
                'This email is already registered.'
            );
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $statement = $pdo->prepare(
            'INSERT INTO users
            (first_name, last_name, email, password, birth_date, role)
            VALUES
            (:first_name, :last_name, :email, :password, :birth_date, :role)'
        );

        // The current database schema requires birth_date.
        $birthDate = $data['birth_date'] ?? '';

        if (!is_string($birthDate) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
            throw new InvalidArgumentException(
                'A valid birth_date (YYYY-MM-DD) is required.'
            );
        }

        $date = DateTime::createFromFormat('!Y-m-d', $birthDate);

        if (!$date || $date->format('Y-m-d') !== $birthDate) {
            throw new InvalidArgumentException(
                'Invalid birth date.'
            );
        }

        $statement->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':password' => $passwordHash,
            ':birth_date' => $birthDate,
            ':role' => 'user'
        ]);

        http_response_code(201);
        echo json_encode([
            'message' => 'Registration successful.',
            'user_id' => (int) $pdo->lastInsertId()
        ]);
        exit;
    }

    // LOGIN
    if ($action === 'login') {
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            throw new InvalidArgumentException(
                'Email and password are required.'
            );
        }

        $statement = $pdo->prepare(
            'SELECT user_id, first_name, last_name, email, password, role
             FROM users
             WHERE email = :email'
        );

        $statement->execute([':email' => $email]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode([
                'error' => 'Incorrect email or password.'
            ]);
            exit;
        }

        unset($user['password']);

        echo json_encode([
            'message' => 'Login successful.',
            'user' => $user
        ]);
        exit;
    }

    throw new InvalidArgumentException(
        'Unknown action. Use register or login.'
    );

} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode(['error' => $exception->getMessage()]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error.']);
}
