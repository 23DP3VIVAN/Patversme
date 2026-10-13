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

    $decoded = json_decode($rawBody, true);

    if (!is_array($decoded)) {
        throw new InvalidArgumentException('Request body must be valid JSON.');
    }

    return $decoded;
}

try {
    $pdo = getDatabaseConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $sql = 'SELECT a.adoption_id, a.animal_id, a.user_id, a.adoption_date,
                       u.first_name, u.last_name, u.email,
                       an.name AS animal_name, an.species
                FROM adoptions a
                INNER JOIN users u ON u.user_id = a.user_id
                INNER JOIN animals an ON an.animal_id = a.animal_id
                ORDER BY a.adoption_id DESC';

        $statement = $pdo->query($sql);
        echo json_encode($statement->fetchAll());
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = readJsonBody();
        $requiredFields = ['animal_id', 'user_id', 'adoption_date'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new InvalidArgumentException("Field '{$field}' is required.");
            }
        }

        $statement = $pdo->prepare('INSERT INTO adoptions (animal_id, user_id, adoption_date) VALUES (:animal_id, :user_id, :adoption_date)');
        $statement->execute([
            ':animal_id' => (int) $data['animal_id'],
            ':user_id' => (int) $data['user_id'],
            ':adoption_date' => trim((string) $data['adoption_date']),
        ]);

        $pdo->prepare('UPDATE animals SET adoption_status = :status WHERE animal_id = :animal_id')
            ->execute([
                ':status' => 'Adoptets',
                ':animal_id' => (int) $data['animal_id'],
            ]);

        http_response_code(201);
        echo json_encode([
            'message' => 'Adoption submitted successfully.',
            'adoption_id' => (int) $pdo->lastInsertId(),
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
    echo json_encode(['error' => 'Unable to process adoption request.']);
    error_log($exception->getMessage());
    exit;
}
