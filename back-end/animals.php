<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$secureSessionCookie = isSecureRequest();
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
header('Content-Type: application/json; charset=utf-8');

function readJsonBody(): array
{
    return $_POST;
}

function getAnimalFilters(): array
{
    $species = $_GET['species'] ?? null;
    $status = $_GET['status'] ?? null;

    if ($species !== null && !is_string($species)) {
        throw new InvalidArgumentException('Nederīgs sugas filtrs.');
    }
    if ($status !== null && !is_string($status)) {
        throw new InvalidArgumentException('Nederīgs statusa filtrs.');
    }

    $species = $species !== null ? trim($species) : null;
    $status = $status !== null ? trim($status) : null;
    if ($species !== null && $species !== '' && textLength($species) > 30) {
        throw new InvalidArgumentException('Nederīgs sugas filtrs.');
    }
    if ($status !== null && $status !== '' && !in_array($status, ['Pieejams', 'Adoptets', 'Rezervets'], true)) {
        throw new InvalidArgumentException('Nederīgs statusa filtrs.');
    }

    return [
        'species' => $species,
        'status' => $status,
    ];
}

function textLength(string $value): int
{
    $length = preg_match_all('/./us', $value);
    return $length === false ? PHP_INT_MAX : $length;
}

function textField(array $data, string $field, int $maxLength, bool $required = true): ?string
{
    $value = $data[$field] ?? null;
    if ($value === null && !$required) return null;
    if (!is_string($value)) {
        throw new InvalidArgumentException("Nederīga lauka vērtība: {$field}.");
    }

    $value = trim($value);
    if (($required && $value === '') || textLength($value) > $maxLength) {
        throw new InvalidArgumentException("Nederīga lauka vērtība: {$field}.");
    }
    return $value;
}

function validDate(string $value): bool
{
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    $errors = DateTime::getLastErrors();
    return $date !== false && $date->format('Y-m-d') === $value &&
        ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
}

try {
    $pdo = getDatabaseConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $filters = getAnimalFilters();
        $sql = 'SELECT animal_id, animal_id AS id, name, birth_date,
                       species, breed, health_status, age, description,
                       photo, arrival_date, adoption_status, owner_requirements
                FROM animals WHERE 1=1';
        $params = [];

        if ($filters['species'] !== null && $filters['species'] !== '') {
            $sql .= ' AND species = :species';
            $params[':species'] = $filters['species'];
        }

        if ($filters['status'] !== null && $filters['status'] !== '') {
            $sql .= ' AND adoption_status = :status';
            $params[':status'] = $filters['status'];
        }

        $sql .= ' ORDER BY animal_id ASC';
        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        echo json_encode($statement->fetchAll(), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sessionUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$sessionUserId || $sessionUserId < 1) {
            http_response_code(401);
            echo json_encode(['error' => 'Administrator login is required.']);
            exit;
        }

        $roleStatement = $pdo->prepare('SELECT role FROM users WHERE user_id = ?');
        $roleStatement->execute([$sessionUserId]);
        if ($roleStatement->fetchColumn() !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Administrator access is required.']);
            exit;
        }

        if (!isSameOriginRequest()) {
            http_response_code(403);
            echo json_encode(['error' => 'Request origin is not allowed.']);
            exit;
        }

        $data = readJsonBody();
        $name = textField($data, 'name', 30);
        $birthDate = textField($data, 'birth_date', 10, false);
        $species = textField($data, 'species', 30);
        $breed = textField($data, 'breed', 50);
        $healthStatus = textField($data, 'health_status', 20);
        $description = textField($data, 'description', 16000);
        $photo = textField($data, 'photo', 255);
        $arrivalDate = textField($data, 'arrival_date', 10);
        $ownerRequirements = textField($data, 'owner_requirements', 16000);

        if ($birthDate === '') $birthDate = null;
        if ($birthDate !== null && !validDate($birthDate)) {
            throw new InvalidArgumentException('Nederīgs dzimšanas datums.');
        }
        if (!validDate($arrivalDate)) {
            throw new InvalidArgumentException('Nederīgs ierašanās datums.');
        }

        $age = null;
        if (array_key_exists('age', $data) && $data['age'] !== null && $data['age'] !== '') {
            $age = filter_var($data['age'], FILTER_VALIDATE_INT);
            if ($age === false || $age < 0 || $age > 2147483647) {
                throw new InvalidArgumentException('Nederīgs vecums.');
            }
        }

        $sql = 'INSERT INTO animals (
            name,
            birth_date,
            species,
            breed,
            health_status,
            age,
            description,
            photo,
            arrival_date,
            owner_requirements
        ) VALUES (
            :name,
            :birth_date,
            :species,
            :breed,
            :health_status,
            :age,
            :description,
            :photo,
            :arrival_date,
            :owner_requirements
        )';

        $statement = $pdo->prepare($sql);
        $statement->execute([
            ':name' => $name,
            ':birth_date' => $birthDate,
            ':species' => $species,
            ':breed' => $breed,
            ':health_status' => $healthStatus,
            ':age' => $age,
            ':description' => $description,
            ':photo' => $photo,
            ':arrival_date' => $arrivalDate,
            ':owner_requirements' => $ownerRequirements,
        ]);

        http_response_code(201);
        echo json_encode([
            'message' => 'Animal created successfully.',
            'animal_id' => (int) $pdo->lastInsertId(),
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode(['error' => $exception->getMessage()]);
    exit;
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to process animal request.']);
    error_log($exception->getMessage());
    exit;
}
