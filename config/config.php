<?php
// config/config.php
// Global configuration for database and base URL.

// Adjust these for your local XAMPP setup if needed.
define('DB_HOST', 'localhost');
define('DB_NAME', 'mama_fashion');
require_once __DIR__ . '/database_supabase.php'; // Make sure this matches your phpMyAdmin database name.
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP MySQL password is empty.

// Base URL for building links (no trailing slash).
// Your folder is "mama" in htdocs, so:
$baseUrl = 'http://localhost/mama';

// Start the session once, here, so all pages that include this file can use $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

