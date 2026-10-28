<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$secureSessionCookie = isSecureRequest();
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secureSessionCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('Content-Type: application/json; charset=utf-8');

function readJsonBody(): array
{
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return $_POST;
}

function respond(int $status, array $result): void
{
    http_response_code($status);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

function textLength(string $value): int
{
    $length = preg_match_all('/./us', $value);
    return $length === false ? PHP_INT_MAX : $length;
}

function requiredText(array $data, string $field, int $maxLength): string
{
    $value = $data[$field] ?? null;
    if (!is_string($value)) {
        throw new InvalidArgumentException('Aizpildiet laukus korekti.');
    }

    $value = trim($value);
    if ($value === '' || textLength($value) > $maxLength) {
        throw new InvalidArgumentException('Aizpildiet laukus korekti.');
    }

    return $value;
}

function currentUserId(): int
{
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$userId || $userId < 1) {
        respond(401, ['success' => false, 'error' => 'Lai turpinātu, pieslēdzieties.']);
    }
    return $userId;
}

function validImageData(?string $image, int $maxLength): bool
{
    if ($image === null) {
        return true;
    }
    if (strlen($image) > $maxLength ||
        preg_match('/^data:image\/(?:jpeg|png|webp);base64,([A-Za-z0-9+\/]+=*)$/D', $image, $matches) !== 1) {
        return false;
    }
    $contents = base64_decode($matches[1], true);
    if ($contents === false || strlen($contents) > 2 * 1024 * 1024 ||
        !function_exists('getimagesizefromstring')) {
        return false;
    }
    $imageInfo = getimagesizefromstring($contents);
    return is_array($imageInfo) &&
        in_array($imageInfo['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp'], true);
}

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    $data = $method === 'POST' ? readJsonBody() : [];
    $action = $_GET['action'] ?? ($data['action'] ?? '');

    if ($method === 'GET' && $action === 'session') {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$userId || $userId < 1) {
            respond(200, ['success' => true, 'logged_in' => false]);
        }

        $pdo = getDatabaseConnection();
        $statement = $pdo->prepare(
            'SELECT first_name, last_name, role FROM users WHERE user_id = ?'
        );
        $statement->execute([$userId]);
        $user = $statement->fetch();
        if (!$user) {
            $_SESSION = [];
            session_destroy();
            respond(200, ['success' => true, 'logged_in' => false]);
        }

        respond(200, [
            'success' => true,
            'logged_in' => true,
            'user' => $user,
        ]);
    }

    if ($method === 'GET' && in_array($action, ['profile', 'favorites'], true)) {
        $userId = currentUserId();
        $pdo = getDatabaseConnection();
        if ($action === 'profile') {
            $statement = $pdo->prepare(
                'SELECT first_name AS firstName, last_name AS lastName, email,
                        birth_date AS birthDate, profile_description AS description,
                        profile_photo AS photo
                 FROM users WHERE user_id = ?'
            );
            $statement->execute([$userId]);
            $user = $statement->fetch();
            if (!$user) {
                respond(401, ['success' => false, 'error' => 'Lietotāja sesija vairs nav derīga.']);
            }
            respond(200, ['success' => true, 'user' => $user]);
        }

        $statement = $pdo->prepare('SELECT animal_id FROM favorites WHERE user_id = ? ORDER BY created_at DESC');
        $statement->execute([$userId]);
        respond(200, ['success' => true, 'favorites' => array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN))]);
    }

    if ($method !== 'POST') {
        respond(405, [
            'success' => false,
            'error' => 'Metode nav atļauta.'
        ]);
    }

    if (!isSameOriginRequest()) {
        respond(403, [
            'success' => false,
            'error' => 'Pieprasījums no šī avota nav atļauts.'
        ]);
    }

    if ($action === 'profile') {
        $userId = currentUserId();
        $firstName = requiredText($data, 'firstName', 30);
        $lastName = requiredText($data, 'lastName', 30);
        $email = strtolower(requiredText($data, 'email', 254));
        $description = $data['description'] ?? '';
        $birthDate = $data['birthDate'] ?? '';
        $photo = $data['photo'] ?? null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || textLength($email) > 254 ||
            !is_string($description) || textLength($description) > 500 ||
            !is_string($birthDate) || ($birthDate !== '' &&
            (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $birthDate) ||
            !checkdate((int) substr($birthDate, 5, 2), (int) substr($birthDate, 8, 2), (int) substr($birthDate, 0, 4)))) ||
            !is_bool($data['removePhoto'] ?? false) ||
            ($photo !== null && !is_string($photo)) ||
            !validImageData(is_string($photo) ? $photo : null, 2800000)) {
            throw new InvalidArgumentException('Pārbaudiet profila datus un attēlu.');
        }

        $pdo = getDatabaseConnection();
        $statement = $pdo->prepare('SELECT email FROM users WHERE user_id = ?');
        $statement->execute([$userId]);
        $currentEmail = $statement->fetchColumn();
        if (!$currentEmail) {
            respond(401, ['success' => false, 'error' => 'Lietotāja konts nav atrasts.']);
        }
        $statement = $pdo->prepare('SELECT user_id FROM users WHERE email = ? AND user_id <> ?');
        $statement->execute([$email, $userId]);
        if ($statement->fetch()) {
            respond(409, ['success' => false, 'error' => 'Šis e-pasts jau ir reģistrēts.']);
        }
        $statement = $pdo->prepare(
            'UPDATE users
             SET first_name = ?, last_name = ?, email = ?, birth_date = ?,
                 profile_description = ?,
                 profile_photo = CASE WHEN ? THEN NULL ELSE COALESCE(?, profile_photo) END
             WHERE user_id = ?'
        );
        $statement->execute([
            $firstName,
            $lastName,
            $email,
            $birthDate === '' ? null : $birthDate,
            $description,
            $data['removePhoto'] ? 1 : 0,
            $photo,
            $userId,
        ]);
        respond(200, ['success' => true, 'message' => 'Profils atjaunināts.']);
    }

    if ($action === 'favorite') {
        $userId = currentUserId();
        $animalId = filter_var($data['animal_id'] ?? null, FILTER_VALIDATE_INT);
        $favorite = $data['favorite'] ?? null;
        if ($animalId === false || $animalId < 1 || !is_bool($favorite)) {
            throw new InvalidArgumentException('Nederīgs favorīta pieprasījums.');
        }
        $pdo = getDatabaseConnection();
        if ($favorite) {
            $statement = $pdo->prepare('INSERT IGNORE INTO favorites (user_id, animal_id) SELECT ?, animal_id FROM animals WHERE animal_id = ?');
            $statement->execute([$userId, $animalId]);
            if ($statement->rowCount() === 0) {
                $check = $pdo->prepare('SELECT animal_id FROM animals WHERE animal_id = ?');
                $check->execute([$animalId]);
                if (!$check->fetch()) {
                    respond(404, ['success' => false, 'error' => 'Dzīvnieks nav atrasts.']);
                }
            }
        } else {
            $statement = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND animal_id = ?');
            $statement->execute([$userId, $animalId]);
        }
        respond(200, ['success' => true, 'favorite' => $favorite]);
    }

    if ($action === 'changePassword') {
        $userId = currentUserId();
        $currentPassword = $data['currentPassword'] ?? null;
        $newPassword = $data['newPassword'] ?? null;
        if (!is_string($currentPassword) || !is_string($newPassword) ||
            textLength($newPassword) < 8 || textLength($newPassword) > 1024 ||
            preg_match('/\p{L}/u', $newPassword) !== 1 ||
            preg_match('/\p{N}/u', $newPassword) !== 1) {
            throw new InvalidArgumentException('Parolei jābūt vismaz 8 rakstzīmes garai un jāsatur burti un cipari.');
        }
        $pdo = getDatabaseConnection();
        $statement = $pdo->prepare('SELECT password FROM users WHERE user_id = ?');
        $statement->execute([$userId]);
        $passwordHash = $statement->fetchColumn();
        if (!$passwordHash || !password_verify($currentPassword, $passwordHash)) {
            respond(401, ['success' => false, 'error' => 'Pašreizējā parole nav pareiza.']);
        }
        $statement = $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?');
        $statement->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
        respond(200, ['success' => true, 'message' => 'Parole nomainīta.']);
    }

    if ($action === '' && isset($data['registerEmail'])) {
        $action = 'register';
    }

    if ($action === '' && isset($data['loginEmail'])) {
        $action = 'login';
    }

    // REGISTRATION
    if ($action === 'register') {
        $firstName = requiredText($data, 'firstName', 30);
        $lastName = requiredText($data, 'lastName', 30);
        $email = strtolower(requiredText($data, 'email', 254));
        $password = $data['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'Nederīga e-pasta adrese.'
            );
        }

        if (!is_string($password) || textLength($password) < 8 || textLength($password) > 1024 ||
            preg_match('/\p{L}/u', $password) !== 1 ||
            preg_match('/\p{N}/u', $password) !== 1) {
            throw new InvalidArgumentException(
                'Parolei jābūt vismaz 8 rakstzīmes garai un jāsatur burti un cipari.'
            );
        }

        $pdo = getDatabaseConnection();
        $check = $pdo->prepare(
            'SELECT user_id FROM users WHERE email = ?'
        );
        $check->execute([$email]);

        if ($check->fetch()) {
            respond(409, [
                'success' => false,
                'error' => 'Šis e-pasts jau ir reģistrēts.'
            ]);
        }

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $statement = $pdo->prepare(
            'INSERT INTO users
            (first_name, last_name, email, password)
            VALUES (?, ?, ?, ?)'
        );

        $statement->execute([
            $firstName,
            $lastName,
            $email,
            $passwordHash
        ]);

        respond(201, [
            'success' => true,
            'message' => 'Reģistrācija veiksmīga.',
            'user_id' => (int) $pdo->lastInsertId()
        ]);
    }

    if ($action === 'login') {
        $identifier = strtolower(requiredText($data, 'email', 254));
        $password = $data['password'] ?? '';

        if ((!filter_var($identifier, FILTER_VALIDATE_EMAIL) && $identifier !== 'admin') || !is_string($password) ||
            $password === '' || textLength($password) > 1024) {
            throw new InvalidArgumentException('Ievadiet derīgu e-pastu vai administratora lietotājvārdu un paroli.');
        }

        $pdo = getDatabaseConnection();
        $statement = $pdo->prepare(
            'SELECT user_id AS id, first_name, last_name, email, password, role
             FROM users
             WHERE email = ?'
        );

        $statement->execute([$identifier]);
        $user = $statement->fetch();

        if (!$user && $identifier === 'admin') {
            $statement = $pdo->query(
                "SELECT user_id AS id, first_name, last_name, email, password, role
                 FROM users WHERE role = 'admin' AND first_name = 'Admin'
                 ORDER BY user_id ASC"
            );
            foreach ($statement->fetchAll() as $admin) {
                if (password_verify($password, $admin['password'])) {
                    $user = $admin;
                    break;
                }
            }
        }

        if (
            !$user ||
            !password_verify($password, $user['password'])
        ) {
            respond(401, [
                'success' => false,
                'error' => 'Nepareizs e-pasts vai parole.'
            ]);
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = $user['role'];

        unset($user['password']);

        respond(200, [
            'success' => true,
            'message' => 'Pieslēgšanās veiksmīga.',
            'user' => $user
        ]);
    }

    throw new InvalidArgumentException(
        'Nezināma darbība.'
    );

} catch (InvalidArgumentException $exception) {
    respond(400, [
        'success' => false,
        'error' => $exception->getMessage()
    ]);

} catch (PDOException $exception) {
    error_log($exception->getMessage());

    $driverCode = (int) ($exception->errorInfo[1] ?? 0);
    if ($driverCode === 1062) {
        respond(409, [
            'success' => false,
            'error' => 'Šis e-pasts jau ir reģistrēts.'
        ]);
    }

    if ($exception->getMessage() === 'Database password is not configured.') {
        respond(503, [
            'success' => false,
            'error' => 'Nav iestatīta MySQL parole. Pārbaudiet DB_PASS vai paroli config/database.php.'
        ]);
    }
    if (in_array($driverCode, [1044, 1045], true)) {
        respond(503, [
            'success' => false,
            'error' => 'MySQL noraidīja pieslēgumu. Pārbaudiet DB_HOST, DB_NAME, DB_USER un datubāzes paroli.'
        ]);
    }
    if ($driverCode === 1049) {
        respond(503, [
            'success' => false,
            'error' => 'Norādītā MySQL datubāze nav atrasta. Pārbaudiet DB_NAME.'
        ]);
    }
    if (in_array($driverCode, [2002, 2003], true)) {
        respond(503, [
            'success' => false,
            'error' => 'Neizdevās sasniegt MySQL serveri. Pārbaudiet DB_HOST un to, vai PHP vide var piekļūt datubāzei.'
        ]);
    }
    if ($driverCode === 1146 || ($exception->errorInfo[0] ?? '') === '42S02') {
        respond(503, [
            'success' => false,
            'error' => 'Datubāzē trūkst vajadzīgās tabulas. Importējiet Database/animal_shelter.sql pareizajā datubāzē.'
        ]);
    }

    respond(500, [
        'success' => false,
        'error' => 'Neizdevās apstrādāt pieprasījumu.'
    ]);
}
