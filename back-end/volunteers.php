<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

function volunteerField(array $data, string $field, int $maxLength, bool $required = true): ?string
{
    $value = $data[$field] ?? null;
    if ($value === null && !$required) return null;
    if (!is_string($value)) {
        throw new InvalidArgumentException('Lūdzu, pārbaudiet ievadītos datus.');
    }
    $value = trim($value);
    $length = preg_match_all('/./us', $value);
    if (($required && $value === '') || $length === false || $length > $maxLength) {
        throw new InvalidArgumentException('Lūdzu, pārbaudiet ievadītos datus.');
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

    $firstName = volunteerField($data, 'name', 100);
    $lastName = volunteerField($data, 'surname', 100);
    $email = volunteerField($data, 'email', 100);
    $phone = volunteerField($data, 'phone', 50);
    $activity = volunteerField($data, 'activity', 100);
    $message = volunteerField($data, 'message', 16000, false) ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Lūdzu, ievadiet derīgu e-pasta adresi.');
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

} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode(['error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Neizdevās iesniegt pieteikumu.'], JSON_UNESCAPED_UNICODE);
}
