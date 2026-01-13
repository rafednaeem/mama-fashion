<?php
// config/database.php
// Creates a reusable PDO database connection.

require_once __DIR__ . '/config.php';

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            // In a real production app, log this instead of echoing.
            die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
        }
    }

    return $pdo;
}

