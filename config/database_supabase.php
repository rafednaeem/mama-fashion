<?php
// config/database.php
// Updated for Supabase PostgreSQL connection

// Use the full connection string from Supabase
$connectionString = $_ENV['POSTGRES_URL'] ?? 
    "postgres://postgres.eydancfwhwwdygpeivol:uNN1hJ4y4GjFbTxK@aws-1-us-east-1.pooler.supabase.com:6543/postgres?sslmode=require";

try {
    $pdo = new PDO($connectionString);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function getPDO(): PDO
{
    global $pdo;
    return $pdo;
}
