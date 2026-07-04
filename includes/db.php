<?php
/**
 * Database Connection Configuration
 * 
 * Update these values to match your MySQL/MariaDB setup.
 * Create the database and tables by running:
 *   1. database/schema.sql
 *   2. database/seed.sql
 */

// Database credentials — update for your environment
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'isjm_donations');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection (singleton)
 * 
 * @return PDO
 * @throws PDOException
 */
function getDB(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    
    return $pdo;
}

/**
 * Test database connection
 * 
 * @return array ['success' => bool, 'message' => string]
 */
function testDBConnection(): array {
    try {
        $db = getDB();
        $db->query('SELECT 1');
        return ['success' => true, 'message' => 'Database connected successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
    }
}
