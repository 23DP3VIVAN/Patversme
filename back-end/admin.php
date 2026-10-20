
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/config/database.php";

try {
    $pdo = require __DIR__ . "/config/database.php";

    $method = $_SERVER["REQUEST_METHOD"];

    if ($method === "GET") {
        $stmt = $pdo->query("SELECT * FROM animals ORDER BY animal_id DESC");

        echo json_encode([
            "success" => true,
            "animals" => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    } else {
        http_response_code(405);

        echo json_encode([
            "success" => false,
            "message" => "Metode nav atļauts"
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Datubāzes kļūda"
    ]);
}
?>
