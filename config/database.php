<?php
/**
 * Database connection (PDO / MySQL)
 * -----------------------------------------------------------------
 * Default values below match a fresh Laragon install:
 *   host     = 127.0.0.1
 *   port     = 3306
 *   user     = root
 *   password = "" (empty)
 *
 * If you changed your MySQL root password in Laragon, update DB_PASS.
 * See README.md for the full step-by-step Laragon setup guide.
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'crumb_and_cream');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Returns a PDO connection, or null if the database is unreachable.
 * The site is designed to keep working (minus the order form) even
 * if the database hasn't been set up yet, so we never let a DB
 * error take down the whole page.
 */
function get_db_connection(): ?PDO
{
    static $pdo = null;
    static $attempted = false;

    if ($pdo !== null) {
        return $pdo;
    }

    if ($attempted) {
        return null; // already failed once this request, don't retry
    }

    $attempted = true;

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Log to PHP's error log instead of exposing details to visitors.
        error_log('Database connection failed: ' . $e->getMessage());
        return null;
    }
}
