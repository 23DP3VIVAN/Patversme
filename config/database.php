<?php

declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'animal_shelter';
const DB_USER = 'root';
const DB_PASS = '';

function getDatabaseConnection(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_NAME
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed.']);
        exit;
    }
}

