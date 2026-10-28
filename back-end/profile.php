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
header('Cache-Control: no-store');

function profileRespond(int $status, array $result): void
{
    http_response_code($status);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

function profileRequestData(): array
{
    $input = json_decode(file_get_contents('php://input'), true);
    return is_array($input) ? $input : $_POST;
}

function profileUserId(): int
{
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$userId || $userId < 1) {
        profileRespond(401, ['success' => false, 'error' => 'Lai turpinātu, pieslēdzieties.']);
    }
    return $userId;
}

function profileText(array $data, string $key, int $maxLength): string
{
    $value = $data[$key] ?? null;
    $length = is_string($value) ? preg_match_all('/./us', trim($value)) : false;
    if (!is_string($value) || trim($value) === '' || $length === false || $length > $maxLength) {
        throw new InvalidArgumentException('Pārbaudiet ievadītos datus.');
    }
    return trim($value);
}

function validProfileImage(?string $image): bool
{
    if ($image === null) {
        return true;
    }
    if (strlen($image) > 2800000 ||
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
    $data = in_array($method, ['POST', 'DELETE'], true) ? profileRequestData() : [];
    $action = $_GET['action'] ?? ($data['action'] ?? '');

    if ($method === 'GET') {
        $userId = profileUserId();
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
                profileRespond(401, ['success' => false, 'error' => 'Lietotāja konts nav atrasts.']);
            }
            profileRespond(200, ['success' => true, 'user' => $user]);
        }

        if ($action === 'adoptions') {
            $statement = $pdo->prepare(
                'SELECT a.adoption_id, a.animal_id, a.adoption_date, a.status,
                        a.application_message, a.rejection_reason, an.name AS animal_name,
                        an.species, an.breed, an.age, an.photo, an.adoption_status AS animal_status
                 FROM adoptions a
                 JOIN animals an ON an.animal_id = a.animal_id
                 WHERE a.user_id = ?
                 ORDER BY a.adoption_id DESC'
            );
            $statement->execute([$userId]);
            profileRespond(200, ['success' => true, 'adoptions' => $statement->fetchAll()]);
        }

        if ($action === 'favorites') {
            $statement = $pdo->prepare(
                'SELECT an.animal_id, an.name, an.species, an.breed, an.age, an.photo,
                        an.adoption_status, f.created_at AS saved_at
                 FROM favorites f
                 JOIN animals an ON an.animal_id = f.animal_id
                 WHERE f.user_id = ?
                 ORDER BY f.created_at DESC'
            );
            $statement->execute([$userId]);
            profileRespond(200, ['success' => true, 'favorites' => $statement->fetchAll()]);
        }

        if ($action === 'donations') {
            $statement = $pdo->prepare(
                'SELECT amount, created_at, payment_method, status
                 FROM donations WHERE user_id = ?
                 ORDER BY created_at DESC'
            );
            $statement->execute([$userId]);
            profileRespond(200, ['success' => true, 'donations' => $statement->fetchAll()]);
        }

        if ($action === 'volunteerApplications') {
            $statement = $pdo->prepare(
                'SELECT id, activity, message, created_at, is_processed
                 FROM volunteer_applications
                 WHERE user_id = ?
                 ORDER BY created_at DESC, id DESC'
            );
            $statement->execute([$userId]);
            profileRespond(200, ['success' => true, 'applications' => $statement->fetchAll()]);
        }

        profileRespond(400, ['success' => false, 'error' => 'Neatbalstīta darbība.']);
    }

    if (!in_array($method, ['POST', 'DELETE'], true)) {
        header('Allow: GET, POST, DELETE');
        profileRespond(405, ['success' => false, 'error' => 'Metode nav atļauta.']);
    }
    if (!isSameOriginRequest()) {
        profileRespond(403, ['success' => false, 'error' => 'Pieprasījums no šī avota nav atļauts.']);
    }

    $userId = profileUserId();
    $pdo = getDatabaseConnection();

    if ($method === 'POST' && $action === 'profile') {
        $firstName = profileText($data, 'firstName', 30);
        $lastName = profileText($data, 'lastName', 30);
        $email = strtolower(profileText($data, 'email', 254));
        $description = $data['description'] ?? '';
        $birthDate = $data['birthDate'] ?? '';
        $photo = $data['photo'] ?? null;
        $removePhoto = $data['removePhoto'] ?? false;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
            !is_string($description) || preg_match_all('/./us', $description) > 500 ||
            !is_string($birthDate) ||
            ($birthDate !== '' && (
                !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $birthDate) ||
                !checkdate((int) substr($birthDate, 5, 2), (int) substr($birthDate, 8, 2), (int) substr($birthDate, 0, 4))
            )) ||
            !is_bool($removePhoto) || ($photo !== null && !is_string($photo)) ||
            !validProfileImage(is_string($photo) ? $photo : null)) {
            throw new InvalidArgumentException('Pārbaudiet profila datus un attēlu.');
        }

        $statement = $pdo->prepare('SELECT user_id FROM users WHERE email = ? AND user_id <> ?');
        $statement->execute([$email, $userId]);
        if ($statement->fetch()) {
            profileRespond(409, ['success' => false, 'error' => 'Šis e-pasts jau ir reģistrēts.']);
        }

        $statement = $pdo->prepare(
            'UPDATE users
             SET first_name = ?, last_name = ?, email = ?, birth_date = ?, profile_description = ?,
                 profile_photo = CASE WHEN ? THEN NULL ELSE COALESCE(?, profile_photo) END
             WHERE user_id = ?'
        );
        $statement->execute([
            $firstName,
            $lastName,
            $email,
            $birthDate === '' ? null : $birthDate,
            $description,
            $removePhoto ? 1 : 0,
            $photo,
            $userId,
        ]);
        profileRespond(200, ['success' => true, 'message' => 'Profila dati saglabāti.']);
    }

    if ($method === 'POST' && $action === 'changePassword') {
        $currentPassword = $data['currentPassword'] ?? null;
        $newPassword = $data['newPassword'] ?? null;
        if (!is_string($currentPassword) || !is_string($newPassword) ||
            strlen($newPassword) > 1024 ||
            preg_match('/^(?=.*\p{Ll})(?=.*\p{Lu})(?=.*\p{N})(?=.*[^\p{L}\p{N}\s])\S{15,}$/uD', $newPassword) !== 1) {
            throw new InvalidArgumentException('Jaunajai parolei jāatbilst drošības prasībām.');
        }
        $statement = $pdo->prepare('SELECT password FROM users WHERE user_id = ?');
        $statement->execute([$userId]);
        $passwordHash = $statement->fetchColumn();
        if (!$passwordHash || !password_verify($currentPassword, $passwordHash)) {
            profileRespond(401, ['success' => false, 'error' => 'Pašreizējā parole nav pareiza.']);
        }
        $statement = $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?');
        $statement->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
        profileRespond(200, ['success' => true, 'message' => 'Parole nomainīta.']);
    }

    if ($method === 'POST' && $action === 'favorite') {
        $animalId = filter_var($data['animal_id'] ?? null, FILTER_VALIDATE_INT);
        $favorite = $data['favorite'] ?? null;
        if (!$animalId || $animalId < 1 || !is_bool($favorite)) {
            throw new InvalidArgumentException('Nederīgs favorīta pieprasījums.');
        }
        if ($favorite) {
            $statement = $pdo->prepare(
                'INSERT IGNORE INTO favorites (user_id, animal_id)
                 SELECT ?, animal_id FROM animals WHERE animal_id = ?'
            );
            $statement->execute([$userId, $animalId]);
            if ($statement->rowCount() === 0) {
                $statement = $pdo->prepare('SELECT animal_id FROM animals WHERE animal_id = ?');
                $statement->execute([$animalId]);
                if (!$statement->fetch()) {
                    profileRespond(404, ['success' => false, 'error' => 'Dzīvnieks nav atrasts.']);
                }
            }
        } else {
            $statement = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND animal_id = ?');
            $statement->execute([$userId, $animalId]);
        }
        profileRespond(200, ['success' => true, 'favorite' => $favorite]);
    }

    if ($method === 'DELETE' && $action === 'adoptions') {
        $adoptionId = filter_var($data['adoption_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$adoptionId || $adoptionId < 1) {
            throw new InvalidArgumentException('Nederīgs pieteikuma numurs.');
        }
        $pdo->beginTransaction();
        $statement = $pdo->prepare(
            'SELECT animal_id FROM adoptions
             WHERE adoption_id = ? AND user_id = ?'
        );
        $statement->execute([$adoptionId, $userId]);
        $animalId = $statement->fetchColumn();
        if (!$animalId) {
            $pdo->rollBack();
            profileRespond(404, ['success' => false, 'error' => 'Pieteikums nav atrasts.']);
        }
        $statement = $pdo->prepare('SELECT animal_id FROM animals WHERE animal_id = ? FOR UPDATE');
        $statement->execute([$animalId]);
        if (!$statement->fetch()) {
            $pdo->rollBack();
            profileRespond(404, ['success' => false, 'error' => 'Dzīvnieks vairs nav pieejams.']);
        }
        $statement = $pdo->prepare(
            'SELECT status FROM adoptions
             WHERE adoption_id = ? AND user_id = ? FOR UPDATE'
        );
        $statement->execute([$adoptionId, $userId]);
        $applicationStatus = $statement->fetchColumn();
        if (!in_array($applicationStatus, ['Gaida', 'Izskatīšanā'], true)) {
            $pdo->rollBack();
            profileRespond(409, ['success' => false, 'error' => 'Šo pieteikumu vairs nevar atcelt.']);
        }
        $statement = $pdo->prepare("UPDATE adoptions SET status = 'Atcelta' WHERE adoption_id = ?");
        $statement->execute([$adoptionId]);
        $statement = $pdo->prepare(
            "SELECT SUM(status = 'Pabeigta') AS completed,
                    SUM(status IN ('Gaida', 'Izskatīšanā', 'Apstiprināta')) AS pending
             FROM adoptions WHERE animal_id = ?"
        );
        $statement->execute([$animalId]);
        $counts = $statement->fetch();
        $animalStatus = (int) $counts['completed'] > 0 ? 'Adoptets' :
            ((int) $counts['pending'] > 0 ? 'Rezervets' : 'Pieejams');
        $statement = $pdo->prepare('UPDATE animals SET adoption_status = ? WHERE animal_id = ?');
        $statement->execute([$animalStatus, $animalId]);
        $pdo->commit();
        profileRespond(200, ['success' => true, 'message' => 'Pieteikums atcelts.']);
    }

    if ($method === 'DELETE' && $action === 'account') {
        $currentPassword = $data['currentPassword'] ?? null;
        if (!is_string($currentPassword) || $currentPassword === '') {
            throw new InvalidArgumentException('Ievadiet konta paroli.');
        }
        $statement = $pdo->prepare('SELECT password, role FROM users WHERE user_id = ?');
        $statement->execute([$userId]);
        $user = $statement->fetch();
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            profileRespond(401, ['success' => false, 'error' => 'Parole nav pareiza.']);
        }
        if ($user['role'] === 'admin') {
            profileRespond(403, ['success' => false, 'error' => 'Administratora kontu nevar dzēst šajā lapā.']);
        }
        $pdo->beginTransaction();
        $statement = $pdo->prepare('SELECT DISTINCT animal_id FROM adoptions WHERE user_id = ?');
        $statement->execute([$userId]);
        $affectedAnimalIds = array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
        $statement = $pdo->prepare('DELETE FROM adoptions WHERE user_id = ?');
        $statement->execute([$userId]);
        $statement = $pdo->prepare('DELETE FROM favorites WHERE user_id = ?');
        $statement->execute([$userId]);
        $statement = $pdo->prepare(
            "UPDATE donations SET user_id = NULL, donor_name = 'Deleted user',
             donor_email = CONCAT('deleted+', donation_id, '@invalid.example') WHERE user_id = ?"
        );
        $statement->execute([$userId]);
        $statement = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
        $statement->execute([$userId]);
        foreach ($affectedAnimalIds as $animalId) {
            $statement = $pdo->prepare(
                "SELECT SUM(status = 'Pabeigta') AS completed,
                        SUM(status IN ('Gaida', 'Izskatīšanā', 'Apstiprināta')) AS pending
                 FROM adoptions WHERE animal_id = ?"
            );
            $statement->execute([$animalId]);
            $counts = $statement->fetch();
            $statement = $pdo->prepare('SELECT adoption_status FROM animals WHERE animal_id = ?');
            $statement->execute([$animalId]);
            $currentStatus = $statement->fetchColumn();
            $animalStatus = (int) $counts['completed'] > 0 || $currentStatus === 'Adoptets'
                ? 'Adoptets'
                : ((int) $counts['pending'] > 0 ? 'Rezervets' : 'Pieejams');
            $statement = $pdo->prepare('UPDATE animals SET adoption_status = ? WHERE animal_id = ?');
            $statement->execute([$animalStatus, $animalId]);
        }
        $pdo->commit();
        $_SESSION = [];
        session_destroy();
        profileRespond(200, ['success' => true, 'message' => 'Konts dzēsts.']);
    }

    profileRespond(400, ['success' => false, 'error' => 'Neatbalstīta darbība.']);
} catch (InvalidArgumentException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    profileRespond(400, ['success' => false, 'error' => $exception->getMessage()]);
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    if (($exception->errorInfo[1] ?? null) === 1062) {
        profileRespond(409, ['success' => false, 'error' => 'Šis e-pasts jau ir reģistrēts.']);
    }
    profileRespond(500, ['success' => false, 'error' => 'Neizdevās apstrādāt profila pieprasījumu.']);
}
