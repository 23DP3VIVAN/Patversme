
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {
    $search = trim($_GET['search'] ?? '');
    $species = trim($_GET['species'] ?? '');
    $status = trim($_GET['status'] ?? 'Pieejams');
    $sort = $_GET['sort'] ?? 'newest';

    $allowedStatuses = [
        'Pieejams',
        'Adoptets',
        'Rezervets',
        'all'
    ];

    $allowedSorts = [
        'newest' => 'animal_id DESC',
        'oldest' => 'animal_id ASC',
        'name' => 'name ASC',
        'age' => 'age ASC'
    ];

    if (!in_array($status, $allowedStatuses, true)) {
        http_response_code(400);

        echo json_encode([
            'error' => 'Nederīgs dzīvnieka statuss.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!isset($allowedSorts[$sort])) {
        http_response_code(400);

        echo json_encode([
            'error' => 'Nederīga kārtošanas izvēle.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

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
            WHERE 1 = 1";

    $params = [];

    if ($status !== 'all') {
        $sql .= ' AND adoption_status = :status';
        $params['status'] = $status;
    }

    if ($search !== '') {
        $sql .= ' AND (
            name LIKE :search_name
            OR breed LIKE :search_breed
            OR species LIKE :search_species
            OR description LIKE :search_description
        )';

        $searchTerm = '%' . $search . '%';

        $params['search_name'] = $searchTerm;
        $params['search_breed'] = $searchTerm;
        $params['search_species'] = $searchTerm;
        $params['search_description'] = $searchTerm;
    }

    if ($species !== '') {
        $sql .= ' AND species = :species';
        $params['species'] = $species;
    }

    $sql .= ' ORDER BY ' . $allowedSorts[$sort];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'animals' => $stmt->fetchAll(),
        'filters' => [
            'search' => $search,
            'species' => $species,
            'status' => $status,
            'sort' => $sort
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'error' => 'Neizdevās ielādēt dzīvniekus.'
    ], JSON_UNESCAPED_UNICODE);
}
