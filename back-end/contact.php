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

    $data = $_POST;

    $firstName = trim($data['name'] ?? '');
    $lastName = trim($data['surname'] ?? '');
    $email = trim($data['email'] ?? '');
    $topic = trim($data['topic'] ?? '');
    $message = trim($data['message'] ?? '');

    if (
        $firstName === '' ||
        $lastName === '' ||
        $topic === '' ||
        $message === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Please provide valid contact details.']);
        exit;
    }

    if (mb_strlen($message) > 5000) {
        http_response_code(400);
        echo json_encode(['error' => 'Message is too long.']);
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

} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to save your message.']);
}
