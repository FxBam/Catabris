<?php
// Load environment variables (if present)
require_once __DIR__ . '/../load_env.php';

$host = getenv('DB_HOST') ?: 'mysql.infuseting.fr';
$dbname = getenv('DB_NAME') ?: 'catabris';
$user = getenv('DB_USER') ?: 'catabris';
$pass = getenv('DB_PASS') ?: '';

try {
    $bdd = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $host, $dbname), $user, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In development, show the error; in production consider logging instead.
    die($e->getMessage());
}
?>
