<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {
    $sql = "SELECT
                animal_id,
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
            FROM animals
            WHERE adoption_status = :status
            ORDER BY animal_id DESC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'status' => 'Pieejams'
    ]);

    echo json_encode(
        $stmt->fetchAll(),
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'error' => 'Neizdevās ielādēt dzīvniekus.'
    ], JSON_UNESCAPED_UNICODE);
}
