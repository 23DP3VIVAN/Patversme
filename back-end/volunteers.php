
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

    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody ?: '', true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON.']);
        exit;
    }

    $firstName = trim($data['first_name'] ?? '');
    $lastName = trim($data['last_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $activity = trim($data['activity'] ?? '');
    $message = trim($data['message'] ?? '');

    if (
        $firstName === '' ||
        $lastName === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $phone === '' ||
        $activity === ''
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Please fill in all required fields.']);
        exit;
    }

    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'INSERT INTO volunteer_applications
        (first_name, last_name, email, phone, activity, message)
        VALUES
        (:first_name, :last_name, :email, :phone, :activity, :message)'
    );

    $statement->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':email' => $email,
        ':phone' => $phone,
        ':activity' => $activity,
        ':message' => $message
    ]);

    http_response_code(201);
    echo json_encode([
        'message' => 'Volunteer application submitted successfully.'
    ]);

} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to submit application.']);
}

