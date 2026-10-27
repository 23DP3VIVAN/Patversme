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
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/database.php';

function respond($status, $result) {
    http_response_code($status);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

function textLength(string $value): int {
    $length = preg_match_all('/./us', $value);
    return $length === false ? PHP_INT_MAX : $length;
}

function textField(array $data, string $field, int $maxLength, bool $required = true): ?string {
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

function validDate(string $value): bool {
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    $errors = DateTime::getLastErrors();
    return $date !== false && $date->format('Y-m-d') === $value &&
        ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
}

$sessionUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$sessionUserId || $sessionUserId < 1) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Piekļure liegta. Nav administratora tiesību."], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];
$action = $_GET["action"] ?? "";

$input = json_decode(file_get_contents("php://input"), true);
$data = is_array($input) && !empty($input) ? $input : $_POST;

try {
    $pdo = getDatabaseConnection();
    $roleStatement = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $roleStatement->execute([$sessionUserId]);
    if ($roleStatement->fetchColumn() !== 'admin') {
        respond(403, ["success" => false, "error" => "Piekļuve liegta. Nav administratora tiesību."]);
    }

    if ($method === "GET" && $action === "animals") {
        $stmt = $pdo->query("SELECT * FROM animals ORDER BY animal_id DESC");
        respond(200, ["success" => true, "animals" => $stmt->fetchAll()]);
    }

    if ($method === "GET" && $action === "adoptions") {
        $stmt = $pdo->query("SELECT * FROM adoptions ORDER BY adoption_id DESC");
        respond(200, ["success" => true, "adoptions" => $stmt->fetchAll()]);
    }

    if ($method === "POST" && $action === "animals") {
        $name = textField($data, "name", 30);
        $birthDate = textField($data, "birth_date", 10, false);
        $species = textField($data, "species", 30);
        $breed = textField($data, "breed", 50);
        $healthStatus = textField($data, "health_status", 20);
        $description = textField($data, "description", 16000);
        $photo = textField($data, "photo", 255);
        $arrivalDate = textField(
            $data + ["arrival_date" => date("Y-m-d")],
            "arrival_date",
            10
        );
        $ownerRequirements = textField($data, "owner_requirements", 16000);

        if ($birthDate === '') $birthDate = null;
        if ($birthDate !== null && !validDate($birthDate)) {
            throw new InvalidArgumentException("Nederīgs dzimšanas datums.");
        }
        if (!validDate($arrivalDate)) {
            throw new InvalidArgumentException("Nederīgs ierašanās datums.");
        }

        $age = null;
        if (array_key_exists("age", $data) && $data["age"] !== null && $data["age"] !== '') {
            $age = filter_var($data["age"], FILTER_VALIDATE_INT);
            if ($age === false || $age < 0 || $age > 2147483647) {
                throw new InvalidArgumentException("Nederīgs vecums.");
            }
        }

        $stmt = $pdo->prepare(
            "INSERT INTO animals
            (name, birth_date, species, breed, health_status, age,
             description, photo, arrival_date, owner_requirements)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $name,
            $birthDate,
            $species,
            $breed,
            $healthStatus,
            $age,
            $description,
            $photo,
            $arrivalDate,
            $ownerRequirements
        ]);
        respond(201, ["success" => true, "message" => "Dzīvnieks pievienots"]);
    }

    if ($method === "PUT" && $action === "animals") {
        $animalId = filter_var($data["animal_id"] ?? null, FILTER_VALIDATE_INT);
        if ($animalId === false || $animalId < 1) {
            throw new InvalidArgumentException("Nederīgs dzīvnieka ID.");
        }

        $name = textField($data, "name", 30);
        $species = textField($data, "species", 30);
        $breed = textField($data, "breed", 50);
        $healthStatus = textField($data, "health_status", 20);
        $description = textField($data, "description", 16000);
        $photo = textField($data, "photo", 255);

        $exists = $pdo->prepare("SELECT animal_id FROM animals WHERE animal_id = ?");
        $exists->execute([$animalId]);
        if (!$exists->fetch()) {
            respond(404, ["success" => false, "message" => "Dzīvnieks nav atrasts."]);
        }

        $stmt = $pdo->prepare(
            "UPDATE animals SET name=?, species=?, breed=?,
             health_status=?, description=?, photo=?
             WHERE animal_id=?"
        );
        $stmt->execute([
            $name,
            $species,
            $breed,
            $healthStatus,
            $description,
            $photo,
            $animalId
        ]);
        respond(200, ["success" => true, "message" => "Dzīvnieka dati atjaunināti"]);
    }

    if ($method === "DELETE" && $action === "animals") {
        $animalId = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);
        if ($animalId === false || $animalId < 1) {
            throw new InvalidArgumentException("Nederīgs dzīvnieka ID.");
        }

        $stmt = $pdo->prepare("DELETE FROM animals WHERE animal_id = ?");
        $stmt->execute([$animalId]);
        if ($stmt->rowCount() === 0) {
            respond(404, ["success" => false, "message" => "Dzīvnieks nav atrasts."]);
        }
        respond(200, ["success" => true, "message" => "Dzīvnieks dzēsts"]);
    }

    if ($method === "PATCH" && $action === "adoptions") {
        $newStatus = $data["status"] ?? null;
        $adoptionId = filter_var($data["adoption_id"] ?? null, FILTER_VALIDATE_INT);
        $allowedStatuses = ['Gaida', 'Apstiprināts', 'Noraidīts'];
        if (!is_string($newStatus) || !in_array($newStatus, $allowedStatuses, true)) {
            throw new InvalidArgumentException("Nederīgs pieteikuma statuss.");
        }
        if ($adoptionId === false || $adoptionId < 1) {
            throw new InvalidArgumentException("Nederīgs pieteikuma ID.");
        }

        $findAdoption = $pdo->prepare("SELECT animal_id FROM adoptions WHERE adoption_id = ?");
        $findAdoption->execute([$adoptionId]);
        $adoptionReference = $findAdoption->fetch();
        if (!$adoptionReference) {
            respond(404, ["success" => false, "message" => "Pieteikums nav atrasts."]);
        }

        $pdo->beginTransaction();

        // Lock the animal first so this decision is serialized with new applications.
        $lockAnimal = $pdo->prepare("SELECT animal_id FROM animals WHERE animal_id = ? FOR UPDATE");
        $lockAnimal->execute([$adoptionReference['animal_id']]);
        if (!$lockAnimal->fetch()) {
            $pdo->rollBack();
            respond(404, ["success" => false, "message" => "Dzīvnieks nav atrasts."]);
        }

        $lockAdoption = $pdo->prepare("SELECT adoption_id FROM adoptions WHERE adoption_id = ? FOR UPDATE");
        $lockAdoption->execute([$adoptionId]);
        if (!$lockAdoption->fetch()) {
            $pdo->rollBack();
            respond(404, ["success" => false, "message" => "Pieteikums nav atrasts."]);
        }

        $stmt = $pdo->prepare("UPDATE adoptions SET status=? WHERE adoption_id=?");
        $stmt->execute([$newStatus, $adoptionId]);

        $statusCounts = $pdo->prepare(
            "SELECT
                SUM(status = 'Apstiprināts') AS approved_count,
                SUM(status = 'Gaida') AS pending_count
             FROM adoptions
             WHERE animal_id = ?"
        );
        $statusCounts->execute([$adoptionReference['animal_id']]);
        $counts = $statusCounts->fetch();

        if ((int) $counts['approved_count'] > 0) {
            $animalStatus = 'Adoptets';
        } elseif ((int) $counts['pending_count'] > 0) {
            $animalStatus = 'Rezervets';
        } else {
            $animalStatus = 'Pieejams';
        }

        $updateAnimal = $pdo->prepare("UPDATE animals SET adoption_status = ? WHERE animal_id = ?");
        $updateAnimal->execute([$animalStatus, $adoptionReference['animal_id']]);
        $pdo->commit();

        respond(200, ["success" => true, "message" => "Pieteikuma statuss atjaunināts"]);
    }

    respond(400, ["success" => false, "message" => "Neatbalstīta darbība"]);

} catch (InvalidArgumentException $exception) {
    respond(400, ["success" => false, "message" => $exception->getMessage()]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    if (($e->errorInfo[1] ?? null) === 1451) {
        respond(409, ["success" => false, "message" => "Dzīvniekam ir saistīti adopcijas pieteikumi."]);
    }
    respond(500, ["success" => false, "message" => "Datubāzes kļūda"]);
}
