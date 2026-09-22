<?php
require_once __DIR__ . '/config/database.php';

echo "<h1>Database Connection Test</h1>";

$pdo = get_db_connection();

if ($pdo) {
    echo "<p style='color: green;'>✅ Successfully connected to Supabase!</p>";
    
    // Let's also check if the tables exist
    try {
        $stmt = $pdo->query("SELECT count(*) FROM orders");
        echo "<p style='color: green;'>✅ Orders table exists.</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Connected to Supabase, but the 'orders' table doesn't exist yet. Make sure you ran the SQL from database/schema.sql in Supabase.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Failed to connect.</p>";
    echo "<p>Please check your config/database.php file and ensure you entered the correct password.</p>";
    echo "<p>Also make sure the <code>pdo_pgsql</code> extension is enabled in Laragon (Right click Laragon -> PHP -> Quick Settings).</p>";
}
