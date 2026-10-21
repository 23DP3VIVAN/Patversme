<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$method = $_SERVER["REQUEST_METHOD"];
$action = $_GET["action"] ?? "";
$data = json_decode(file_get_contents("php://input"), true) ?? [];

function respond($status, $result) {
    http_response_code($status);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($method === "GET" && $action === "animals") {
        $stmt = $pdo->query("SELECT * FROM animals ORDER BY animal_id DESC");
        respond(200, ["success" => true, "animals" => $stmt->fetchAll()]);
    }

    if ($method === "GET" && $action === "adoptions") {
        $stmt = $pdo->query("SELECT * FROM adoptions ORDER BY adoption_id DESC");
        respond(200, ["success" => true, "adoptions" => $stmt->fetchAll()]);
    }

    if ($method === "POST" && $action === "animals") {
        $stmt = $pdo->prepare(
            "INSERT INTO animals
            (name, birth_date, species, breed, health_status,
             description, photo, arrival_date, owner_requirements)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data["name"] ?? "",
            $data["birth_date"] ?? null,
            $data["species"] ?? "",
            $data["breed"] ?? null,
            $data["health_status"] ?? "",
            $data["description"] ?? null,
            $data["photo"] ?? null,
            $data["arrival_date"] ?? date("Y-m-d"),
            $data["owner_requirements"] ?? null
        ]);

        respond(201, ["success" => true, "message" => "Dzīvnieks pievienots"]);
    }

    if ($method === "PUT" && $action === "animals") {
        $stmt = $pdo->prepare(
            "UPDATE animals SET name=?, species=?, breed=?,
             health_status=?, description=?, photo=?
             WHERE animal_id=?"
        );

        $stmt->execute([
            $data["name"] ?? "",
            $data["species"] ?? "",
            $data["breed"] ?? null,
            $data["health_status"] ?? "",
            $data["description"] ?? null,
            $data["photo"] ?? null,
            $data["animal_id"] ?? 0
        ]);

        respond(200, ["success" => true, "message" => "Dzīvnieka dati atjaunināti"]);
    }

    if ($method === "DELETE" && $action === "animals") {
        $stmt = $pdo->prepare("DELETE FROM animals WHERE animal_id = ?");
        $stmt->execute([$_GET["id"] ?? 0]);

        respond(200, ["success" => true, "message" => "Dzīvnieks dzēsts"]);
    }

    if ($method === "PATCH" && $action === "adoptions") {
        $stmt = $pdo->prepare(
            "UPDATE adoptions SET status=? WHERE adoption_id=?"
        );

        $stmt->execute([
            $data["status"] ?? "",
            $data["adoption_id"] ?? 0
        ]);

        respond(200, ["success" => true, "message" => "Pieteikuma statuss atjaunināts"]);
    }

    respond(400, ["success" => false, "message" => "Neatbalstīta darbība"]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    respond(500, ["success" => false, "message" => "Datubāzes kļūda"]);
}
