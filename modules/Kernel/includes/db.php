<?php
/**
 * Database Connection Configuration
 * 
 * Credentials are read from environment variables (loaded by vlucas/phpdotenv
 * via config.php). Because this file is loaded by composer autoload BEFORE
 * config.php loads .env, we read from $_ENV at connection time, not at include time.
 */

define('DB_CHARSET', 'utf8mb4');

/**
 * Helper: read a DB env var, checking $_ENV, $_SERVER, then getenv()
 */
function _dbEnv(string $key, string $default = ''): string {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

/**
 * Get PDO database connection (singleton)
 * 
 * @return PDO
 * @throws PDOException
 */
function getDB(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        // Read credentials at connection time so .env is loaded first
        $host = _dbEnv('DB_HOST', 'localhost');
        $name = _dbEnv('DB_NAME', 'isjm_donations');
        $user = _dbEnv('DB_USER', 'root');
        $pass = _dbEnv('DB_PASS', '');

        if (!defined('DB_HOST')) define('DB_HOST', $host);
        if (!defined('DB_NAME')) define('DB_NAME', $name);
        if (!defined('DB_USER')) define('DB_USER', $user);
        if (!defined('DB_PASS')) define('DB_PASS', $pass);

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $host, $name, DB_CHARSET
        );
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, $user, $pass, $options);
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
