<?php
// Load environment variables (if present)
require_once __DIR__ . '/../load_env.php';

$host = getenv('DB_HOST') ?: 'mysql.infuseting.fr';
$dbname = getenv('DB_NAME') ?: 'catabris';
$user = getenv('DB_USER') ?: 'catabris';
$pass = getenv('DB_PASS') ?: '';

try {
    $bdd = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $dbname), $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
    ]);
} catch (PDOException $e) {
    // In development, show the error; in production consider logging instead.
    die($e->getMessage());
}
?>
