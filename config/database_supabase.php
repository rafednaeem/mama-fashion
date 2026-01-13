<?php
// config/database.php
// Updated for Supabase PostgreSQL connection

// Get environment variables from Vercel
$host = $_ENV['POSTGRES_HOST'] ?? 'localhost';
$database = $_ENV['POSTGRES_DATABASE'] ?? 'postgres';
$user = $_ENV['POSTGRES_USER'] ?? 'postgres';
$password = $_ENV['POSTGRES_PASSWORD'] ?? '';
$port = $_ENV['POSTGRES_PORT'] ?? '5432';

// Build connection string
$dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function getPDO(): PDO
{
    global $pdo;
    return $pdo;
}
