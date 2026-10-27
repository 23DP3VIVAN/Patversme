<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

function donationField(array $data, string $field, int $maxLength): string
{
    $value = $data[$field] ?? null;
    if (!is_string($value)) {
        throw new InvalidArgumentException('Invalid donation data.');
    }
    $value = trim($value);
    $length = preg_match_all('/./us', $value);
    if ($value === '' || $length === false || $length > $maxLength) {
        throw new InvalidArgumentException('Invalid donation data.');
    }
    return $value;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $data = is_array($input) && !empty($input) ? $input : $_POST;

    $firstName = donationField($data, 'firstName', 30);
    $lastName = donationField($data, 'lastName', 30);
    $email = donationField($data, 'email', 40);
    $amount = $data['customAmount'] ?? null;
    $paymentMethod = $data['payment_method'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !is_int($amount) && !is_float($amount) && !is_string($amount) ||
        !is_numeric($amount) || !is_finite((float) $amount) || (float) $amount <= 0 ||
        (float) $amount > 99999999.99) {
        throw new InvalidArgumentException('Invalid donation data.');
    }

    $amountValue = (string) $amount;
    if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $amountValue)) {
        throw new InvalidArgumentException('Invalid donation data.');
    }

    $donorName = $firstName . ' ' . $lastName;
    $donorNameLength = preg_match_all('/./us', $donorName);
    if ($donorNameLength === false || $donorNameLength > 60) {
        throw new InvalidArgumentException('Invalid donation data.');
    }

    if (!is_string($paymentMethod)) {
        throw new InvalidArgumentException('Invalid payment method.');
    }

    $allowedMethods = [
        'VISA',
        'Apple Pay',
        'Google Pay',
        'Internet Bank',
        'Mastercard',
        'PayPal'
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
        ':donor_name' => $donorName,
        ':donor_email' => $email,
        ':amount' => $amountValue,
        ':payment_method' => $paymentMethod
    ]);

    http_response_code(201);
    echo json_encode([
        'message' => 'Donation saved successfully.',
        'donation_id' => (int) $pdo->lastInsertId()
    ]);

} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode(['error' => $exception->getMessage()]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to save donation.']);
}
