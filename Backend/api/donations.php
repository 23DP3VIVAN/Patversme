
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'error' => 'Nepareiza pieprasījuma metode.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    $data = $_POST;
}

$donorName = trim($data['donor_name'] ?? '');
$donorEmail = trim($data['donor_email'] ?? '');
$amount = filter_var(
    $data['amount'] ?? null,
    FILTER_VALIDATE_INT
);
$paymentMethod = $data['payment_method'] ?? '';

$allowedMethods = [
    'VISA',
    'Apple Pay',
    'Google Pay',
    'Internet Bank',
    'Mastercard'
];

$errors = [];

if ($donorName === '' || mb_strlen($donorName) > 60) {
    $errors['donor_name'] = 'Ievadiet derīgu vārdu.';
}

if (
    !filter_var($donorEmail, FILTER_VALIDATE_EMAIL) ||
    mb_strlen($donorEmail) > 40
) {
    $errors['donor_email'] = 'Ievadiet derīgu e-pasta adresi.';
}

if ($amount === false || $amount === null || $amount <= 0) {
    $errors['amount'] = 'Ziedojuma summai jābūt lielākai par 0.';
}

if (!in_array($paymentMethod, $allowedMethods, true)) {
    $errors['payment_method'] = 'Izvēlieties derīgu maksājuma veidu.';
}

if (!empty($errors)) {
    http_response_code(422);

    echo json_encode([
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO donations
            (donor_name, donor_email, amount, payment_method)
         VALUES
            (:donor_name, :donor_email, :amount, :payment_method)'
    );

    $stmt->execute([
        'donor_name' => $donorName,
        'donor_email' => $donorEmail,
        'amount' => $amount,
        'payment_method' => $paymentMethod
    ]);

    http_response_code(201);

    echo json_encode([
        'message' => 'Paldies par jūsu ziedojumu!'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'error' => 'Neizdevās saglabāt ziedojumu.'
    ], JSON_UNESCAPED_UNICODE);
}
