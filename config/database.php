<?php
declare(strict_types=1);

define('DB_HOST', 'sql101.infinityfree.com');
define('DB_NAME', 'if0_43288354_animal_shelter');
define('DB_USER', 'if0_43288354');
define('DB_PASS', '200788vika');

function isSecureRequest(): bool
{
    if (isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
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
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

/**
 * Check browser-supplied request origin for cookie-authenticated mutations.
 * Set APP_ORIGIN (scheme and host, with optional port) when the app is behind
 * a TLS-terminating proxy so the public origin can be validated correctly.
 */
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
        $secureRequest = isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
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
