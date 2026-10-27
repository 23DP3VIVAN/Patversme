<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

function contactField(array $data, string $field, int $maxLength): string
{
    $value = $data[$field] ?? null;
    if (!is_string($value)) {
        throw new InvalidArgumentException('Lūdzu, pārbaudiet ievadītos datus.');
    }
    $value = trim($value);
    $length = preg_match_all('/./us', $value);
    if ($value === '' || $length === false || $length > $maxLength) {
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

    $firstName = contactField($data, 'name', 100);
    $lastName = contactField($data, 'surname', 100);
    $email = contactField($data, 'email', 100);
    $topic = contactField($data, 'topic', 100);
    $message = contactField($data, 'message', 16000);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Lūdzu, ievadiet derīgu e-pasta adresi.');
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

} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode(['error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Neizdevās saglabāt ziņu.'], JSON_UNESCAPED_UNICODE);
}
