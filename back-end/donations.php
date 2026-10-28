<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => isSecureRequest(),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
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
    if (!isSameOriginRequest()) {
        http_response_code(403);
        echo json_encode(['error' => 'Request origin is not allowed.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $data = is_array($input) && !empty($input) ? $input : $_POST;

    $firstName = donationField($data, 'firstName', 30);
    $lastName = donationField($data, 'lastName', 30);
    $email = donationField($data, 'email', 254);
    $amount = $data['customAmount'] ?? null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !is_int($amount) && !is_float($amount) && !is_string($amount) ||
        !is_numeric($amount) || !is_finite((float) $amount) || (float) $amount <= 0 ||
        (float) $amount > 100000) {
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

    $pdo = getDatabaseConnection();
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if ($userId !== false && $userId !== null && $userId > 0) {
        $userCheck = $pdo->prepare('SELECT user_id FROM users WHERE user_id = ?');
        $userCheck->execute([$userId]);
        if (!$userCheck->fetch()) {
            http_response_code(401);
            echo json_encode(['error' => 'Please sign in again.']);
            exit;
        }
    } else {
        $userId = null;
    }

    $statement = $pdo->prepare(
        'INSERT INTO donations
        (donor_name, donor_email, amount, payment_method, status, user_id)
        VALUES (:donor_name, :donor_email, :amount, :payment_method, :status, :user_id)'
    );

    $statement->execute([
        ':donor_name' => $donorName,
        ':donor_email' => $email,
        ':amount' => $amountValue,
        ':payment_method' => 'Demonstrācija',
        ':status' => 'not_processed',
        ':user_id' => $userId
    ]);

    http_response_code(201);
    echo json_encode([
        'message' => 'Demonstration donation record saved. No payment was processed.',
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
