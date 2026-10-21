<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

function readJsonBody(): array
{
    return $_POST;
}

function getAnimalFilters(): array
{
    $species = $_GET['species'] ?? null;
    $status = $_GET['status'] ?? null;

    return [
        'species' => $species !== null ? trim((string) $species) : null,
        'status' => $status !== null ? trim((string) $status) : null,
    ];
}

try {
    $pdo = getDatabaseConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $filters = getAnimalFilters();
        $sql = 'SELECT * FROM animals WHERE 1=1';
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

        echo json_encode($statement->fetchAll());
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = readJsonBody();
        $requiredFields = [
            'name',
            'birth_date',
            'species',
            'breed',
            'health_status',
            'description',
            'photo',
            'arrival_date',
            'owner_requirements',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new InvalidArgumentException("Field '{$field}' is required.");
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
            adoption_status,
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
            :adoption_status,
            :owner_requirements
        )';

        $statement = $pdo->prepare($sql);
        $statement->execute([
            ':name' => trim((string) $data['name']),
            ':birth_date' => trim((string) $data['birth_date']),
            ':species' => trim((string) $data['species']),
            ':breed' => trim((string) $data['breed']),
            ':health_status' => trim((string) $data['health_status']),
            ':age' => isset($data['age']) && $data['age'] !== '' ? (int) $data['age'] : null,
            ':description' => trim((string) $data['description']),
            ':photo' => trim((string) $data['photo']),
            ':arrival_date' => trim((string) $data['arrival_date']),
            ':adoption_status' => isset($data['adoption_status']) && trim((string) $data['adoption_status']) !== ''
                ? trim((string) $data['adoption_status'])
                : 'Pieejams',
            ':owner_requirements' => trim((string) $data['owner_requirements']),
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
