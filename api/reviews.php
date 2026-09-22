<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$pdo = get_db_connection();

if (!$pdo) {
    echo json_encode([]);
    exit;
}

// Create table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `reviews` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
            `reviewer_name` varchar(120) NOT NULL,
            `content` text NOT NULL,
            `rating` tinyint UNSIGNED NOT NULL DEFAULT '5',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (PDOException $e) {
    // Ignore if create fails due to permissions etc.
}

try {
    $stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC LIMIT 10");
    $reviews = $stmt->fetchAll();
    
    echo json_encode($reviews);
} catch (PDOException $e) {
    // Return empty array if error
    echo json_encode([]);
}
