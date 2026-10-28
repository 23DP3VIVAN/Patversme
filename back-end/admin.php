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

function respond(int $status, array $result): void
{
    http_response_code($status);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

function requestData(): array
{
    $input = json_decode(file_get_contents('php://input'), true);
    return is_array($input) ? $input : $_POST;
}

function requiredField(array $data, string $field, int $maxLength): string
{
    $value = $data[$field] ?? null;
    $valueLength = is_string($value) ? preg_match_all('/./us', trim($value)) : false;
    if (!is_string($value) || trim($value) === '' ||
        $valueLength === false || $valueLength > $maxLength) {
        throw new InvalidArgumentException('Pārbaudiet obligātos laukus.');
    }
    return trim($value);
}

function optionalDate(array $data, string $field, bool $required = false): ?string
{
    $value = $data[$field] ?? '';
    if ($value === '' && !$required) {
        return null;
    }
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) ||
        !checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4))) {
        throw new InvalidArgumentException('Ievadiet derīgu datumu.');
    }
    return $value;
}

function validImage(?string $image): bool
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

function validDateFilter(string $date): bool
{
    return preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) === 1 &&
        checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4));
}

$method = $_SERVER['REQUEST_METHOD'] ?? '';
$action = $_GET['action'] ?? '';
$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId || $userId < 1) {
    respond(401, ['success' => false, 'error' => 'Piesakieties administratora kontā.']);
}

try {
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('SELECT role FROM users WHERE user_id = ?');
    $statement->execute([$userId]);
    if ($statement->fetchColumn() !== 'admin') {
        respond(403, ['success' => false, 'error' => 'Administratora piekļuve ir liegta.']);
    }

    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && !isSameOriginRequest()) {
        respond(403, ['success' => false, 'error' => 'Pieprasījums no šī avota nav atļauts.']);
    }

    $data = requestData();
    if ($method === 'GET') {
        if ($action === 'animals') {
            $query = $_GET['q'] ?? '';
            $species = $_GET['species'] ?? '';
            $breed = $_GET['breed'] ?? '';
            $health = $_GET['health'] ?? '';
            $ageMin = $_GET['age_min'] ?? '';
            $ageMax = $_GET['age_max'] ?? '';
            $gender = $_GET['gender'] ?? '';
            $status = $_GET['status'] ?? '';
            $sort = $_GET['sort'] ?? 'newest';
            if (!is_string($query) || strlen($query) > 100 || !is_string($species) || strlen($species) > 30 ||
                !is_string($breed) || strlen($breed) > 50 || !is_string($health) || strlen($health) > 100 ||
                !is_string($ageMin) || ($ageMin !== '' && (filter_var($ageMin, FILTER_VALIDATE_INT) === false || (int) $ageMin < 0)) ||
                !is_string($ageMax) || ($ageMax !== '' && (filter_var($ageMax, FILTER_VALIDATE_INT) === false || (int) $ageMax < 0)) ||
                !in_array($gender, ['', 'male', 'female', 'unknown'], true) ||
                !in_array($status, ['', 'Pieejams', 'Rezervets', 'Adoptets'], true) ||
                ($ageMin !== '' && $ageMax !== '' && (int) $ageMin > (int) $ageMax) ||
                !in_array($sort, ['newest', 'oldest', 'name'], true)) {
                throw new InvalidArgumentException('Nederīgi dzīvnieku filtri.');
            }
            $where = [];
            $params = [];
            if ($query !== '') {
                if (ctype_digit($query)) {
                    $where[] = '(animal_id = :id OR name LIKE :q)';
                    $params[':id'] = (int) $query;
                    $params[':q'] = '%' . $query . '%';
                } else {
                    $where[] = 'name LIKE :q';
                    $params[':q'] = '%' . $query . '%';
                }
            }
            foreach (['species' => $species, 'breed' => $breed, 'health_status' => $health] as $column => $value) {
                if ($value !== '') {
                    if ($column === 'species' && $value === 'cits') {
                        $where[] = "species NOT IN ('suns', 'kakis')";
                        continue;
                    }
                    $where[] = $column . ' LIKE :' . $column;
                    $params[':' . $column] = '%' . $value . '%';
                }
            }
            if ($gender !== '') $where[] = $gender === 'unknown' ? 'gender IS NULL' : 'gender = :gender';
            if ($gender !== '' && $gender !== 'unknown') $params[':gender'] = $gender;
            if ($status !== '') {
                $where[] = 'adoption_status = :status';
                $params[':status'] = $status;
            }
            $ageExpression = 'COALESCE(age, TIMESTAMPDIFF(YEAR, birth_date, CURDATE()))';
            if ($ageMin !== '') { $where[] = $ageExpression . ' >= :age_min'; $params[':age_min'] = (int) $ageMin; }
            if ($ageMax !== '') { $where[] = $ageExpression . ' <= :age_max'; $params[':age_max'] = (int) $ageMax; }
            $order = $sort === 'name' ? 'name ASC, animal_id DESC' :
                ($sort === 'oldest' ? 'created_at ASC, animal_id ASC' : 'created_at DESC, animal_id DESC');
            $sql = 'SELECT animal_id AS id, name, species, breed, gender, health_status, created_at,
                        special_needs, age,
                        COALESCE(age, TIMESTAMPDIFF(YEAR, birth_date, CURDATE())) AS display_age,
                        birth_date, description, photo, arrival_date,
                        adoption_status AS status, owner_requirements
                    FROM animals' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $order;
            $statement = $pdo->prepare($sql);
            $statement->execute($params);
            respond(200, ['success' => true, 'animals' => $statement->fetchAll()]);
        }
        if ($action === 'adoptions') {
            $q = $_GET['q'] ?? '';
            $animal = $_GET['animal'] ?? '';
            $status = $_GET['status'] ?? '';
            $date = $_GET['date'] ?? '';
            if (!is_string($q) || strlen($q) > 100 || !is_string($animal) || strlen($animal) > 100 ||
                !in_array($status, ['', 'Gaida', 'Izskatīšanā', 'Apstiprināta', 'Pabeigta', 'Noraidīta', 'Atcelta'], true) ||
                !is_string($date) || ($date !== '' && !validDateFilter($date))) {
                throw new InvalidArgumentException('Nederīgi pieteikumu filtri.');
            }
            $where = [];
            $params = [];
            if ($q !== '') {
                $where[] = '(CAST(a.adoption_id AS CHAR) = :id OR CONCAT(u.first_name, " ", u.last_name) LIKE :applicant OR u.email LIKE :email OR an.name LIKE :animalname)';
                $params += [':id' => $q, ':applicant' => '%' . $q . '%', ':email' => '%' . $q . '%', ':animalname' => '%' . $q . '%'];
            }
            if ($animal !== '') {
                if (ctype_digit($animal)) {
                    $where[] = '(a.animal_id = :animal_id OR an.name LIKE :animal_search)';
                    $params[':animal_id'] = (int) $animal;
                    $params[':animal_search'] = '%' . $animal . '%';
                } else {
                    $where[] = 'an.name LIKE :animal_search';
                    $params[':animal_search'] = '%' . $animal . '%';
                }
            }
            if ($status !== '') { $where[] = 'a.status = :status'; $params[':status'] = $status; }
            if ($date !== '') { $where[] = 'a.adoption_date = :date'; $params[':date'] = $date; }
            $sql = "SELECT a.adoption_id AS id, a.animal_id, a.user_id, a.adoption_date AS date,
                        a.status, a.application_message, a.rejection_reason, an.name AS animal,
                        CONCAT(u.first_name, ' ', u.last_name) AS applicant, u.email,
                        (SELECT COUNT(*) FROM adoptions other
                         WHERE other.animal_id = a.animal_id AND other.adoption_id <> a.adoption_id
                           AND other.status IN ('Gaida', 'Izskatīšanā')) AS other_active_count
                 FROM adoptions a
                 JOIN animals an ON an.animal_id = a.animal_id
                 JOIN users u ON u.user_id = a.user_id
                " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY a.adoption_id DESC';
            $statement = $pdo->prepare($sql);
            $statement->execute($params);
            respond(200, ['success' => true, 'adoptions' => $statement->fetchAll()]);
        }
        if ($action === 'users') {
            $search = $_GET['q'] ?? '';
            if (!is_string($search) || strlen($search) > 100) {
                throw new InvalidArgumentException('Nederīgs lietotāja meklējums.');
            }
            $statement = $pdo->prepare(
                'SELECT user_id, first_name, last_name, email, role, created_at FROM users
                 WHERE (:empty = 1 OR user_id = :id OR first_name LIKE :q1 OR last_name LIKE :q2 OR email LIKE :q3)
                 ORDER BY user_id DESC LIMIT 200'
            );
            $statement->execute([
                ':empty' => $search === '' ? 1 : 0,
                ':id' => ctype_digit($search) ? (int) $search : 0,
                ':q1' => '%' . $search . '%', ':q2' => '%' . $search . '%', ':q3' => '%' . $search . '%',
            ]);
            $users = $statement->fetchAll();
            $count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            respond(200, ['success' => true, 'users' => $users, 'total' => (int) $count]);
        }
        if ($action === 'favorites') {
            $search = $_GET['q'] ?? '';
            if (!is_string($search) || strlen($search) > 100) {
                throw new InvalidArgumentException('Nederīgs favorītu meklējums.');
            }
            $statement = $pdo->prepare(
                'SELECT u.user_id, u.first_name, u.last_name, u.email,
                        a.animal_id, a.name AS animal_name, a.species, a.breed,
                        f.created_at
                 FROM favorites f
                 JOIN users u ON u.user_id = f.user_id
                 JOIN animals a ON a.animal_id = f.animal_id
                 WHERE (:empty = 1 OR CONCAT_WS(" ", u.first_name, u.last_name, u.email,
                                                   a.name, a.species, a.breed) LIKE :search)
                 ORDER BY f.created_at DESC, u.user_id DESC LIMIT 500'
            );
            $statement->execute([
                ':empty' => $search === '' ? 1 : 0,
                ':search' => '%' . $search . '%',
            ]);
            respond(200, ['success' => true, 'favorites' => $statement->fetchAll()]);
        }
        if ($action === 'volunteers') {
            $q = $_GET['q'] ?? '';
            $date = $_GET['date'] ?? '';
            $processed = $_GET['processed'] ?? '';
            if (!is_string($q) || strlen($q) > 100 || !is_string($date) ||
                ($date !== '' && !validDateFilter($date)) ||
                !in_array($processed, ['', '0', '1'], true)) {
                throw new InvalidArgumentException('Nederīgi brīvprātīgo pieteikumu filtri.');
            }
            $statement = $pdo->prepare(
                'SELECT id, first_name, last_name, email, phone, activity, message,
                        created_at, is_processed
                 FROM volunteer_applications
                 WHERE (:q = "" OR CONCAT_WS(" ", first_name, last_name, email, phone, activity) LIKE :search)
                   AND (:date = "" OR DATE(created_at) = :application_date)
                   AND (:processed = "" OR is_processed = :processed_value)
                 ORDER BY created_at DESC LIMIT 300'
            );
            $statement->execute([
                ':q' => $q,
                ':search' => '%' . $q . '%',
                ':date' => $date,
                ':application_date' => $date,
                ':processed' => $processed,
                ':processed_value' => (int) $processed,
            ]);
            respond(200, ['success' => true, 'applications' => $statement->fetchAll()]);
        }
        if ($action === 'messages') {
            $q = $_GET['q'] ?? '';
            $date = $_GET['date'] ?? '';
            $processed = $_GET['processed'] ?? '';
            if (!is_string($q) || strlen($q) > 100 || !is_string($date) ||
                ($date !== '' && !validDateFilter($date)) ||
                !in_array($processed, ['', '0', '1'], true)) {
                throw new InvalidArgumentException('Nederīgi ziņojumu filtri.');
            }
            $statement = $pdo->prepare(
                "SELECT * FROM (
                   SELECT id, 'contact' AS type, first_name, last_name, email,
                          topic AS subject, message, created_at, is_processed FROM contact_messages
                   UNION ALL
                   SELECT id, 'volunteer' AS type, first_name, last_name, email,
                          activity AS subject, message, created_at, is_processed FROM volunteer_applications
                 ) AS all_messages
                 WHERE (:q = '' OR CONCAT_WS(' ', first_name, last_name, email, subject) LIKE :search)
                   AND (:date = '' OR DATE(created_at) = :message_date)
                   AND (:processed = '' OR is_processed = :processed_value)
                 ORDER BY created_at DESC LIMIT 300"
            );
            $statement->execute([
                ':q' => $q, ':search' => '%' . $q . '%', ':date' => $date, ':message_date' => $date,
                ':processed' => $processed, ':processed_value' => (int) $processed,
            ]);
            respond(200, ['success' => true, 'messages' => $statement->fetchAll()]);
        }
        if ($action === 'donations') {
            $q = $_GET['q'] ?? '';
            $from = $_GET['from'] ?? '';
            $to = $_GET['to'] ?? '';
            $min = $_GET['min'] ?? '';
            $max = $_GET['max'] ?? '';
            $status = $_GET['status'] ?? '';
            if (!is_string($q) || strlen($q) > 100 || !is_string($from) || !is_string($to) ||
                ($from !== '' && !validDateFilter($from)) ||
                ($to !== '' && !validDateFilter($to)) ||
                ($from !== '' && $to !== '' && $from > $to) ||
                !is_string($min) || ($min !== '' && (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $min) || (float) $min < 0)) ||
                !is_string($max) || ($max !== '' && (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $max) || (float) $max < 0)) ||
                ($min !== '' && $max !== '' && (float) $min > (float) $max) ||
                !in_array($status, ['', 'not_processed', 'pending', 'succeeded', 'failed'], true)) {
                throw new InvalidArgumentException('Nederīgi ziedojumu filtri.');
            }
            $where = [];
            $params = [];
            if ($q !== '') {
                $where[] = '(CAST(donation_id AS CHAR) = :id OR donor_name LIKE :name OR donor_email LIKE :email)';
                $params += [':id' => $q, ':name' => '%' . $q . '%', ':email' => '%' . $q . '%'];
            }
            if ($from !== '') { $where[] = 'DATE(created_at) >= :from_date'; $params[':from_date'] = $from; }
            if ($to !== '') { $where[] = 'DATE(created_at) <= :to_date'; $params[':to_date'] = $to; }
            if ($min !== '') { $where[] = 'amount >= :min_amount'; $params[':min_amount'] = $min; }
            if ($max !== '') { $where[] = 'amount <= :max_amount'; $params[':max_amount'] = $max; }
            if ($status !== '') { $where[] = 'status = :status'; $params[':status'] = $status; }
            $sql = 'SELECT donation_id AS id, donor_name, donor_email, amount,
                        payment_method, status, created_at FROM donations ' .
                ($where ? 'WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY donation_id DESC';
            $statement = $pdo->prepare($sql);
            $statement->execute($params);
            $donations = $statement->fetchAll();
            $totalWhere = ["status = 'succeeded'"];
            $totalParams = [];
            if ($from !== '') { $totalWhere[] = 'DATE(created_at) >= :from_date'; $totalParams[':from_date'] = $from; }
            if ($to !== '') { $totalWhere[] = 'DATE(created_at) <= :to_date'; $totalParams[':to_date'] = $to; }
            $totalStatement = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM donations WHERE ' . implode(' AND ', $totalWhere));
            $totalStatement->execute($totalParams);
            $total = (float) $totalStatement->fetchColumn();
            respond(200, ['success' => true, 'donations' => $donations, 'total' => $total]);
        }
        if ($action === 'summary') {
            $summary = [
                'animals' => (int) $pdo->query('SELECT COUNT(*) FROM animals')->fetchColumn(),
                'available' => (int) $pdo->query("SELECT COUNT(*) FROM animals WHERE adoption_status = 'Pieejams'")->fetchColumn(),
                'applications' => (int) $pdo->query('SELECT COUNT(*) FROM adoptions')->fetchColumn(),
                'newApplications' => (int) $pdo->query("SELECT COUNT(*) FROM adoptions WHERE status = 'Gaida'")->fetchColumn(),
                'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
                'favorites' => (int) $pdo->query('SELECT COUNT(*) FROM favorites')->fetchColumn(),
                'donations' => (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'succeeded'")->fetchColumn(),
            ];
            respond(200, ['success' => true, 'summary' => $summary]);
        }
        if ($action === 'periodStats') {
            $from = $_GET['from'] ?? '';
            $to = $_GET['to'] ?? '';
            if (!is_string($from) || !is_string($to) ||
                !validDateFilter($from) || !validDateFilter($to) || $from > $to) {
                throw new InvalidArgumentException('Izvēlieties derīgu periodu.');
            }
            $statement = $pdo->prepare(
                "SELECT
                    (SELECT COUNT(*) FROM users
                     WHERE role = 'user' AND DATE(created_at) BETWEEN :users_from AND :users_to) AS registrations,
                    (SELECT COUNT(*) FROM adoptions
                     WHERE adoption_date BETWEEN :applications_from AND :applications_to) AS applications,
                    (SELECT COUNT(*) FROM adoptions
                     WHERE status = 'Pabeigta'
                       AND DATE(completed_at) BETWEEN :completed_from AND :completed_to) AS completed_adoptions,
                    (SELECT COALESCE(SUM(amount), 0) FROM donations
                     WHERE status = 'succeeded'
                       AND DATE(created_at) BETWEEN :donations_from AND :donations_to) AS donations"
            );
            $statement->execute([
                ':users_from' => $from,
                ':users_to' => $to,
                ':applications_from' => $from,
                ':applications_to' => $to,
                ':completed_from' => $from,
                ':completed_to' => $to,
                ':donations_from' => $from,
                ':donations_to' => $to,
            ]);
            $stats = $statement->fetch();
            respond(200, [
                'success' => true,
                'stats' => [
                    'registrations' => (int) $stats['registrations'],
                    'applications' => (int) $stats['applications'],
                    'completedAdoptions' => (int) $stats['completed_adoptions'],
                    'donations' => (float) $stats['donations'],
                ],
            ]);
        }
        respond(400, ['success' => false, 'error' => 'Neatbalstīta darbība.']);
    }

    if ($method === 'POST' && $action === 'animals') {
        $photo = $data['photo'] ?? null;
        if (!is_string($photo) || !validImage($photo)) {
            throw new InvalidArgumentException('Pievienojot dzīvnieku, augšupielādējiet derīgu JPG, PNG vai WEBP fotogrāfiju līdz 2 MB.');
        }
        $animal = validateAnimal($data);
        $statement = $pdo->prepare(
            'INSERT INTO animals
             (name, species, breed, gender, health_status, special_needs, age, birth_date,
              description, photo, arrival_date, adoption_status, owner_requirements)
             VALUES (:name, :species, :breed, :gender, :health, :special, :age, :birth,
                     :description, :photo, :arrival, :status, :requirements)'
        );
        $statement->execute($animal);
        respond(201, ['success' => true, 'id' => (int) $pdo->lastInsertId()]);
    }

    if ($method === 'PUT' && $action === 'animals') {
        $animalId = filter_var($data['animal_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$animalId || $animalId < 1) {
            throw new InvalidArgumentException('Nederīgs dzīvnieka numurs.');
        }
        $animal = validateAnimal($data);
        $pdo->beginTransaction();
        $lock = $pdo->prepare('SELECT animal_id FROM animals WHERE animal_id = ? FOR UPDATE');
        $lock->execute([$animalId]);
        if (!$lock->fetch()) {
            $pdo->rollBack();
            respond(404, ['success' => false, 'error' => 'Dzīvnieks nav atrasts.']);
        }
        $statusCheck = $pdo->prepare(
            "SELECT
                SUM(status = 'Pabeigta') AS completed,
                SUM(status IN ('Gaida', 'Izskatīšanā', 'Apstiprināta')) AS pending
             FROM adoptions WHERE animal_id = ?"
        );
        $statusCheck->execute([$animalId]);
        $activeApplications = $statusCheck->fetch();
        $requiredStatus = (int) $activeApplications['completed'] > 0 ? 'Adoptets' :
            ((int) $activeApplications['pending'] > 0 ? 'Rezervets' : null);
        if ($requiredStatus !== null && $animal[':status'] !== $requiredStatus) {
            $pdo->rollBack();
            throw new InvalidArgumentException('Dzīvnieka statusam jāatbilst aktīvajiem adopcijas pieteikumiem.');
        }
        $animal[':id'] = $animalId;
        $statement = $pdo->prepare(
            'UPDATE animals SET name=:name, species=:species, breed=:breed, gender=:gender,
             health_status=:health, special_needs=:special, age=:age, birth_date=:birth,
             description=:description, photo=:photo, arrival_date=:arrival,
             adoption_status=:status, owner_requirements=:requirements WHERE animal_id=:id'
        );
        $statement->execute($animal);
        $pdo->commit();
        respond(200, ['success' => true, 'message' => 'Dzīvnieka dati atjaunināti.']);
    }

    if ($method === 'DELETE' && $action === 'animals') {
        $animalId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$animalId || $animalId < 1) {
            throw new InvalidArgumentException('Nederīgs dzīvnieka numurs.');
        }
        $statement = $pdo->prepare("DELETE FROM animals WHERE animal_id = ? AND adoption_status <> 'Adoptets'");
        $statement->execute([$animalId]);
        if ($statement->rowCount() === 0) {
            $statement = $pdo->prepare('SELECT adoption_status FROM animals WHERE animal_id = ?');
            $statement->execute([$animalId]);
            $status = $statement->fetchColumn();
            if ($status === 'Adoptets') {
                respond(409, ['success' => false, 'error' => 'Adoptētu dzīvnieku nevar dzēst; atstājiet ierakstu vēsturei.']);
            }
            respond(404, ['success' => false, 'error' => 'Dzīvnieks nav atrasts.']);
        }
        respond(200, ['success' => true]);
    }

    if ($method === 'PATCH' && $action === 'adoptions') {
        $adoptionId = filter_var($data['adoption_id'] ?? null, FILTER_VALIDATE_INT);
        $newStatus = $data['status'] ?? null;
        if (!$adoptionId || $adoptionId < 1 ||
            !is_string($newStatus) ||
            !in_array($newStatus, ['Izskatīšanā', 'Apstiprināta', 'Pabeigta', 'Noraidīta'], true)) {
            throw new InvalidArgumentException('Nederīgs pieteikuma numurs vai statuss.');
        }
        $rejectionReason = $data['rejection_reason'] ?? '';
        if (!is_string($rejectionReason) || preg_match_all('/./us', trim($rejectionReason)) > 2000) {
            throw new InvalidArgumentException('Atteikuma iemesls ir pārāk garš.');
        }

        $pdo->beginTransaction();
        $statement = $pdo->prepare('SELECT animal_id FROM adoptions WHERE adoption_id = ?');
        $statement->execute([$adoptionId]);
        $animalId = $statement->fetchColumn();
        if (!$animalId) {
            $pdo->rollBack();
            respond(404, ['success' => false, 'error' => 'Pieteikums nav atrasts.']);
        }
        $statement = $pdo->prepare('SELECT adoption_status FROM animals WHERE animal_id = ? FOR UPDATE');
        $statement->execute([$animalId]);
        $lockedAnimalStatus = $statement->fetchColumn();
        if ($lockedAnimalStatus === false) {
            $pdo->rollBack();
            respond(404, ['success' => false, 'error' => 'Dzīvnieks nav atrasts.']);
        }

        $statement = $pdo->prepare('SELECT status FROM adoptions WHERE adoption_id = ? FOR UPDATE');
        $statement->execute([$adoptionId]);
        $currentStatus = $statement->fetchColumn();
        if ($currentStatus === false) {
            $pdo->rollBack();
            respond(404, ['success' => false, 'error' => 'Pieteikums nav atrasts.']);
        }
        $validTransition =
            (in_array($currentStatus, ['Gaida', 'Izskatīšanā'], true) &&
                in_array($newStatus, ['Izskatīšanā', 'Apstiprināta', 'Noraidīta'], true) &&
                !($newStatus === 'Izskatīšanā' && $currentStatus !== 'Gaida')) ||
            ($currentStatus === 'Apstiprināta' && $newStatus === 'Pabeigta');
        if (!$validTransition) {
            $pdo->rollBack();
            respond(409, ['success' => false, 'error' => 'Pieteikuma statusu vairs nevar mainīt uz šo vērtību.']);
        }

        if ($newStatus === 'Apstiprināta' && $lockedAnimalStatus === 'Adoptets') {
            $pdo->rollBack();
            respond(409, ['success' => false, 'error' => 'Šis dzīvnieks jau ir atzīmēts kā adoptēts.']);
        }

        if ($newStatus === 'Apstiprināta') {
            $statement = $pdo->prepare(
                "SELECT COUNT(*) FROM adoptions
                 WHERE animal_id = ? AND status IN ('Apstiprināta', 'Pabeigta') AND adoption_id <> ?"
            );
            $statement->execute([$animalId, $adoptionId]);
            if ((int) $statement->fetchColumn() > 0) {
                $pdo->rollBack();
                respond(409, ['success' => false, 'error' => 'Šim dzīvniekam jau ir apstiprināts pieteikums.']);
            }
            $statement = $pdo->prepare(
                "UPDATE adoptions
                 SET status = 'Noraidīta',
                     rejection_reason = 'Dzīvnieks apstiprināts citam pieteicējam.'
                 WHERE animal_id = ? AND adoption_id <> ?
                   AND status IN ('Gaida', 'Izskatīšanā')"
            );
            $statement->execute([$animalId, $adoptionId]);
        }
        if ($newStatus === 'Pabeigta') {
            $statement = $pdo->prepare(
                "SELECT COUNT(*) FROM adoptions
                 WHERE animal_id = ? AND adoption_id <> ?
                   AND status IN ('Gaida', 'Izskatīšanā', 'Apstiprināta')"
            );
            $statement->execute([$animalId, $adoptionId]);
            if ((int) $statement->fetchColumn() > 0) {
                $pdo->rollBack();
                respond(409, ['success' => false, 'error' => 'Pirms pabeigšanas jāatrisina pārējie aktīvie pieteikumi.']);
            }
        }
        $statement = $pdo->prepare(
            'UPDATE adoptions
             SET status = ?, rejection_reason = ?,
                 completed_at = CASE WHEN ? = \'Pabeigta\' THEN CURRENT_TIMESTAMP ELSE NULL END
             WHERE adoption_id = ?'
        );
        $statement->execute([
            $newStatus,
            $newStatus === 'Noraidīta' && trim($rejectionReason) !== '' ? trim($rejectionReason) : null,
            $newStatus,
            $adoptionId,
        ]);

        $statement = $pdo->prepare(
            "SELECT
                SUM(status = 'Pabeigta') AS completed,
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
        respond(200, ['success' => true, 'message' => 'Pieteikuma statuss atjaunināts.']);
    }

    if ($method === 'PATCH' && $action === 'messages') {
        $messageId = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        $type = $data['type'] ?? null;
        $processed = $data['is_processed'] ?? null;
        if (!$messageId || $messageId < 1 || !in_array($type, ['contact', 'volunteer'], true) || !is_bool($processed)) {
            throw new InvalidArgumentException('Nederīgs ziņojuma atjauninājums.');
        }
        $table = $type === 'contact' ? 'contact_messages' : 'volunteer_applications';
        $statement = $pdo->prepare("UPDATE {$table} SET is_processed = ? WHERE id = ?");
        $statement->execute([$processed ? 1 : 0, $messageId]);
        if ($statement->rowCount() === 0) {
            $statement = $pdo->prepare("SELECT id FROM {$table} WHERE id = ?");
            $statement->execute([$messageId]);
            if (!$statement->fetch()) respond(404, ['success' => false, 'error' => 'Ziņojums nav atrasts.']);
        }
        respond(200, ['success' => true]);
    }

    respond(405, ['success' => false, 'error' => 'Metode vai darbība nav atbalstīta.']);
} catch (InvalidArgumentException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    respond(400, ['success' => false, 'error' => $exception->getMessage()]);
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    if (($exception->errorInfo[1] ?? null) === 1451) {
        respond(409, ['success' => false, 'error' => 'Dzīvniekam ir saistīti adopcijas pieteikumi, tāpēc to nevar dzēst.']);
    }
    respond(500, ['success' => false, 'error' => 'Neizdevās apstrādāt administratora pieprasījumu.']);
}

function validateAnimal(array $data): array
{
    $name = requiredField($data, 'name', 30);
    $species = requiredField($data, 'species', 30);
    if ($species === '') {
        throw new InvalidArgumentException('Izvēlieties derīgu dzīvnieka sugu.');
    }
    $breed = requiredField($data, 'breed', 50);
    $gender = $data['gender'] ?? '';
    if (!is_string($gender) || !in_array($gender, ['', 'male', 'female'], true)) {
        throw new InvalidArgumentException('Izvēlieties derīgu dzimumu.');
    }
    $health = requiredField($data, 'health_status', 100);
    $description = requiredField($data, 'description', 16000);
    $requirements = requiredField($data, 'owner_requirements', 16000);
    $photo = $data['photo'] ?? 'src/img/logo.jpg';
    $isDataImage = is_string($photo) && preg_match('/^data:image\/(?:jpeg|png|webp);base64,/', $photo) === 1;
    $isWebUrl = is_string($photo) && filter_var($photo, FILTER_VALIDATE_URL) &&
        in_array(strtolower((string) parse_url($photo, PHP_URL_SCHEME)), ['http', 'https'], true);
    $isRelativePath = is_string($photo) &&
        preg_match('/^(?!.*(?:^|\/)\.\.(?:\/|$))[A-Za-z0-9_.\/-]+$/D', $photo) === 1;
    if (!is_string($photo) || strlen($photo) > 2800000 ||
        (!$isDataImage && !$isWebUrl && !$isRelativePath) ||
        ($isDataImage && !validImage($photo))) {
        throw new InvalidArgumentException('Izvēlieties derīgu attēlu vai attēla saiti.');
    }
    $birthDate = optionalDate($data, 'birth_date');
    $arrivalDate = optionalDate($data, 'arrival_date', true);
    $age = $data['age'] ?? '';
    if ($age === '' || $age === null) {
        $age = null;
    } else {
        $age = filter_var($age, FILTER_VALIDATE_INT);
        if ($age === false || $age < 0 || $age > 150) {
            throw new InvalidArgumentException('Vecumam jābūt no 0 līdz 150 gadiem.');
        }
    }
    if ($birthDate === null && $age === null) {
        throw new InvalidArgumentException('Norādiet dzimšanas datumu vai vecumu.');
    }
    $status = $data['status'] ?? null;
    if (!is_string($status) || !in_array($status, ['Pieejams', 'Rezervets', 'Adoptets'], true)) {
        throw new InvalidArgumentException('Izvēlieties derīgu adopcijas statusu.');
    }
    return [
        ':name' => $name,
        ':species' => $species,
        ':breed' => $breed,
        ':gender' => $gender === '' ? null : $gender,
        ':health' => $health,
        ':special' => !empty($data['special_needs']) ? 1 : 0,
        ':age' => $age,
        ':birth' => $birthDate,
        ':description' => $description,
        ':photo' => $photo,
        ':arrival' => $arrivalDate,
        ':status' => $status,
        ':requirements' => $requirements,
    ];
}
