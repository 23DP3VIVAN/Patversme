<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $data = is_array($input) && !empty($input) ? $input : $_POST;

    $firstName = trim($data['name'] ?? '');
    $lastName = trim($data['surname'] ?? '');
    $email = trim($data['email'] ?? '');
    $topic = trim($data['topic'] ?? '');
    $message = trim($data['message'] ?? '');

    // Проверяем каждое поле по отдельности, чтобы понять, где проблема
    if ($firstName === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Kļūda: Nav ievadīts vārds (name)!']);
        exit;
    }
    if ($lastName === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Kļūda: Nav ievadīts uzvārds (surname)!']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Kļūda: Nederīgs e-pasts (email)!']);
        exit;
    }
    if ($topic === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Kļūda: Nav izvēlēta tēma (topic)!']);
        exit;
    }
    if ($message === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Kļūda: Ziņa ir tukša (message)!']);
        exit;
    }

    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'INSERT INTO contact_messages
        (first_name, last_name, email, topic, message)
        VALUES
        (:first_name, :last_name, :email, :topic, :message)'
    );

    $statement->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':email' => $email,
        ':topic' => $topic,
        ':message' => $message
    ]);

    http_response_code(201);
    echo json_encode([
        'message' => 'Your message has been received.'
    ]);

} catch (Exception $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Neizdevās saglabāt ziņu: ' . $exception->getMessage()]);
}