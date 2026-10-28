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
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : $_POST;
}

function getAnimalFilters(): array
{
    $filters = [];
    foreach (['species' => 30, 'status' => 20, 'gender' => 10, 'breed' => 50, 'health_status' => 100, 'search' => 200] as $field => $limit) {
        $value = $_GET[$field] ?? '';
        if (!is_string($value) || textLength(trim($value)) > $limit) {
            throw new InvalidArgumentException('Nederīgs dzīvnieku filtrs.');
        }
        $filters[$field] = trim($value);
    }

    if ($filters['status'] !== '' && !in_array($filters['status'], ['Pieejams', 'Adoptets', 'Rezervets'], true)) {
        throw new InvalidArgumentException('Nederīgs statusa filtrs.');
    }
    if ($filters['gender'] !== '' && !in_array($filters['gender'], ['male', 'female'], true)) {
        throw new InvalidArgumentException('Nederīgs dzimuma filtrs.');
    }

    $ages = $_GET['age'] ?? [];
    if (!is_array($ages)) {
        $ages = [$ages];
    }
    foreach ($ages as $age) {
        if (!is_string($age) || !in_array($age, ['baby', 'young', 'adult', 'senior'], true)) {
            throw new InvalidArgumentException('Nederīgs vecuma filtrs.');
        }
    }
    $filters['ages'] = array_values(array_unique($ages));
    $filters['special'] = ($_GET['special'] ?? '') === '1';
    $filters['category_age'] = $_GET['category_age'] ?? '';
    if ($filters['category_age'] !== '' && !in_array($filters['category_age'], ['baby'], true)) {
        throw new InvalidArgumentException('Nederīgs kategorijas filtrs.');
    }
    $id = $_GET['id'] ?? '';
    if ($id !== '' && (!is_string($id) || filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id < 1)) {
        throw new InvalidArgumentException('Nederīgs dzīvnieka numurs.');
    }
    $filters['id'] = $id === '' ? null : (int) $id;

    return $filters;
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
        $ageExpression = 'COALESCE(age, TIMESTAMPDIFF(YEAR, birth_date, CURDATE()))';
        $sql = "SELECT animal_id AS id, animal_id, name, birth_date,
                       species, breed, gender, health_status, special_needs,
                       {$ageExpression} AS age, description, photo, arrival_date,
                       adoption_status, owner_requirements
                FROM animals WHERE 1=1";
        $params = [];

        if ($filters['id'] !== null) {
            $sql .= ' AND animal_id = :animal_id';
            $params[':animal_id'] = $filters['id'];
        }
        if ($filters['species'] !== '') {
            if ($filters['species'] === 'cits') {
                $sql .= " AND species NOT IN ('suns', 'kakis')";
            } else {
                $sql .= ' AND species = :species';
                $params[':species'] = $filters['species'];
            }
        }
        if ($filters['status'] !== '') {
            $sql .= ' AND adoption_status = :status';
            $params[':status'] = $filters['status'];
        }
        if ($filters['gender'] !== '') {
            $sql .= ' AND gender = :gender';
            $params[':gender'] = $filters['gender'];
        }
        if ($filters['breed'] !== '') {
            $sql .= ' AND breed LIKE :breed';
            $params[':breed'] = '%' . $filters['breed'] . '%';
        }
        if ($filters['health_status'] !== '') {
            $sql .= ' AND health_status LIKE :health_status';
            $params[':health_status'] = '%' . $filters['health_status'] . '%';
        }
        if ($filters['search'] !== '') {
            $sql .= ' AND CONCAT_WS(CHAR(32), name, species, breed, health_status, description) LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if ($filters['special']) {
            $sql .= ' AND special_needs = 1';
        }
        if ($filters['category_age'] === 'baby') {
            $sql .= " AND {$ageExpression} < 1";
        }
        if ($filters['ages']) {
            $ageConditions = [];
            foreach ($filters['ages'] as $ageGroup) {
                if ($ageGroup === 'baby') {
                    $ageConditions[] = "{$ageExpression} < 1";
                } elseif ($ageGroup === 'young') {
                    $ageConditions[] = "{$ageExpression} BETWEEN 1 AND 3";
                } elseif ($ageGroup === 'adult') {
                    $ageConditions[] = "{$ageExpression} BETWEEN 4 AND 7";
                } else {
                    $ageConditions[] = "{$ageExpression} > 7";
                }
            }
            $sql .= ' AND (' . implode(' OR ', $ageConditions) . ')';
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
        $healthStatus = textField($data, 'health_status', 100);
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
