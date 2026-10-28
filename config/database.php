<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

$requestScript = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
if ($requestScript !== false && $requestScript === realpath(__FILE__)) {
    http_response_code(404);
    exit;
}

function isSecureRequest(): bool
{
    if (isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    // Shared hosts and reverse proxies may terminate HTTPS before PHP sees the request.
    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    if (is_string($forwardedProto) && strtolower(trim(explode(',', $forwardedProto)[0])) === 'https') {
        return true;
    }

    $configuredOrigin = getenv('APP_ORIGIN');
    if (!is_string($configuredOrigin) || $configuredOrigin === '') {
        return false;
    }

    $originParts = parse_url($configuredOrigin);
    return is_array($originParts) && strtolower($originParts['scheme'] ?? '') === 'https';
}

function getDatabaseConnection(): PDO
{
    $host = getenv('DB_HOST') ?: 'sql101.infinityfree.com';
    $name = getenv('DB_NAME') ?: 'if0_43288354_animal_shelter';
    $user = getenv('DB_USER') ?: 'if0_43288354';
    $port = getenv('DB_PORT') ?: '3306';
    $password = getenv('DB_PASS') ?: '200788Viktorija';

    if ($password === false || $password === '') {
        throw new PDOException('Database password is not configured.');
    }

    $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';

    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function isSameOriginRequest(): bool
{
    $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($requestOrigin === '') {
        $requestOrigin = $_SERVER['HTTP_REFERER'] ?? '';
    }
    if (!is_string($requestOrigin) || $requestOrigin === '') {
        return false;
    }

    $requestParts = parse_url($requestOrigin);
    if ($requestParts === false || empty($requestParts['host']) ||
        !in_array(strtolower($requestParts['scheme'] ?? ''), ['http', 'https'], true) ||
        isset($requestParts['user']) || isset($requestParts['pass'])) {
        return false;
    }

    $configuredOrigin = getenv('APP_ORIGIN');
    if (is_string($configuredOrigin) && $configuredOrigin !== '') {
        $expectedParts = parse_url($configuredOrigin);
    } else {
        $hostHeader = $_SERVER['HTTP_HOST'] ?? '';
        $secureRequest = isSecureRequest();
        if (!is_string($hostHeader) || $hostHeader === '') {
            return false;
        }
        $expectedParts = parse_url(($secureRequest ? 'https' : 'http') . '://' . $hostHeader);
    }

    if ($expectedParts === false || empty($expectedParts['host']) ||
        !in_array(strtolower($expectedParts['scheme'] ?? ''), ['http', 'https'], true) ||
        isset($expectedParts['user']) || isset($expectedParts['pass'])) {
        return false;
    }

    $requestScheme = strtolower($requestParts['scheme']);
    $expectedScheme = strtolower($expectedParts['scheme']);
    $requestPort = $requestParts['port'] ?? ($requestScheme === 'https' ? 443 : 80);
    $expectedPort = $expectedParts['port'] ?? ($expectedScheme === 'https' ? 443 : 80);

    return $requestScheme === $expectedScheme &&
        strcasecmp($requestParts['host'], $expectedParts['host']) === 0 &&
        $requestPort === $expectedPort;
}
