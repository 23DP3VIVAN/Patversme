
<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

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
    $amount = $data['amount'] ?? null;
    $paymentMethod = $data['payment_method'] ?? '';

    if (
        $firstName === '' ||
        $lastName === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !is_numeric($amount) ||
        !is_finite((float) $amount) ||
        (float) $amount <= 0 ||
        !is_string($paymentMethod)
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid donation data.']);
        exit;
    }

    $allowedMethods = [
        'VISA',
        'Apple Pay',
        'Google Pay',
        'Internet Bank',
        'Mastercard'
    ];

    if (!in_array($paymentMethod, $allowedMethods, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payment method.']);
        exit;
    }

    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'INSERT INTO donations
        (donor_name, donor_email, amount, payment_method)
        VALUES (:donor_name, :donor_email, :amount, :payment_method)'
    );

    $statement->execute([
        ':donor_name' => $firstName . ' ' . $lastName,
        ':donor_email' => $email,
        ':amount' => $amount,
        ':payment_method' => $paymentMethod
    ]);

    http_response_code(201);
    echo json_encode([
        'message' => 'Donation saved successfully.',
        'donation_id' => (int) $pdo->lastInsertId()
    ]);

} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to save donation.']);
}
