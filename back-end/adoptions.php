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

function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function readJsonBody(): array
{
    $input = json_decode(file_get_contents('php://input'), true);
    return is_array($input) && !empty($input) ? $input : $_POST;
}

try {
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$userId || $userId < 1) {
        respond(401, ['success' => false, 'error' => 'Lai turpinātu, pieslēdzieties.']);
    }

    $pdo = getDatabaseConnection();

    $userStatement = $pdo->prepare('SELECT user_id FROM users WHERE user_id = ?');
    $userStatement->execute([$userId]);
    if (!$userStatement->fetch()) {
        respond(401, ['success' => false, 'error' => 'Lietotāja sesija vairs nav derīga.']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $sql = 'SELECT a.adoption_id, a.animal_id, a.user_id,
                       a.adoption_date, a.status,
                       u.first_name, u.last_name, u.email,
                       an.name AS animal_name, an.species
                FROM adoptions a
                INNER JOIN users u ON u.user_id = a.user_id
                INNER JOIN animals an ON an.animal_id = a.animal_id
                WHERE a.user_id = :user_id
                ORDER BY a.adoption_id DESC';

        $statement = $pdo->prepare($sql);
        $statement->execute([':user_id' => $userId]);

        echo json_encode([
            'success' => true,
            'adoptions' => $statement->fetchAll()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = readJsonBody();

        foreach (['animal_id', 'adoption_date'] as $field) {
            if (!isset($data[$field]) || !is_scalar($data[$field]) || trim((string)$data[$field]) === '') {
                throw new InvalidArgumentException(
                    "Trūkst obligātā lauka: {$field}"
                );
            }
        }

        $animalId = filter_var($data['animal_id'], FILTER_VALIDATE_INT);
        $adoptionDate = (string)$data['adoption_date'];

        $date = DateTime::createFromFormat('!Y-m-d', $adoptionDate);
        $dateErrors = DateTime::getLastErrors();

        if ($animalId === false || $animalId < 1 ||
            !$date || $date->format('Y-m-d') !== $adoptionDate ||
            ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
            throw new InvalidArgumentException('Nepareizi ievadīti dati.');
        }

        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            "SELECT adoption_status FROM animals
             WHERE animal_id = ? FOR UPDATE"
        );
        $statement->execute([$animalId]);
        $animal = $statement->fetch();

        if (!$animal) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Dzīvnieks nav atrasts.'
            ]);
            exit;
        }

        if ($animal['adoption_status'] !== 'Pieejams') {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Dzīvnieks vairs nav pieejams adopcijai.'
            ]);
            exit;
        }

        $statement = $pdo->prepare(
            "INSERT INTO adoptions
             (animal_id, user_id, adoption_date, status)
             VALUES (?, ?, ?, 'Gaida')"
        );
        $statement->execute([$animalId, $userId, $adoptionDate]);

        $statement = $pdo->prepare(
            "UPDATE animals SET adoption_status = 'Rezervets'
             WHERE animal_id = ?"
        );
        $statement->execute([$animalId]);

        $adoptionId = (int)$pdo->lastInsertId();

        $pdo->commit();

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Adopcijas pieteikums iesniegts.',
            'adoption_id' => $adoptionId,
            'status' => 'Gaida'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(405);
    header('Allow: GET, POST');
    echo json_encode([
        'success' => false,
        'error' => 'Šī metode nav atļauta.'
    ]);

} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $exception->getMessage()
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($exception->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Neizdevās apstrādāt adopcijas pieteikumu.'
    ], JSON_UNESCAPED_UNICODE);
}
